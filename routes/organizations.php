<?php

use Illuminate\Support\Facades\Route;

$controller = config('organizations.controller');
$writeMiddleware = config('organizations.route_write_middleware', []);

if (config('organizations.register_workspace_routes', true)) {
    Route::get('/workspaces/{workspaceId}/organizations', [$controller, 'index']);
    Route::post('/workspaces/{workspaceId}/organizations', [$controller, 'store'])
        ->middleware($writeMiddleware);
}

$routes = Route::apiResource('/organizations', $controller)
    ->only(config('organizations.route_actions'));

if ($writeMiddleware !== []) {
    $routes->middlewareFor(['store', 'update', 'destroy'], $writeMiddleware);
}

if (config('organizations.register_member_routes', true)) {
    Route::get('/organizations/{id}/members', [$controller, 'getMembers']);
    Route::post('/organizations/{id}/members', [$controller, 'addMember'])
        ->middleware($writeMiddleware);
    Route::delete('/organizations/{id}/members/{member_id}', [$controller, 'removeMember'])
        ->middleware($writeMiddleware);
}
