<?php

declare(strict_types = 1);

namespace App\Models;

use App\Enums\DealStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'performer_id',
        'title',
        'topic',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => DealStatus::class
    ];

    public function getId(): int
    {
        return $this->getAttribute('id');
    }

    public function getTitle(): string
    {
        return $this->getAttribute('title');
    }

    public function getTopic(): ?string
    {
        return $this->getAttribute('topic');
    }

    public function getDescription(): string
    {
        return $this->getAttribute('description');
    }

    public function getStatus(): string
    {
        return $this->getAttribute('status');
    }

    public function getStatusValue(): string
    {
        return $this->getStatusEnum()->value;
    }

    public function getStatusLabel(): string
    {
        return $this->getStatusEnum()->label();
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at?->toISOString();
    }

    public function getStatusEnum(): DealStatus
    {
        return DealStatus::from($this->getStatus());
    }

    public function getStatusFormatted(): array
    {
        $status = $this->getStatusEnum();

        return [
            'value' => $status->value,
            'label' => $status->label(),
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performer_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
