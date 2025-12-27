<?php

namespace App\Entity;

use App\Repository\RuleDifficultyLevelRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RuleDifficultyLevelRepository::class)]
#[ORM\Table(name: 'rule_difficulty_levels')]
#[ORM\UniqueConstraint(name: 'UNIQ_RULE_DIFFICULTY', columns: ['rule_id', 'difficulty_level'])]
class RuleDifficultyLevel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'difficultyLevels')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Rule $rule = null;

    #[ORM\Column(type: 'smallint')]
    private ?int $difficultyLevel = null;

    #[ORM\Column(nullable: true)]
    private ?int $durationMinutes = null;

    #[ORM\Column(nullable: true)]
    private ?int $amount = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRule(): ?Rule
    {
        return $this->rule;
    }

    public function setRule(?Rule $rule): static
    {
        $this->rule = $rule;

        return $this;
    }

    public function getDifficultyLevel(): ?int
    {
        return $this->difficultyLevel;
    }

    public function setDifficultyLevel(int $difficultyLevel): static
    {
        $this->difficultyLevel = $difficultyLevel;

        return $this;
    }

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(?int $durationMinutes): static
    {
        $this->durationMinutes = $durationMinutes;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(?int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
