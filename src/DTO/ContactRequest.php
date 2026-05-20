<?php

namespace App\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class ContactRequest
{
    #[Assert\NotBlank]
    public string $firstName = '';

    #[Assert\NotBlank]
    public string $lastName = '';

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email = '';

    #[Assert\NotBlank]
    public string $reason = '';

    #[Assert\NotBlank]
    public string $detail = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 1000)]
    public string $description = '';

    /**
     * @var UploadedFile[]
     */
    public array $attachments = [];

    public ?string $rideId = null;

    public ?string $honeypot = null;

    #[Assert\NotBlank]
    public string $turnstileToken = '';
}
