<?php

namespace App\Entity;

use App\Repository\RulesetRuleCardRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RulesetRuleCardRepository::class)]
#[ORM\Table(name: 'ruleset_rule_cards')]
class RulesetRuleCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Ruleset::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Ruleset $ruleset = null;

    #[ORM\ManyToOne(targetEntity: Rule::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Rule $rule = null;

    #[ORM\ManyToOne(targetEntity: TarotCard::class)]
    #[ORM\JoinColumn(name: 'tarot_card_identifier', referencedColumnName: 'identifier', nullable: false, onDelete: 'CASCADE')]
    private ?TarotCard $tarotCard = null;

    #[ORM\Column(type: 'smallint')]
    private ?int $position = null;

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

    public function getRuleset(): ?Ruleset
    {
        return $this->ruleset;
    }

    public function setRuleset(?Ruleset $ruleset): static
    {
        $this->ruleset = $ruleset;

        return $this;
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

    public function getTarotCard(): ?TarotCard
    {
        return $this->tarotCard;
    }

    public function setTarotCard(?TarotCard $tarotCard): static
    {
        $this->tarotCard = $tarotCard;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

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
