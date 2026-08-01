<?php

use Whilesmart\Organizations\Http\Controllers\OrganizationController;
use Whilesmart\Organizations\Http\Resources\OrganizationResource;
use Whilesmart\Organizations\Models\Organization;
use Whilesmart\Organizations\ResponseFormatters\DefaultResponseFormatter;

return [
    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    */
    'user_model' => env('ORGANIZATIONS_USER_MODEL', 'App\\Models\\User'),
    'workspace_model' => env('ORGANIZATIONS_WORKSPACE_MODEL', 'App\\Models\\Workspace'),
    'model' => Organization::class,

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */
    'register_routes' => env('ORGANIZATIONS_REGISTER_ROUTES', true),
    'route_prefix' => env('ORGANIZATIONS_ROUTE_PREFIX', ''),
    'route_middleware' => ['auth:sanctum'],
    'route_write_middleware' => [],
    'route_actions' => ['index', 'store', 'show', 'update', 'destroy'],
    'register_workspace_routes' => true,
    'register_member_routes' => true,
    'controller' => OrganizationController::class,
    'resource' => OrganizationResource::class,
    'response_formatter' => DefaultResponseFormatter::class,
    'hooks' => [],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */
    'workspace_scoped' => env('ORGANIZATIONS_WORKSPACE_SCOPED', true),
];
