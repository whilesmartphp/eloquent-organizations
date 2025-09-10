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
Route::apiResource('/organizations', OrganizationController::class);

// organization members
Route::get('/organizations/{id}/members', [OrganizationController::class, 'getMembers']);
Route::post('/organizations/{id}/members', [OrganizationController::class, 'addMember']);
Route::delete('/organizations/{id}/members/{member_id}', [OrganizationController::class, 'removeMember']);
