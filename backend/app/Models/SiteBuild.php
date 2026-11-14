<?php

namespace App\Models;

use App\Enums\BuildStatus;
use App\Enums\BuildTrigger;
use Illuminate\Database\Eloquent\Model;

class SiteBuild extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'status' => BuildStatus::class,
            'trigger' => BuildTrigger::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $build): void {
            $build->created_at ??= now();
        });
    }
}
