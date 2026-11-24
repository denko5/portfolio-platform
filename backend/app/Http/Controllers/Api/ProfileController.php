<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use App\Models\SiteSetting;

class ProfileController extends Controller
{
    /** Site owner details, contact links and site-wide content settings. */
    public function __invoke(): ProfileResource
    {
        return new ProfileResource(SiteSetting::current()->load('media'));
    }
}
