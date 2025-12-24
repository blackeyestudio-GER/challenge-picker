<?php

namespace App\Entity;

use App\Repository\PlaythroughRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PlaythroughRepository::class)]
#[ORM\Table(name: 'playthroughs')]
class Playthrough
{
    public const STATUS_SETUP = 'setup';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_COMPLETED = 'completed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private ?Uuid $uuid = null;

    #[ORM\ManyToOne(inversedBy: 'playthroughs')]
    #[ORM\JoinColumn(name: 'user_uuid', referencedColumnName: 'uuid', nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'playthroughs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ORM\ManyToOne(inversedBy: 'playthroughs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ruleset $ruleset = null;

    #[ORM\Column]
    private ?int $maxConcurrentRules = 3;

    #[ORM\Column(length: 20)]
    private ?string $status = self::STATUS_SETUP;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalDuration = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'playthrough', targetEntity: PlaythroughRule::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $playthroughRules;

    public function __construct()
    {
        $this->playthroughRules = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->uuid = Uuid::v7();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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

    public function getRuleset(): ?Ruleset
    {
        return $this->ruleset;
    }

    public function setRuleset(?Ruleset $ruleset): static
    {
        $this->ruleset = $ruleset;

        return $this;
    }

    public function getMaxConcurrentRules(): ?int
    {
        return $this->maxConcurrentRules;
    }

    public function setMaxConcurrentRules(int $maxConcurrentRules): static
    {
        $this->maxConcurrentRules = $maxConcurrentRules;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getTotalDuration(): ?int
    {
        return $this->totalDuration;
    }

    public function setTotalDuration(?int $totalDuration): static
    {
        $this->totalDuration = $totalDuration;

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
            $playthroughRule->setPlaythrough($this);
        }

        return $this;
    }

    public function removePlaythroughRule(PlaythroughRule $playthroughRule): static
    {
        if ($this->playthroughRules->removeElement($playthroughRule)) {
            // set the owning side to null (unless already changed)
            if ($playthroughRule->getPlaythrough() === $this) {
                $playthroughRule->setPlaythrough(null);
            }
        }

        return $this;
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_SETUP, self::STATUS_ACTIVE, self::STATUS_PAUSED]);
    }
}
