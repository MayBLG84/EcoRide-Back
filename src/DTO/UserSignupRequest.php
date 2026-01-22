<?php

namespace App\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserSignupRequest
{
    public string $firstName;
    public string $lastName;
    public string $nickname;
    public \DateTimeImmutable $birthday;
    public string $telephone;
    public string $email;
    public string $password;
    public string $usageType;
    public ?array $address = null; // street, number, complement, city, zipcode, country
    public ?UploadedFile $profilePicture = null;
}
