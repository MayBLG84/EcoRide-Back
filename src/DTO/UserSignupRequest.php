<?php

namespace App\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class UserSignupRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    public string $firstName;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $lastName;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $nickname;

    #[Assert\NotBlank]
    #[Assert\Type(\DateTimeImmutable::class)]
    public \DateTimeImmutable $birthday;

    #[Assert\Length(max: 15)]
    public string $telephone;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    public string $password;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['PASSENGER', 'DRIVER', 'BOTH'])]
    public string $usageType;

    /**
     * Address fields:
     * number, street, city, zipcode, country (complement optional)
     */
    #[Assert\Type('array')]
    public ?array $address = null;

    #[Assert\File(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp']
    )]
    public ?UploadedFile $profilePicture = null;
}
