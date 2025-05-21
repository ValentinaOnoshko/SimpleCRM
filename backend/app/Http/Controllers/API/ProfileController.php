<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function __construct(private readonly CacheService $cacheService) {}

    public function show(Request $request): JsonResponse
    {
        $userId = $request->user()->getId();
        $cacheKey = $this->cacheService->generateCacheKey('profile', ['user_id' => $userId]);

        $user = $this->cacheService->get($cacheKey, function () use ($request) {
            return $request->user()->load('deals.creator');
        }, 60);

        return response()->json([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'registrationDate' => $user->getRegistrationDate(),
            'role' => $user->getRole(),
            'specialization' => $user->getSpecialization(),
            'avatar' => $user->getAvatarUrl(),
            'deals' => $user->deals->map(function ($deal) {
                return [
                    'id' => $deal->getId(),
                    'title' => $deal->getTitle(),
                    'description' => $deal->getDescription(),
                    'status' => [
                        'value' => $deal->getStatusValue(),
                        'label' => $deal->getStatusLabel()
                    ],
                    'created_at' => $deal->getCreatedAt(),
                    'creator' => $deal->creator ? ['name' => $deal->creator->getName()] : null
                ];
            })
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->getId()],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048']
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarPath = $avatar->storeAs('avatars', Str::uuid() . '.' .
                $avatar->getClientOriginalExtension(), 'public');

            if ($user->getAvatar() && Storage::disk('public')->exists($user->getAvatar())) {
                Storage::disk('public')->delete($user->getAvatar());
            }

            $validated['avatar'] = $avatarPath;
        }

        $user->update($validated);
        $this->cacheService->clear('profile');
        return response()->json([
            'message' => 'Профиль обновлён успешно',
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'specialization' => $user->getSpecialization(),
                'avatar' => $user->getAvatarUrl()
            ]
        ]);
    }
}
