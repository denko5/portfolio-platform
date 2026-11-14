<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienceHighlight extends Model
{
    protected $guarded = [];


    public function experience(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Experience::class);
    }
}
