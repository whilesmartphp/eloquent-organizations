<?php

namespace Whilesmart\Organizations\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Whilesmart\Organizations\Enums\OrganizationAction;

interface OrganizationHook
{
    public function before(Request $request, OrganizationAction $action): Request;

    public function after(Request $request, JsonResponse $response, OrganizationAction $action): JsonResponse;
}
