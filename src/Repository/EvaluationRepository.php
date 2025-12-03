<?php

namespace App\Repository;

use App\Entity\Evaluation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Evaluation entity.
 * Uses the "rate" column (as in your Evaluation entity).
 */
class EvaluationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evaluation::class);
    }

    /**
     * Return the average rating (rate) for a driver (user id) or null if none.
     *
     * Note: Evaluation entity stores the numeric score in 'rate'.
     *
     * @return float|null
     */
    public function getAverageRatingForDriver(int $driverId): ?float
    {
        $qb = $this->createQueryBuilder('e')
            ->select('AVG(e.rate) as avg_rate')
            ->join('e.ride', 'r')
            ->join('r.driver', 'd')
            ->andWhere('d.id = :driverId')
            ->setParameter('driverId', $driverId)
            ->getQuery()
            ->getSingleScalarResult();

        if ($qb === null) {
            return null;
        }

        return (float) $qb;
    }
}
