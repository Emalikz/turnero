<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'schema',
        'primary_domain',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }
}
