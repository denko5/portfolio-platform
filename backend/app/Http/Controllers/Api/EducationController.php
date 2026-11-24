<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EducationResource;
use App\Models\Education;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EducationController extends Controller
{
    /** Published education entries. */
    public function __invoke(): AnonymousResourceCollection
    {
        return EducationResource::collection(Education::query()->published()->orderBy('sort_order')->get());
    }
}
