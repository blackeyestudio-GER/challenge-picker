<?php

namespace App\Entity;

use App\Repository\UserFavoriteRulesetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserFavoriteRulesetRepository::class)]
#[ORM\Table(name: 'user_favorite_rulesets')]
#[ORM\UniqueConstraint(name: 'unique_user_ruleset', columns: ['user_uuid', 'ruleset_id'])]
class UserFavoriteRuleset
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
