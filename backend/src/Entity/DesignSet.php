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
}

