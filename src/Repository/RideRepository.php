<?php

namespace App\Repository;

use App\Entity\Ride;
use App\Repository\EvaluationRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Ride repository with two helper searches:
 *  - searchExact: matches rides within the given date (00:00 — 23:59)
 *  - searchFuture: matches rides from a given date (inclusive) onward
 */
class RideRepository extends ServiceEntityRepository
{
    private EvaluationRepository $evaluationRepository;

    public function __construct(ManagerRegistry $registry, EvaluationRepository $evaluationRepository)
    {
        parent::__construct($registry, Ride::class);
        $this->evaluationRepository = $evaluationRepository;
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
        $durationExpr = "
            TIMESTAMPDIFF(
                MINUTE,
                CONCAT(r.departureDate, ' ', r.departureIntendedTime),
                CONCAT(r.arrivalDate, ' ', r.arrivalEstimatedTime)
            )
        ";
        if (!empty($orderBy)) {
            switch ($orderBy) {
                case 'PRICE_ASC':
                    $qb->orderBy('r.pricePerson', 'ASC');
                    break;
                case 'PRICE_DESC':
                    $qb->orderBy('r.pricePerson', 'DESC');
                    break;
                case 'DURATION_ASC':
                    $qb
                        ->addSelect("$durationExpr AS HIDDEN duration")
                        ->andWhere('r.departureIntendedTime IS NOT NULL')
                        ->andWhere('r.arrivalEstimatedTime IS NOT NULL')
                        ->orderBy('duration', 'ASC');
                    break;

                case 'DURATION_DESC':
                    $qb
                        ->addSelect("$durationExpr AS HIDDEN duration")
                        ->andWhere('r.departureIntendedTime IS NOT NULL')
                        ->andWhere('r.arrivalEstimatedTime IS NOT NULL')
                        ->orderBy('duration', 'DESC');
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
            ->resetDQLPart('select')
            ->resetDQLPart('orderBy')
            ->select('COUNT(r.id)')
            ->setFirstResult(null)
            ->setMaxResults(null);

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'results' => $results,
            'totalResults' => $total,
        ];
    }

    public function getFiltersMeta(
        string $originCity,
        string $destinyCity,
        \DateTimeImmutable $date,
        array $filters = []
    ): array {
        $start = $date->setTime(0, 0, 0);
        $end   = $date->setTime(23, 59, 59);

        // ---------------- PRICE (SQL) ----------------
        $qb = $this->createQueryBuilder('r')
            ->select('MIN(r.pricePerson) AS priceMin', 'MAX(r.pricePerson) AS priceMax')
            ->andWhere('LOWER(r.originCity) = :origin')
            ->andWhere('LOWER(r.destinyCity) = :destiny')
            ->andWhere('r.departureDate BETWEEN :start AND :end')
            ->andWhere('r.nbPlacesAvailable > 0')
            ->andWhere('r.cancelledAt IS NULL')
            ->setParameter('origin', mb_strtolower($originCity))
            ->setParameter('destiny', mb_strtolower($destinyCity))
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        // Apply filter "electricOnly" for price
        if (!empty($filters['electricOnly'])) {
            $qb->join('r.vehicle', 'v')
                ->andWhere('v.electric = true');
        }

        $priceResult = $qb->getQuery()->getSingleResult();

        // ---------------- DURATION (PHP) ----------------
        $qbDur = $this->createQueryBuilder('r')
            ->andWhere('LOWER(r.originCity) = :origin')
            ->andWhere('LOWER(r.destinyCity) = :destiny')
            ->andWhere('r.departureDate BETWEEN :start AND :end')
            ->andWhere('r.nbPlacesAvailable > 0')
            ->andWhere('r.cancelledAt IS NULL')
            ->setParameter('origin', mb_strtolower($originCity))
            ->setParameter('destiny', mb_strtolower($destinyCity))
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        // Apply filter "electricOnly" for duration
        if (!empty($filters['electricOnly'])) {
            $qbDur->join('r.vehicle', 'v')
                ->andWhere('v.electric = true');
        }

        $rides = $qbDur->getQuery()->getResult();

        $durations = [];

        foreach ($rides as $ride) {
            $depDate = $ride->getDepartureDate();
            $arrDate = $ride->getArrivalDate();
            $depTime = $ride->getDepartureIntendedTime();
            $arrTime = $ride->getArrivalEstimatedTime();

            // Ignore incomplete rides
            if (!$depDate || !$arrDate || !$depTime || !$arrTime) {
                continue;
            }

            $dep = (clone $depDate)->setTime((int)$depTime->format('H'), (int)$depTime->format('i'));
            $arr = (clone $arrDate)->setTime((int)$arrTime->format('H'), (int)$arrTime->format('i'));

            $minutes = (int)(($arr->getTimestamp() - $dep->getTimestamp()) / 60);

            if ($minutes > 0) {
                $durations[] = $minutes;
            }
        }

        // Default values
        $minDuration = !empty($durations) ? min($durations) : 10;
        $maxDuration = !empty($durations) ? max($durations) : 1440;

        return [
            'price' => [
                'min' => (float) ($priceResult['priceMin'] ?? 0),
                'max' => (float) ($priceResult['priceMax'] ?? 0),
            ],
            'duration' => [
                'min' => $minDuration,
                'max' => $maxDuration,
            ],
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
