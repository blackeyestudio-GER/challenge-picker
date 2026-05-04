<?php

namespace App\Service;

use App\Entity\Playthrough;
use App\Entity\PlaythroughRule;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Repository\PlaythroughRepository;
use App\Repository\RulesetRepository;
use Doctrine\ORM\EntityManagerInterface;

class PlaythroughService
{
    public const SHORT_RUN_THRESHOLD_SECONDS = 180;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly GameRepository $gameRepository,
        private readonly RulesetRepository $rulesetRepository
    ) {
    }

    /**
     * Create a new playthrough session.
     *
     * @param array<string, mixed>|null $configuration Optional JSON configuration snapshot
     *
     * @throws \Exception
     */
    public function createPlaythrough(
        User $user,
        int $gameId,
        int $rulesetId,
        int $maxConcurrentRules,
        bool $requireAuth = false,
        bool $allowViewerPicks = false,
        ?array $configuration = null
    ): Playthrough {
        // Check if user already has an active playthrough (setup, active, or paused)
        $existingActive = $this->playthroughRepository->findActiveByUser($user);
        if ($existingActive) {
            $status = $existingActive->getStatus();
            $uuid = $existingActive->getUuid()->toRfc4122();

            throw new \Exception("You already have a playthrough in '{$status}' status. Please end it before starting a new one. (UUID: {$uuid})");
        }

        // Verify game exists
        $game = $this->gameRepository->find($gameId);
        if (!$game) {
            throw new \Exception('Game not found');
        }

        // Verify ruleset exists and is available for the game
        // This includes both game-specific and category-based rulesets
        $ruleset = $this->rulesetRepository->find($rulesetId);
        if (!$ruleset) {
            throw new \Exception('Ruleset not found');
        }

        // Check if the ruleset is available for this game (either directly assigned or via category)
        $availableRulesets = $this->rulesetRepository->findByGameWithMetadata($gameId);
        $rulesetAvailable = false;
        foreach ($availableRulesets as $item) {
            if ($item['ruleset']->getId() === $rulesetId) {
                $rulesetAvailable = true;
                break;
            }
        }

        if (!$rulesetAvailable) {
            throw new \Exception('Ruleset is not available for the selected game');
        }

        // Get next ID for this user (user-scoped sequence)
        $nextId = $this->getNextPlaythroughIdForUser($user);

        // Create playthrough in SETUP status (not auto-started)
        $playthrough = new Playthrough();
        $playthrough->setId($nextId);
        $playthrough->setUser($user);
        $playthrough->setGame($game);
        $playthrough->setRuleset($ruleset);
        $playthrough->setMaxConcurrentRules($maxConcurrentRules);
        $playthrough->setRequireAuth($requireAuth);
        $playthrough->setAllowViewerPicks($allowViewerPicks);
        $playthrough->setStatus(Playthrough::STATUS_SETUP);
        // startedAt is null until the user actually starts the playthrough

        // Build configuration snapshot (always required, revision-safe)
        // If not provided, build it from the ruleset
        if ($configuration === null) {
            $configuration = [
                'version' => '1.0',
                'rulesetId' => $rulesetId,
                'rulesetName' => $ruleset->getName(),
                'maxConcurrentRules' => $maxConcurrentRules,
                'rules' => [],
            ];

            // Build rules array from ruleset
            foreach ($ruleset->getRulesetRuleCards() as $rulesetRuleCard) {
                $rule = $rulesetRuleCard->getRule();
                $tarotCard = $rulesetRuleCard->getTarotCard();
                if ($rule === null) {
                    continue;
                }

                // Get all difficulty levels for this rule
                foreach ($rule->getDifficultyLevels() as $difficultyLevel) {
                    $configuration['rules'][] = [
                        'id' => $rule->getId(),
                        'ruleId' => $rule->getId(),
                        'ruleName' => $rule->getName(),
                        'ruleDescription' => $rule->getDescription(),
                        'ruleType' => $rule->getRuleType(),
                        'difficultyLevel' => $difficultyLevel->getDifficultyLevel(),
                        'durationSeconds' => $difficultyLevel->getDurationSeconds(),
                        'amount' => $difficultyLevel->getAmount(),
                        'tarotCardIdentifier' => $tarotCard?->getIdentifier(),
                        'iconIdentifier' => $rule->getIconIdentifier(),
                        // Icon styling (color, brightness, opacity) is now in DesignSet, not Rule
                        'isDefault' => $rulesetRuleCard->isDefault(),
                        'isEnabled' => true, // All rules enabled by default
                    ];
                }
            }
        } else {
            // Configuration provided from frontend - normalize it to flat structure
            if (isset($configuration['rules']) && is_array($configuration['rules'])) {
                $flatRules = [];
                /** @var array<string, mixed> $ruleConfig */
                foreach ($configuration['rules'] as $ruleConfig) {
                    // Check if this is nested format (has difficultyLevels array)
                    $difficultyLevels = ArrayTypeHelper::tryGetArray($ruleConfig, 'difficultyLevels');
                    if ($difficultyLevels !== null) {
                        // Flatten: create one entry per difficulty level
                        /** @var array<string, mixed> $levelConfig */
                        foreach ($difficultyLevels as $levelConfig) {
                            $ruleId = ArrayTypeHelper::tryGetInt($ruleConfig, 'id');
                            $flatRules[] = [
                                'id' => $ruleId,
                                'ruleId' => $ruleId,
                                'ruleName' => ArrayTypeHelper::tryGetString($ruleConfig, 'name') ?? 'Unknown Rule',
                                'ruleDescription' => ArrayTypeHelper::tryGetString($ruleConfig, 'description'),
                                'ruleType' => ArrayTypeHelper::tryGetString($ruleConfig, 'ruleType') ?? 'basic',
                                'difficultyLevel' => ArrayTypeHelper::tryGetInt($levelConfig, 'difficultyLevel') ?? 1,
                                'durationSeconds' => ArrayTypeHelper::tryGetInt($levelConfig, 'durationSeconds'),
                                'amount' => ArrayTypeHelper::tryGetInt($levelConfig, 'amount'),
                                'tarotCardIdentifier' => ArrayTypeHelper::tryGetString($levelConfig, 'tarotCardIdentifier'),
                                'iconIdentifier' => ArrayTypeHelper::tryGetString($ruleConfig, 'iconIdentifier'),
                                // Icon styling (color, brightness, opacity) is now in DesignSet, not Rule
                                'isDefault' => ArrayTypeHelper::tryGetBool($ruleConfig, 'isDefault') ?? false,
                                'isEnabled' => ArrayTypeHelper::tryGetBool($levelConfig, 'enabled') ?? true,
                            ];
                        }
                    } else {
                        // Already flat format, keep as is
                        $flatRules[] = $ruleConfig;
                    }
                }
                $configuration['rules'] = $flatRules;
            }
        }

        // Ensure configuration has required fields
        $config = array_merge([
            'version' => '1.0',
            'createdAt' => (new \DateTimeImmutable())->format('c'),
            'rulesetId' => $rulesetId,
            'rulesetName' => $ruleset->getName(),
            'maxConcurrentRules' => $maxConcurrentRules,
        ], $configuration);
        $playthrough->setConfiguration($config);

        // Persist and flush playthrough first to ensure ID is available for composite foreign keys
        $this->entityManager->persist($playthrough);
        $this->entityManager->flush();

        // Create playthrough rules based on configuration or default behavior
        $rulesConfig = null;
        if (isset($configuration['rules']) && is_array($configuration['rules'])) {
            $rulesConfig = [];
            /** @var array<string, mixed> $ruleConfig */
            foreach ($configuration['rules'] as $ruleConfig) {
                $ruleId = ArrayTypeHelper::tryGetInt($ruleConfig, 'id');
                if ($ruleId !== null) {
                    $rulesConfig[$ruleId] = $ruleConfig;
                }
            }
        }

        foreach ($ruleset->getRulesetRuleCards() as $rulesetRuleCard) {
            $rule = $rulesetRuleCard->getRule();
            if ($rule === null) {
                continue;
            }

            $ruleId = $rule->getId();
            assert($ruleId !== null);

            // Check if rule is enabled in configuration
            $isEnabled = true; // Default: all rules enabled
            $isDefault = $rulesetRuleCard->isDefault();

            if ($rulesConfig !== null && isset($rulesConfig[$ruleId])) {
                // Use configuration to determine if rule is enabled
                $ruleConfigItem = $rulesConfig[$ruleId];
                $isEnabled = ArrayTypeHelper::tryGetBool($ruleConfigItem, 'enabled') ?? true;
                // Override isDefault from config if present
                $isDefaultFromConfig = ArrayTypeHelper::tryGetBool($ruleConfigItem, 'isDefault');
                if ($isDefaultFromConfig !== null) {
                    $isDefault = $isDefaultFromConfig;
                }
            }

            // Only create playthrough rule if it's enabled
            if ($isEnabled) {
                $playthroughRule = new PlaythroughRule();
                $playthroughRule->setPlaythrough($playthrough);
                $playthroughRule->setRule($rule);
                // Only activate if it's a default/permanent rule
                // Optional rules stay inactive until picked
                $playthroughRule->setIsActive($isDefault);
                // Set startedAt for default rules immediately (they start active)
                if ($isDefault) {
                    $playthroughRule->setStartedAt(new \DateTimeImmutable());
                }

                $this->entityManager->persist($playthroughRule);
                $playthrough->addPlaythroughRule($playthroughRule);
            }
        }

        // Flush again to persist the playthrough rules
        $this->entityManager->flush();

        return $playthrough;
    }

    /**
     * Toggle a rule's active status in a playthrough.
     *
     * @throws \Exception
     */
    public function toggleRule(Playthrough $playthrough, int $ruleId): PlaythroughRule
    {
        // Only allow toggling in setup mode
        if ($playthrough->getStatus() !== Playthrough::STATUS_SETUP) {
            throw new \Exception('Rules can only be toggled during setup phase');
        }

        // Find the playthrough rule
        $playthroughRule = null;
        foreach ($playthrough->getPlaythroughRules() as $pr) {
            $rule = $pr->getRule();
            if ($rule !== null && $rule->getId() === $ruleId) {
                $playthroughRule = $pr;
                break;
            }
        }

        if (!$playthroughRule) {
            throw new \Exception('Rule not found in this playthrough');
        }

        // Toggle the active status
        $playthroughRule->setIsActive(!$playthroughRule->isActive());

        $this->entityManager->flush();

        return $playthroughRule;
    }

    /**
     * Update max concurrent rules for a playthrough.
     *
     * @throws \Exception
     */
    public function updateMaxConcurrent(Playthrough $playthrough, int $maxConcurrentRules): Playthrough
    {
        // Only allow updating in setup mode
        if ($playthrough->getStatus() !== Playthrough::STATUS_SETUP) {
            throw new \Exception('Max concurrent rules can only be updated during setup phase');
        }

        $playthrough->setMaxConcurrentRules($maxConcurrentRules);

        $this->entityManager->flush();

        return $playthrough;
    }

    /**
     * Start a playthrough session (transition from SETUP to ACTIVE).
     *
     * @throws \Exception
     */
    public function startPlaythrough(Playthrough $playthrough): Playthrough
    {
        if ($playthrough->getStatus() !== Playthrough::STATUS_SETUP) {
            throw new \Exception('Only playthroughs in setup can be started');
        }

        // Ensure all default rules are activated when starting
        $configuration = $playthrough->getConfiguration();
        if (isset($configuration['rules']) && is_array($configuration['rules'])) {
            foreach ($playthrough->getPlaythroughRules() as $playthroughRule) {
                $rule = $playthroughRule->getRule();
                if ($rule === null) {
                    continue;
                }

                // Check if this rule is marked as default in the configuration
                $isDefault = false;
                foreach ($configuration['rules'] as $ruleConfig) {
                    if (
                        is_array($ruleConfig)
                        && isset($ruleConfig['ruleId'])
                        && is_int($ruleConfig['ruleId'])
                        && $ruleConfig['ruleId'] === $rule->getId()
                    ) {
                        $isDefault = isset($ruleConfig['isDefault']) && $ruleConfig['isDefault'] === true;
                        break;
                    }
                }

                // Activate default rules and set startedAt
                if ($isDefault) {
                    $playthroughRule->setIsActive(true);
                    // Always set startedAt when starting playthrough (even if already active)
                    if (!$playthroughRule->getStartedAt()) {
                        $playthroughRule->setStartedAt(new \DateTimeImmutable());
                    }
                }
            }
        }

        $playthrough->setStatus(Playthrough::STATUS_ACTIVE);
        $playthrough->setStartedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $playthrough;
    }

    /**
     * Pause a playthrough session.
     *
     * @throws \Exception
     */
    public function pausePlaythrough(Playthrough $playthrough): Playthrough
    {
        // Can only pause an active session
        if ($playthrough->getStatus() !== Playthrough::STATUS_ACTIVE) {
            throw new \Exception('Only active sessions can be paused');
        }

        $playthrough->setStatus(Playthrough::STATUS_PAUSED);
        $playthrough->setPausedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $playthrough;
    }

    /**
     * Resume a paused playthrough session.
     *
     * @throws \Exception
     */
    public function resumePlaythrough(Playthrough $playthrough): Playthrough
    {
        // Can only resume a paused session
        if ($playthrough->getStatus() !== Playthrough::STATUS_PAUSED) {
            throw new \Exception('Only paused sessions can be resumed');
        }

        // Calculate paused duration and add to total
        if ($playthrough->getPausedAt()) {
            $pausedDuration = (new \DateTimeImmutable())->getTimestamp() - $playthrough->getPausedAt()->getTimestamp();
            $currentTotalPaused = $playthrough->getTotalPausedDuration() ?? 0;
            $playthrough->setTotalPausedDuration($currentTotalPaused + $pausedDuration);
            $playthrough->setPausedAt(null); // Clear paused timestamp
        }

        $playthrough->setStatus(Playthrough::STATUS_ACTIVE);

        $this->entityManager->flush();

        return $playthrough;
    }

    /**
     * End a playthrough session.
     *
     * @return array{playthrough: Playthrough|null, deleted: bool, uuid: string, message: string}
     *
     * @throws \Exception
     */
    public function endPlaythrough(Playthrough $playthrough): array
    {
        // Can only end active or paused sessions
        if (!in_array($playthrough->getStatus(), [Playthrough::STATUS_ACTIVE, Playthrough::STATUS_PAUSED])) {
            throw new \Exception('Only active or paused sessions can be ended');
        }

        $playthrough->setStatus(Playthrough::STATUS_COMPLETED);
        $playthrough->setEndedAt(new \DateTimeImmutable());

        // Calculate total active play time (excluding paused time)
        if ($playthrough->getStartedAt() && $playthrough->getEndedAt()) {
            $totalElapsed = $playthrough->getEndedAt()->getTimestamp() - $playthrough->getStartedAt()->getTimestamp();

            // Add any remaining paused time if still paused
            $currentPausedDuration = $playthrough->getTotalPausedDuration() ?? 0;
            if ($playthrough->getPausedAt()) {
                $remainingPaused = (new \DateTimeImmutable())->getTimestamp() - $playthrough->getPausedAt()->getTimestamp();
                $currentPausedDuration += $remainingPaused;
                $playthrough->setTotalPausedDuration($currentPausedDuration);
            }

            // Total duration = elapsed time - paused time
            $activeDuration = $totalElapsed - $currentPausedDuration;
            $playthrough->setTotalDuration(max(0, $activeDuration)); // Ensure non-negative
        }

        $duration = $playthrough->getTotalDuration() ?? 0;
        if ($duration < self::SHORT_RUN_THRESHOLD_SECONDS) {
            $owner = $playthrough->getUser();
            $uuid = $playthrough->getUuid()->toRfc4122();

            $this->deleteShortCompletedPlaythrough($playthrough, $owner);

            return [
                'playthrough' => null,
                'deleted' => true,
                'uuid' => $uuid,
                'message' => 'Short run discarded automatically because it was under 3 minutes.',
            ];
        }

        $this->entityManager->flush();

        return [
            'playthrough' => $playthrough,
            'deleted' => false,
            'uuid' => $playthrough->getUuid()->toRfc4122(),
            'message' => 'Playthrough ended successfully.',
        ];
    }

    /**
     * Delete a completed short playthrough owned by the user.
     *
     * @throws \Exception
     */
    public function deleteShortCompletedPlaythrough(Playthrough $playthrough, User $user): void
    {
        if (!$playthrough->getUser()->getUuid()->equals($user->getUuid())) {
            throw new \Exception('You do not have access to this playthrough');
        }

        if ($playthrough->getStatus() !== Playthrough::STATUS_COMPLETED) {
            throw new \Exception('Only completed playthroughs can be deleted');
        }

        $totalDuration = $playthrough->getTotalDuration();
        if ($totalDuration === null || $totalDuration >= self::SHORT_RUN_THRESHOLD_SECONDS) {
            throw new \Exception('Only completed playthroughs shorter than 3 minutes can be deleted');
        }

        $connection = $this->entityManager->getConnection();
        $userUuidBinary = $user->getUuid()->toBinary();
        $playthroughId = $playthrough->getId();

        // Clear challenge references where this run became the resulting playthrough.
        $connection->executeStatement(
            'UPDATE challenges
             SET resulting_playthrough_id = NULL, resulting_playthrough_user_uuid = NULL
             WHERE resulting_playthrough_id = :playthroughId
               AND resulting_playthrough_user_uuid = :userUuid',
            [
                'playthroughId' => $playthroughId,
                'userUuid' => $userUuidBinary,
            ]
        );

        // Delete challenges originated from this playthrough.
        $connection->executeStatement(
            'DELETE FROM challenges
             WHERE playthrough_id = :playthroughId
               AND playthrough_user_uuid = :userUuid',
            [
                'playthroughId' => $playthroughId,
                'userUuid' => $userUuidBinary,
            ]
        );

        // Delete queued rule entries for this playthrough.
        $connection->executeStatement(
            'DELETE FROM playthrough_rule_queue
             WHERE playthrough_id = :playthroughId',
            [
                'playthroughId' => $playthroughId,
            ]
        );

        $this->entityManager->remove($playthrough);
        $this->entityManager->flush();
    }

    /**
     * Get the next playthrough ID for a user (user-scoped sequence).
     */
    private function getNextPlaythroughIdForUser(User $user): int
    {
        // Get max ID for this user by joining on user table and comparing UUIDs
        /** @var int|string|null $maxId */
        $maxId = $this->playthroughRepository->createQueryBuilder('p')
            ->select('MAX(p.id)')
            ->leftJoin('p.user', 'u')
            ->where('u.uuid = :userUuid')
            ->setParameter('userUuid', $user->getUuid(), 'uuid')
            ->getQuery()
            ->getSingleScalarResult();

        $maxIdInt = is_numeric($maxId) ? (int) $maxId : 0;

        return $maxIdInt + 1;
    }
}
