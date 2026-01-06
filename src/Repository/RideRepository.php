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
     *    @return array{
     *     results: Ride[],
     *     totalResults: int
     * }
     */
    public function searchExact(string $originCity, string $destinyCity, \DateTimeImmutable $date, int $limit = 18, int $offset = 0, array $filters = [], ?string $orderBy = null): array
    {
        $start = $date->setTime(0, 0, 0);
        $end   = $date->setTime(23, 59, 59);

        // ------------------------ MAIN QUERY (paginated) ------------------------
        $qb = $this->createQueryBuilder('r')
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
            ->setMaxResults($limit);


        // ------------------------ JOIN COM VEÍCULO ------------------------
        // Only if "electricOnly" is defined
        if (!empty($filters['electricOnly'])) {
            $qb->join('r.vehicle', 'v');
            $qb->andWhere('v.electric = true');
        }
        // ------------------ DINAMIC FILTERS ------------------
        if (!empty($filters)) {
            if (isset($filters['priceMin'])) {
                $qb->andWhere('r.pricePerson >= :priceMin')
                    ->setParameter('priceMin', $filters['priceMin']);
            }
            if (isset($filters['priceMax'])) {
                $qb->andWhere('r.pricePerson <= :priceMax')
                    ->setParameter('priceMax', $filters['priceMax']);
            }
        }

        // ------------------ DINAMIC ORDER ------------------
        if (!empty($orderBy)) {
            switch ($orderBy) {
                case 'PRICE_ASC':
                    $qb->orderBy('r.price', 'ASC');
                    break;
                case 'PRICE_DESC':
                    $qb->orderBy('r.price', 'DESC');
                    break;
                case 'DURATION_ASC':
                    $qb->orderBy('r.estimatedDuration', 'ASC');
                    break;
                case 'DURATION_DESC':
                    $qb->orderBy('r.estimatedDuration', 'DESC');
                    break;
                default:
                    $qb->orderBy('r.departureDate', 'ASC')
                        ->addOrderBy('r.departureIntendedTime', 'ASC');
            }
        }

        // ------------------   RESULTS ------------------
        $results = $qb->getQuery()->getResult();   // always array

        // ------------------------ FILTER DURATION ------------------------
        if (isset($filters['durationMin']) || isset($filters['durationMax'])) {
            $results = array_filter($results, function ($ride) use ($filters) {
                $duration = $ride->getArrivalDate() && $ride->getDepartureDate()
                    && $ride->getArrivalEstimatedTime() && $ride->getDepartureIntendedTime()
                    ? (
                        ($ride->getArrivalDate()->getTimestamp() + $ride->getArrivalEstimatedTime()->getTimestamp())
                        - ($ride->getDepartureDate()->getTimestamp() + $ride->getDepartureIntendedTime()->getTimestamp())
                    ) / 60
                    : null;

                if ($duration === null) return false;
                if (isset($filters['durationMin']) && $duration < $filters['durationMin']) return false;
                if (isset($filters['durationMax']) && $duration > $filters['durationMax']) return false;
                return true;
            });
        }

        // ------------------ FILTER RATING ------------------
        if (!empty($filters['ratingMin'])) {
            $results = array_filter($results, function ($ride) use ($filters) {
                $driver = $ride->getDriver();
                if (!$driver) return false;

                $avgRating = $this->evaluationRepository->getAverageRatingForDriver($driver->getId());
                return $avgRating !== null && $avgRating >= $filters['ratingMin'];
            });
        }


        // ------------------------ COUNT QUERY (no pagination) ------------------------
        $countQb = clone $qb;
        $countQb
            ->select('COUNT(r.id)')
            ->resetDQLPart('orderBy')
            ->setFirstResult(null)
            ->setMaxResults(null);

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'results' => $results,
            'totalResults' => $total,
        ];
    }

    /**
     * Find rides from $fromDate (inclusive) forward.
     *
     * @param \DateTimeImmutable $fromDate Inclusive lower bound (00:00)
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
