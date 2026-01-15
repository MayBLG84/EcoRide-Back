<?php

namespace App\Entity;

use App\Repository\RideRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RideRepository::class)]
class Ride
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'ridesAsDriver')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $driver = null;

    #[ORM\Column(length: 60)]
    private ?string $originCity = null;

    #[ORM\Column(length: 255)]
    private ?string $pickPoint = null;

    #[ORM\Column]
    private ?\DateTime $departureDate = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $departureIntendedTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $departureRealTime = null;

    #[ORM\Column(length: 60)]
    private ?string $destinyCity = null;

    #[ORM\Column(length: 255)]
    private ?string $dropPoint = null;

    #[ORM\Column]
    private ?\DateTime $arrivalDate = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $arrivalEstimatedTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $arrivalRealTime = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?RideStatus $rideStatus = null;

    #[ORM\Column]
    private ?int $nbPlacesOffered = null;

    #[ORM\Column]
    private ?int $nbPlacesAvailable = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'ridesAsPassenger')]
    #[ORM\JoinTable(
        name: 'ride_passenger',
        joinColumns: [
            new ORM\JoinColumn(name: 'ride_id', referencedColumnName: 'id')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')
        ]
    )]
    private Collection $passengers;

    #[ORM\Column]
    private ?float $pricePerson = null;

    #[ORM\ManyToOne(targetEntity: Vehicle::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vehicle $vehicle = null;

    #[ORM\Column]
    private ?bool $smokersAllowed = false;

    #[ORM\Column]
    private ?bool $animalsAllowed = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $otherPreferences = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $cancelledAt = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $estimatedDuration = 0;

    /**
     * @var Collection<int, Evaluation>
     */
    #[ORM\OneToMany(targetEntity: Evaluation::class, mappedBy: 'ride')]
    private Collection $evaluations;


    public function __construct()
    {
        $this->passengers = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->evaluations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDriver(): ?User
    {
        return $this->driver;
    }

    public function setDriver(?User $driver): static
    {
        $this->driver = $driver;
        return $this;
    }

    public function getOriginCity(): ?string
    {
        return $this->originCity;
    }

    public function setOriginCity(string $originCity): static
    {
        $this->originCity = $originCity;
        return $this;
    }

    public function getPickPoint(): ?string
    {
        return $this->pickPoint;
    }

    public function setPickPoint(string $pickPoint): static
    {
        $this->pickPoint = $pickPoint;

        return $this;
    }

    public function getDepartureDate(): ?\DateTime
    {
        return $this->departureDate;
    }

    public function setDepartureDate(\DateTime $departureDate): static
    {
        $this->departureDate = $departureDate;

        return $this;
    }

    public function getDestinyCity(): ?string
    {
        return $this->destinyCity;
    }

    public function setDestinyCity(string $destinyCity): static
    {
        $this->destinyCity = $destinyCity;

        return $this;
    }

    public function getDropPoint(): ?string
    {
        return $this->dropPoint;
    }

    public function setDropPoint(string $dropPoint): static
    {
        $this->dropPoint = $dropPoint;

        return $this;
    }

    public function getArrivalDate(): ?\DateTime
    {
        return $this->arrivalDate;
    }

    public function setArrivalDate(\DateTime $arrivalDate): static
    {
        $this->arrivalDate = $arrivalDate;

        return $this;
    }

    public function getDepartureIntendedTime(): ?\DateTime
    {
        return $this->departureIntendedTime;
    }

    public function setDepartureIntendedTime(\DateTime $departureIntendedTime): static
    {
        $this->departureIntendedTime = $departureIntendedTime;

        return $this;
    }

    public function getDepartureRealTime(): ?\DateTime
    {
        return $this->departureRealTime;
    }

    public function setDepartureRealTime(?\DateTime $departureRealTime): static
    {
        $this->departureRealTime = $departureRealTime;

        return $this;
    }

    public function getArrivalEstimatedTime(): ?\DateTime
    {
        return $this->arrivalEstimatedTime;
    }

    public function setArrivalEstimatedTime(\DateTime $arrivalEstimatedTime): static
    {
        $this->arrivalEstimatedTime = $arrivalEstimatedTime;

        return $this;
    }

    public function getArrivalRealTime(): ?\DateTime
    {
        return $this->arrivalRealTime;
    }

    public function setArrivalRealTime(?\DateTime $arrivalRealTime): static
    {
        $this->arrivalRealTime = $arrivalRealTime;

        return $this;
    }

    public function getRideStatus(): ?RideStatus
    {
        return $this->rideStatus;
    }

    public function setRideStatus(?RideStatus $rideStatus): static
    {
        $this->rideStatus = $rideStatus;

        return $this;
    }

    public function getNbPlacesOffered(): ?int
    {
        return $this->nbPlacesOffered;
    }

    public function setNbPlacesOffered(int $nbPlacesOffered): static
    {
        $this->nbPlacesOffered = $nbPlacesOffered;
        $this->nbPlacesAvailable = $nbPlacesOffered;
        return $this;
    }

    public function getNbPlacesAvailable(): ?int
    {
        return $this->nbPlacesAvailable;
    }

    public function setNbPlacesAvailable(int $nbPlacesAvailable): static
    {
        $this->nbPlacesAvailable = $nbPlacesAvailable;

        return $this;
    }

    private function updateNbPlacesAvailable(): void
    {
        $this->nbPlacesAvailable = $this->nbPlacesOffered - count($this->passengers);
    }

    public function getPassengers(): Collection
    {
        return $this->passengers;
    }

    public function addPassenger(User $user): static
    {
        if (!$this->passengers->contains($user)) {
            $this->passengers->add($user);
            $user->addRideAsPassenger($this);
            $this->updateNbPlacesAvailable();
        }
        return $this;
    }

    public function removePassenger(User $user): static
    {
        if ($this->passengers->removeElement($user)) {
            $user->removeRideAsPassenger($this);
            $this->updateNbPlacesAvailable();
        }
        return $this;
    }

    public function getPricePerson(): ?float
    {
        return $this->pricePerson;
    }

    public function setPricePerson(float $pricePerson): static
    {
        $this->pricePerson = $pricePerson;

        return $this;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): static
    {
        if ($vehicle && $this->driver && !$this->driver->getVehicles()->contains($vehicle)) {
            throw new \InvalidArgumentException("Le véhicule doit appartenir au conducteur du trajet.");
        }
        $this->vehicle = $vehicle;
        return $this;
    }

    public function isSmokersAllowed(): ?bool
    {
        return $this->smokersAllowed;
    }

    public function setSmokersAllowed(bool $smokersAllowed): static
    {
        $this->smokersAllowed = $smokersAllowed;

        return $this;
    }

    public function isAnimalsAllowed(): ?bool
    {
        return $this->animalsAllowed;
    }

    public function setAnimalsAllowed(bool $animalsAllowed): static
    {
        $this->animalsAllowed = $animalsAllowed;

        return $this;
    }

    public function getOtherPreferences(): ?string
    {
        return $this->otherPreferences;
    }

    public function setOtherPreferences(?string $otherPreferences): static
    {
        $this->otherPreferences = $otherPreferences;

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

    public function getCancelledAt(): ?\DateTimeImmutable
    {
        return $this->cancelledAt;
    }

    public function setCancelledAt(?\DateTimeImmutable $cancelledAt): static
    {
        $this->cancelledAt = $cancelledAt;

        return $this;
    }

    /**
     * @return Collection<int, Evaluation>
     */
    public function getEvaluations(): Collection
    {
        return $this->evaluations;
    }

    public function addEvaluation(Evaluation $evaluation): static
    {
        if (!$this->evaluations->contains($evaluation)) {
            $this->evaluations->add($evaluation);
            $evaluation->setRide($this);
        }

        return $this;
    }

    public function removeEvaluation(Evaluation $evaluation): static
    {
        if ($this->evaluations->removeElement($evaluation)) {
            // set the owning side to null (unless already changed)
            if ($evaluation->getRide() === $this) {
                $evaluation->setRide(null);
            }
        }

        return $this;
    }

    public function getEstimatedDuration(): int
    {
        return $this->estimatedDuration;
    }

    public function setEstimatedDuration(int $minutes): static
    {
        $this->estimatedDuration = $minutes;
        return $this;
    }
}
