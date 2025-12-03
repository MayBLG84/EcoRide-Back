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
     * Search rides by originCity, destinyCity and date (YYYY-MM-DD).
     *
     * This endpoint returns a JSON object with:
     *  - status: "EXACT_MATCH" | "FUTURE_MATCH" | "NO_MATCH" | "INVALID_REQUEST"
     *  - rides: array of ride objects (each contains driver, vehicle, preferences, etc.)
     */
    #[Route('/api/rides/search', name: 'search_rides', methods: ['GET'])]
    #[OA\Get(
        summary: "Search rides",
        parameters: [
            new OA\Parameter(name: "originCity", in: "query", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "destinyCity", in: "query", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "date", in: "query", required: true, description: "Format: YYYY-MM-DD", schema: new OA\Schema(type: "string", format: "date"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Search results (status + rides)"),
            new OA\Response(response: 400, description: "Invalid request")
        ]
    )]
    public function search(Request $request): JsonResponse
    {
        $dto = new RideSearchRequest(
            originCity: $request->query->get('originCity'),
            destinyCity: $request->query->get('destinyCity'),
            date: $request->query->get('date')
        );

        $responseDto = $this->rideSearchService->search($dto);

        // Return plain array to ensure stable JSON output
        return $this->json([
            'status' => $responseDto->status,
            'rides'  => $responseDto->rides,
        ]);
    }
}
