<?php

namespace App\Service;

use App\Entity\Playthrough;
use App\Entity\PlaythroughRule;
use App\Entity\PlaythroughRuleQueue;
use App\Entity\Rule;
use App\Repository\PlaythroughRuleQueueRepository;
use App\Repository\PlaythroughRuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class QueueService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PlaythroughRuleQueueRepository $queueRepository,
        private readonly PlaythroughRuleRepository $playthroughRuleRepository,
        private readonly \App\Repository\PlaythroughRepository $playthroughRepository,
    ) {
    }

    /**
     * Add a rule to the queue.
     *
     * @return array{success: bool, queueEntry: ?PlaythroughRuleQueue, position: int, eta: int, message: string}
     */
    public function addToQueue(
        Playthrough $playthrough,
        Rule $rule,
        int $difficultyLevel,
        ?Uuid $queuedByUserUuid = null
    ): array {
        // Get next position
        $position = $this->queueRepository->getNextPosition($playthrough);

        // Create queue entry
        $queueEntry = new PlaythroughRuleQueue();
        $queueEntry->setPlaythrough($playthrough);
        $queueEntry->setRule($rule);
        $queueEntry->setDifficultyLevel($difficultyLevel);
        $queueEntry->setPosition($position);
        $queueEntry->setQueuedByUserUuid($queuedByUserUuid);
        $queueEntry->setStatus(PlaythroughRuleQueue::STATUS_PENDING);

        $this->entityManager->persist($queueEntry);
        $this->entityManager->flush();

        // Calculate ETA (considering if this rule is already active)
        $eta = $this->calculateETA($playthrough, $position, $rule);

        return [
            'success' => true,
            'queueEntry' => $queueEntry,
            'position' => $position,
            'eta' => $eta,
            'message' => $eta > 0
                ? "Card queued! It will activate in approximately {$eta} seconds."
                : 'Card queued! It will activate shortly.',
        ];
    }

    /**
     * Calculate ETA (in seconds) for a specific queue position.
     *
     * This factors in:
     * - If the same rule is already active or recently completed, wait for it to expire + configurable cooldown period
     * - Rate limit (2 seconds between picks)
     * - Max concurrent rules (wait for slot to free)
     * - Time until current active rules expire
     *
     * @param Rule|null $queuedRule The rule being queued (to check if already active/recently completed)
     */
    public function calculateETA(Playthrough $playthrough, int $queuePosition, ?Rule $queuedRule = null): int
    {
        $now = new \DateTimeImmutable();
        $activeRules = $this->playthroughRuleRepository->findActiveByPlaythrough($playthrough);

        // PRIORITY 1: If this exact rule is already active or recently completed,
        // ETA = when it expires + cooldown period
        $cooldownSeconds = $playthrough->getRuleCooldownSeconds();

        if ($queuedRule !== null) {
            $queuedRuleId = $queuedRule->getId();

            // Check if rule is currently active
            foreach ($activeRules as $activeRule) {
                if ($activeRule->getRule()?->getId() === $queuedRuleId) {
                    // Same rule is active - calculate when it will expire
                    $expiresAt = $activeRule->getExpiresAt();
                    if ($expiresAt) {
                        $secondsUntilExpire = max(0, $expiresAt->getTimestamp() - $now->getTimestamp());

                        return $secondsUntilExpire + $cooldownSeconds; // Expiration time + cooldown period
                    }

                    // Counter rule without expiration - check if it's close to 0
                    $currentAmount = $activeRule->getCurrentAmount();
                    if ($currentAmount !== null && $currentAmount <= 3) {
                        // Nearly complete counter rule - estimate ~30s per remaining count
                        return ($currentAmount * 30) + $cooldownSeconds;
                    }

                    // Rule is active but no clear expiration - return large estimate
                    return 300; // 5 minutes estimate
                }
            }

            // Check if rule was recently completed (within cooldown period)
            foreach ($playthrough->getPlaythroughRules() as $pr) {
                if ($pr->getRule()?->getId() === $queuedRuleId && !$pr->isActive()) {
                    $completedAt = $pr->getCompletedAt();
                    if ($completedAt) {
                        $secondsSinceCompleted = $now->getTimestamp() - $completedAt->getTimestamp();
                        if ($secondsSinceCompleted < $cooldownSeconds) {
                            // Rule completed recently - ETA = remaining cooldown time
                            return $cooldownSeconds - $secondsSinceCompleted;
                        }
                    }
                }
            }
        }

        // Base ETA: queue position * rate limit (2 seconds per pick)
        $eta = ($queuePosition - 1) * 2;

        // Check if max concurrent rules is reached
        // Count only active timed/counter rules (exclude ALL permanent/legendary rules)
        $activeCountableCount = 0;
        $activeTimedRules = [];
        foreach ($activeRules as $pr) {
            $prRule = $pr->getRule();
            // Exclude permanent rules (legendary rarity) - they don't count toward limit
            if ($prRule && $prRule->getRuleType() !== 'legendary') {
                ++$activeCountableCount;
                // Track timed rules separately for ETA calculation
                if ($pr->getExpiresAt() !== null) {
                    $activeTimedRules[] = $pr;
                }
            }
        }

        $maxConcurrentRules = $playthrough->getMaxConcurrentRules();

        // PRIORITY 2: If slots are full, ETA = when next slot will be free
        if ($activeCountableCount >= $maxConcurrentRules && count($activeTimedRules) > 0) {
            $timeUntilSlotFree = $this->calculateTimeUntilSlotFree($activeTimedRules);
            $eta = max($eta, $timeUntilSlotFree);
        } elseif ($activeCountableCount >= $maxConcurrentRules) {
            // All slots full with counter/permanent rules - can't predict when slots will free
            $eta += 60; // Add 1 minute as a rough estimate
        }

        return max(0, $eta);
    }

    /**
     * Calculate time (in seconds) until a rule slot becomes free.
     *
     * @param PlaythroughRule[] $activeRules
     */
    private function calculateTimeUntilSlotFree(array $activeRules): int
    {
        $now = new \DateTimeImmutable();
        $minTimeToExpire = PHP_INT_MAX;

        foreach ($activeRules as $activeRule) {
            // Counter rules don't expire automatically (unless decremented to 0)
            // These can be identified by having a non-null currentAmount
            if ($activeRule->getCurrentAmount() !== null) {
                continue;
            }

            // Timed rules expire based on expiresAt
            $expiresAt = $activeRule->getExpiresAt();
            if ($expiresAt !== null) {
                $secondsUntilExpire = $expiresAt->getTimestamp() - $now->getTimestamp();

                if ($secondsUntilExpire > 0 && $secondsUntilExpire < $minTimeToExpire) {
                    $minTimeToExpire = $secondsUntilExpire;
                }
            }
        }

        return $minTimeToExpire === PHP_INT_MAX ? 0 : (int) $minTimeToExpire;
    }

    /**
     * Process the queue (activate next pending entry if conditions are met).
     * Skips rules on cooldown and tries the next available one.
     *
     * @return array{activated: bool, message: string, queueEntry: ?PlaythroughRuleQueue}
     */
    public function processQueue(Playthrough $playthrough): array
    {
        // Check if playthrough is active
        if ($playthrough->getStatus() !== Playthrough::STATUS_ACTIVE) {
            return [
                'activated' => false,
                'message' => 'Playthrough is not active',
                'queueEntry' => null,
            ];
        }

        // Check rate limit (2 seconds since last pick)
        $lastPickAt = $playthrough->getLastPickAt();
        if ($lastPickAt !== null) {
            $now = new \DateTimeImmutable();
            $secondsSinceLastPick = $now->getTimestamp() - $lastPickAt->getTimestamp();

            if ($secondsSinceLastPick < 2) {
                return [
                    'activated' => false,
                    'message' => 'Rate limit active',
                    'queueEntry' => null,
                ];
            }
        }

        // Check max concurrent rules
        // Count only active timed/counter rules (exclude ALL permanent/legendary rules)
        $activeRules = $this->playthroughRuleRepository->findActiveByPlaythrough($playthrough);
        $activeCountableCount = 0;
        foreach ($activeRules as $pr) {
            $prRule = $pr->getRule();
            // Exclude permanent rules (legendary rarity) - they don't count toward limit
            if ($prRule && $prRule->getRuleType() !== 'legendary') {
                ++$activeCountableCount;
            }
        }

        $maxConcurrentRules = $playthrough->getMaxConcurrentRules();

        if ($activeCountableCount >= $maxConcurrentRules) {
            return [
                'activated' => false,
                'message' => 'Max concurrent rules reached',
                'queueEntry' => null,
            ];
        }

        // Get all pending entries and try to activate the first non-cooldown rule
        $pendingEntries = $this->queueRepository->getPendingQueue($playthrough);

        if (empty($pendingEntries)) {
            return [
                'activated' => false,
                'message' => 'Queue is empty',
                'queueEntry' => null,
            ];
        }

        $cooldownRuleIds = $playthrough->getCooldownRuleIds() ?? [];

        // Get all currently active rule IDs (to prevent duplicates with different difficulty levels)
        $activeRuleIds = [];
        foreach ($activeRules as $activeRule) {
            $ruleId = $activeRule->getRule()?->getId();
            if ($ruleId !== null) {
                $activeRuleIds[] = $ruleId;
            }
        }

        // Try to find a rule that can be activated (or replace an existing one)
        foreach ($pendingEntries as $queueEntry) {
            $rule = $queueEntry->getRule();
            $ruleId = $rule ? $rule->getId() : null;

            if (!$ruleId) {
                continue;
            }

            // Check if this rule is already active (any difficulty level)
            $existingActiveRule = null;
            foreach ($activeRules as $activeRule) {
                if ($activeRule->getRule()?->getId() === $ruleId) {
                    $existingActiveRule = $activeRule;
                    break;
                }
            }

            if ($existingActiveRule) {
                // Rule is already active - can only replace if it has expired or completed
                // Check if expired (timed rule)
                $now = new \DateTimeImmutable();
                $expiresAt = $existingActiveRule->getExpiresAt();
                $isExpired = $expiresAt && $expiresAt < $now;

                // Check if completed (counter rule)
                $currentAmount = $existingActiveRule->getCurrentAmount();
                $isCompleted = $currentAmount !== null && $currentAmount <= 0;

                if (!$isExpired && !$isCompleted) {
                    // Still active, can't replace yet - skip to next queue entry
                    continue;
                }

                // Rule has expired/completed - deactivate it and activate the queued one
                $existingActiveRule->setIsActive(false);
                $existingActiveRule->setCompletedAt($now);
                $this->entityManager->flush();
            }

            // Check for recently completed instances of the same rule (configurable cooldown period)
            // This prevents the same rule from being stacked back-to-back
            $cooldownSeconds = $playthrough->getRuleCooldownSeconds();
            $recentlyCompleted = false;
            foreach ($playthrough->getPlaythroughRules() as $pr) {
                if ($pr->getRule()?->getId() === $ruleId && !$pr->isActive()) {
                    $completedAt = $pr->getCompletedAt();
                    if ($completedAt) {
                        $now = new \DateTimeImmutable();
                        $secondsSinceCompleted = $now->getTimestamp() - $completedAt->getTimestamp();
                        if ($secondsSinceCompleted < $cooldownSeconds) {
                            // Same rule completed less than cooldown period ago - skip it (keep in queue!)
                            $recentlyCompleted = true;
                            break;
                        }
                    }
                }
            }

            if ($recentlyCompleted) {
                // Rule was recently completed - wait for cooldown period (stays in queue)
                continue;
            }

            // Check if we have room (only if not replacing)
            if (!$existingActiveRule) {
                // Skip if on cooldown
                if (in_array($ruleId, $cooldownRuleIds, true)) {
                    continue;
                }

                // Check max concurrent rules (only count non-legendary)
                $activeCountableCount = 0;
                foreach ($activeRules as $pr) {
                    $prRule = $pr->getRule();
                    if ($prRule && $prRule->getRuleType() !== 'legendary' && $pr->isActive()) {
                        ++$activeCountableCount;
                    }
                }

                if ($activeCountableCount >= $maxConcurrentRules) {
                    // No room for new rules
                    continue;
                }
            }

            // Found an activatable rule (or replacement)! Activate it
            try {
                $this->activateQueueEntry($queueEntry);

                return [
                    'activated' => true,
                    'message' => $existingActiveRule ? 'Rule replaced successfully' : 'Rule activated successfully',
                    'queueEntry' => $queueEntry,
                ];
            } catch (\Exception $e) {
                // Failed to activate, try next
                continue;
            }
        }

        // All pending rules are on cooldown - clear cooldown and activate the first one
        if (!empty($pendingEntries)) {
            $playthrough->setCooldownRuleIds([]);
            $this->entityManager->flush();

            $firstEntry = $pendingEntries[0];
            $this->activateQueueEntry($firstEntry);

            return [
                'activated' => true,
                'message' => 'Cooldown cleared, rule activated',
                'queueEntry' => $firstEntry,
            ];
        }

        // Queue is truly empty
        return [
            'activated' => false,
            'message' => 'Queue is empty',
            'queueEntry' => null,
        ];
    }

    /**
     * Activate a queue entry (create PlaythroughRule).
     */
    private function activateQueueEntry(PlaythroughRuleQueue $queueEntry): void
    {
        // Get playthrough UUID directly from the database to avoid proxy conflicts
        $conn = $this->entityManager->getConnection();
        $queueId = $queueEntry->getId();

        $sql = 'SELECT BIN_TO_UUID(p.uuid) as uuid FROM playthrough_rule_queue q 
                INNER JOIN playthroughs p ON p.id = q.playthrough_id
                WHERE q.id = ?';

        $playthroughUuid = $conn->fetchOne($sql, [$queueId]);

        if (!$playthroughUuid) {
            throw new \Exception('Playthrough not found for queue entry');
        }

        // Fetch a fresh managed playthrough instance
        $playthrough = $this->playthroughRepository->findByUuid($playthroughUuid);

        if (!$playthrough) {
            throw new \Exception('Playthrough not found');
        }

        $rule = $queueEntry->getRule();
        $difficultyLevel = $queueEntry->getDifficultyLevel();

        if (!$rule) {
            throw new \Exception('Invalid queue entry: rule is null');
        }

        // Get the difficulty level details
        $difficultyLevelEntity = null;
        foreach ($rule->getDifficultyLevels() as $dl) {
            if ($dl->getDifficultyLevel() === $difficultyLevel) {
                $difficultyLevelEntity = $dl;
                break;
            }
        }

        if (!$difficultyLevelEntity) {
            throw new \Exception('Invalid difficulty level for this rule');
        }

        // Create and activate the playthrough rule
        $playthroughRule = new PlaythroughRule();
        $playthroughRule->setPlaythrough($playthrough);
        $playthroughRule->setRule($rule);
        $playthroughRule->setIsActive(true);
        $playthroughRule->setStartedAt(new \DateTimeImmutable());

        // Set duration/amount based on difficulty level
        if ($difficultyLevelEntity->getDurationSeconds()) {
            $expiresAt = new \DateTimeImmutable(sprintf('+%d seconds', $difficultyLevelEntity->getDurationSeconds()));
            $playthroughRule->setExpiresAt($expiresAt);
        }

        if ($difficultyLevelEntity->getAmount()) {
            $playthroughRule->setCurrentAmount($difficultyLevelEntity->getAmount());
        }

        $this->entityManager->persist($playthroughRule);

        // Update playthrough's last pick time
        $playthrough->setLastPickAt(new \DateTimeImmutable());

        // Add rule to cooldown list (keep last 5)
        $ruleId = $rule->getId();
        if ($ruleId !== null) {
            $playthrough->addCooldownRuleId($ruleId, 5);
        }

        // Mark queue entry as activated
        $queueEntry->setStatus(PlaythroughRuleQueue::STATUS_ACTIVATED);
        $queueEntry->setProcessedAt(new \DateTimeImmutable());

        $this->entityManager->flush();
    }

    /**
     * Get queue status for a playthrough.
     *
     * @return array{queueLength: int, pendingRules: array<int, array{ruleId: int, ruleName: string, position: int, eta: int}>}
     */
    public function getQueueStatus(Playthrough $playthrough): array
    {
        $pendingQueue = $this->queueRepository->getPendingQueue($playthrough);

        $pendingRules = [];
        foreach ($pendingQueue as $queueEntry) {
            $rule = $queueEntry->getRule();
            if (!$rule) {
                continue; // Skip queue entries with null rules
            }

            $ruleId = $rule->getId();
            $ruleName = $rule->getName();

            if ($ruleId === null || $ruleName === null) {
                continue; // Skip entries with incomplete data
            }

            $pendingRules[] = [
                'ruleId' => $ruleId,
                'ruleName' => $ruleName,
                'ruleType' => $rule->getRuleType(),
                'position' => $queueEntry->getPosition(),
                'eta' => $this->calculateETA($playthrough, $queueEntry->getPosition(), $rule),
            ];
        }

        return [
            'queueLength' => count($pendingQueue),
            'pendingRules' => $pendingRules,
        ];
    }
}
