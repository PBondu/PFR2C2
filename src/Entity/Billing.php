<?php

namespace App\Entity;

use App\Repository\BillingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BillingRepository::class)]
class Billing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column]
    private ?int $Contract_id = null;

    #[ORM\Column]
    private ?float $Amount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getContractId(): ?int
    {
        return $this->Contract_id;
    }

    public function setContractId(int $Contract_id): static
    {
        $this->Contract_id = $Contract_id;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->Amount;
    }

    public function setAmount(float $Amount): static
    {
        $this->Amount = $Amount;

        return $this;
    }
}
