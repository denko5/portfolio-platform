<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Project extends Model implements HasMedia
{
    use Publishable, SoftDeletes, InteractsWithMedia;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'publication_status' => PublicationStatus::class,
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('downloads');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ProjectCategory::class, 'category_id');
    }

    public function features(): HasMany
    {
        return $this->hasMany(\App\Models\ProjectFeature::class)->orderBy('sort_order');
    }

    public function links(): HasMany
    {
        return $this->hasMany(\App\Models\ProjectLink::class)->orderBy('sort_order');
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Technology::class)->withPivot('sort_order')->orderByPivot('sort_order');
    }
}
