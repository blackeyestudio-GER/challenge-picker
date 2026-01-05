<?php

namespace App\Controller\Api\Playthrough;

use App\DTO\Response\Play\PlayScreenResponse;
use App\Entity\Playthrough;
use App\Repository\PlaythroughRepository;
use App\Service\QueueService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/playthrough/{uuid}/dashboard', name: 'api_playthrough_dashboard_data', methods: ['GET'])]
class GetDashboardDataController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly QueueService $queueService
    ) {
    }

    public function __invoke(string $uuid): JsonResponse
    {
        // Get authenticated user (optional for public playthroughs)
        $currentUser = $this->getUser();

        // Find playthrough by UUID
        $playthrough = $this->playthroughRepository->findByUuidWithRelations($uuid);

        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAYTHROUGH_NOT_FOUND',
                    'message' => 'Playthrough session not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Check if playthrough requires authentication
        if ($playthrough->isRequireAuth() && !$currentUser) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'AUTH_REQUIRED',
                    'message' => 'This session requires you to be logged in to view',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Check if current user is the host
        $isHost = $currentUser instanceof \App\Entity\User
            && $playthrough->getUser()->getUuid()->equals($currentUser->getUuid());

        // Ensure default rules are activated (safety check for existing playthroughs)
        if ($playthrough->getStatus() === Playthrough::STATUS_ACTIVE || $playthrough->getStatus() === Playthrough::STATUS_PAUSED) {
            $this->ensureDefaultRulesActive($playthrough);
        }

        // Process queue (attempt to activate next pending rule)
        if ($playthrough->getStatus() === Playthrough::STATUS_ACTIVE) {
            $this->queueService->processQueue($playthrough);
        }

        // Get playthrough data
        $playthroughData = PlayScreenResponse::fromPlaythrough($playthrough);

        // Get active rules with real-time countdowns
        $activeRulesData = $this->getActiveRules($playthrough);

        // Get pick status (only for host and active sessions)
        $pickStatusData = null;
        if ($isHost && $playthrough->getStatus() === Playthrough::STATUS_ACTIVE) {
            $pickStatusData = $this->getPickStatus($playthrough);
        }

        // Get queue status
        $queueStatusData = $this->queueService->getQueueStatus($playthrough);

        return $this->json([
            'success' => true,
            'data' => [
                'playthrough' => $playthroughData->data,
                'activeRules' => $activeRulesData,
                'pickStatus' => $pickStatusData,
                'queueStatus' => $queueStatusData,
                'isHost' => $isHost,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Get active rules with real-time countdowns.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getActiveRules(Playthrough $playthrough): array
    {
        $now = new \DateTimeImmutable();
        $activeRules = [];

        // Auto-deactivate expired timed rules and track duplicates
        $hasChanges = false;
        $seenRuleIds = [];

        foreach ($playthrough->getPlaythroughRules() as $playthroughRule) {
            if (!$playthroughRule->isActive()) {
                continue;
            }

            $rule = $playthroughRule->getRule();
            if (!$rule) {
                continue;
            }

            // Check for duplicate rules (any type, any difficulty level) and deactivate extras
            $ruleId = $rule->getId();
            if ($ruleId !== null) {
                if (isset($seenRuleIds[$ruleId])) {
                    // Duplicate rule (same base rule, different difficulty) - deactivate it
                    $playthroughRule->setIsActive(false);
                    $playthroughRule->setCompletedAt($now);
                    $hasChanges = true;
                    continue; // Skip this duplicate
                }
                $seenRuleIds[$ruleId] = true;
            }

            // Check if timed rule has expired
            $expiresAt = $playthroughRule->getExpiresAt();
            if ($expiresAt && $expiresAt < $now) {
                $playthroughRule->setIsActive(false);
                $playthroughRule->setCompletedAt($now);
                $hasChanges = true;
                continue; // Don't include expired rules
            }

            // Check if counter rule has reached 0
            $currentAmount = $playthroughRule->getCurrentAmount();
            if ($currentAmount !== null && $currentAmount <= 0) {
                $playthroughRule->setIsActive(false);
                $playthroughRule->setCompletedAt($now);
                $hasChanges = true;
                continue; // Don't include completed counter rules
            }

            // Determine rule type
            $type = 'permanent';
            if ($playthroughRule->getExpiresAt() !== null && $playthroughRule->getCurrentAmount() !== null) {
                $type = 'hybrid';
            } elseif ($playthroughRule->getExpiresAt() !== null) {
                $type = 'time';
            } elseif ($playthroughRule->getCurrentAmount() !== null) {
                $type = 'counter';
            }

            // Calculate time remaining (in seconds)
            $timeRemaining = null;
            if ($expiresAt && $playthrough->getStatus() === Playthrough::STATUS_ACTIVE) {
                $timeRemaining = max(0, $expiresAt->getTimestamp() - $now->getTimestamp());
            } elseif ($expiresAt && $playthrough->getStatus() === Playthrough::STATUS_PAUSED) {
                // If paused, keep original duration (don't count down)
                $startedAt = $playthroughRule->getStartedAt();
                if ($startedAt) {
                    $pausedAt = $playthrough->getPausedAt();
                    if ($pausedAt) {
                        $timeRemaining = max(0, $expiresAt->getTimestamp() - $pausedAt->getTimestamp());
                    }
                }
            }

            $activeRules[] = [
                'id' => $playthroughRule->getId(),
                'ruleId' => $rule->getId(),
                'ruleName' => $rule->getName(),
                'ruleType' => $rule->getRuleType(),
                'type' => $type,
                'currentAmount' => $playthroughRule->getCurrentAmount(),
                'initialAmount' => null, // Initial amount not stored, only current
                'durationSeconds' => $playthroughRule->getExpiresAt()
                    ? ($playthroughRule->getExpiresAt()->getTimestamp() - ($playthroughRule->getStartedAt()?->getTimestamp() ?? 0))
                    : null,
                'expiresAt' => $playthroughRule->getExpiresAt()?->format('c'),
                'timeRemaining' => $timeRemaining,
                'startedAt' => $playthroughRule->getStartedAt()?->format('c'),
            ];
        }

        // Persist changes if any rules were auto-deactivated or duplicates were removed
        if ($hasChanges) {
            $this->entityManager->flush();
        }

        return $activeRules;
    }

    /**
     * Get pick status for host.
     *
     * @return array<string, mixed>
     */
    private function getPickStatus(Playthrough $playthrough): array
    {
        // Calculate rate limit status
        $canPick = true;
        $rateLimitSeconds = null;
        $message = 'Ready to draw';

        $lastPickAt = $playthrough->getLastPickAt();
        if ($lastPickAt !== null) {
            $now = new \DateTimeImmutable();
            $secondsSinceLastPick = $now->getTimestamp() - $lastPickAt->getTimestamp();
            if ($secondsSinceLastPick < 2) {
                $canPick = false;
                $rateLimitSeconds = 2 - $secondsSinceLastPick;
                $message = sprintf('Wait %ds', $rateLimitSeconds);
            }
        }

        // Get cooldown info
        $cooldownRuleIds = $playthrough->getCooldownRuleIds() ?? [];

        // Get total available non-default rules count
        $configuration = $playthrough->getConfiguration();
        $availableRulesCount = 0;
        if (isset($configuration['rules']) && is_array($configuration['rules'])) {
            foreach ($configuration['rules'] as $ruleConfig) {
                if (
                    is_array($ruleConfig)
                    && isset($ruleConfig['ruleId'])
                    && (!isset($ruleConfig['isDefault']) || $ruleConfig['isDefault'] !== true)
                    && (!isset($ruleConfig['isEnabled']) || $ruleConfig['isEnabled'] === true)
                ) {
                    ++$availableRulesCount;
                }
            }
        }

        return [
            'canPick' => $canPick,
            'rateLimitSeconds' => $rateLimitSeconds,
            'cooldownRuleIds' => $cooldownRuleIds,
            'availableRulesCount' => $availableRulesCount,
            'message' => $message,
        ];
    }

    /**
     * Ensure default rules are activated (safety check for existing playthroughs).
     * Also creates missing PlaythroughRule entries if configuration has default rules but no entries exist.
     */
    private function ensureDefaultRulesActive(Playthrough $playthrough): void
    {
        $configuration = $playthrough->getConfiguration();
        if (!isset($configuration['rules']) || !is_array($configuration['rules'])) {
            return;
        }

        $hasChanges = false;

        // First, check if we have any PlaythroughRule entries at all
        $existingRules = [];
        foreach ($playthrough->getPlaythroughRules() as $pr) {
            $rule = $pr->getRule();
            if ($rule) {
                $existingRules[$rule->getId()] = $pr;
            }
        }

        // If no rules exist but configuration has default rules, create them
        if (count($existingRules) === 0) {
            foreach ($configuration['rules'] as $ruleConfig) {
                if (
                    is_array($ruleConfig)
                    && isset($ruleConfig['isDefault'])
                    && $ruleConfig['isDefault'] === true
                    && isset($ruleConfig['ruleId'])
                ) {
                    $ruleId = $ruleConfig['ruleId'];
                    $rule = $this->entityManager->getRepository(\App\Entity\Rule::class)->find($ruleId);

                    if ($rule) {
                        $playthroughRule = new \App\Entity\PlaythroughRule();
                        $playthroughRule->setPlaythrough($playthrough);
                        $playthroughRule->setRule($rule);
                        $playthroughRule->setIsActive(true);
                        $playthroughRule->setStartedAt(new \DateTimeImmutable());

                        $this->entityManager->persist($playthroughRule);
                        $hasChanges = true;
                    }
                }
            }
        } else {
            // Rules exist, just make sure default ones are active
            foreach ($existingRules as $ruleId => $playthroughRule) {
                // Check if this rule is marked as default in the configuration
                $isDefault = false;
                foreach ($configuration['rules'] as $ruleConfig) {
                    if (
                        is_array($ruleConfig)
                        && isset($ruleConfig['ruleId'])
                        && is_int($ruleConfig['ruleId'])
                        && $ruleConfig['ruleId'] === $ruleId
                    ) {
                        $isDefault = isset($ruleConfig['isDefault']) && $ruleConfig['isDefault'] === true;
                        break;
                    }
                }

                // Activate default rules if not already active
                if ($isDefault && !$playthroughRule->isActive()) {
                    $playthroughRule->setIsActive(true);
                    if (!$playthroughRule->getStartedAt()) {
                        $playthroughRule->setStartedAt(new \DateTimeImmutable());
                    }
                    $hasChanges = true;
                }
            }
        }

        if ($hasChanges) {
            $this->entityManager->flush();
        }
    }
}
