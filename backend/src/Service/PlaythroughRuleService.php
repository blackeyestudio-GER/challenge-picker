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

        // Rate limiting: Check if enough time has passed since last pick (2 seconds)
        $lastPickAt = $playthrough->getLastPickAt();
        if ($lastPickAt !== null) {
            $now = new \DateTimeImmutable();
            $secondsSinceLastPick = $now->getTimestamp() - $lastPickAt->getTimestamp();
            if ($secondsSinceLastPick < 2) {
                $remainingSeconds = 2 - $secondsSinceLastPick;

                throw new \Exception(sprintf('Please wait %d second%s before picking another card', $remainingSeconds, $remainingSeconds > 1 ? 's' : ''));
            }
        }

        // Cooldown check: Verify rule is not in cooldown list
        $cooldownRuleIds = $playthrough->getCooldownRuleIds() ?? [];
        $ruleId = $rule->getId();
        assert($ruleId !== null);

        // If rule is on cooldown, check if we can use fallback (all rules on cooldown)
        $allRulesOnCooldown = false;
        if (in_array($ruleId, $cooldownRuleIds, true)) {
            // Get all non-default rules from configuration
            $configuration = $playthrough->getConfiguration();
            $allNonDefaultRuleIds = [];
            if (isset($configuration['rules']) && is_array($configuration['rules'])) {
                foreach ($configuration['rules'] as $ruleConfig) {
                    if (
                        is_array($ruleConfig)
                        && isset($ruleConfig['ruleId'])
                        && (!isset($ruleConfig['isDefault']) || $ruleConfig['isDefault'] !== true)
                    ) {
                        $allNonDefaultRuleIds[] = $ruleConfig['ruleId'];
                    }
                }
            }

            // Check if ALL non-default rules are on cooldown (fallback scenario)
            $allNonDefaultRuleIds = array_unique($allNonDefaultRuleIds);
            $allRulesOnCooldown = count($allNonDefaultRuleIds) > 0
                && count(array_diff($allNonDefaultRuleIds, $cooldownRuleIds)) === 0;

            if (!$allRulesOnCooldown) {
                // Not all rules are on cooldown, so this specific rule cannot be picked
                $indexInCooldown = array_search($ruleId, $cooldownRuleIds, true);
                if ($indexInCooldown !== false) {
                    assert(is_int($indexInCooldown));
                    $remainingPicks = 5 - count($cooldownRuleIds) + $indexInCooldown + 1;
                } else {
                    $remainingPicks = 5 - count($cooldownRuleIds);
                }

                throw new \Exception(sprintf('This rule was recently picked. Pick %d more card%s to make it available again.', $remainingPicks, $remainingPicks > 1 ? 's' : ''));
            }
            // If all rules are on cooldown, allow picking (fallback to full pool)
        }

        // Check max concurrent rules limit (only count non-default optional rules)
        // Get configuration to check which rules are default
        $configuration = $playthrough->getConfiguration();
        $defaultRuleIds = [];
        if (isset($configuration['rules']) && is_array($configuration['rules'])) {
            foreach ($configuration['rules'] as $ruleConfig) {
                if (
                    is_array($ruleConfig)
                    && isset($ruleConfig['ruleId'])
                    && isset($ruleConfig['isDefault'])
                    && $ruleConfig['isDefault'] === true
                ) {
                    $defaultRuleIds[] = $ruleConfig['ruleId'];
                }
            }
        }

        // Count only active, non-default rules
        $activeOptionalCount = 0;
        foreach ($playthrough->getPlaythroughRules() as $pr) {
            $prRule = $pr->getRule();
            if ($prRule && $pr->isActive() && !in_array($prRule->getId(), $defaultRuleIds, true)) {
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

        // Update playthrough state: rate limit and cooldown tracking
        $playthrough->setLastPickAt(new \DateTimeImmutable());
        $playthrough->addCooldownRuleId($ruleId, 5); // Keep last 5 picked rule IDs

        $this->entityManager->flush();

        return $playthroughRule;
    }
}
