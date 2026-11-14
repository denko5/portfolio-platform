<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->string('organisation', 160);
            $table->string('role_title', 160);
            $table->string('location', 120)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('summary')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('publication_status', 20)->default('draft');
            $table->timestamps();
        });

        Schema::create('experience_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experience_id')->constrained()->cascadeOnDelete();
            $table->string('body', 400);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('experience_technology', function (Blueprint $table) {
            $table->foreignId('experience_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->constrained()->cascadeOnDelete();
            $table->primary(['experience_id', 'technology_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('experience_technology');
        Schema::dropIfExists('experience_highlights');
        Schema::dropIfExists('experiences');
    }
};
