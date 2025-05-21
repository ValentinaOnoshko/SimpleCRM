<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(private readonly UserRepositoryInterface $userRepository) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $user = $this->userRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? 'client',
                'specialization' => ($data['role'] ?? 'client') === 'performer' ? ($data['specialization'] ?? null) : null,
            ]);

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'Пользователь успешно зарегистрирован',
                'user' => new UserResource($user),
                'token' => $token,
            ], 201);
        } catch (Exception $e) {
            Log::error('Registration error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Ошибка при регистрации пользователя',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!$token = JWTAuth::attempt($credentials)) {
            Log::info('Login failed for email: ' . $credentials['email']);
            return response()->json(['message' => 'Неверные учетные данные'], 401);
        }

        /** @var User $user */
        $user = Auth::user();
        Log::info('User logged in: ' . $user->getId());

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Вы вышли из системы']);
    }

    public function refresh(): JsonResponse
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json(['token' => $token]);
        } catch (Exception) {
            return response()->json(['message' => 'Не удалось обновить токен'], 401);
        }
    }
}
