<?php

namespace App\Entity;

use App\Repository\RulesetVoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RulesetVoteRepository::class)]
#[ORM\Table(name: 'ruleset_votes')]
#[ORM\UniqueConstraint(name: 'unique_user_ruleset', columns: ['user_uuid', 'ruleset_id'])]
class RulesetVote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_uuid', referencedColumnName: 'uuid', nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ruleset $ruleset = null;

    #[ORM\Column(type: 'smallint')]
    private int $voteType = 1; // 1 for upvote, -1 for downvote

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
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

    public function getVoteType(): int
    {
        return $this->voteType;
    }

    public function setVoteType(int $voteType): static
    {
        if (!in_array($voteType, [1, -1])) {
            throw new \InvalidArgumentException('Vote type must be 1 (upvote) or -1 (downvote)');
        }

        $this->voteType = $voteType;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
