<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'vk_id',
        'specialization',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'vk_id' => 'integer',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->getRole(),
            'email' => $this->getEmail(),
        ];
    }

    public function getId(): int
    {
        return $this->getAttribute('id');
    }

    public function getName(): string
    {
        return $this->getAttribute('name');
    }

    public function getEmail(): string
    {
        return $this->getAttribute('email');
    }

    public function getRole(): string
    {
        return $this->getAttribute('role');
    }

    public function getSpecialization(): ?string
    {
        return $this->getAttribute('specialization');
    }

    public function getRoleEnum(): ?UserRole
    {
        return UserRole::tryFrom($this->getRole());
    }

    public function dealsCreated(): HasMany
    {
        return $this->hasMany(Deal::class, 'client_id');
    }

    public function dealsAssigned(): HasMany
    {
        return $this->hasMany(Deal::class, 'performer_id');
    }
    public function allDeals(): HasMany
    {
        return $this->dealsCreated()->union($this->dealsAssigned());
    }
}
