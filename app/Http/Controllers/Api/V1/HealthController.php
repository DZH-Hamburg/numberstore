<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class HealthController extends Controller
{
    #[OA\Get(
        path: '/api/v1/health',
        summary: 'API-Erreichbarkeit prüfen',
        tags: ['Health'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'app', type: 'string', example: 'Numberstore'),
                    ]
                )
            ),
        ]
    )]
    public function show(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'app' => config('app.name'),
        ]);
    }
}
