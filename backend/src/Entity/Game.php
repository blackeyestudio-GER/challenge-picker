<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
#[ORM\Table(name: 'games')]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Ruleset::class, cascade: ['remove'])]
    private Collection $rulesets;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Playthrough::class)]
    private Collection $playthroughs;

    public function __construct()
    {
        $this->rulesets = new ArrayCollection();
        $this->playthroughs = new ArrayCollection();
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

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
     * @return Collection<int, Ruleset>
     */
    public function getRulesets(): Collection
    {
        return $this->rulesets;
    }

    public function addRuleset(Ruleset $ruleset): static
    {
        if (!$this->rulesets->contains($ruleset)) {
            $this->rulesets->add($ruleset);
            $ruleset->setGame($this);
        }

        return $this;
    }

    public function removeRuleset(Ruleset $ruleset): static
    {
        if ($this->rulesets->removeElement($ruleset)) {
            // set the owning side to null (unless already changed)
            if ($ruleset->getGame() === $this) {
                $ruleset->setGame(null);
            }
        }

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
            $playthrough->setGame($this);
        }

        return $this;
    }

    public function removePlaythrough(Playthrough $playthrough): static
    {
        if ($this->playthroughs->removeElement($playthrough)) {
            // set the owning side to null (unless already changed)
            if ($playthrough->getGame() === $this) {
                $playthrough->setGame(null);
            }
        }

        return $this;
    }
}

