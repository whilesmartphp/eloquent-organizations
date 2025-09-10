<?php

namespace Whilesmart\Organizations\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
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
class ApiController extends BaseController
{
    /**
     * Return a success response.
     *
     * @param  mixed  $data
     */
    protected function success($data = null, string $message = 'Operation successful', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

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
    protected function failure(string $message = 'Operation failed', int $statusCode = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
