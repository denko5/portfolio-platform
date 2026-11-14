<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use Publishable;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'publication_status' => PublicationStatus::class,
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(\App\Models\ServiceItem::class)->orderBy('sort_order');
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Technology::class);
    }
}
