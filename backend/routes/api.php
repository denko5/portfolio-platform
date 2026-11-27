<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CertificationController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\EducationController;
use App\Http\Controllers\Api\ExperienceController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\TechnologyController;
use App\Http\Middleware\PublicApiCache;
use Illuminate\Support\Facades\Route;

/*
| Public read API, served under /api/v1 (see bootstrap/app.php).
| Only published content is ever returned. Successful responses carry an ETag and a short
| Cache-Control; errors are never cached (see PublicApiCache).
*/
// Contact form submission: flood guard here, stricter per-visitor limits in the controller. Never cached.
Route::post('contact', ContactController::class)->middleware('throttle:contact-attempts');

Route::middleware(['throttle:public-api', PublicApiCache::class])->group(function (): void {
    Route::get('profile', ProfileController::class);
    Route::get('projects', [ProjectController::class, 'index']);
    Route::get('projects/{slug}', [ProjectController::class, 'show']);
    Route::get('services', ServiceController::class);
    Route::get('skills', SkillController::class);
    Route::get('experiences', ExperienceController::class);
    Route::get('education', EducationController::class);
    Route::get('certifications', CertificationController::class);
    Route::get('technologies', TechnologyController::class);
    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/{slug}', [ArticleController::class, 'show']);
});
