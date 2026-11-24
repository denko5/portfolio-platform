<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TechnologyResource;
use App\Models\Technology;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TechnologyController extends Controller
{
    /** All technologies with type and featured flag, for filters and the scrolling banner. */
    public function __invoke(): AnonymousResourceCollection
    {
        return TechnologyResource::collection(Technology::query()->orderBy('sort_order')->orderBy('name')->get());
    }
}
