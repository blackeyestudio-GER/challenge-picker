<?php

namespace App\Entity;

use App\Repository\RuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RuleRepository::class)]
#[ORM\Table(name: 'rules')]
class Rule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private ?string $text = null;

    #[ORM\Column]
    private ?int $durationMinutes = null;

    #[ORM\ManyToOne(inversedBy: 'rules')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Ruleset $ruleset = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'rule', targetEntity: PlaythroughRule::class)]
    private Collection $playthroughRules;

    public function __construct()
    {
        $this->playthroughRules = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(int $durationMinutes): static
    {
        $this->durationMinutes = $durationMinutes;

        return $this;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, PlaythroughRule>
     */
    public function getPlaythroughRules(): Collection
    {
        return $this->playthroughRules;
    }

    public function addPlaythroughRule(PlaythroughRule $playthroughRule): static
    {
        if (!$this->playthroughRules->contains($playthroughRule)) {
            $this->playthroughRules->add($playthroughRule);
            $playthroughRule->setRule($this);
        }

        return $this;
    }

    public function removePlaythroughRule(PlaythroughRule $playthroughRule): static
    {
        if ($this->playthroughRules->removeElement($playthroughRule)) {
            // set the owning side to null (unless already changed)
            if ($playthroughRule->getRule() === $this) {
                $playthroughRule->setRule(null);
            }
        }

        return $this;
    }
}

