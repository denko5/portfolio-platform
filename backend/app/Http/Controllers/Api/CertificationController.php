<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CertificationResource;
use App\Models\Certification;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CertificationController extends Controller
{
    /** Published certifications. */
    public function __invoke(): AnonymousResourceCollection
    {
        return CertificationResource::collection(Certification::query()->published()->orderBy('sort_order')->get());
    }
}
