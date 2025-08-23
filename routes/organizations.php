<?php

use Illuminate\Support\Facades\Route;
use Whilesmart\Organizations\Http\Controllers\OrganizationController;

/*
|--------------------------------------------------------------------------
| Organizations API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for organization management. These routes are
| automatically registered by the OrganizationsServiceProvider.
|
*/

// Organization Management (workspace-scoped if enabled)
Route::get('/workspaces/{workspaceId}/organizations', [OrganizationController::class, 'index']);
Route::post('/workspaces/{workspaceId}/organizations', [OrganizationController::class, 'store']);

// Organization Management (standalone routes)
Route::get('/organizations', [OrganizationController::class, 'index']);
Route::post('/organizations', [OrganizationController::class, 'store']);
