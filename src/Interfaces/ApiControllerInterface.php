<?php

namespace Whilesmart\Organizations\Interfaces;

use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Components(
    securitySchemes: [
        new OA\SecurityScheme(
            securityScheme: 'bearerAuth',
            type: 'http',
            description: 'Bearer token authentication',
            scheme: 'bearer'
        ),
    ]
)]
interface ApiControllerInterface
{
    /**
     * Return a success response.
     *
     * @param  mixed  $data
     */
    public function success($data = null, string $message = 'Operation successful', int $statusCode = 200): JsonResponse;

    /**
     * Return a failure response.
     */
    #[OA\Schema(
        schema: 'ErrorResponse',
        properties: [
            new OA\Property(
                property: 'message',
                type: 'string'
            ),
            new OA\Property(
                property: 'errors',
                type: 'array',
                items: new OA\Items(type: 'object', additionalProperties: true)
            ),
        ],
        type: 'object'
    )]
    public function failure(string $message = 'Operation failed', int $statusCode = 400, array $errors = []): JsonResponse;
}
