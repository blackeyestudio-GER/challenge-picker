<?php

namespace App\Controller\Api\Playthrough;

use App\Entity\PlaythroughRule;
use App\Repository\PlaythroughRepository;
use App\Repository\PlaythroughRuleRepository;
use App\Repository\RuleRepository;
use App\Service\QueueService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PickRuleController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly RuleRepository $ruleRepository,
        private readonly PlaythroughRuleRepository $playthroughRuleRepository,
        private readonly QueueService $queueService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/api/playthroughs/{uuid}/pick-rule', name: 'api_playthrough_pick_rule', methods: ['POST'])]
    public function __invoke(string $uuid, Request $request): JsonResponse
    {
        // Get authenticated user (optional - viewers can also pick if allowed)
        $user = $this->getUser();

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
        if ($playthrough->getStatus() !== 'active') {
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

        // Get rule ID from request
        $data = json_decode($request->getContent(), true);
        $ruleId = $data['ruleId'] ?? null;
        $difficultyLevel = $data['difficultyLevel'] ?? null;

        if (!$ruleId || !$difficultyLevel) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_REQUEST',
                    'message' => 'ruleId and difficultyLevel are required',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Verify rule exists
        $rule = $this->ruleRepository->find($ruleId);
        if (!$rule) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'RULE_NOT_FOUND',
                    'message' => 'Rule not found',
                ],
            ], Response::HTTP_NOT_FOUND);
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

                    return $this->json([
                        'success' => true,
                        'data' => [
                            'ruleId' => $rule->getId(),
                            'ruleName' => $rule->getName(),
                            'activated' => true,
                            'message' => 'Permanent rule activated immediately',
                        ],
                    ], Response::HTTP_OK);
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

            return $this->json([
                'success' => true,
                'data' => [
                    'ruleId' => $rule->getId(),
                    'ruleName' => $rule->getName(),
                    'position' => $result['position'],
                    'eta' => $result['eta'],
                    'message' => $result['message'],
                ],
            ], Response::HTTP_OK);
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
