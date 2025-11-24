<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $model = null;

    #[ORM\Column(length: 255)]
    private ?string $color = null;

    #[ORM\Column(length: 15)]
    private ?string $registration = null;

    #[ORM\Column]
    private ?\DateTime $firstRgDate = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $electric = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'boolean')]
    private bool $sharedVehicle = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: "RESTRICT")]
    private ?VehicleBrand $vehicleBrand = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'vehicles')]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getRegistration(): ?string
    {
        return $this->registration;
    }

    public function setRegistration(string $registration): static
    {
        $this->registration = $registration;

        return $this;
    }

    public function getFirstRgDate(): ?\DateTime
    {
        return $this->firstRgDate;
    }

    public function setFirstRgDate(\DateTime $firstRgDate): static
    {
        $this->firstRgDate = $firstRgDate;

        return $this;
    }

    public function isElectric(): ?bool
    {
        return $this->electric;
    }

    public function setElectric(bool $electric): static
    {
        $this->electric = $electric;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getVehicleBrand(): ?VehicleBrand
    {
        return $this->vehicleBrand;
    }

    public function setVehicleBrand(?VehicleBrand $vehicleBrand): static
    {
        $this->vehicleBrand = $vehicleBrand;

        return $this;
    }

    public function isSharedVehicle(): bool
    {
        return $this->sharedVehicle;
    }

    public function setSharedVehicle(bool $sharedVehicle): static
    {
        $this->sharedVehicle = $sharedVehicle;
        return $this;
    }
}
