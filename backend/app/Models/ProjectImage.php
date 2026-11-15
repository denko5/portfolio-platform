<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProjectImage extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_placeholder' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Project::class);
    }
}
