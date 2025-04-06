<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use App\Validator\NotEqualAccounts;
use App\Validator\NotCompatibleCurrency;
use App\Validator\InsufficientFunds;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    private ?Account $from_account = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $to_account = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(length: 255)]
    private ?string $currency = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6)]
    private ?string $exchange_rate = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromAccount(): ?Account
    {
        return $this->from_account;
    }

    public function setFromAccount(?Account $from_account): static
    {
        $this->from_account = $from_account;

        return $this;
    }

    public function getToAccount(): ?Account
    {
        return $this->to_account;
    }

    public function setToAccount(?Account $to_account): static
    {
        $this->to_account = $to_account;

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

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getExchangeRate(): ?string
    {
        return $this->exchange_rate;
    }

    public function setExchangeRate(string $exchange_rate): static
    {
        $this->exchange_rate = $exchange_rate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('from_account', new NotBlank());
        $metadata->addPropertyConstraint('from_account', new NotEqualAccounts());

        $metadata->addPropertyConstraint('to_account', new NotBlank());
        $metadata->addPropertyConstraint('to_account', new NotEqualAccounts());
        
        $metadata->addPropertyConstraint('currency', new NotBlank());
        $metadata->addPropertyConstraint('currency', new NotCompatibleCurrency());

        $metadata->addPropertyConstraint('amount', new NotBlank());
        $metadata->addPropertyConstraint('amount', new InsufficientFunds());

        $metadata->addPropertyConstraint('exchange_rate', new NotBlank());
    }
}
