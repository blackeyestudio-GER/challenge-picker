<?php

namespace App\Entity;

use App\Repository\RulesetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RulesetRepository::class)]
#[ORM\Table(name: 'rulesets')]
class Ruleset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Game::class, inversedBy: 'rulesets')]
    #[ORM\JoinTable(name: 'ruleset_games')]
    private Collection $games;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'ruleset', targetEntity: Playthrough::class)]
    private Collection $playthroughs;

    #[ORM\OneToMany(mappedBy: 'ruleset', targetEntity: RulesetRuleCard::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $rulesetRuleCards;

    public function __construct()
    {
        $this->games = new ArrayCollection();
        $this->playthroughs = new ArrayCollection();
        $this->rulesetRuleCards = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    /**
     * @return Collection<int, Game>
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): static
    {
        if (!$this->games->contains($game)) {
            $this->games->add($game);
        }

        return $this;
    }

    public function removeGame(Game $game): static
    {
        $this->games->removeElement($game);

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
     * @return Collection<int, Playthrough>
     */
    public function getPlaythroughs(): Collection
    {
        return $this->playthroughs;
    }

    public function addPlaythrough(Playthrough $playthrough): static
    {
        if (!$this->playthroughs->contains($playthrough)) {
            $this->playthroughs->add($playthrough);
            $playthrough->setRuleset($this);
        }

        return $this;
    }

    public function removePlaythrough(Playthrough $playthrough): static
    {
        if ($this->playthroughs->removeElement($playthrough)) {
            // set the owning side to null (unless already changed)
            if ($playthrough->getRuleset() === $this) {
                $playthrough->setRuleset(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RulesetRuleCard>
     */
    public function getRulesetRuleCards(): Collection
    {
        return $this->rulesetRuleCards;
    }

    public function addRulesetRuleCard(RulesetRuleCard $rulesetRuleCard): static
    {
        if (!$this->rulesetRuleCards->contains($rulesetRuleCard)) {
            $this->rulesetRuleCards->add($rulesetRuleCard);
            $rulesetRuleCard->setRuleset($this);
        }

        return $this;
    }

    public function removeRulesetRuleCard(RulesetRuleCard $rulesetRuleCard): static
    {
        if ($this->rulesetRuleCards->removeElement($rulesetRuleCard)) {
            // set the owning side to null (unless already changed)
            if ($rulesetRuleCard->getRuleset() === $this) {
                $rulesetRuleCard->setRuleset(null);
            }
        }

        return $this;
    }
}
