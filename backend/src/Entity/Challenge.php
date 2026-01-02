<?php

namespace App\Entity;

use App\Repository\ChallengeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ChallengeRepository::class)]
#[ORM\Table(name: 'challenges')]
#[ORM\Index(columns: ['challenged_user_uuid'], name: 'idx_challenged_user')]
#[ORM\Index(columns: ['status'], name: 'idx_status')]
class Challenge
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_EXPIRED = 'expired';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $uuid;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'challenger_uuid', referencedColumnName: 'uuid', nullable: false)]
    private User $challenger;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'challenged_user_uuid', referencedColumnName: 'uuid', nullable: false)]
    private User $challengedUser;

    #[ORM\ManyToOne(targetEntity: Playthrough::class)]
    #[ORM\JoinColumn(name: 'playthrough_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\JoinColumn(name: 'playthrough_user_uuid', referencedColumnName: 'user_uuid', nullable: false)]
    private Playthrough $sourcePlaythrough;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $respondedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $expiresAt;

    public function __construct()
    {
        $this->uuid = Uuid::v7();
        $this->createdAt = new \DateTimeImmutable();
        // Challenges expire after 7 days
        $this->expiresAt = new \DateTimeImmutable('+7 days');
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getChallenger(): User
    {
        return $this->challenger;
    }

    public function setChallenger(User $challenger): self
    {
        $this->challenger = $challenger;

        return $this;
    }

    public function getChallengedUser(): User
    {
        return $this->challengedUser;
    }

    public function setChallengedUser(User $challengedUser): self
    {
        $this->challengedUser = $challengedUser;

        return $this;
    }

    public function getSourcePlaythrough(): Playthrough
    {
        return $this->sourcePlaythrough;
    }

    public function setSourcePlaythrough(Playthrough $sourcePlaythrough): self
    {
        $this->sourcePlaythrough = $sourcePlaythrough;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getRespondedAt(): ?\DateTimeImmutable
    {
        return $this->respondedAt;
    }

    public function setRespondedAt(?\DateTimeImmutable $respondedAt): self
    {
        $this->respondedAt = $respondedAt;

        return $this;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpired(): bool
    {
        return new \DateTimeImmutable() > $this->expiresAt;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING && !$this->isExpired();
    }
}
