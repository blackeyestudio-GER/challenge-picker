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
     * @throws \Exception
     */
    public function createPlaythrough(
        User $user,
        int $gameId,
        int $rulesetId,
        int $maxConcurrentRules
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

        // Verify ruleset exists and belongs to the game
        $ruleset = $this->rulesetRepository->find($rulesetId);
        if (!$ruleset) {
            throw new \Exception('Ruleset not found');
        }

        if ($ruleset->getGame()->getId() !== $gameId) {
            throw new \Exception('Ruleset does not belong to the selected game');
        }

        // Create playthrough
        $playthrough = new Playthrough();
        $playthrough->setUser($user);
        $playthrough->setGame($game);
        $playthrough->setRuleset($ruleset);
        $playthrough->setMaxConcurrentRules($maxConcurrentRules);
        $playthrough->setStatus(Playthrough::STATUS_SETUP);

        // Create playthrough rules for all rules in the ruleset (all active by default)
        foreach ($ruleset->getRules() as $rule) {
            $playthroughRule = new PlaythroughRule();
            $playthroughRule->setPlaythrough($playthrough);
            $playthroughRule->setRule($rule);
            $playthroughRule->setIsActive(true);

            $playthrough->addPlaythroughRule($playthroughRule);
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
            if ($pr->getRule()->getId() === $ruleId) {
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

        // Calculate total duration if started
        if ($playthrough->getStartedAt() && $playthrough->getEndedAt()) {
            $duration = $playthrough->getEndedAt()->getTimestamp() - $playthrough->getStartedAt()->getTimestamp();
            $playthrough->setTotalDuration($duration);
        }

        $this->entityManager->flush();

        return $playthrough;
    }
}
