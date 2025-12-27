<?php

namespace App\Service;

use App\Entity\Rule;

class RuleValidationService
{
    public function __construct(
        private readonly TarotCardService $tarotCardService
    ) {
    }

    /**
     * Validate rule difficulty levels from request data (before creating entities).
     * Returns error message string if validation fails, null if valid.
     *
     * @param array<array{difficultyLevel: int, durationMinutes: ?int}> $difficultyLevelsData
     */
    public function validateRuleDifficultyLevels(string $ruleType, array $difficultyLevelsData): ?string
    {
        if (!in_array($ruleType, ['basic', 'court', 'legendary'], true)) {
            return "Invalid rule type: {$ruleType}. Must be 'basic', 'court', or 'legendary'.";
        }

        if (empty($difficultyLevelsData)) {
            return 'At least one difficulty level is required';
        }

        // Extract difficulty level numbers and check durations
        $levels = [];
        foreach ($difficultyLevelsData as $levelData) {
            if (!isset($levelData['difficultyLevel'])) {
                return 'Each difficulty level must have a difficultyLevel field';
            }

            $levels[] = $levelData['difficultyLevel'];

            $hasDuration = isset($levelData['durationMinutes']) && $levelData['durationMinutes'] !== null;
            $hasAmount = isset($levelData['amount']) && $levelData['amount'] !== null;

            // Validate duration/amount combination
            if ($ruleType === 'legendary') {
                // Legendary rules: can have duration, amount, both, or neither (permanent)
                if ($hasDuration && $levelData['durationMinutes'] <= 0) {
                    return 'Duration must be greater than 0 minutes';
                }
                if ($hasAmount && $levelData['amount'] <= 0) {
                    return 'Amount must be greater than 0';
                }
            } else {
                // Basic/Court rules: must have duration OR amount (or both)
                if (!$hasDuration && !$hasAmount) {
                    return 'Basic/Court rules must have duration or amount (or both)';
                }
                if ($hasDuration && $levelData['durationMinutes'] <= 0) {
                    return 'Duration must be greater than 0 minutes';
                }
                if ($hasAmount && $levelData['amount'] <= 0) {
                    return 'Amount must be greater than 0';
                }
            }
        }

        sort($levels);
        $levelCount = count($levels);

        // Validate level structure based on rule type
        switch ($ruleType) {
            case 'basic':
                return $this->validateBasicLevels($levels, $levelCount);
            case 'court':
                return $this->validateCourtLevels($levels, $levelCount);
            case 'legendary':
                return $this->validateLegendaryLevels($levels, $levelCount);
        }

        return null;
    }

    /**
     * Validate rule difficulty levels based on rule type
     * Uses the tarot card system as the source of truth.
     *
     * @throws \InvalidArgumentException if validation fails
     */
    public function validateRule(Rule $rule): void
    {
        $ruleType = $rule->getRuleType();
        $difficultyLevels = $rule->getDifficultyLevels();
        $levelCount = $difficultyLevels->count();

        // Extract difficulty level numbers
        $levels = [];
        foreach ($difficultyLevels as $difficultyLevel) {
            $levels[] = $difficultyLevel->getDifficultyLevel();
        }
        sort($levels);

        switch ($ruleType) {
            case 'basic':
                $error = $this->validateBasicLevels($levels, $levelCount);
                if ($error) {
                    throw new \InvalidArgumentException($error);
                }
                break;
            case 'court':
                $error = $this->validateCourtLevels($levels, $levelCount);
                if ($error) {
                    throw new \InvalidArgumentException($error);
                }
                break;
            case 'legendary':
                $error = $this->validateLegendaryLevels($levels, $levelCount);
                if ($error) {
                    throw new \InvalidArgumentException($error);
                }
                break;
            default:
                throw new \InvalidArgumentException("Invalid rule type: {$ruleType}. Must be 'basic', 'court', or 'legendary'.");
        }
    }

    /**
     * Basic rules must have exactly 9 difficulty levels (1-9) for cards 2-10.
     *
     * @param array<int> $levels
     */
    private function validateBasicLevels(array $levels, int $count): ?string
    {
        if ($count !== 9) {
            return "Basic rules must have exactly 9 difficulty levels (for cards 2-10). Found: {$count}";
        }

        $expected = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        if ($levels !== $expected) {
            $missing = array_diff($expected, $levels);
            $extra = array_diff($levels, $expected);

            $errors = [];
            if (!empty($missing)) {
                $errors[] = 'Missing levels: ' . implode(', ', $missing);
            }
            if (!empty($extra)) {
                $errors[] = 'Invalid levels: ' . implode(', ', $extra);
            }

            return 'Basic rules must have difficulty levels 1-9 (no gaps). ' . implode('. ', $errors);
        }

        return null;
    }

    /**
     * Court rules must have exactly 4 difficulty levels (1-4) for Page/Knight/Queen/King.
     *
     * @param array<int> $levels
     */
    private function validateCourtLevels(array $levels, int $count): ?string
    {
        if ($count !== 4) {
            return "Court rules must have exactly 4 difficulty levels (for Page/Knight/Queen/King). Found: {$count}";
        }

        $expected = [1, 2, 3, 4];
        if ($levels !== $expected) {
            $missing = array_diff($expected, $levels);
            $extra = array_diff($levels, $expected);

            $errors = [];
            if (!empty($missing)) {
                $errors[] = 'Missing levels: ' . implode(', ', $missing);
            }
            if (!empty($extra)) {
                $errors[] = 'Invalid levels: ' . implode(', ', $extra);
            }

            return 'Court rules must have difficulty levels 1-4 (no gaps). ' . implode('. ', $errors);
        }

        return null;
    }

    /**
     * Legendary rules must have exactly 1 difficulty level (1) for a specific Major Arcana card.
     * Legendary rules are permanent (no time limit).
     *
     * @param array<int> $levels
     */
    private function validateLegendaryLevels(array $levels, int $count): ?string
    {
        if ($count !== 1) {
            return "Legendary rules must have exactly 1 difficulty level. Found: {$count}";
        }

        $level = $levels[0];
        if ($level !== 1) {
            return "Legendary rules must have difficulty level 1. Found: {$level}";
        }

        return null;
    }

    /**
     * Validate if a rule can be marked as default in a ruleset.
     * Only permanent legendary rules (no duration AND no amount) can be default.
     */
    public function canBeDefault(Rule $rule): bool
    {
        // Must be legendary
        if ($rule->getRuleType() !== 'legendary') {
            return false;
        }

        // Check all difficulty levels - if ANY has duration or amount, it's not permanent
        foreach ($rule->getDifficultyLevels() as $level) {
            if ($level->getDurationMinutes() !== null || $level->getAmount() !== null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a rule is permanent (has no duration AND no amount).
     */
    public function isPermanent(Rule $rule): bool
    {
        foreach ($rule->getDifficultyLevels() as $level) {
            if ($level->getDurationMinutes() !== null || $level->getAmount() !== null) {
                return false;
            }
        }

        return true;
    }
}
