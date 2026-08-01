<?php

namespace Whilesmart\Organizations\Contracts;

use Illuminate\Http\JsonResponse;

interface ResponseFormatter
{
    public function success(mixed $data = null, string $message = 'Operation successful', int $statusCode = 200): JsonResponse;

    public function failure(string $message = 'Operation failed', int $statusCode = 400, array $errors = []): JsonResponse;
}
