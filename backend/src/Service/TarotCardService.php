<?php

namespace App\Service;

class TarotCardService
{
    private const SUITS = ['wands', 'cups', 'swords', 'pentacles'];
    private const COURT_CARDS = ['page', 'knight', 'queen', 'king'];
    private const BASIC_CARD_VALUES = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]; // Ace = 1, then 2-10

    /**
     * Derive tarot card identifier for a difficulty level based on rule type and position.
     * This ensures each difficulty level gets a unique card, cycling through suits to avoid repeats.
     *
     * @param string $ruleType 'basic', 'court', or 'legendary'
     * @param int $difficultyLevel The difficulty level (1-10 for basic, 1-4 for court, any for legendary)
     * @param string|null $baseCardIdentifier The base card assigned to the rule (used for legendary, or as starting point)
     * @param int $rulePosition The position of this rule in the ruleset (0-based, used for suit cycling)
     *
     * @return string Tarot card identifier
     */
    public function deriveCardIdentifierForDifficultyLevel(
        string $ruleType,
        int $difficultyLevel,
        ?string $baseCardIdentifier = null,
        int $rulePosition = 0
    ): string {
        return match ($ruleType) {
            'basic' => $this->deriveBasicCardIdentifier($difficultyLevel, $rulePosition),
            'court' => $this->deriveCourtCardIdentifier($difficultyLevel, $rulePosition),
            'legendary' => $this->deriveLegendaryCardIdentifier($baseCardIdentifier, $difficultyLevel, $rulePosition),
            default => throw new \InvalidArgumentException("Unknown rule type: {$ruleType}"),
        };
    }

    /**
     * Derive basic card identifier (Ace-10) based on difficulty level.
     * Cycles through suits based on rule position to avoid repeats.
     */
    private function deriveBasicCardIdentifier(int $difficultyLevel, int $rulePosition): string
    {
        // Map difficulty level (1-10) to card value (1-10, where 1 = Ace)
        $cardValue = $difficultyLevel;
        if ($cardValue < 1 || $cardValue > 10) {
            $cardValue = (($cardValue - 1) % 10) + 1; // Wrap around if needed
        }

        // Cycle through suits based on rule position to distribute cards evenly
        $suitIndex = $rulePosition % count(self::SUITS);
        $suit = self::SUITS[$suitIndex];

        // Format: "ace_of_wands" or "2_of_wands", etc.
        if ($cardValue === 1) {
            return "ace_of_{$suit}";
        }

        return "{$cardValue}_of_{$suit}";
    }

    /**
     * Derive court card identifier (Page, Knight, Queen, King) based on difficulty level.
     * Cycles through suits based on rule position to avoid repeats.
     */
    private function deriveCourtCardIdentifier(int $difficultyLevel, int $rulePosition): string
    {
        // Map difficulty level (1-4) to court card index (0-3)
        $courtIndex = (($difficultyLevel - 1) % count(self::COURT_CARDS));
        $courtCard = self::COURT_CARDS[$courtIndex];

        // Cycle through suits based on rule position
        $suitIndex = $rulePosition % count(self::SUITS);
        $suit = self::SUITS[$suitIndex];

        return "{$courtCard}_of_{$suit}";
    }

    /**
     * Derive legendary card identifier.
     * For legendary rules, we use the base card for all levels, but can cycle through Major Arcana
     * if multiple legendary rules exist in the same ruleset.
     */
    private function deriveLegendaryCardIdentifier(?string $baseCardIdentifier, int $difficultyLevel, int $rulePosition): string
    {
        // If we have a base card identifier, use it for all difficulty levels
        // (since legendary cards are unique and there are 22 of them)
        if ($baseCardIdentifier !== null) {
            return $baseCardIdentifier;
        }

        // Fallback: Use difficulty level to select from Major Arcana (0-21)
        // This shouldn't happen in practice, but provides a fallback
        $majorArcana = [
            'the_fool', 'the_magician', 'the_high_priestess', 'the_empress', 'the_emperor',
            'the_hierophant', 'the_lovers', 'the_chariot', 'strength', 'the_hermit',
            'wheel_of_fortune', 'justice', 'the_hanged_man', 'death', 'temperance',
            'the_devil', 'the_tower', 'the_star', 'the_moon', 'the_sun',
            'judgement', 'the_world',
        ];

        $cardIndex = ($rulePosition * 10 + ($difficultyLevel - 1)) % count($majorArcana);

        return $majorArcana[$cardIndex];
    }
}
