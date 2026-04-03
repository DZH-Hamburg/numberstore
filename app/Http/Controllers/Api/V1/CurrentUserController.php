<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CurrentUserController extends Controller
{
    #[OA\Get(
        path: '/api/v1/user',
        summary: 'Profil des authentifizierten Benutzers',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Benutzerdaten',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'email', type: 'string', format: 'email'),
                        new OA\Property(property: 'email_verified_at', type: 'string', format: 'date-time', nullable: true),
                        new OA\Property(property: 'avatar_url', type: 'string', format: 'uri'),
                        new OA\Property(property: 'is_platform_admin', type: 'boolean'),
                        new OA\Property(property: 'can_create_groups', type: 'boolean'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Nicht authentifiziert'),
        ]
    )]
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            ...$user->only(['id', 'name', 'email', 'email_verified_at']),
            'avatar_url' => $user->avatarUrl(),
            'is_platform_admin' => $user->is_platform_admin,
            'can_create_groups' => $user->can_create_groups,
        ]);
    }
}
