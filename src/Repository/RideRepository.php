<?php

namespace App\Repository;

use App\Entity\Ride;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Ride repository with two helper searches:
 *  - searchExact: matches rides within the given date (00:00 — 23:59)
 *  - searchFuture: matches rides from a given date (inclusive) onward
 */
class RideRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ride::class);
    }

    /**
     * Find rides where originCity and destinyCity match and departureDate is on the given date.
     *
     * @return Ride[]
     */
    public function searchExact(string $originCity, string $destinyCity, \DateTimeImmutable $date, int $limit = 18, int $offset = 0): array
    {
        $start = $date->setTime(0, 0, 0);
        $end   = $date->setTime(23, 59, 59);

        return $this->createQueryBuilder('r')
            ->andWhere('LOWER(r.originCity) = :origin')
            ->andWhere('LOWER(r.destinyCity) = :destiny')
            ->andWhere('r.departureDate BETWEEN :start AND :end')
            ->andWhere('r.nbPlacesAvailable > 0')
            ->andWhere('r.cancelledAt IS NULL')
            ->setParameter('origin', mb_strtolower($originCity))
            ->setParameter('destiny', mb_strtolower($destinyCity))
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('r.departureDate', 'ASC')
            ->addOrderBy('r.departureIntendedTime', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find rides from $fromDate (inclusive) forward.
     *
     * @return Ride[]
     */
    public function searchFuture(string $originCity, string $destinyCity, \DateTimeImmutable $fromDate, int $limit = 6): array
    {
        $from = $fromDate->setTime(0, 0, 0);

        return $this->createQueryBuilder('r')
            ->andWhere('LOWER(r.originCity) = :origin')
            ->andWhere('LOWER(r.destinyCity) = :destiny')
            ->andWhere('r.departureDate >= :from')
            ->andWhere('r.nbPlacesAvailable > 0')
            ->andWhere('r.cancelledAt IS NULL')
            ->setParameter('origin', mb_strtolower($originCity))
            ->setParameter('destiny', mb_strtolower($destinyCity))
            ->setParameter('from', $from)
            ->orderBy('r.departureDate', 'ASC')
            ->addOrderBy('r.departureIntendedTime', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
