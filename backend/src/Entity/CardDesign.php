<?php

namespace App\Entity;

use App\Enum\TarotCardIdentifier;
use App\Repository\CardDesignRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardDesignRepository::class)]
#[ORM\Table(name: 'card_designs')]
#[ORM\UniqueConstraint(name: 'unique_card_per_set', columns: ['design_set_id', 'card_identifier'])]
class CardDesign
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cardDesigns')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?DesignSet $designSet = null;

    #[ORM\Column(length: 50)]
    private string $cardIdentifier; // Stores TarotCardIdentifier enum value or TEMPLATE_XXX

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $imageBase64 = null;

    #[ORM\Column]
    private bool $isTemplate = false; // True for TEMPLATE_BASIC, TEMPLATE_COURT, TEMPLATE_LEGENDARY

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $templateType = null; // 'basic', 'court', 'legendary' (only if isTemplate=true)

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignSet(): ?DesignSet
    {
        return $this->designSet;
    }

    public function setDesignSet(?DesignSet $designSet): static
    {
        $this->designSet = $designSet;

        return $this;
    }

    public function getCardIdentifier(): string
    {
        return $this->cardIdentifier;
    }

    public function setCardIdentifier(string $cardIdentifier): static
    {
        $this->cardIdentifier = $cardIdentifier;

        return $this;
    }

    public function getCardIdentifierEnum(): ?TarotCardIdentifier
    {
        return TarotCardIdentifier::tryFrom($this->cardIdentifier);
    }

    public function setCardIdentifierEnum(TarotCardIdentifier $identifier): static
    {
        $this->cardIdentifier = $identifier->value;

        return $this;
    }

    public function getImageBase64(): ?string
    {
        return $this->imageBase64;
    }

    public function setImageBase64(?string $imageBase64): static
    {
        $this->imageBase64 = $imageBase64;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isTemplate(): bool
    {
        return $this->isTemplate;
    }

    public function setIsTemplate(bool $isTemplate): static
    {
        $this->isTemplate = $isTemplate;

        return $this;
    }

    public function getTemplateType(): ?string
    {
        return $this->templateType;
    }

    public function setTemplateType(?string $templateType): static
    {
        if ($templateType !== null && !in_array($templateType, ['basic', 'court', 'legendary'], true)) {
            throw new \InvalidArgumentException("Template type must be 'basic', 'court', or 'legendary'");
        }
        $this->templateType = $templateType;

        return $this;
    }

    public function requiresIconComposition(): bool
    {
        return $this->isTemplate;
    }
}
