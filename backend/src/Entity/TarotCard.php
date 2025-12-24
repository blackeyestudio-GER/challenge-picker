<?php

namespace App\Entity;

use App\Repository\TarotCardRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TarotCardRepository::class)]
#[ORM\Table(name: 'tarot_cards')]
class TarotCard
{
    #[ORM\Id]
    #[ORM\Column(length: 50)]
    private string $identifier; // e.g., "The_Fool", "Wands_Ace"

    #[ORM\Column(length: 100)]
    private string $displayName; // e.g., "The Fool", "Ace of Wands"

    #[ORM\Column(length: 20)]
    private string $rarity; // legendary, rare, common

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $suit = null; // Wands, Cups, Swords, Pentacles (null for Major Arcana)

    #[ORM\Column(type: 'smallint')]
    private int $cardValue; // 0-21 for Major Arcana, 1-14 for Minor Arcana

    #[ORM\Column(type: 'smallint')]
    private int $sortOrder; // Global sort order (0-77)

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): static
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getRarity(): string
    {
        return $this->rarity;
    }

    public function setRarity(string $rarity): static
    {
        $this->rarity = $rarity;

        return $this;
    }

    public function getSuit(): ?string
    {
        return $this->suit;
    }

    public function setSuit(?string $suit): static
    {
        $this->suit = $suit;

        return $this;
    }

    public function getCardValue(): int
    {
        return $this->cardValue;
    }

    public function setCardValue(int $cardValue): static
    {
        $this->cardValue = $cardValue;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
