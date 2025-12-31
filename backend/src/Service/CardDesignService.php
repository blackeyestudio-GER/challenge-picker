<?php

namespace App\Service;

use App\Entity\CardDesign;
use App\Entity\DesignSet;
use App\Enum\TarotCardIdentifier;
use App\Repository\CardDesignRepository;

class CardDesignService
{
    public function __construct(
        private readonly CardDesignRepository $cardDesignRepository,
        private readonly TarotCardService $tarotCardService
    ) {
    }

    /**
     * Get the appropriate card design for a given tarot card and design set.
     * For full sets: Returns the exact card design.
     * For template sets: Falls back to the appropriate template if exact match not found.
     */
    public function getCardDesignForTarotCard(DesignSet $designSet, string $tarotCardIdentifier): ?CardDesign
    {
        // Try to find exact match first
        $cardDesign = $this->cardDesignRepository->findOneBy([
            'designSet' => $designSet,
            'cardIdentifier' => $tarotCardIdentifier,
        ]);

        if ($cardDesign !== null) {
            return $cardDesign;
        }

        // If not found and this is a template set, fall back to template
        if ($designSet->isTemplate()) {
            $templateIdentifier = $this->getTemplateIdentifierForCard($tarotCardIdentifier);

            return $this->cardDesignRepository->findOneBy([
                'designSet' => $designSet,
                'cardIdentifier' => $templateIdentifier,
            ]);
        }

        return null;
    }

    /**
     * Determine which template to use for a given tarot card.
     *
     * @return string 'TEMPLATE_BASIC', 'TEMPLATE_COURT', or 'TEMPLATE_LEGENDARY'
     */
    public function getTemplateIdentifierForCard(string $tarotCardIdentifier): string
    {
        $tarotCardEnum = TarotCardIdentifier::tryFrom($tarotCardIdentifier);

        if ($tarotCardEnum === null) {
            throw new \InvalidArgumentException("Invalid tarot card identifier: {$tarotCardIdentifier}");
        }

        // Major Arcana (The Fool, The Magician, etc.) -> TEMPLATE_LEGENDARY
        if (str_starts_with($tarotCardIdentifier, 'The_')) {
            return 'TEMPLATE_LEGENDARY';
        }

        // Court cards (Page, Knight, Queen, King) -> TEMPLATE_COURT
        $courtCards = ['Page', 'Knight', 'Queen', 'King'];
        foreach ($courtCards as $court) {
            if (str_contains($tarotCardIdentifier, "_{$court}")) {
                return 'TEMPLATE_COURT';
            }
        }

        // Numbered cards (2-10, Ace) -> TEMPLATE_BASIC
        return 'TEMPLATE_BASIC';
    }

    /**
     * Check if a card design requires icon composition (is a template).
     */
    public function requiresIconComposition(CardDesign $cardDesign): bool
    {
        return $cardDesign->isTemplate();
    }

    /**
     * Get the appropriate template type for a tarot card.
     *
     * @return string 'basic', 'court', or 'legendary'
     */
    public function getTemplateTypeForCard(string $tarotCardIdentifier): string
    {
        $templateIdentifier = $this->getTemplateIdentifierForCard($tarotCardIdentifier);

        return match ($templateIdentifier) {
            'TEMPLATE_BASIC' => 'basic',
            'TEMPLATE_COURT' => 'court',
            'TEMPLATE_LEGENDARY' => 'legendary',
            default => 'basic',
        };
    }
}
