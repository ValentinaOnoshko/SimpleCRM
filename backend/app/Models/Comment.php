<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'user_id',
        'content',
        'file_path',
        'file_name',
        'file_type',
    ];

    public function getId(): int
    {
        return $this->getAttribute('id');
    }

    public function getContent(): string
    {
        return $this->getAttribute('content');
    }

    public function getFilePath(): ?string
    {
        return $this->getAttribute('file_path');
    }

    public function getFileName(): ?string
    {
        return $this->getAttribute('file_name');
    }

    public function getFileType(): ?string
    {
        return $this->getAttribute('file_type');
    }

    public function getCreatedAt(): string
    {
        return $this->getAttribute('created_at')->format('d.m.Y H:i');
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
