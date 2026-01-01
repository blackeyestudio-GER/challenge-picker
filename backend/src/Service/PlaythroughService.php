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
        ?array $configuration = null
    ): Playthrough {
        // Check if user already has an active playthrough
        $existingActive = $this->playthroughRepository->findActiveByUser($user);
        if ($existingActive) {
            throw new \Exception('You already have an active playthrough. Please end it before starting a new one.');
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

        // Create playthrough
        $playthrough = new Playthrough();
        $playthrough->setUser($user);
        $playthrough->setGame($game);
        $playthrough->setRuleset($ruleset);
        $playthrough->setMaxConcurrentRules($maxConcurrentRules);
        $playthrough->setStatus(Playthrough::STATUS_SETUP);

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

        // Create playthrough rules based on configuration or default behavior
        $rulesConfig = null;
        if (isset($configuration['rules']) && is_array($configuration['rules'])) {
            $rulesConfig = [];
            foreach ($configuration['rules'] as $ruleConfig) {
                if (isset($ruleConfig['id']) && is_int($ruleConfig['id'])) {
                    $rulesConfig[$ruleConfig['id']] = $ruleConfig;
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

            // Determine if rule should be active based on configuration
            $isActive = true; // Default: all rules active

            if ($rulesConfig !== null && isset($rulesConfig[$ruleId])) {
                // Use configuration to determine if rule is enabled
                // This respects user's choice even for default rules
                $ruleConfig = $rulesConfig[$ruleId];
                $isActive = isset($ruleConfig['enabled']) && $ruleConfig['enabled'] === true;
            } else {
                // Fallback: If no configuration, all rules are active by default
                $isActive = true;
            }

            // Only create playthrough rule if it's enabled
            if ($isActive) {
                $playthroughRule = new PlaythroughRule();
                $playthroughRule->setPlaythrough($playthrough);
                $playthroughRule->setRule($rule);
                $playthroughRule->setIsActive(true);

                $playthrough->addPlaythroughRule($playthroughRule);
            }
        }

        $this->entityManager->persist($playthrough);
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
     * Start a playthrough session.
     *
     * @throws \Exception
     */
    public function startPlaythrough(Playthrough $playthrough): Playthrough
    {
        // Can only start from setup phase
        if ($playthrough->getStatus() !== Playthrough::STATUS_SETUP) {
            throw new \Exception('Playthrough can only be started from setup phase');
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
     * @throws \Exception
     */
    public function endPlaythrough(Playthrough $playthrough): Playthrough
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

        $this->entityManager->flush();

        return $playthrough;
    }
}
