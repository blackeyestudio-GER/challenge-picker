<?php

namespace App\Entity;

use App\Repository\PlaythroughRuleQueueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PlaythroughRuleQueueRepository::class)]
#[ORM\Table(name: 'playthrough_rule_queue')]
#[ORM\Index(name: 'idx_playthrough_status', columns: ['playthrough_id', 'queued_by_user_uuid', 'status'])]
#[ORM\Index(name: 'idx_status_position', columns: ['status', 'position'])]
class PlaythroughRuleQueue
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_ACTIVATED = 'activated';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'playthrough_id', referencedColumnName: 'id', nullable: false)]
    private ?Playthrough $playthrough = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rule $rule = null;

    #[ORM\Column]
    private int $difficultyLevel;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $queuedAt;

    #[ORM\Column(type: 'uuid', nullable: true, name: 'queued_by_user_uuid')]
    private ?Uuid $queuedByUserUuid = null; // User who added to queue (null = host/system)

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $processedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $failureReason = null;

    public function __construct()
    {
        $this->queuedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlaythrough(): ?Playthrough
    {
        return $this->playthrough;
    }

    public function setPlaythrough(Playthrough $playthrough): static
    {
        $this->playthrough = $playthrough;

        return $this;
    }

    public function getRule(): ?Rule
    {
        return $this->rule;
    }

    public function setRule(Rule $rule): static
    {
        $this->rule = $rule;

        return $this;
    }

    public function getDifficultyLevel(): int
    {
        return $this->difficultyLevel;
    }

    public function setDifficultyLevel(int $difficultyLevel): static
    {
        $this->difficultyLevel = $difficultyLevel;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getQueuedAt(): \DateTimeImmutable
    {
        return $this->queuedAt;
    }

    public function setQueuedAt(\DateTimeImmutable $queuedAt): static
    {
        $this->queuedAt = $queuedAt;

        return $this;
    }

    public function getQueuedByUserUuid(): ?Uuid
    {
        return $this->queuedByUserUuid;
    }

    public function setQueuedByUserUuid(?Uuid $queuedByUserUuid): static
    {
        $this->queuedByUserUuid = $queuedByUserUuid;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function setProcessedAt(?\DateTimeImmutable $processedAt): static
    {
        $this->processedAt = $processedAt;

        return $this;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function setFailureReason(?string $failureReason): static
    {
        $this->failureReason = $failureReason;

        return $this;
    }
}
