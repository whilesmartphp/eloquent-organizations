<?php

namespace Whilesmart\Organizations\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Whilesmart\Organizations\Events\OrganizationCreatedEvent;
use Whilesmart\Organizations\Events\OrganizationUpdatedEvent;
use Whilesmart\Organizations\Interfaces\OrganizationControllerInterface;
use Whilesmart\Organizations\Models\Organization;
use Whilesmart\Roles\Models\RoleAssignment;

class OrganizationController extends ApiController implements OrganizationControllerInterface
{
    public function index(Request $request, ?string $workspaceId = null): JsonResponse
    {
        $query = Organization::query();

        if ($workspaceId && config('organizations.workspace_scoped', true)) {
            $query->where('workspace_id', $workspaceId);

            // Check workspace access if workspace package is available
            if (class_exists('Whilesmart\\Workspaces\\Models\\Workspace')) {
                if (!auth()->user()->hasRole('workspace-member', 'Whilesmart\\Workspaces\\Models\\Workspace', $workspaceId) &&
                    !auth()->user()->hasRole('workspace-owner', 'Whilesmart\\Workspaces\\Models\\Workspace', $workspaceId) &&
                    !auth()->user()->hasRole('workspace-admin', 'Whilesmart\\Workspaces\\Models\\Workspace', $workspaceId)) {
                    return $this->failure('Unauthorized', 403);
                }
            }
        }

        $user = $request->user();
        $organization_ids = $user->roleAssignments()->where('context_type', 'organization')->pluck('context_id')->toArray();

        $per_page = $request->get('per_page', 10);
        $organizations = $query->wherein('id', $organization_ids)->paginate($per_page);

        return $this->success($organizations);
    }

    public function store(Request $request, ?string $workspaceId = null): JsonResponse
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:organization,individual',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return $this->failure(
                'The given data was invalid.',
                422,
                $validator->errors()->toArray());
        }

        if ($workspaceId && config('organizations.workspace_scoped', true)) {
            // Check workspace access if workspace package is available
            if (class_exists('Whilesmart\\Workspaces\\Models\\Workspace')) {
                if (!auth()->user()->hasRole('workspace-member', 'Whilesmart\\Workspaces\\Models\\Workspace', $workspaceId) &&
                    !auth()->user()->hasRole('workspace-owner', 'Whilesmart\\Workspaces\\Models\\Workspace', $workspaceId) &&
                    !auth()->user()->hasRole('workspace-admin', 'Whilesmart\\Workspaces\\Models\\Workspace', $workspaceId)) {
                    return $this->failure('Unauthorized', 403);
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
            'owner_type' => get_class($user),
            'owner_id' => $user->id,
            'slug' => Str::slug($request->name)

        ];

        if ($workspaceId && config('organizations.workspace_scoped', true)) {
            $organizationData['workspace_id'] = $workspaceId;
        }

        $organization = Organization::where('owner_type', config('organizations.user_model'))->where('owner_id', $user->id)->where('name', $request->get('name'))->first();
        if ($organization) {
            return $this->failure('Organization name already exists.', 400);
        }

        $organization = Organization::create($organizationData);
        // assign this user the owner role

         $user->assignRole("owner", "organization", $organization->id);

        $organization->refresh();
        OrganizationCreatedEvent::dispatch($organization);

        return $this->success($organization, 'Organization Created', 201);

    }

    public function show(Request $request, string $id): JsonResponse
    {
        $organization = Organization::find($id);
        if ($organization) {
            $user = $request->user();
            // check if I am a member of this organization
            if ($user->hasRole('owner', 'organization', $id) || $user->hasRole('admin', 'organization', $id) || $user->hasRole('member', 'organization', $id)) {
                return $this->success($organization);
            } else {
                return $this->failure('You are not authorized to access this resource.', 403);
            }
        } else {
            return $this->failure('This organization does not exist', 404);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {

        $organization = Organization::find($id);
        if ($organization) {
            $user = $request->user();
            // check if I am the owner of this organization
            if ($user->hasRole('owner', 'organization', $id)) {
                $organization->delete();

                return $this->success(null, 'Organization Deleted');
            } else {
                return $this->failure('You are not authorized to access this resource.', 403);
            }
        } else {
            return $this->failure('This organization does not exist', 404);
        }
    }

    public function addMember(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required',
            'role' => 'nullable|string|in:member,admin',
        ]);

        $organization = Organization::find($id);
        if ($organization) {
            $user_to_invite = config('organizations.user_model')::where('email', $data['email'])->first();
            if ($user_to_invite) {
                $user = $request->user();

                // check if I am an admin of this organization
                if ($user->hasRole('owner', 'organization', $id) || $user->hasRole('admin', 'organization', $id)) {
                    // check if this member already belongs to this organization
                    if ($user_to_invite->hasRole('member', 'organization', $id) || $user_to_invite->hasRole('admin', 'organization', $id)) {
                        return $this->failure('This person has already been invited to this organization.', 400);
                    } else {
                        $user_to_invite->assignRole($data['role'], 'organization', $id);

                        // todo: Send a notification or email to this user.
                        return $this->success(message: 'Member Added');
                    }
                } else {
                    return $this->failure('You are not authorized to access this resource.', 403);
                }
            } else {
                return $this->failure('We could not find a user account with this email.', 400);
            }

        } else {
            return $this->failure('This organization does not exist', 404);
        }
    }

    public function getMembers(Request $request, string $id): JsonResponse
    {

        $organization = Organization::find($id);
        if ($organization) {

            $user = $request->user();

            // check if this member already belongs to this organization
            if ($user->hasRole('owner', 'organization', $id) || $user->hasRole('admin', 'organization', $id) || $user->hasRole('member', 'organization', $id)) {

                $member_ids = RoleAssignment::where('context_type', 'organization')
                    ->where('context_id', $id)->pluck('assignable_id')->toArray();

                $members = config('organizations.user_model')::whereIn('id', $member_ids)->get();
                return $this->success($members);

            } else {
                return $this->failure('You are not authorized to access this resource.', 403);
            }

        } else {
            return $this->failure('This organization does not exist', 404);
        }
    }

    public function removeMember(Request $request, string $id, $member_id): JsonResponse
    {
        $organization = Organization::find($id);
        if ($organization) {
            $user = $request->user();
            // check if I am an admin of this organization
            if ($user->hasRole('owner', 'organization', $id) || $user->hasRole('admin', 'organization', $id)) {
                // check if this member belongs to this organization
                $member = config('organizations.user_model')::find($member_id);
                if ($member) {
                    // prevent the owner from being deleted
                    if ($member->hasRole('owner', 'organization', $id)) {
                        return $this->failure('You cannot remove this member from this organization.', 403);
                    }

                    if ($member->hasRole('member', 'organization', $id) || $user->hasRole('admin', 'organization', $id)) {
                        // ensure user is not trying to remove himself
                        if ($user->id == $member->id) {
                            return $this->failure(message: "You can't remove yourself");
                        }

                        if ($member->hasRole('member', 'organization', $id)) {
                            $member->removeRole('member', 'organization', $id);
                        } else {
                            $member->removeRole('admin', 'organization', $id);
                        }

                        // todo: Send a notification or email to this user.
                        return $this->success(message: 'Member Deleted');
                    } else {
                        return $this->failure('This person has not been invited to this organization.', 400);
                    }
                } else {
                    return $this->failure('This user does not exist.', 400);
                }

            } else {
                return $this->failure('You are not authorized to access this resource.', 403);
            }
        } else {
            return $this->failure('This organization does not exist', 404);
        }
    }


    public function update(Request $request, string $id, ?string $workspaceId = null): JsonResponse
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
            return $this->failure(
                'The given data was invalid.',
                422,
                $validator->errors()->toArray());
        }

        if ($workspaceId && config('organizations.workspace_scoped', true)) {
            // Check workspace access if workspace package is available
            if (class_exists('Whilesmart\\Workspaces\\Models\\Workspace')) {
                if (!auth()->user()->hasRole('workspace-member', 'Whilesmart\\Workspaces\\Models\\Workspace', $workspaceId) &&
                    !auth()->user()->hasRole('workspace-owner', 'Whilesmart\\Workspaces\\Models\\Workspace', $workspaceId) &&
                    !auth()->user()->hasRole('workspace-admin', 'Whilesmart\\Workspaces\\Models\\Workspace', $workspaceId)) {
                    return $this->failure('Unauthorized', 403);
                }
            }
        }

        $user = $request->user();
        if ($user->hasRole('owner', 'organization', $id) || $user->hasRole('admin', 'organization', $id)) {
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

            $organization = Organization::find($id);
            if ($organization) {
                $organization->update($organizationData);
                OrganizationUpdatedEvent::dispatch($organization);

                return $this->success($organization, 'Organization Updated', 200);
            } else {
                return $this->failure('Organization not found', 404);
            }
        } else {
            return $this->failure('Unauthorized', 403);
        }
    }

}
