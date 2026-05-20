<?php

namespace App\Controller;

use App\DTO\ContactRequest;
use App\Service\ContactService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use OpenApi\Attributes as OA;

final class ContactController extends AbstractController
{
    #[Route('/api/contact', methods: ['POST'])]
    #[OA\Post(
        path: '/api/contact',
        summary: 'Send a contact request',
        tags: ['Contact'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: [
                        'firstName',
                        'lastName',
                        'email',
                        'reason',
                        'detail',
                        'description',
                        'turnstileToken'
                    ],
                    properties: [

                        new OA\Property(
                            property: 'firstName',
                            type: 'string',
                            maxLength: 255,
                            example: 'Jean'
                        ),

                        new OA\Property(
                            property: 'lastName',
                            type: 'string',
                            maxLength: 255,
                            example: 'Dupont'
                        ),

                        new OA\Property(
                            property: 'email',
                            type: 'string',
                            format: 'email',
                            example: 'jean.dupont@email.com'
                        ),

                        new OA\Property(
                            property: 'reason',
                            type: 'string',
                            maxLength: 255,
                            example: 'Trip issue'
                        ),

                        new OA\Property(
                            property: 'detail',
                            type: 'string',
                            maxLength: 255,
                            example: 'Driver delay'
                        ),

                        new OA\Property(
                            property: 'description',
                            type: 'string',
                            maxLength: 1000,
                            example: 'The driver arrived 30 minutes late.'
                        ),

                        new OA\Property(
                            property: 'rideId',
                            type: 'string',
                            nullable: true,
                            example: 'RIDE_123456'
                        ),

                        new OA\Property(
                            property: 'attachments',
                            type: 'array',
                            items: new OA\Items(
                                type: 'string',
                                format: 'binary'
                            ),
                            description: 'Optional attachments (PDF, JPG, PNG - max 2MB each)'
                        ),

                        new OA\Property(
                            property: 'honeypot',
                            type: 'string',
                            nullable: true,
                            example: ''
                        ),

                        new OA\Property(
                            property: 'turnstileToken',
                            type: 'string',
                            example: '0.xxxxxxxxxxxxxxxxx'
                        ),
                    ]
                )
            )
        ),

        responses: [

            new OA\Response(
                response: 200,
                description: 'Operation processed',
                content: new OA\JsonContent(
                    properties: [

                        new OA\Property(
                            property: 'status',
                            type: 'string',
                            example: 'SUCCESS',
                            enum: [
                                'SUCCESS',
                                'INVALID_INPUT',
                                'INVALID_FIRST_NAME',
                                'INVALID_LAST_NAME',
                                'INVALID_EMAIL',
                                'INVALID_ATTACHMENTS',
                                'INVALID_CAPTCHA',
                                'BOT_DETECTED',
                                'TOO_MANY_ATTEMPTS',
                                'UPLOAD_ERROR',
                                'INTERNAL_ERROR'
                            ]
                        ),

                        new OA\Property(
                            property: 'id',
                            type: 'string',
                            nullable: true,
                            example: '682a1e51f0c123456789abcd'
                        ),

                        new OA\Property(
                            property: 'createdAt',
                            type: 'string',
                            nullable: true,
                            example: '2026-05-19T14:22:00+00:00'
                        ),

                        new OA\Property(
                            property: 'errors',
                            type: 'array',
                            nullable: true,
                            items: new OA\Items(type: 'string'),
                            example: [
                                'email: This value is not a valid email address.'
                            ]
                        ),
                    ]
                )
            ),

            new OA\Response(
                response: 500,
                description: 'Internal server error'
            )
        ]
    )]
    public function create(
        Request $request,
        ContactService $contactService,
    ): JsonResponse {

        // ─────────────────────────────────────
        // Map Request → DTO
        // ─────────────────────────────────────
        $dto = new ContactRequest();

        $dto->firstName = (string) $request->request->get('firstName', '');

        $dto->lastName = (string) $request->request->get('lastName', '');

        $dto->email = (string) $request->request->get('email', '');

        $dto->reason = (string) $request->request->get('reason', '');

        $dto->detail = (string) $request->request->get('detail', '');

        $dto->description = (string) $request->request->get('description', '');

        $dto->rideId = $request->request->get('rideId')
            ? (string) $request->request->get('rideId')
            : null;

        $dto->honeypot = $request->request->get('honeypot')
            ? (string) $request->request->get('honeypot')
            : null;

        $dto->turnstileToken = (string)
        $request->request->get('turnstileToken', '');

        // ─────────────────────────────────────
        // Attachments
        // ─────────────────────────────────────
        $uploadedFiles = $request->files->all('attachments');

        if ($uploadedFiles instanceof UploadedFile) {
            $dto->attachments = [$uploadedFiles];
        } elseif (is_array($uploadedFiles)) {

            foreach ($uploadedFiles as $file) {

                if ($file instanceof UploadedFile) {
                    $dto->attachments[] = $file;
                }
            }
        }

        // ─────────────────────────────────────
        // Service
        // ─────────────────────────────────────
        $response = $contactService->createContact(
            $dto,
            $request->getClientIp() ?? 'unknown'
        );

        // ─────────────────────────────────────
        // HTTP Status
        // ─────────────────────────────────────
        $statusCode = match ($response->status) {

            'INTERNAL_ERROR',
            'UPLOAD_ERROR' => 500,

            default => 200,
        };

        return $this->json($response, $statusCode);
    }
}
