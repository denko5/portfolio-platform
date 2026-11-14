<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_builds', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20);
            $table->string('trigger', 20);
            $table->string('release_name', 40)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('error_excerpt', 1000)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['status', 'created_at']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('site_builds');
    }
};
