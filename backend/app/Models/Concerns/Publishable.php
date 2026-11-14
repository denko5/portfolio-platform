<?php

namespace App\Models\Concerns;

use App\Enums\PublicationStatus;
use Illuminate\Database\Eloquent\Builder;

trait Publishable
{
    /** Only published rows. Drafts must never reach the public API. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('publication_status', PublicationStatus::Published->value);
    }

    public function isPublished(): bool
    {
        return $this->publication_status === PublicationStatus::Published;
    }
}
