<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TwoFactorMethod;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorEmailCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use OTPHP\TOTP;

class AuthTokenController extends Controller
{
    public function __construct(
        private TwoFactorEmailCodeService $emailCodeService,
    ) {}

    #[OA\Post(
        path: '/api/v1/auth/token',
        summary: 'API-Token per E-Mail, Passwort und zweitem Faktor ausstellen',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(
                        property: 'otp',
                        description: '6-stelliger Code (E-Mail oder Authenticator-App). Beim ersten Aufruf ohne otp wird bei Methode E-Mail ein Code gesendet.',
                        type: 'string',
                        example: '123456',
                        nullable: true
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token ausgestellt',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string'),
                        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                        new OA\Property(
                            property: 'user',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'email', type: 'string'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validierungsfehler oder ungültige Zugangsdaten / 2FA'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'otp' => ['sometimes', 'nullable', 'string', 'regex:/^\d{6}$/'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $otp = $request->input('otp');
        if ($otp === null || $otp === '') {
            if ($user->two_factor_method === TwoFactorMethod::Email) {
                $this->emailCodeService->ensureLoginCodeSent($user);
            }

            throw ValidationException::withMessages([
                'otp' => [
                    $user->two_factor_method === TwoFactorMethod::Email
                        ? __('Gib den 6-stelligen Code aus der Login-E-Mail im Feld „otp“ an. Bei Methode E-Mail wurde ein Code versendet.')
                        : __('Gib den 6-stelligen Code aus deiner Authenticator-App im Feld „otp“ an.'),
                ],
            ]);
        }

        $valid = match ($user->two_factor_method) {
            TwoFactorMethod::Email => $this->emailCodeService->verifyLoginCode($user, $otp),
            TwoFactorMethod::Totp => $this->verifyTotp($user, $otp),
        };

        if (! $valid) {
            throw ValidationException::withMessages([
                'otp' => [__('Ungültiger oder abgelaufener Code.')],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->only(['id', 'name', 'email']),
        ]);
    }

    private function verifyTotp(User $user, string $code): bool
    {
        if ($user->two_factor_confirmed_at === null) {
            return false;
        }

        $secret = $user->two_factor_secret;
        if (! is_string($secret) || $secret === '') {
            return false;
        }

        $totp = TOTP::createFromSecret($secret);

        return $totp->verify($code, null, 1);
    }
}
