<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\InvalidStateException;

class SocialAuthController extends Controller
{
    public function redirectToVK(): RedirectResponse
    {
        return Socialite::driver('vkontakte')->redirect();
    }

    public function handleVKCallback(): JsonResponse
    {
        try {
            $socialUser = Socialite::driver('vkontakte')->user();

            $user = User::firstOrCreate(
                ['email' => $socialUser->getEmail()],
                [
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Без имени',
                    'password' => bcrypt(Str::random()),
                    'role' => 'client',
                ]
            );

            $token = $user->createToken('authToken')->accessToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);
        } catch (InvalidStateException) {
            return response()->json([
                'error' => 'Invalid state. Try again.',
            ], 400);
        }
    }
}
