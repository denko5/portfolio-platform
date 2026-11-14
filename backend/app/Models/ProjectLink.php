<?php

namespace App\Models;

use App\Enums\LinkType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectLink extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => LinkType::class,
            'is_visible' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Project::class);
    }
}
