<?php

namespace App\Service;

use App\Entity\TarotCard;
use App\Repository\TarotCardRepository;

/**
 * Service to centralize all tarot card logic and mapping
 */
class TarotCardService
{
    public function __construct(
        private readonly TarotCardRepository $tarotCardRepository
    ) {}

    /**
     * Get all basic cards (number cards 2-10) for all suits
     * These map to difficulty levels 1-9
     * 
     * @return TarotCard[] Ordered by suit and card value
     */
    public function getBasicCards(): array
    {
        return $this->tarotCardRepository->findBy(
            ['rarity' => 'common'],
            ['suit' => 'ASC', 'cardValue' => 'ASC']
        );
    }

    /**
     * Get all court cards (Page/Knight/Queen/King) for all suits
     * These map to difficulty levels 1-4
     * 
     * @return TarotCard[] Ordered by suit and card value
     */
    public function getCourtCards(): array
    {
        return $this->tarotCardRepository->findBy(
            ['rarity' => 'rare'],
            ['suit' => 'ASC', 'cardValue' => 'ASC']
        );
    }

    /**
     * Get all legendary cards (Major Arcana)
     * Each is unique and maps to difficulty level 1
     * 
     * @return TarotCard[] Ordered by card value
     */
    public function getLegendaryCards(): array
    {
        return $this->tarotCardRepository->findBy(
            ['rarity' => 'legendary'],
            ['cardValue' => 'ASC']
        );
    }

    /**
     * Get basic cards for a specific suit
     * 
     * @param string $suit "Wands", "Cups", "Swords", or "Pentacles"
     * @return TarotCard[] 9 cards (values 2-10)
     */
    public function getBasicCardsForSuit(string $suit): array
    {
        return $this->tarotCardRepository->createQueryBuilder('tc')
            ->where('tc.suit = :suit')
            ->andWhere('tc.rarity IN (:rarities)')
            ->setParameter('suit', $suit)
            ->setParameter('rarities', ['common', 'uncommon'])
            ->orderBy('tc.cardValue', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get court cards for a specific suit
     * 
     * @param string $suit "Wands", "Cups", "Swords", or "Pentacles"
     * @return TarotCard[] 4 cards (Page/Knight/Queen/King)
     */
    public function getCourtCardsForSuit(string $suit): array
    {
        return $this->tarotCardRepository->findBy(
            ['suit' => $suit, 'rarity' => 'rare'],
            ['cardValue' => 'ASC']
        );
    }

    /**
     * Get all suits
     * 
     * @return string[] ["Wands", "Cups", "Swords", "Pentacles"]
     */
    public function getAllSuits(): array
    {
        return ['Wands', 'Cups', 'Swords', 'Pentacles'];
    }

    /**
     * Get court card names in order
     * 
     * @return string[] ["Page", "Knight", "Queen", "King"]
     */
    public function getCourtNames(): array
    {
        return ['Page', 'Knight', 'Queen', 'King'];
    }

    /**
     * Map difficulty level to card value for basic rules
     * 
     * @param int $difficultyLevel 1-9
     * @return int Card value (2-10)
     */
    public function mapBasicDifficultyToCardValue(int $difficultyLevel): int
    {
        if ($difficultyLevel < 1 || $difficultyLevel > 9) {
            throw new \InvalidArgumentException("Basic difficulty level must be 1-9. Got: {$difficultyLevel}");
        }
        return $difficultyLevel + 1; // Level 1 = Card 2, Level 9 = Card 10
    }

    /**
     * Map card value to difficulty level for basic rules
     * 
     * @param int $cardValue 2-10
     * @return int Difficulty level (1-9)
     */
    public function mapBasicCardValueToDifficulty(int $cardValue): int
    {
        if ($cardValue < 2 || $cardValue > 10) {
            throw new \InvalidArgumentException("Basic card value must be 2-10. Got: {$cardValue}");
        }
        return $cardValue - 1; // Card 2 = Level 1, Card 10 = Level 9
    }

    /**
     * Map difficulty level to card value for court rules
     * 
     * @param int $difficultyLevel 1-4
     * @return int Card value (11-14 for Page/Knight/Queen/King)
     */
    public function mapCourtDifficultyToCardValue(int $difficultyLevel): int
    {
        if ($difficultyLevel < 1 || $difficultyLevel > 4) {
            throw new \InvalidArgumentException("Court difficulty level must be 1-4. Got: {$difficultyLevel}");
        }
        return $difficultyLevel + 10; // Level 1 = 11 (Page), Level 4 = 14 (King)
    }

    /**
     * Map card value to difficulty level for court rules
     * 
     * @param int $cardValue 11-14
     * @return int Difficulty level (1-4)
     */
    public function mapCourtCardValueToDifficulty(int $cardValue): int
    {
        if ($cardValue < 11 || $cardValue > 14) {
            throw new \InvalidArgumentException("Court card value must be 11-14. Got: {$cardValue}");
        }
        return $cardValue - 10; // 11 (Page) = Level 1, 14 (King) = Level 4
    }

    /**
     * Get the difficulty level for a given tarot card based on rule type
     * 
     * @param TarotCard $card
     * @param string $ruleType 'basic', 'court', or 'legendary'
     * @return int Difficulty level
     */
    public function getCardDifficultyLevel(TarotCard $card, string $ruleType): int
    {
        return match ($ruleType) {
            'basic' => $this->mapBasicCardValueToDifficulty($card->getCardValue()),
            'court' => $this->mapCourtCardValueToDifficulty($card->getCardValue()),
            'legendary' => 1,
            default => throw new \InvalidArgumentException("Invalid rule type: {$ruleType}")
        };
    }

    /**
     * Get cards that are valid for a given rule type
     * 
     * @param string $ruleType 'basic', 'court', or 'legendary'
     * @return TarotCard[]
     */
    public function getCardsForRuleType(string $ruleType): array
    {
        return match ($ruleType) {
            'basic' => $this->getBasicCards(),
            'court' => $this->getCourtCards(),
            'legendary' => $this->getLegendaryCards(),
            default => throw new \InvalidArgumentException("Invalid rule type: {$ruleType}")
        };
    }

    /**
     * Validate that a card is valid for a given rule type
     * 
     * @param TarotCard $card
     * @param string $ruleType
     * @return bool
     */
    public function isCardValidForRuleType(TarotCard $card, string $ruleType): bool
    {
        return match ($ruleType) {
            'basic' => in_array($card->getRarity(), ['common', 'uncommon']),
            'court' => $card->getRarity() === 'rare',
            'legendary' => $card->getRarity() === 'legendary',
            default => false
        };
    }
}

