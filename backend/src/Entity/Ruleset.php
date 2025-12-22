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

    #[ORM\ManyToOne(inversedBy: 'rulesets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Game $game = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'ruleset', targetEntity: Playthrough::class)]
    private Collection $playthroughs;

    public function __construct()
    {
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

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;

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
}

