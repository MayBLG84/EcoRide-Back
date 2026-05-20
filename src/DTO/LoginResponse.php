<?php

namespace App\DTO;

class LoginResponse
{
    public string $status;
    public ?string $userId = null;
    public ?string $token = null;
    /** @var string[] */
    public array $roles = [];

    /** @var string[] */
    public array $errors = [];
}
