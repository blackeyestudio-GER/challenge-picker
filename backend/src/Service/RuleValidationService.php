<?php

namespace App\Service;

use App\Entity\Rule;

class RuleValidationService
{
    public function __construct(
        private readonly TarotCardService $tarotCardService
    ) {}

    /**
     * Validate rule difficulty levels based on rule type
     * Uses the tarot card system as the source of truth
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
                $this->validateBasicRule($levels, $levelCount);
                break;
            case 'court':
                $this->validateCourtRule($levels, $levelCount);
                break;
            case 'legendary':
                $this->validateLegendaryRule($levels, $levelCount);
                break;
            default:
                throw new \InvalidArgumentException("Invalid rule type: {$ruleType}. Must be 'basic', 'court', or 'legendary'.");
        }
    }
    
    /**
     * Basic rules must have exactly 9 difficulty levels (1-9) for cards 2-10
     */
    private function validateBasicRule(array $levels, int $count): void
    {
        if ($count !== 9) {
            throw new \InvalidArgumentException("Basic rules must have exactly 9 difficulty levels (for cards 2-10). Found: {$count}");
        }
        
        $expected = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        if ($levels !== $expected) {
            $missing = array_diff($expected, $levels);
            $extra = array_diff($levels, $expected);
            
            $errors = [];
            if (!empty($missing)) {
                $errors[] = "Missing levels: " . implode(', ', $missing);
            }
            if (!empty($extra)) {
                $errors[] = "Invalid levels: " . implode(', ', $extra);
            }
            
            throw new \InvalidArgumentException(
                "Basic rules must have difficulty levels 1-9 (no gaps). " . implode('. ', $errors)
            );
        }
    }
    
    /**
     * Court rules must have exactly 4 difficulty levels (1-4) for Page/Knight/Queen/King
     */
    private function validateCourtRule(array $levels, int $count): void
    {
        if ($count !== 4) {
            throw new \InvalidArgumentException("Court rules must have exactly 4 difficulty levels (for Page/Knight/Queen/King). Found: {$count}");
        }
        
        $expected = [1, 2, 3, 4];
        if ($levels !== $expected) {
            $missing = array_diff($expected, $levels);
            $extra = array_diff($levels, $expected);
            
            $errors = [];
            if (!empty($missing)) {
                $errors[] = "Missing levels: " . implode(', ', $missing);
            }
            if (!empty($extra)) {
                $errors[] = "Invalid levels: " . implode(', ', $extra);
            }
            
            throw new \InvalidArgumentException(
                "Court rules must have difficulty levels 1-4 (no gaps). " . implode('. ', $errors)
            );
        }
    }
    
    /**
     * Legendary rules must have exactly 1 difficulty level (1) for a specific Major Arcana card
     */
    private function validateLegendaryRule(array $levels, int $count): void
    {
        if ($count !== 1) {
            throw new \InvalidArgumentException("Legendary rules must have exactly 1 difficulty level. Found: {$count}");
        }
        
        if ($levels[0] !== 1) {
            throw new \InvalidArgumentException("Legendary rules must have difficulty level 1. Found: {$levels[0]}");
        }
    }
}

