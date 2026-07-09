<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class TenantUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'must_change_password',
        'password_reset_token',
        'password_reset_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'password_reset_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_reset_expires_at' => 'datetime',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'password' => 'hashed',
        ];
    }
}
