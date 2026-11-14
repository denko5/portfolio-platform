<?php

namespace App\Models;

use App\Enums\TechnologyType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Technology extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => TechnologyType::class,
            'is_featured' => 'boolean',
        ];
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Project::class);
    }
}
