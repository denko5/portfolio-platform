<?php

namespace App\Models;

use App\Enums\TechnologyType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

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

    /**
     * Find a technology by slug or by name (ignoring letter case and stray spaces), or create it.
     * Prevents duplicates such as "Power BI" and "Power BI ".
     */
    public static function resolve(string $name, string $type = 'tool'): self
    {
        $name = trim($name);

        return static::query()
            ->where('slug', Str::slug($name))
            ->orWhereRaw('lower(trim(name)) = ?', [Str::lower($name)])
            ->first()
            ?? static::create(['name' => $name, 'slug' => Str::slug($name), 'type' => $type]);
    }
}
