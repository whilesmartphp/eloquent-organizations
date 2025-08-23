<?php

namespace Whilesmart\Organizations\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Whilesmart\Organizations\Models\Organization;

class OrganizationController extends Controller
{
    public function index(Request $request, ?string $workspaceId = null): JsonResponse
    {
        $query = Organization::query();

        if ($workspaceId && config('organizations.workspace_scoped', true)) {
            $query->where('workspace_id', $workspaceId);

            // Check workspace access if workspace package is available
            if (class_exists('Whilesmart\\Workspaces\\Models\\Workspace')) {
                if (! auth()->user()->hasRole('workspace-member', 'Whilesmart\\Workspaces\\Models\\Workspace', $workspaceId) &&
                    ! auth()->user()->hasRole('workspace-owner', 'Whilesmart\\Workspaces\\Models\\Workspace', $workspaceId) &&
                    ! auth()->user()->hasRole('workspace-admin', 'Whilesmart\\Workspaces\\Models\\Workspace', $workspaceId)) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
            }
        }

        $organizations = $query->get()->map(function ($organization) {
            return [
                'id' => $organization->id,
                'name' => $organization->name,
                'type' => $organization->type,
                'email' => $organization->email,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $organizations,
        ]);
    }

    public function store(Request $request, ?string $workspaceId = null): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:organization,individual',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($workspaceId && config('organizations.workspace_scoped', true)) {
            // Check workspace access if workspace package is available
            if (class_exists('Whilesmart\\Workspaces\\Models\\Workspace')) {
                if (! auth()->user()->hasRole('workspace-member', 'Whilesmart\\Workspaces\\Models\\Workspace', $workspaceId) &&
                    ! auth()->user()->hasRole('workspace-owner', 'Whilesmart\\Workspaces\\Models\\Workspace', $workspaceId) &&
                    ! auth()->user()->hasRole('workspace-admin', 'Whilesmart\\Workspaces\\Models\\Workspace', $workspaceId)) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
            }
        }

        $organizationData = [
            'name' => $request->name,
            'type' => $request->type,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'website' => $request->website,
        ];

        if ($workspaceId && config('organizations.workspace_scoped', true)) {
            $organizationData['workspace_id'] = $workspaceId;
        }

        $organization = Organization::create($organizationData);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'type' => $organization->type,
                'email' => $organization->email,
            ],
        ], 201);
    }
}
