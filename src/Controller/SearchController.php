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
    #[Route('/api/search/rides', name: 'search_rides', methods: ['GET'])]
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
                name: "year",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "integer", example: 2025)
            ),
            new OA\Parameter(
                name: "month",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "integer", example: 12)
            ),
            new OA\Parameter(
                name: "day",
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

        $year  = $request->query->get('year');
        $month = $request->query->get('month');
        $day   = $request->query->get('day');

        // Convert NgbDateStruct query params into array
        $dateStruct = [
            'year'  => $year !== null ? (int)$year : null,
            'month' => $month !== null ? (int)$month : null,
            'day'   => $day !== null ? (int)$day : null,
        ];

        $dto = new RideSearchRequest(
            originCity: $originCity,
            destinyCity: $destinyCity,
            date: $dateStruct,
            page: $page !== null ? (int)$page : 1
        );

        $responseDto = $this->rideSearchService->search($dto);

        return $this->json([
            'status' => $responseDto->status,
            'rides'  => $responseDto->rides,
        ]);
    }
}
