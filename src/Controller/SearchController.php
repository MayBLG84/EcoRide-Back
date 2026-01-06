<?php

namespace App\Controller;

use App\DTO\RideSearchRequest;
use App\Service\RideSearchService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    public function __construct(
        private readonly RideSearchService $rideSearchService
    ) {}

    /**
     * Search rides by originCity, destinyCity and date (NgbDateStruct via query string).
     *
     * Returns a JSON object:
     *  - status: "EXACT_MATCH" | "FUTURE_MATCH" | "NO_MATCH" | "INVALID_REQUEST"
     *  - rides: array of ride objects
     */
    #[Route('/api/rides/search', name: 'search_rides', methods: ['GET'])]
    #[OA\Get(
        summary: "Search rides",
        parameters: [
            new OA\Parameter(
                name: "originCity",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "destinyCity",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "date[year]",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "integer", example: 2025)
            ),
            new OA\Parameter(
                name: "date[month]",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "integer", example: 12)
            ),
            new OA\Parameter(
                name: "date[day]",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "integer", example: 26)
            ),
            new OA\Parameter(
                name: "page",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Search results (status + rides)"),
            new OA\Response(response: 400, description: "Invalid request")
        ]
    )]
    public function search(Request $request): JsonResponse
    {
        $originCity  = $request->query->get('originCity');
        $destinyCity = $request->query->get('destinyCity');
        $page        = $request->query->get('page');

        $dateArray = $request->query->all('date');
        if (!is_array($dateArray)) {
            $dateArray = [];
        }

        $dateStruct = [
            'year'  => isset($dateArray['year'])  ? (int)$dateArray['year']  : null,
            'month' => isset($dateArray['month']) ? (int)$dateArray['month'] : null,
            'day'   => isset($dateArray['day'])   ? (int)$dateArray['day']   : null,
        ];

        $filters = $request->query->all('filters');
        if (!is_array($filters)) {
            $filters = [];
        }
        $orderBy = $request->query->get('orderBy', null);

        $dto = new RideSearchRequest(
            originCity: $originCity,
            destinyCity: $destinyCity,
            date: $dateStruct,
            page: $page !== null ? (int)$page : 1,
            filters: $filters,
            orderBy: $orderBy
        );

        $responseDto = $this->rideSearchService->search($dto);

        return $this->json([
            'status' => $responseDto->status,
            'rides'  => $responseDto->rides,
        ]);
    }
}
