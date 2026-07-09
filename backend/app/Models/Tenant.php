<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\DatabaseConfig;

class Tenant extends Model implements TenantWithDatabase
{
    use HasFactory;

    protected $table = 'tenants';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function ($model): void {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

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
            'data' => 'array',
        ];
    }

    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey()
    {
        return $this->getKey();
    }

    public function database(): DatabaseConfig
    {
        return new DatabaseConfig($this);
    }

    public function run(callable $callback)
    {
        $originalTenant = tenant();

        tenancy()->initialize($this);
        $result = $callback($this);

        if ($originalTenant) {
            tenancy()->initialize($originalTenant);
        } else {
            tenancy()->end();
        }

        return $result;
    }

    public function getInternal(string $key)
    {
        $data = $this->data ?? [];

        return $data['tenancy_' . $key] ?? null;
    }

    public function setInternal(string $key, $value)
    {
        $data = $this->data ?? [];
        $data['tenancy_' . $key] = $value;
        $this->data = $data;

        return $this;
    }
}
