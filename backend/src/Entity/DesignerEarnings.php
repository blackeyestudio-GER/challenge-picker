<?php

namespace App\Entity;

use App\Repository\DesignerEarningsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DesignerEarningsRepository::class)]
#[ORM\Table(name: 'designer_earnings')]
class DesignerEarnings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $designer = null;

    #[ORM\ManyToOne(targetEntity: DesignSet::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?DesignSet $designSet = null;

    #[ORM\ManyToOne(targetEntity: UserDesignSet::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?UserDesignSet $purchase = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $amount = null; // Designer's commission amount

    #[ORM\Column(type: 'decimal', precision: 5, scale: 4)]
    private ?string $feePercentage = null; // Fee percentage used at time of purchase

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $purchasePrice = null; // Total purchase price

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $currency = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $earnedAt = null;

    public function __construct()
    {
        $this->earnedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesigner(): ?User
    {
        return $this->designer;
    }

    public function setDesigner(?User $designer): static
    {
        $this->designer = $designer;

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

    public function getPurchase(): ?UserDesignSet
    {
        return $this->purchase;
    }

    public function setPurchase(?UserDesignSet $purchase): static
    {
        $this->purchase = $purchase;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getFeePercentage(): ?string
    {
        return $this->feePercentage;
    }

    public function setFeePercentage(string $feePercentage): static
    {
        $this->feePercentage = $feePercentage;

        return $this;
    }

    public function getPurchasePrice(): ?string
    {
        return $this->purchasePrice;
    }

    public function setPurchasePrice(string $purchasePrice): static
    {
        $this->purchasePrice = $purchasePrice;

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

    public function getEarnedAt(): ?\DateTimeImmutable
    {
        return $this->earnedAt;
    }

    public function setEarnedAt(\DateTimeImmutable $earnedAt): static
    {
        $this->earnedAt = $earnedAt;

        return $this;
    }
}
