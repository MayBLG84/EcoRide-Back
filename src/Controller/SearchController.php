<?php

namespace App\Controller;

use App\Repository\RideRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/search', name: 'api_search', methods: ['POST'])]
#[OA\Post(
    path: '/api/search',
    summary: 'Search rides by origin city, destination city and date',
    requestBody: new OA\RequestBody(
        description: 'Search parameters',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'originCity', type: 'string', example: 'Paris'),
                new OA\Property(property: 'destinyCity', type: 'string', example: 'Lyon'),
                new OA\Property(property: 'date', type: 'string', format: 'date', example: '2025-11-22'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of rides found',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'origin', type: 'string'),
                        new OA\Property(property: 'destiny', type: 'string'),
                        new OA\Property(property: 'date', type: 'string', format: 'date'),
                    ]
                )
            )
        ),
        new OA\Response(response: 400, description: 'Invalid parameters')
    ]
)]
class SearchController extends AbstractController
{
    private RideRepository $rideRepository;

    public function __construct(RideRepository $rideRepository)
    {
        $this->rideRepository = $rideRepository;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $origin = $data['originCity'] ?? null;
        $destiny = $data['destinyCity'] ?? null;
        $date = $data['date'] ?? null;

        if (!$origin || !$destiny || !$date) {
            return $this->json(['error' => 'Missing parameters'], 400);
        }

        $rides = $this->rideRepository->searchRides($origin, $destiny, $date);

        return $this->json($rides);
    }
}
