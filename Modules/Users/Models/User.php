<?php

namespace Modules\Users\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Modules\Users\Observers\UserObserver;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy(UserObserver::class)]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'dni',
        'phone',
        'email',
        'email_verified_at',
        'avatar',
        'password',
        'remember_token',
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
    ];

    protected static function booted()
    {
        static::saving(function ($user) {
            $user->full_name = "{$user->name} {$user->last_name}";
        });
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar === null ? 'https://api.dicebear.com/10.x/initials/svg?seed='.urlencode($this->full_name) : (Str::startsWith($this->avatar, 'http') ? $this->avatar : Storage::url($this->avatar));
    }

    // The previous `harvests()` relationship was removed: it created a
    // Users → Fields cross-module dependency that nothing in the codebase
    // actually used. If a feature needs it back, expose it via a service
    // in the Fields module rather than re-introducing the coupling.
}
