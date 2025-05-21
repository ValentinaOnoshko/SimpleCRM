<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Deal;
use App\Repositories\Interfaces\DealRepositoryInterface;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;

class DealRepository implements DealRepositoryInterface
{
    public function __construct(private readonly CacheService $cacheService) {}

    public function all(): Collection
    {
        return $this->cacheService->get('deals_all', function () {
            return Deal::all();
        }, 60);
    }

    public function find(int $id): ?Deal
    {
        return $this->cacheService->get("deal_{$id}", function () use ($id) {
            return Deal::find($id);
        }, 60);
    }

    public function create(array $data): Deal
    {
        $deal = Deal::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $data['status'],
            'client_id' => $data['client_id'],
            'performer_id' => $data['performer_id'] ?? null,
        ]);
        $this->cacheService->clear('deals');
        return $deal;
    }

    public function update(Deal $deal, array $data): bool
    {
        $result = $deal->update($data);
        $this->cacheService->clear('deals');
        return $result;
    }

    public function delete(Deal $deal): bool
    {
        $result = $deal->delete();
        $this->cacheService->clear('deals');
        return $result;
    }
}
