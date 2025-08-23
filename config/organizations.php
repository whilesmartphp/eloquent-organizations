<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    */
    'user_model' => env('ORGANIZATIONS_USER_MODEL', 'App\\Models\\User'),
    'workspace_model' => env('ORGANIZATIONS_WORKSPACE_MODEL', 'App\\Models\\Workspace'),

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */
    'register_routes' => env('ORGANIZATIONS_REGISTER_ROUTES', true),
    'route_prefix' => env('ORGANIZATIONS_ROUTE_PREFIX', ''),
    'route_middleware' => ['auth:sanctum'],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */
    'workspace_scoped' => env('ORGANIZATIONS_WORKSPACE_SCOPED', true),
];
