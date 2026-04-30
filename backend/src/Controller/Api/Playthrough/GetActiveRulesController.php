<?php

namespace App\Controller\Api\Playthrough;

use App\DTO\Response\Playthrough\ActiveRuleResponseItem;
use App\DTO\Response\Playthrough\ActiveRulesResponse;
use App\Repository\PlaythroughRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/playthrough/active-rules', name: 'api_playthrough_active_rules', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
class GetActiveRulesController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user instanceof \App\Entity\User) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => 'Authentication required',
                    ],
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Find user's active or paused playthrough
            $playthrough = $this->playthroughRepository->findActiveByUser($user);

            if (!$playthrough) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'NO_ACTIVE_PLAYTHROUGH',
                        'message' => 'No active playthrough found. Start a playthrough first.',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            $activeRules = [];
            foreach ($playthrough->getPlaythroughRules() as $playthroughRule) {
                if (!$playthroughRule->isActive()) {
                    continue;
                }

                $rule = $playthroughRule->getRule();
                if (!$rule) {
                    continue;
                }

                $difficultyLevel = $rule->getDifficultyLevels()->first();

                // Determine rule behavior type
                $hasDuration = $difficultyLevel && $difficultyLevel->getDurationSeconds() !== null;
                $hasAmount = $difficultyLevel && $difficultyLevel->getAmount() !== null;

                $type = 'permanent';
                if ($hasDuration && $hasAmount) {
                    $type = 'hybrid';
                } elseif ($hasDuration) {
                    $type = 'time';
                } elseif ($hasAmount) {
                    $type = 'counter';
                }

                // Calculate time remaining (if time-based)
                $timeRemaining = null;
                if ($playthroughRule->getExpiresAt()) {
                    $now = new \DateTimeImmutable();
                    $diff = $now->diff($playthroughRule->getExpiresAt());
                    $timeRemaining = ($diff->days * 86400) + ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
                    if ($diff->invert) {
                        $timeRemaining = 0; // Already expired
                        // Deactivate expired rule
                        $playthroughRule->setIsActive(false);
                        $this->entityManager->persist($playthroughRule);
                        continue; // Skip adding this rule to active rules
                    }
                }

                $playthroughRuleId = $playthroughRule->getId();
                if ($playthroughRuleId === null) {
                    continue;
                }

                $activeRules[] = new ActiveRuleResponseItem(
                    id: $playthroughRuleId,
                    ruleId: $rule->getId(),
                    ruleName: $rule->getName(),
                    description: $rule->getDescription(),
                    ruleType: $rule->getRuleType(),
                    type: $type,
                    currentAmount: $playthroughRule->getCurrentAmount(),
                    initialAmount: $difficultyLevel ? $difficultyLevel->getAmount() : null,
                    durationSeconds: $difficultyLevel ? $difficultyLevel->getDurationSeconds() : null,
                    expiresAt: $playthroughRule->getExpiresAt()?->format('c'),
                    timeRemaining: $timeRemaining,
                    startedAt: $playthroughRule->getStartedAt()?->format('c')
                );
            }

            // Flush any deactivated rules
            $this->entityManager->flush();

            return $this->json(
                ActiveRulesResponse::fromValues($playthrough->getId(), $playthrough->getStatus(), $activeRules),
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            error_log('Failed to get active rules: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FETCH_FAILED',
                    'message' => 'Failed to fetch active rules',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
