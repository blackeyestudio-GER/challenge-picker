<?php

namespace App\Entity;

use App\Repository\UserDesignSetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserDesignSetRepository::class)]
#[ORM\Table(name: 'user_design_sets')]
class UserDesignSet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $userUuid = null;

    #[ORM\ManyToOne(targetEntity: DesignSet::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?DesignSet $designSet = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $purchasedAt = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $pricePaid = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $currency = null;

    public function __construct()
    {
        $this->purchasedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserUuid(): ?Uuid
    {
        return $this->userUuid;
    }

    public function setUserUuid(Uuid $userUuid): static
    {
        $this->userUuid = $userUuid;

        return $this;
    }

    public function getDesignSet(): ?DesignSet
    {
        return $this->designSet;
    }

    public function setDesignSet(?DesignSet $designSet): static
    {
        $this->designSet = $designSet;

        return $this;
    }

    public function getPurchasedAt(): ?\DateTimeImmutable
    {
        return $this->purchasedAt;
    }

    public function setPurchasedAt(\DateTimeImmutable $purchasedAt): static
    {
        $this->purchasedAt = $purchasedAt;

        return $this;
    }

    public function getPricePaid(): ?string
    {
        return $this->pricePaid;
    }

    public function setPricePaid(string $pricePaid): static
    {
        $this->pricePaid = $pricePaid;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }
}
