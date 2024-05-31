<?php

namespace App\Entity;

use App\Repository\ContractRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContractRepository::class)]
class Contract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $vehicle_uid = null;

    #[ORM\Column(length: 255)]
    private ?string $customer_uid = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $sign_datetime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $locbegin_datetime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $locend_datetime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $returning_datetime = null;

    #[ORM\Column]
    private ?float $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getVehicleUid(): ?string
    {
        return $this->vehicle_uid;
    }

    public function setVehicleUid(string $vehicle_uid): static
    {
        $this->vehicle_uid = $vehicle_uid;

        return $this;
    }

    public function getCustomerUid(): ?string
    {
        return $this->customer_uid;
    }

    public function setCustomerUid(string $customer_uid): static
    {
        $this->customer_uid = $customer_uid;

        return $this;
    }

    public function getSignDatetime(): ?\DateTimeInterface
    {
        return $this->sign_datetime;
    }

    public function setSignDatetime(\DateTimeInterface $sign_datetime): static
    {
        $this->sign_datetime = $sign_datetime;

        return $this;
    }

    public function getLocbeginDatetime(): ?\DateTimeInterface
    {
        return $this->locbegin_datetime;
    }

    public function setLocbeginDatetime(\DateTimeInterface $locbegin_datetime): static
    {
        $this->locbegin_datetime = $locbegin_datetime;

        return $this;
    }

    public function getLocendDatetime(): ?\DateTimeInterface
    {
        return $this->locend_datetime;
    }

    public function setLocendDatetime(\DateTimeInterface $locend_datetime): static
    {
        $this->locend_datetime = $locend_datetime;

        return $this;
    }

    public function getReturningDatetime(): ?\DateTimeInterface
    {
        return $this->returning_datetime;
    }

    public function setReturningDatetime(\DateTimeInterface $returning_datetime): static
    {
        $this->returning_datetime = $returning_datetime;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }
}
