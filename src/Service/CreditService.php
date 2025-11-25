<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CreditService
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Check if the user has enough credit
     */
    public function hasEnoughCredit(User $user, float $amount): bool
    {
        return $user->getCredit() >= $amount;
    }

    /**
     * Deduct credit from the user
     *
     * @throws \RuntimeException if user has insufficient credit
     */
    public function deductCredit(User $user, float $amount): void
    {
        if ($user->getCredit() < $amount) {
            throw new \RuntimeException("Crédit insuffisant pour effectuer cette opération.");
        }
        $user->setCredit($user->getCredit() - $amount);
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Add credit to the user
     */
    public function addCredit(User $user, float $amount): void
    {
        $user->setCredit($user->getCredit() + $amount);
        $this->em->persist($user);
        $this->em->flush();
    }
}
