<?php

namespace App\Entity;

use App\Repository\DesignSetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DesignSetRepository::class)]
#[ORM\Table(name: 'design_sets')]
class DesignSet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'designSets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?DesignName $designName = null;

    #[ORM\Column(length: 20)]
    private string $type = 'full'; // 'full' or 'template'

    #[ORM\Column]
    private bool $isPremium = false;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $price = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $theme = null; // 'gothic', 'horror', 'scifi', 'cyberpunk', etc.

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'smallint')]
    private int $sortOrder = 0;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(mappedBy: 'designSet', targetEntity: CardDesign::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $cardDesigns;

    public function __construct()
    {
        $this->cardDesigns = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignName(): ?DesignName
    {
        return $this->designName;
    }

    public function setDesignName(?DesignName $designName): static
    {
        $this->designName = $designName;

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

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, CardDesign>
     */
    public function getCardDesigns(): Collection
    {
        return $this->cardDesigns;
    }

    public function addCardDesign(CardDesign $cardDesign): static
    {
        if (!$this->cardDesigns->contains($cardDesign)) {
            $this->cardDesigns->add($cardDesign);
            $cardDesign->setDesignSet($this);
        }

        return $this;
    }

    public function removeCardDesign(CardDesign $cardDesign): static
    {
        if ($this->cardDesigns->removeElement($cardDesign)) {
            if ($cardDesign->getDesignSet() === $this) {
                $cardDesign->setDesignSet(null);
            }
        }

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        if (!in_array($type, ['full', 'template'], true)) {
            throw new \InvalidArgumentException("Type must be 'full' or 'template'");
        }
        $this->type = $type;

        return $this;
    }

    public function isPremium(): bool
    {
        return $this->isPremium;
    }

    public function setIsPremium(bool $isPremium): static
    {
        $this->isPremium = $isPremium;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(?string $theme): static
    {
        $this->theme = $theme;

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

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function isTemplate(): bool
    {
        return $this->type === 'template';
    }

    public function isFull(): bool
    {
        return $this->type === 'full';
    }
}
