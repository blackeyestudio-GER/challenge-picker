<?php

namespace App\Controller\Api\Playthrough;

use App\DTO\Request\Playthrough\PickRuleRequest;
use App\DTO\Response\Playthrough\PickRuleResponse;
use App\Entity\Playthrough;
use App\Entity\PlaythroughRule;
use App\Entity\User;
use App\Repository\PlaythroughRepository;
use App\Repository\PlaythroughRuleRepository;
use App\Repository\RuleRepository;
use App\Service\QueueService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PickRuleController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly RuleRepository $ruleRepository,
        private readonly PlaythroughRuleRepository $playthroughRuleRepository,
        private readonly QueueService $queueService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[Route('/api/playthroughs/{uuid}/pick-rule', name: 'api_playthrough_pick_rule', methods: ['POST'])]
    public function __invoke(string $uuid, PickRuleRequest $request): JsonResponse
    {
        // Get authenticated user (optional - viewers can also pick if allowed)
        $user = $this->getUser();
        if (!$user instanceof User) {
            $user = null;
        }

        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => implode(', ', $errorMessages),
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Get playthrough
        $playthrough = $this->playthroughRepository->findByUuid($uuid);
        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAYTHROUGH_NOT_FOUND',
                    'message' => 'Playthrough not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Check if session is active
        if ($playthrough->getStatus() !== Playthrough::STATUS_ACTIVE) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'SESSION_NOT_ACTIVE',
                    'message' => 'Session must be active to pick rules',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check permissions
        $isHost = $user && $playthrough->getUser()->getUuid()->equals($user->getUuid());
        if (!$isHost && !$playthrough->isAllowViewerPicks()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'VIEWER_PICKS_DISABLED',
                    'message' => 'Viewers are not allowed to pick rules for this session',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        // Verify rule exists
        $rule = $this->ruleRepository->find($request->ruleId);
        if (!$rule) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'RULE_NOT_FOUND',
                    'message' => 'Rule not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        $ruleId = $rule->getId();
        $ruleName = $rule->getName();
        if ($ruleId === null || $ruleName === null) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'RULE_INVALID',
                    'message' => 'Rule is missing required data',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $difficultyLevel = $request->difficultyLevel;
        if ($difficultyLevel === null) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'difficultyLevel is required',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Check if this is a permanent/legendary rule
            $isPermanent = $rule->getRuleType() === 'legendary';

            if ($isPermanent) {
                // Check if this permanent rule is already active
                $activeRules = $this->playthroughRuleRepository->findActiveByPlaythrough($playthrough);
                $isAlreadyActive = false;
                foreach ($activeRules as $activeRule) {
                    if ($activeRule->getRule() && $activeRule->getRule()->getId() === $rule->getId()) {
                        $isAlreadyActive = true;
                        break;
                    }
                }

                if (!$isAlreadyActive) {
                    // Permanent rules bypass the queue and activate immediately (only if not already active)
                    $playthroughRule = new PlaythroughRule();
                    $playthroughRule->setPlaythrough($playthrough);
                    $playthroughRule->setRule($rule);
                    $playthroughRule->setIsActive(true);
                    $playthroughRule->setStartedAt(new \DateTimeImmutable());

                    $this->entityManager->persist($playthroughRule);
                    $this->entityManager->flush();

                    return $this->json(
                        PickRuleResponse::activated(
                            $ruleId,
                            $ruleName,
                            'Permanent rule activated immediately'
                        ),
                        Response::HTTP_OK
                    );
                }
                // If already active, fall through to queue it (will be skipped by queue processor)
                // This handles race conditions gracefully
            }

            // Get user UUID if logged in (for queue tracking)
            $queuedByUserUuid = $user ? $user->getUuid() : null;

            // Add to queue (always succeeds, even if rule is already active)
            $result = $this->queueService->addToQueue(
                $playthrough,
                $rule,
                $difficultyLevel,
                $queuedByUserUuid
            );

            return $this->json(
                PickRuleResponse::queued(
                    $ruleId,
                    $ruleName,
                    $result['position'],
                    $result['eta'],
                    $result['message']
                ),
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'QUEUE_ERROR',
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
