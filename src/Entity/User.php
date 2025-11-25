<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_NICKNAME', fields: ['nickname'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $firstName;

    #[ORM\Column(length: 255)]
    private ?string $lastName;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $nickname;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email;

    /**
     * @var string The hashed password
     */
    #[ORM\Column(length: 255)]
    private ?string $password;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $birthday;

    #[ORM\Column(type: 'blob', nullable: true)]
    private $photo = null;

    #[ORM\ManyToMany(targetEntity: Vehicle::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'user_vehicle')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $vehicles;

    #[ORM\ManyToMany(targetEntity: Role::class)]
    #[ORM\JoinTable(name: 'user_role')]
    private Collection $roles;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'float')]
    private float $credit;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserAddress::class, cascade: ['persist', 'remove'])]
    private ?UserAddress $address = null;

    #[ORM\OneToMany(targetEntity: Ride::class, mappedBy: 'driver')]
    private Collection $ridesAsDriver;

    #[ORM\ManyToMany(targetEntity: Ride::class, mappedBy: 'passengers')]
    private Collection $ridesAsPassenger;

    /**
     * @var Collection<int, Evaluation>
     */
    #[ORM\OneToMany(targetEntity: Evaluation::class, mappedBy: 'passenger')]
    private Collection $evaluations;

    /**
     * @var Collection<int, Evaluation>
     */
    #[ORM\OneToMany(targetEntity: Evaluation::class, mappedBy: 'treatedBy')]
    private Collection $evaluationManagement;

    public function __construct()
    {
        $this->vehicles = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->credit = 0.0;
        $this->ridesAsDriver = new ArrayCollection();
        $this->ridesAsPassenger = new ArrayCollection();
        $this->evaluations = new ArrayCollection();
        $this->evaluationManagement = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): static
    {
        $this->nickname = $nickname;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getBirthday(): \DateTime
    {
        return $this->birthday;
    }

    public function setBirthday(\DateTime $birthday): static
    {
        $this->birthday = $birthday;
        return $this;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo): static
    {
        $this->photo = $photo;
        return $this;
    }

    /** @return Collection<Vehicle> */
    public function getVehicles(): Collection
    {
        return $this->vehicles;
    }

    public function addVehicle(Vehicle $vehicle): static
    {
        if (!$this->vehicles->contains($vehicle)) {
            $this->vehicles->add($vehicle);
        }
        return $this;
    }

    public function removeVehicle(Vehicle $vehicle): static
    {
        $this->vehicles->removeElement($vehicle);
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles->map(fn(Role $role) => $role->getName())->toArray();
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function getRoleEntities(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
        return $this;
    }

    public function removeRole(Role $role): static
    {
        $this->roles->removeElement($role);
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
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

    public function getCredit(): float
    {
        return $this->credit;
    }

    public function setCredit(float $credit): static
    {
        $this->credit = $credit;
        return $this;
    }

    public function getAddress(): ?UserAddress
    {
        return $this->address;
    }

    public function setAddress(UserAddress $address): static
    {
        if ($address->getUser() !== $this) {
            $address->setUser($this);
        }

        $this->address = $address;
        return $this;
    }

    /**
     * @return Collection<int, Ride>
     */
    public function getRidesAsDriver(): Collection
    {
        return $this->ridesAsDriver;
    }

    public function addRidesAsDriver(Ride $ridesAsDriver): static
    {
        if (!$this->ridesAsDriver->contains($ridesAsDriver)) {
            $this->ridesAsDriver->add($ridesAsDriver);
            $ridesAsDriver->setDriver($this);
        }

        return $this;
    }

    public function removeRidesAsDriver(Ride $ridesAsDriver): static
    {
        if ($this->ridesAsDriver->removeElement($ridesAsDriver)) {
            if ($ridesAsDriver->getDriver() === $this) {
                $ridesAsDriver->setDriver(null);
            }
        }

        return $this;
    }

    public function getRidesAsPassenger(): Collection
    {
        return $this->ridesAsPassenger;
    }

    public function addRideAsPassenger(Ride $ride): static
    {
        if (!$this->ridesAsPassenger->contains($ride)) {
            $this->ridesAsPassenger->add($ride);
            $ride->addPassenger($this);
        }
        return $this;
    }

    public function removeRideAsPassenger(Ride $ride): static
    {
        if ($this->ridesAsPassenger->removeElement($ride)) {
            $ride->removePassenger($this);
        }
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
            $evaluation->setPassenger($this);
        }

        return $this;
    }

    public function removeEvaluation(Evaluation $evaluation): static
    {
        if ($this->evaluations->removeElement($evaluation)) {
            // set the owning side to null (unless already changed)
            if ($evaluation->getPassenger() === $this) {
                $evaluation->setPassenger(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Evaluation>
     */
    public function getEvaluationManagement(): Collection
    {
        return $this->evaluationManagement;
    }

    public function addEvaluationManagement(Evaluation $evaluationManagement): static
    {
        if (!$this->evaluationManagement->contains($evaluationManagement)) {
            $this->evaluationManagement->add($evaluationManagement);
            $evaluationManagement->setTreatedBy($this);
        }

        return $this;
    }

    public function removeEvaluationManagement(Evaluation $evaluationManagement): static
    {
        if ($this->evaluationManagement->removeElement($evaluationManagement)) {
            // set the owning side to null (unless already changed)
            if ($evaluationManagement->getTreatedBy() === $this) {
                $evaluationManagement->setTreatedBy(null);
            }
        }

        return $this;
    }
}
