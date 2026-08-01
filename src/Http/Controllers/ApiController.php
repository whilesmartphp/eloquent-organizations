<?php

namespace Whilesmart\Organizations\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Whilesmart\Organizations\Contracts\ResponseFormatter;
use Whilesmart\Organizations\Interfaces\ApiControllerInterface;

class ApiController extends BaseController implements ApiControllerInterface
{
    /**
     * Return a success response.
     *
     * @param  mixed  $data
     */
    public function success($data = null, string $message = 'Operation successful', int $statusCode = 200): JsonResponse
    {
        return app(ResponseFormatter::class)->success($data, $message, $statusCode);
    }

    /**
     * Return a failure response.
     */
    public function failure(string $message = 'Operation failed', int $statusCode = 400, array $errors = []): JsonResponse
    {
        return app(ResponseFormatter::class)->failure($message, $statusCode, $errors);
    }
}
