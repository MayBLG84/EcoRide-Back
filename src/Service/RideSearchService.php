<?php

namespace App\Service;

use App\DTO\RideSearchRequest;
use App\DTO\RideSearchResponse;
use App\Repository\RideRepository;
use App\Repository\EvaluationRepository;

/**
 * Service responsible for orchestrating ride search logic.
 *
 * Responsibilities:
 * - Validate and sanitize user input from RideSearchRequest
 * - Delegate DB operations to RideRepository
 * - Create thumbnails (if GD is available)
 * - Build consistent RideSearchResponse objects
 *
 * Design notes:
 * - Output format is fully controlled server-side
 * - Fallback search logic (future rides) ensures better UX
 */
class RideSearchService
{
    public function __construct(
        private readonly RideRepository $rideRepository,
        private readonly EvaluationRepository $evaluationRepository,
        private readonly SecurityService $securityService
    ) {}

    /**
     * Execute the search flow:
     *
     * 1. Validate and sanitize input
     * 2. Perform exact-date search (with pagination)
     * 3. If no results, search future rides (limited to 6)
     * 4. If still no results, return NO_MATCH
     * 
     * @param RideSearchRequest $req DTO carrying search parameters from the controller
     * @return RideSearchResponse Response ready for JSON serialization
     */
    public function search(RideSearchRequest $req): RideSearchResponse
    {
        // ------------------ 1. Validate and sanitize input ------------------
        $origin = $req->originCity ?? '';
        $destiny = $req->destinyCity ?? '';
        $dateStr = $req->date ?? null;

        if ($origin === '' || $destiny === '' || $dateStr === null) {
            return new RideSearchResponse('INVALID_REQUEST', []);
        }

        // ------------------ 2. Security validation ------------------
        if (!$this->securityService->isValidCity($origin) || !$this->securityService->isValidCity($destiny)) {
            return new RideSearchResponse('INVALID_CITY', []);
        }

        if (!$this->securityService->isValidDate($dateStr)) {
            return new RideSearchResponse('INVALID_DATE', []);
        }

        // ------------------ 3. Sanitize and normalize ------------------
        $origin = $this->securityService->normalizeString($origin);
        $destiny = $this->securityService->normalizeString($destiny);

        // Convert frontend NgbDateStruct to \DateTimeImmutable 
        $date = $this->securityService->dateStructToDateTimeImmutable($dateStr);
        if ($date === null) {
            return new RideSearchResponse('INVALID_REQUEST', []);
        }

        // ------------------ 4. Pagination handling ------------------

        /**
         * Page is optional in the first request.
         * Defaults to 1 if not provided.
         */
        $page = max(1, (int)($req->page ?? 1));

        $limit = 18;                        // Max number of results per page
        $offset = ($page - 1) * $limit;     // SQL offset

        // ------------------ 5. Exact match search ------------------

        $exact = $this->rideRepository->searchExact($origin, $destiny, $date, $limit, $offset);

        if (!empty($exact['results'])) {
            return new RideSearchResponse(
                status: 'EXACT_MATCH',
                rides: $this->formatRides($exact['results']),
                pagination: [
                    'page' => $page,
                    'limit' => $limit,
                    'totalResults' => $exact['totalResults'],
                ],
                totalResults: $exact['totalResults']
            );
        }

        // ------------------ 6. Future search fallback ------------------

        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));

        /**
         * We do not paginate the fallback results.
         * It always returns max 6 suggestions.
         */
        $future = $this->rideRepository->searchFuture($origin, $destiny, $now, 6);

        if (!empty($future)) {
            return new RideSearchResponse('FUTURE_MATCH', $this->formatRides($future));
        }

        // ------------------ 7. No results ------------------

        return new RideSearchResponse('NO_MATCH', []);
    }

    /**
     * Convert Ride entities into a structured associative array suitable for JSON.
     *
     * Steps:
     * - Safe extraction of driver, vehicle and preferences
     * - Thumbnail generation (GD or fallback)
     * - Average rating lookup
     * - Duration computation
     *
     * @param array<int, \App\Entity\Ride> $rides
     * @return array<int,array<string,mixed>>
     */
    private function formatRides(array $rides): array
    {
        $out = [];

        foreach ($rides as $ride) {

            // ------------------ DRIVER ------------------
            $driver = $ride->getDriver();

            // Photo thumbnail generation
            $thumbnailBase64 = $this->generateDriverThumbnail($driver?->getPhoto());

            // Rating
            $avgRating = null;
            if ($driver && $driver->getId() !== null) {
                $avg = $this->evaluationRepository->getAverageRatingForDriver($driver->getId());
                $avgRating = $avg !== null ? round((float)$avg, 1) : 0.0;
            }

            // ------------------ DATE / TIME ------------------
            $dateStr = $ride->getDepartureDate()?->format('d/m/Y');
            $departureTime = $ride->getDepartureIntendedTime()?->format('H:i');

            // Duration (HH:MM)
            $duration = $this->computeDuration($ride);

            // ------------------ VEHICLE ------------------
            $vehicle = $ride->getVehicle();

            $vehicleBrand = $vehicle?->getVehicleBrand()?->getBrand();
            $vehicleModel = $vehicle?->getModel();
            $vehicleIsElectric = $vehicle ? (bool)$vehicle->isElectric() : false;

            // ------------------ RESULT MAP ------------------
            $out[] = [
                'id' => $ride->getId(),
                'driver' => [
                    'id' => $driver?->getId(),
                    'nickname' => $driver?->getNickname(),
                    'photoThumbnail' => $thumbnailBase64,
                    'avgRating' => $avgRating,
                ],
                'date' => $dateStr,
                'departureTime' => $departureTime,
                'availableSeats' => $ride->getNbPlacesAvailable(),
                'origin' => [
                    'city' => $ride->getOriginCity(),
                    'pickPoint' => $ride->getPickPoint(),
                ],
                'destiny' => [
                    'city' => $ride->getDestinyCity(),
                    'dropPoint' => $ride->getDropPoint(),
                ],
                'estimatedDuration' => $duration,
                'vehicle' => [
                    'brand' => $vehicleBrand,
                    'model' => $vehicleModel,
                    'isElectric' => $vehicleIsElectric,
                ],
                'preferences' => [
                    'smoker' => $ride->isSmokersAllowed(),
                    'animals' => $ride->isAnimalsAllowed(),
                    'other' => $ride->getOtherPreferences(),
                ],
                'pricePerPerson' => $ride->getPricePerson()
            ];
        }

        return $out;
    }

    /**
     * Generate a thumbnail for the driver's photo.
     * Uses GD if available, otherwise returns the original BLOB base64.
     *
     * @param mixed $raw BLOB or resource
     * @return string|null data:image/jpeg;base64,... | null
     */
    private function generateDriverThumbnail(mixed $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        // Convert stream to string
        if (is_resource($raw)) {
            $raw = stream_get_contents($raw);
        }

        if (!is_string($raw) || $raw === '') {
            return null;
        }

        // Try GD thumbnail generation
        if (function_exists('imagecreatefromstring') && function_exists('imagesx')) {
            try {
                $img = imagecreatefromstring($raw);
                if ($img !== false) {

                    $w = imagesx($img);
                    $h = imagesy($img);

                    $thumbW = 100;
                    $thumbH = (int) round(($thumbW / max($w, 1)) * $h);

                    $thumb = imagecreatetruecolor($thumbW, $thumbH);

                    imagealphablending($thumb, false);
                    imagesavealpha($thumb, true);

                    imagecopyresampled($thumb, $img, 0, 0, 0, 0, $thumbW, $thumbH, $w, $h);

                    ob_start();
                    imagejpeg($thumb, null, 75);
                    $thumbData = ob_get_clean();

                    if ($thumbData) {
                        return 'data:image/jpeg;base64,' . base64_encode($thumbData);
                    }
                }
            } catch (\Throwable) {
                // fallback below
            }
        }

        // Fallback – return original blob
        return 'data:image/jpeg;base64,' . base64_encode($raw);
    }

    /**
     * Compute ride duration in HH:MM format.
     *
     * @param \App\Entity\Ride $ride
     * @return string|null
     */
    private function computeDuration($ride): ?string
    {
        $depDate = $ride->getDepartureDate();
        $arrDate = $ride->getArrivalDate();
        $depTime = $ride->getDepartureIntendedTime();
        $arrTime = $ride->getArrivalEstimatedTime();

        if (!$depDate || !$arrDate || !$depTime || !$arrTime) {
            return null;
        }

        $dep = (clone $depDate)->setTime(
            (int)$depTime->format('H'),
            (int)$depTime->format('i'),
            (int)$depTime->format('s')
        );

        $arr = (clone $arrDate)->setTime(
            (int)$arrTime->format('H'),
            (int)$arrTime->format('i'),
            (int)$arrTime->format('s')
        );

        $interval = $dep->diff($arr);

        $hours = $interval->d * 24 + $interval->h;
        $minutes = $interval->i;

        return sprintf('%02d:%02d', $hours, $minutes);
    }
}
