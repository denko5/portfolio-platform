<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Article extends Model implements HasMedia
{
    use Publishable, SoftDeletes, InteractsWithMedia;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'publication_status' => PublicationStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ArticleCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Tag::class);
    }

    /** Published means status is published AND the publish date has passed. */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('publication_status', PublicationStatus::Published->value)
            ->where('published_at', '<=', now());
    }
}
