<?php

namespace Whilesmart\Organizations\Interfaces;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;


#[OA\Schema(
    schema: 'Organization',
    properties: [
        new OA\Property(property: 'id', description: 'ID of the organization', type: 'integer'),
        new OA\Property(property: 'name', description: 'Name of the organization', type: 'string'),
        new OA\Property(property: 'description', description: 'Description of the organization', type: 'string'),
        new OA\Property(property: 'slug', description: 'Organization slug', type: 'string'),
        new OA\Property(property: 'website', description: 'Organization website', type: 'string'),
        new OA\Property(property: 'email', description: 'Organization email', type: 'string'),
        new OA\Property(property: 'phone', description: 'Organization phone', type: 'string'),
        new OA\Property(property: 'type', description: 'Organization type', type: 'string'),
        new OA\Property(property: 'industry', description: 'Organization industry', type: 'string'),
        new OA\Property(property: 'size', description: 'Organization size', type: 'string'),
        new OA\Property(property: 'contact_info', description: 'Organization contact info', type: 'string'),
        new OA\Property(property: 'settings', description: 'Organization settings', type: 'string'),
        new OA\Property(property: 'owner_type', description: 'Organization owner type', type: 'string'),
        new OA\Property(property: 'owner_id', description: 'Organization owner id', type: 'string'),
        new OA\Property(property: 'is_active', description: 'Organization active status', type: 'string'),
        new OA\Property(property: 'created_at', description: 'Date created', type: 'datetime'),
        new OA\Property(property: 'deleted_at', description: 'Date deleted', type: 'datetime'),
    ],
    type: 'object'
)]
interface OrganizationControllerInterface
{
    #[OA\Get(
        path: '/v1/organizations',
        summary: 'Get organizations',
        security: [
            ['sanctum' => []],
        ],
        tags: ['Organization'],
        parameters: [
            new OA\Parameter(
                name: 'per_page',
                description: 'Number of items per page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 20, minimum: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Organizations Loaded',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', properties: [
                            new OA\Property(property: 'current_page', description: 'Current page', type: 'integer', example: 1),
                            new OA\Property(property: 'last_page', description: 'Last page', type: 'integer', example: 1),
                            new OA\Property(property: 'per_page', description: 'Per page', type: 'integer', example: 1),
                            new OA\Property(property: 'total', description: 'Total items', type: 'integer', example: 1),
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Organization')),
                        ], type: 'object'),
                    ]
                )),
            new OA\Response(response: 400, description: 'Bad Request',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 401, description: 'Not Authenticated',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 404, description: 'Not Found',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 500, description: 'Server error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
        ]
    )]
    public function index(Request $request, ?string $workspaceId = null): JsonResponse;

    #[OA\Post(
        path: '/v1/organizations',
        summary: 'Create an organization',
        security: [
            ['sanctum' => []],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(

                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'phone', type: 'string'),
                    new OA\Property(property: 'address', type: 'string'),
                    new OA\Property(property: 'website', type: 'string'),
                ]
            )
        ),
        tags: ['Organization'],
        responses: [
            new OA\Response(response: 201, description: 'Organization created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Organization',
                            type: 'object',
                        ),
                    ],
                    type: 'object'
                )),
            new OA\Response(response: 400, description: 'Bad Request',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 401, description: 'Not Authenticated',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 500, description: 'Server error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
        ]
    )]
    public function store(Request $request, ?string $workspaceId = null): JsonResponse;

    #[OA\Get(
        path: '/v1/organizations/{id}',
        summary: 'Get organization by id',
        security: [
            ['sanctum' => []],
        ],
        tags: ['Organization'],

        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Organization id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Organization loaded',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Organization',
                            type: 'object',
                        ),
                    ],
                    type: 'object'
                )),
            new OA\Response(response: 400, description: 'Bad Request',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 401, description: 'Not Authenticated',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 403, description: 'Unauthorized',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 404, description: 'Not Found',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 500, description: 'Server error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
        ]
    )]
    public function show(Request $request, string $id): JsonResponse;

    #[OA\Delete(
        path: '/v1/organizations/{id}',
        summary: 'Delete organization',
        security: [
            ['sanctum' => []],
        ],
        tags: ['Organization'],

        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Organization id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Organization deleted'),
            new OA\Response(response: 400, description: 'Bad Request',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 401, description: 'Not Authenticated',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 403, description: 'Unauthorized',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 404, description: 'Not Found',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 500, description: 'Server error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
        ]
    )]
    public function destroy(Request $request, string $id): JsonResponse;

    #[OA\Post(
        path: '/v1/organizations/{id}/members',
        summary: 'Add a member to an organization',
        security: [
            ['sanctum' => []],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(

                properties: [
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'role', type: 'string', example: 'admin'),
                ]
            )
        ),
        tags: ['Organization'],

        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Organization id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Member added'),
            new OA\Response(response: 400, description: 'Bad Request',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 401, description: 'Not Authenticated',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 403, description: 'Unauthorized',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 500, description: 'Server error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
        ]
    )]
    public function addMember(Request $request, string $id): JsonResponse;

    #[OA\Get(
        path: '/v1/organizations/{id}/members',
        summary: 'Get organization members',
        security: [
            ['sanctum' => []],
        ],
        tags: ['Organization'],

        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Organization id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Members loaded'),
            new OA\Response(response: 400, description: 'Bad Request',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 401, description: 'Not Authenticated',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 403, description: 'Unauthorized',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 404, description: 'Not Found',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 500, description: 'Server error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
        ]
    )]
    public function getMembers(Request $request, string $id): JsonResponse;

    #[OA\Delete(
        path: '/v1/organizations/{id}/members/{member_id}',
        summary: 'Remove a member from an organization',
        security: [
            ['sanctum' => []],
        ],
        tags: ['Organization'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Organization id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'member_id',
                description: 'Member id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Member removed'),
            new OA\Response(response: 400, description: 'Bad Request',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 401, description: 'Not Authenticated',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 403, description: 'Unauthorized',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 500, description: 'Server error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
        ]
    )]
    public function removeMember(Request $request, string $id, $member_id): JsonResponse;

    #[OA\Put(
        path: '/v1/organizations/{id}',
        summary: 'Create an organization',
        security: [
            ['sanctum' => []],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(

                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'type', type: 'string'),
                    new OA\Property(property: 'email ', type: 'string'),
                    new OA\Property(property: 'phone', type: 'string'),
                    new OA\Property(property: 'address', type: 'string'),
                    new OA\Property(property: 'website', type: 'string'),
                ]
            )
        ),
        tags: ['Organization'],

        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Organization id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Organization updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Organization',
                            type: 'object',
                        ),
                    ],
                    type: 'object'
                )),
            new OA\Response(response: 400, description: 'Bad Request',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 401, description: 'Not Authenticated',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 403, description: 'Unauthorized',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
            new OA\Response(response: 500, description: 'Server error',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    type: 'object'
                )),
        ]
    )]
    public function update(Request $request, string $id, ?string $workspaceId = null): JsonResponse;

}
