<?php

namespace App\DTO;

class UserSignupResponse
{
    public string $status; // SUCCESS, NICKNAME_ALREADY_EXISTS, EMAIL_ALREADY_EXISTS, INVALID_REQUEST
    public ?int $id = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $nickname = null;
    public ?string $email = null;
    public ?float $credit = null;
    public ?string $createdAt = null;
}
