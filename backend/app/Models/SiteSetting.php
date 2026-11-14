<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SiteSetting extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'overcend_visible' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('portrait');
        $this->addMediaCollection('cv');
        $this->addMediaCollection('default-og-image');
        $this->addMediaCollection('content-showcase');
    }

    /** The settings table holds exactly one row. */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'owner_name' => 'Denis Kombe Karisa',
            'headline' => 'Software Developer | Data Analyst',
            'intro_text' => '',
        ]);
    }
}
