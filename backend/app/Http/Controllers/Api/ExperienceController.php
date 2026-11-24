<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExperienceResource;
use App\Models\Experience;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExperienceController extends Controller
{
    /** Published work experience, newest first by the owner-defined order. */
    public function __invoke(): AnonymousResourceCollection
    {
        return ExperienceResource::collection(
            Experience::query()->published()->with(['highlights', 'technologies'])->orderBy('sort_order')->get()
        );
    }
}
