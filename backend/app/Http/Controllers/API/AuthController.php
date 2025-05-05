<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(private readonly UserRepositoryInterface $userRepository) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($data['role'] === 'performer' && empty($data['specialization'])) {
            return response()->json(['message' => 'Специализация обязательна для исполнителей'], 400);
        }

        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'specialization' => $data['role'] === 'performer' ? $data['specialization'] : null,
        ]);

        return response()->json([
            'message' => 'Пользователь успешно зарегистрирован',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Неверные учетные данные'], 401);
        }

        /* @var User $user*/
        $user = Auth::user();
        $token = $user->createToken('authToken')->accessToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return response()->json(['message' => 'Вы вышли из системы']);
    }
}
