<?php

namespace App\Entity;

use App\Repository\DesignNameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DesignNameRepository::class)]
#[ORM\Table(name: 'design_names')]
class DesignName
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'designName', targetEntity: DesignSet::class, cascade: ['remove'])]
    private Collection $designSets;

    public function __construct()
    {
        $this->designSets = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, DesignSet>
     */
    public function getDesignSets(): Collection
    {
        return $this->designSets;
    }

    public function addDesignSet(DesignSet $designSet): static
    {
        if (!$this->designSets->contains($designSet)) {
            $this->designSets->add($designSet);
            $designSet->setDesignName($this);
        }

        return $this;
    }

    public function removeDesignSet(DesignSet $designSet): static
    {
        if ($this->designSets->removeElement($designSet)) {
            if ($designSet->getDesignName() === $this) {
                $designSet->setDesignName(null);
            }
        }

        return $this;
    }
}

