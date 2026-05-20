<?php

namespace App\Resolver;

use App\Repository\RoleRepository;

class UserRoleResolver
{
    public function __construct(
        private readonly RoleRepository $roleRepository
    ) {}

    public function resolve(string $usageType): array
    {
        $roleNames = match ($usageType) {
            'PASSENGER' => ['ROLE_PASSENGER'],
            'DRIVER' => ['ROLE_DRIVER'],
            'BOTH' => ['ROLE_PASSENGER', 'ROLE_DRIVER'],
            default => [],
        };

        $roles = [];

        foreach ($roleNames as $roleName) {
            $role = $this->roleRepository->findRoleByName($roleName);

            if ($role !== null) {
                $roles[] = $role;
            }
        }

        return $roles;
    }
}
