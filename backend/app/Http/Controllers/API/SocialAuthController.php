<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class SocialAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $url = 'https://oauth.vk.com/authorize?' . http_build_query([
                'client_id' => config('services.vk.client_id'),
                'redirect_uri' => config('services.vk.redirect'),
                'response_type' => 'code',
                'scope' => 'email',
                'v' => '5.131'
            ]);

        return redirect($url);
    }

    public function callback(Request $request): JsonResponse
    {
        try {
            $code = $request->input('code');
            $accessToken = $request->input('access_token');
            $userId = $request->input('user_id');
            $role = $request->input('role', 'client');
            $specialization = $request->input('specialization');

            if (!$code && !$accessToken) {
                return response()->json(['error' => 'Authorization data not provided'], 400);
            }

            $tokenData = [];
            if ($accessToken && $userId) {
                $tokenData = [
                    'access_token' => $accessToken,
                    'user_id' => $userId
                ];
            } else {
                $tokenResponse = Http::get('https://oauth.vk.com/access_token', [
                    'client_id' => config('services.vk.client_id'),
                    'client_secret' => config('services.vk.client_secret'),
                    'redirect_uri' => config('services.vk.redirect'),
                    'code' => $code
                ]);

                if (!$tokenResponse->successful()) {
                    Log::error('VK token error', [
                        'response' => $tokenResponse->json(),
                        'status' => $tokenResponse->status()
                    ]);
                    return response()->json(['error' => 'Failed to get VK access token'], 400);
                }

                $tokenData = $tokenResponse->json();
            }

            if (empty($tokenData['access_token']) || empty($tokenData['user_id'])) {
                Log::error('Invalid VK token data', ['data' => $tokenData]);
                return response()->json(['error' => 'Invalid VK response'], 400);
            }

            $userResponse = Http::get('https://api.vk.com/method/users.get', [
                'user_ids' => $tokenData['user_id'],
                'fields' => 'email',
                'access_token' => $tokenData['access_token'],
                'v' => '5.131'
            ]);

            if (!$userResponse->successful()) {
                Log::error('VK user info error', [
                    'response' => $userResponse->json(),
                    'status' => $userResponse->status()
                ]);
                return response()->json(['error' => 'Failed to get VK user info'], 400);
            }

            $userData = $userResponse->json();

            if (!isset($userData['response'][0])) {
                Log::error('Invalid VK user response', ['response' => $userData]);
                return response()->json(['error' => 'Invalid VK user data'], 400);
            }

            $vkUser = $userData['response'][0];

            $email = $tokenData['email'] ?? $vkUser['email'] ?? null;

            $user = User::where('vk_id', $vkUser['id'])->first();

            if (!$user) {
                if ($email) {
                    $user = User::where('email', $email)->first();

                    if ($user) {
                        $user->update(['vk_id' => $vkUser['id']]);
                    }
                }

                if (!$user) {
                    $tempEmail = 'vk_' . $vkUser['id'] . '@temp.user';

                    $user = User::create([
                        'name' => $vkUser['first_name'] . ' ' . $vkUser['last_name'],
                        'email' => $tempEmail,
                        'password' => Hash::make(Str::random(16)),
                        'role' => $role,
                        'specialization' => $role === 'performer' ? $specialization : null,
                        'vk_id' => $vkUser['id'],
                    ]);
                }
            }

            $token = JWTAuth::fromUser($user);

            $needEmailUpdate = !$email || str_contains($user->getEmail(), '@temp.user');

            return response()->json([
                'message' => 'Успешная авторизация через ВКонтакте',
                'user' => new UserResource($user),
                'token' => $token,
                'need_email_update' => $needEmailUpdate,
            ]);

        } catch (Exception $e) {
            Log::error('VK auth error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
