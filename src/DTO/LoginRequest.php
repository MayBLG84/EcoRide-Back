<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class LoginRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 6, max: 255)]
    public string $password = '';

    public ?string $honeypot = null;

    #[Assert\NotBlank]
    public string $turnstileToken = '';
}
