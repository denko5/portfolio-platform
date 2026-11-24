<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceController extends Controller
{
    /** Published services with their items and technologies. */
    public function __invoke(): AnonymousResourceCollection
    {
        return ServiceResource::collection(
            Service::query()->published()->with(['items', 'technologies'])->orderBy('sort_order')->get()
        );
    }
}
