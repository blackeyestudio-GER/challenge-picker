<?php

namespace App\Entity;

use App\Constants\ObsDesigns;
use App\Repository\UserObsPreferenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserObsPreferenceRepository::class)]
#[ORM\Table(name: 'user_obs_preferences')]
class UserObsPreference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_uuid', referencedColumnName: 'uuid', nullable: false)]
    private User $user;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $showTimerInSetup = false;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $showTimerInActive = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $showTimerInPaused = true;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $showTimerInCompleted = false;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $showStatusInSetup = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $showStatusInActive = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $showStatusInPaused = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $showStatusInCompleted = true;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'none'])]
    private string $timerPosition = 'none'; // 'none', 'on_card', 'below_card'

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'numbers'])]
    private string $timerDesign = 'numbers'; // 'numbers' (more styles later)

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'word'])]
    private string $statusDesign = 'word'; // 'word', 'symbols', 'buttons'

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'list'])]
    private string $rulesDesign = 'list'; // 'list' (more layouts later)

    #[ORM\Column(type: 'string', length: 9, options: ['default' => '#00FF00'])]
    private string $chromaKeyColor = '#00FF00'; // Chroma key color for OBS (standard green)

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function isShowTimerInSetup(): bool
    {
        return $this->showTimerInSetup;
    }

    public function setShowTimerInSetup(bool $showTimerInSetup): self
    {
        $this->showTimerInSetup = $showTimerInSetup;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function isShowTimerInActive(): bool
    {
        return $this->showTimerInActive;
    }

    public function setShowTimerInActive(bool $showTimerInActive): self
    {
        $this->showTimerInActive = $showTimerInActive;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function isShowTimerInPaused(): bool
    {
        return $this->showTimerInPaused;
    }

    public function setShowTimerInPaused(bool $showTimerInPaused): self
    {
        $this->showTimerInPaused = $showTimerInPaused;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function isShowTimerInCompleted(): bool
    {
        return $this->showTimerInCompleted;
    }

    public function setShowTimerInCompleted(bool $showTimerInCompleted): self
    {
        $this->showTimerInCompleted = $showTimerInCompleted;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function isShowStatusInSetup(): bool
    {
        return $this->showStatusInSetup;
    }

    public function setShowStatusInSetup(bool $showStatusInSetup): self
    {
        $this->showStatusInSetup = $showStatusInSetup;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function isShowStatusInActive(): bool
    {
        return $this->showStatusInActive;
    }

    public function setShowStatusInActive(bool $showStatusInActive): self
    {
        $this->showStatusInActive = $showStatusInActive;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function isShowStatusInPaused(): bool
    {
        return $this->showStatusInPaused;
    }

    public function setShowStatusInPaused(bool $showStatusInPaused): self
    {
        $this->showStatusInPaused = $showStatusInPaused;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function isShowStatusInCompleted(): bool
    {
        return $this->showStatusInCompleted;
    }

    public function setShowStatusInCompleted(bool $showStatusInCompleted): self
    {
        $this->showStatusInCompleted = $showStatusInCompleted;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getTimerPosition(): string
    {
        return $this->timerPosition;
    }

    public function setTimerPosition(string $timerPosition): self
    {
        if (!ObsDesigns::isValidTimerPosition($timerPosition)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid timer position "%s". Supported: %s', 
                    $timerPosition, 
                    implode(', ', ObsDesigns::TIMER_POSITIONS)
                )
            );
        }
        $this->timerPosition = $timerPosition;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getTimerDesign(): string
    {
        return $this->timerDesign;
    }

    public function setTimerDesign(string $timerDesign): self
    {
        if (!ObsDesigns::isValidTimerDesign($timerDesign)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid timer design "%s". Supported: %s', 
                    $timerDesign, 
                    implode(', ', ObsDesigns::TIMER_DESIGNS)
                )
            );
        }
        $this->timerDesign = $timerDesign;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getStatusDesign(): string
    {
        return $this->statusDesign;
    }

    public function setStatusDesign(string $statusDesign): self
    {
        if (!ObsDesigns::isValidStatusDesign($statusDesign)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid status design "%s". Supported: %s', 
                    $statusDesign, 
                    implode(', ', ObsDesigns::STATUS_DESIGNS)
                )
            );
        }
        $this->statusDesign = $statusDesign;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getRulesDesign(): string
    {
        return $this->rulesDesign;
    }

    public function setRulesDesign(string $rulesDesign): self
    {
        if (!ObsDesigns::isValidRulesDesign($rulesDesign)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid rules design "%s". Supported: %s', 
                    $rulesDesign, 
                    implode(', ', ObsDesigns::RULES_DESIGNS)
                )
            );
        }
        $this->rulesDesign = $rulesDesign;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getChromaKeyColor(): string
    {
        return $this->chromaKeyColor;
    }

    public function setChromaKeyColor(string $chromaKeyColor): self
    {
        // Validate hex color format (#RRGGBB or #RRGGBBAA)
        if (!preg_match('/^#[0-9A-Fa-f]{6}([0-9A-Fa-f]{2})?$/', $chromaKeyColor)) {
            throw new \InvalidArgumentException(
                'Invalid chroma key color format. Use #RRGGBB or #RRGGBBAA (hex)'
            );
        }
        $this->chromaKeyColor = strtoupper($chromaKeyColor);
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }
}

