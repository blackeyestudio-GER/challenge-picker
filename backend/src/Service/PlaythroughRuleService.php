<?php

namespace App\Service;

use App\Entity\Playthrough;
use App\Entity\PlaythroughRule;
use App\Entity\Rule;
use Doctrine\ORM\EntityManagerInterface;

class PlaythroughRuleService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Pick/activate a rule for a playthrough.
     *
     * @throws \Exception
     */
    public function pickRule(
        Playthrough $playthrough,
        Rule $rule,
        int $difficultyLevel
    ): PlaythroughRule {
        // Check if session is active
        if ($playthrough->getStatus() !== Playthrough::STATUS_ACTIVE) {
            throw new \Exception('Session must be active to pick rules');
        }

        // Check if rule is already active
        foreach ($playthrough->getPlaythroughRules() as $pr) {
            if ($pr->getRule()?->getId() === $rule->getId() && $pr->isActive()) {
                throw new \Exception('This rule is already active');
            }
        }

        // Check max concurrent rules limit (only count non-default optional rules)
        $activeOptionalCount = 0;
        foreach ($playthrough->getPlaythroughRules() as $pr) {
            // Count only active, non-permanent rules
            if ($pr->isActive() && $pr->getRule()?->getRuleType() !== 'legendary') {
                ++$activeOptionalCount;
            }
        }

        if ($activeOptionalCount >= $playthrough->getMaxConcurrentRules()) {
            throw new \Exception(sprintf('Maximum concurrent rules reached (%d/%d). Wait for a rule to expire or complete.', $activeOptionalCount, $playthrough->getMaxConcurrentRules()));
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
        $this->entityManager->flush();

        return $playthroughRule;
    }
}
