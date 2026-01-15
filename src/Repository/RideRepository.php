<?php

namespace App\Repository;

use App\Entity\Ride;
use App\Entity\User;
use App\Repository\EvaluationRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Ride repository with exact, filtered and future searches.
 * Includes filters meta: electric, drivers0, price, duration.
 */
class RideRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ride::class);
    }

    /**
     * Search rides for exact date with optional filters and ordering.
     *
     * @return array{results: Ride[], totalResults: int}
     */
    public function searchExact(
        string $originCity,
        string $destinyCity,
        \DateTimeImmutable $date,
        int $limit = 18,
        int $offset = 0,
        array $filters = [],
        ?string $orderBy = null
    ): array {
        $start = $date->setTime(0, 0, 0);
        $end   = $date->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('r')
            ->andWhere('LOWER(r.originCity) = :origin')
            ->andWhere('LOWER(r.destinyCity) = :destiny')
            ->andWhere('r.departureDate BETWEEN :start AND :end')
            ->andWhere('r.nbPlacesAvailable > 0')
            ->andWhere('r.cancelledAt IS NULL')
            ->setParameter('origin', mb_strtolower($originCity))
            ->setParameter('destiny', mb_strtolower($destinyCity))
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        // Electric
        if (!empty($filters['electricOnly'])) {
            $qb->join('r.vehicle', 'v')
                ->andWhere('v.electric = true');
        }

        // Driver (rating)
        if (isset($filters['ratingMin'])) {
            $qb->join('r.driver', 'd')
                ->andWhere('d.avgRating >= :ratingMin')
                ->setParameter('ratingMin', $filters['ratingMin']);
        }

        // Price
        if (isset($filters['priceMin'])) {
            $qb->andWhere('r.pricePerson >= :priceMin')
                ->setParameter('priceMin', $filters['priceMin']);
        }

        if (isset($filters['priceMax'])) {
            $qb->andWhere('r.pricePerson <= :priceMax')
                ->setParameter('priceMax', $filters['priceMax']);
        }

        // Duration
        if (isset($filters['durationMin'])) {
            $qb->andWhere('r.estimatedDuration >= :durationMin')
                ->setParameter('durationMin', $filters['durationMin']);
        }

        if (isset($filters['durationMax'])) {
            $qb->andWhere('r.estimatedDuration <= :durationMax')
                ->setParameter('durationMax', $filters['durationMax']);
        }


        // Duration ordering using estimatedDuration field
        if (!empty($orderBy)) {
            switch ($orderBy) {
                case 'PRICE_ASC':
                    $qb->orderBy('r.pricePerson', 'ASC');
                    break;
                case 'PRICE_DESC':
                    $qb->orderBy('r.pricePerson', 'DESC');
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
        } else {
            $qb->orderBy('r.departureDate', 'ASC')
                ->addOrderBy('r.departureIntendedTime', 'ASC');
        }

        $qb->setFirstResult($offset)->setMaxResults($limit);
        $results = $qb->getQuery()->getResult();

        // Count query
        $countQb = clone $qb;
        $countQb->resetDQLPart('select')
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

    /**
     * Get global filters meta (rides without filters applied).
     */
    public function getGlobalFiltersMeta(string $originCity, string $destinyCity, \DateTimeImmutable $date): array
    {
        $start = $date->setTime(0, 0, 0);
        $end   = $date->setTime(23, 59, 59);

        $rides = $this->createQueryBuilder('r')
            ->andWhere('LOWER(r.originCity) = :origin')
            ->andWhere('LOWER(r.destinyCity) = :destiny')
            ->andWhere('r.departureDate BETWEEN :start AND :end')
            ->andWhere('r.nbPlacesAvailable > 0')
            ->andWhere('r.cancelledAt IS NULL')
            ->setParameter('origin', mb_strtolower($originCity))
            ->setParameter('destiny', mb_strtolower($destinyCity))
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        $prices = [];
        $durations = [];
        $hasElectric = false;
        $drivers0 = false;

        foreach ($rides as $ride) {
            $prices[] = $ride->getPricePerson();
            $durations[] = $ride->getEstimatedDuration();

            $vehicle = $ride->getVehicle();
            if ($vehicle && $vehicle->isElectric()) $hasElectric = true;

            $driver = $ride->getDriver();
            if ($driver && $driver->getAvgRating() === 0) $drivers0 = true;
        }

        return [
            'electric' => $hasElectric,
            'drivers0' => $drivers0,
            'price' => [
                'min' => !empty($prices) ? (float) min($prices) : 0.0,
                'max' => !empty($prices) ? (float) max($prices) : 0.0,
            ],
            'duration' => [
                'min' => !empty($durations) ? (int) min($durations) : 0,
                'max' => !empty($durations) ? (int) max($durations) : 0,
            ],
        ];
    }

    /**
     * Get filters meta for rides with filters applied.
     */
    public function getFilteredFiltersMeta(string $originCity, string $destinyCity, \DateTimeImmutable $date, array $filters): array
    {
        $start = $date->setTime(0, 0, 0);
        $end   = $date->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('r')
            ->andWhere('LOWER(r.originCity) = :origin')
            ->andWhere('LOWER(r.destinyCity) = :destiny')
            ->andWhere('r.departureDate BETWEEN :start AND :end')
            ->andWhere('r.nbPlacesAvailable > 0')
            ->andWhere('r.cancelledAt IS NULL')
            ->setParameter('origin', mb_strtolower($originCity))
            ->setParameter('destiny', mb_strtolower($destinyCity))
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if (!empty($filters['electricOnly'])) {
            $qb->join('r.vehicle', 'v')->andWhere('v.electric = true');
        }

        $qb->join('r.driver', 'd');

        if (isset($filters['priceMin'])) $qb->andWhere('r.pricePerson >= :pMin')->setParameter('pMin', $filters['priceMin']);
        if (isset($filters['priceMax'])) $qb->andWhere('r.pricePerson <= :pMax')->setParameter('pMax', $filters['priceMax']);
        if (isset($filters['durationMin'])) $qb->andWhere('r.estimatedDuration >= :dMin')->setParameter('dMin', $filters['durationMin']);
        if (isset($filters['durationMax'])) $qb->andWhere('r.estimatedDuration <= :dMax')->setParameter('dMax', $filters['durationMax']);

        if (isset($filters['ratingMin'])) {
            $qb->andWhere('d.avgRating >= :ratingMin')->setParameter('ratingMin', $filters['ratingMin']);
        }

        $rides = $qb->getQuery()->getResult();

        $prices = [];
        $durations = [];
        $hasElectric = false;
        $drivers0 = false;

        foreach ($rides as $ride) {
            $prices[] = $ride->getPricePerson();
            $durations[] = $ride->getEstimatedDuration();

            $vehicle = $ride->getVehicle();
            if ($vehicle && $vehicle->isElectric()) $hasElectric = true;

            $driver = $ride->getDriver();
            if ($driver && $driver->getAvgRating() === 0) $drivers0 = true;
        }

        return [
            'electric' => $hasElectric,
            'drivers0' => $drivers0,
            'price' => [
                'min' => !empty($prices) ? (float) min($prices) : 0.0,
                'max' => !empty($prices) ? (float) max($prices) : 0.0,
            ],
            'duration' => [
                'min' => !empty($durations) ? (int) min($durations) : 0,
                'max' => !empty($durations) ? (int) max($durations) : 0,
            ],
        ];
    }

    /**
     * Search rides from a future date (suggestions, max 6).
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
