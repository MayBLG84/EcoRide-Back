<?php

namespace App\DTO;

class UserSignupResponse
{
    public string $status;
    public ?int $id = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $nickname = null;
    public ?string $email = null;
    public ?float $credit = null;
    public ?string $createdAt = null;
}
