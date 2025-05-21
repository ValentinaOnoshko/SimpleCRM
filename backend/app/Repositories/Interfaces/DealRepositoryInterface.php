<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\Deal;
use Illuminate\Database\Eloquent\Collection;

interface DealRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Deal;
    public function create(array $data): Deal;
    public function update(Deal $deal, array $data): bool;
    public function delete(Deal $deal): bool;
}
