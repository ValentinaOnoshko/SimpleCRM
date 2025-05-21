<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\CacheService;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly CacheService $cacheService) {}

    public function create(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'specialization' => $data['specialization'] ?? null,
            'vk_id' => $data['vk_id'] ?? null,
        ]);
        $this->cacheService->clear('users');
        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->cacheService->get("user_email_{$email}", function () use ($email) {
            return User::where('email', $email)->first();
        }, 60);
    }
}
