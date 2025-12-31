<?php

namespace App\Entity;

use App\Repository\RuleIconRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RuleIconRepository::class)]
#[ORM\Table(name: 'rule_icons')]
class RuleIcon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private string $identifier; // e.g., "knife", "heart_broken", "sprint"

    #[ORM\Column(length: 50)]
    private string $category; // "weapon", "movement", "resource", "action", "modifier"

    #[ORM\Column(length: 100)]
    private string $displayName; // "Knife", "No Healing", "Sprint"

    #[ORM\Column(type: Types::TEXT)]
    private string $svgContent; // Actual SVG markup

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $tags = null; // ["weapon", "melee", "blade"]

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $color = null; // Hex color for tinting (e.g., "#FF0000")

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $license = null; // "CC BY 3.0", "MIT", "custom"

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $source = null; // URL or attribution

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

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

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

    public function getSvgContent(): string
    {
        return $this->svgContent;
    }

    public function setSvgContent(string $svgContent): static
    {
        $this->svgContent = $svgContent;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getLicense(): ?string
    {
        return $this->license;
    }

    public function setLicense(?string $license): static
    {
        $this->license = $license;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): static
    {
        $this->source = $source;

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
}
