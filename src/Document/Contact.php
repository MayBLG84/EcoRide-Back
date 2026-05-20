<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: "contacts")]
#[MongoDB\HasLifecycleCallbacks]
#[MongoDB\Index(keys: ['email' => 'asc'])]
#[MongoDB\Index(keys: ['createdAt' => 'desc'])]
class Contact
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: "string")]
    private string $firstName;

    #[MongoDB\Field(type: "string")]
    private string $lastName;

    #[MongoDB\Field(type: "string")]
    private string $email;

    #[MongoDB\Field(type: "string")]
    private string $reason;

    #[MongoDB\Field(type: "string")]
    private string $detail;

    #[MongoDB\Field(type: "string")]
    private string $description;

    #[MongoDB\Field(type: "string", nullable: true)]
    private ?string $rideId = null;

    #[MongoDB\Field(type: "collection")]
    private array $attachments = [];

    #[MongoDB\Field(type: "date")]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->attachments = [];
    }

    // ------------------------
    // Lifecycle Callbacks
    // ------------------------

    #[MongoDB\PrePersist]
    public function setCreatedAtValue(): void
    {
        if (!isset($this->createdAt)) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    // ------------------------
    // GETTERS / SETTERS
    // ------------------------

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }

    public function getDetail(): string
    {
        return $this->detail;
    }

    public function setDetail(string $detail): void
    {
        $this->detail = $detail;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getRideId(): ?string
    {
        return $this->rideId;
    }

    public function setRideId(?string $rideId): void
    {
        $this->rideId = $rideId;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function setAttachments(array $attachments): void
    {
        $this->attachments = $attachments;
    }

    public function addAttachment(string $file): void
    {
        $this->attachments[] = $file;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
