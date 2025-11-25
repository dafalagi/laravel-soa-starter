<?php

namespace Modules\Auth\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasModularFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasModularFactory<\Modules\Auth\Database\Factories\UserFactory> */
    use HasModularFactory, Notifiable;

    protected $table = 'auth_users';
    protected $guarded = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user account is active.
     */
    public function isActive(): bool
    {
        // For now, all users are considered active
        // This can be extended to check account status, suspension, etc.
        return true;
    }
}
