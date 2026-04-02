<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Numberstore API',
    description: 'REST-Schnittstelle der Numberstore-Anwendung (Laravel 13, Sanctum).'
)]
#[OA\Server(
    url: '/',
    description: 'Aktuelle Anwendung'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum',
    description: 'Bearer-Token von POST /api/v1/auth/token (Feld `token`).'
)]
abstract class Controller
{
    use AuthorizesRequests;
}
