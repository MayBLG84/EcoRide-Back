<?php

namespace App\DTO;

class ContactResponse
{
    public string $status;

    public ?string $id = null;

    public ?string $createdAt = null;

    public array $errors = [];
}
