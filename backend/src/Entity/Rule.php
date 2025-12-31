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

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    private ?string $ruleType = null; // 'basic', 'court', 'legendary'

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $iconIdentifier = null; // Links to RuleIcon.identifier

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $iconColor = null; // Hex color code (e.g., '#FF0000')

    #[ORM\Column(type: 'decimal', precision: 3, scale: 2, nullable: true)]
    private ?string $iconBrightness = null; // 0.0 to 2.0 (1.0 = normal)

    #[ORM\Column(type: 'decimal', precision: 3, scale: 2, nullable: true)]
    private ?string $iconOpacity = null; // 0.0 to 1.0 (1.0 = fully opaque)

    #[ORM\OneToMany(mappedBy: 'rule', targetEntity: RuleDifficultyLevel::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $difficultyLevels;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'rule', targetEntity: PlaythroughRule::class)]
    private Collection $playthroughRules;

    public function __construct()
    {
        $this->difficultyLevels = new ArrayCollection();
        $this->playthroughRules = new ArrayCollection();
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

    public function getRuleType(): ?string
    {
        return $this->ruleType;
    }

    public function setRuleType(string $ruleType): static
    {
        $this->ruleType = $ruleType;

        return $this;
    }

    /**
     * @return Collection<int, RuleDifficultyLevel>
     */
    public function getDifficultyLevels(): Collection
    {
        return $this->difficultyLevels;
    }

    public function addDifficultyLevel(RuleDifficultyLevel $difficultyLevel): static
    {
        if (!$this->difficultyLevels->contains($difficultyLevel)) {
            $this->difficultyLevels->add($difficultyLevel);
            $difficultyLevel->setRule($this);
        }

        return $this;
    }

    public function removeDifficultyLevel(RuleDifficultyLevel $difficultyLevel): static
    {
        if ($this->difficultyLevels->removeElement($difficultyLevel)) {
            if ($difficultyLevel->getRule() === $this) {
                $difficultyLevel->setRule(null);
            }
        }

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

    public function getIconIdentifier(): ?string
    {
        return $this->iconIdentifier;
    }

    public function setIconIdentifier(?string $iconIdentifier): static
    {
        $this->iconIdentifier = $iconIdentifier;

        return $this;
    }

    public function getIconColor(): ?string
    {
        return $this->iconColor;
    }

    public function setIconColor(?string $iconColor): static
    {
        $this->iconColor = $iconColor;

        return $this;
    }

    public function getIconBrightness(): ?string
    {
        return $this->iconBrightness;
    }

    public function setIconBrightness(?string $iconBrightness): static
    {
        $this->iconBrightness = $iconBrightness;

        return $this;
    }

    public function getIconOpacity(): ?string
    {
        return $this->iconOpacity;
    }

    public function setIconOpacity(?string $iconOpacity): static
    {
        $this->iconOpacity = $iconOpacity;

        return $this;
    }
}
