<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Roles>
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function findByCode(string $code): ?Role
    {
        return $this->findOneBy(['code' => $code]);
    }

    public function findRoleByName(string $roleName): ?Role
    {
        return $this->findOneBy(['name' => $roleName]);
    }
}
