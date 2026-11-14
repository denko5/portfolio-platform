<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('title', 120);
            $table->string('summary', 300);
            $table->string('icon_key', 40);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('publication_status', 20)->default('draft');
            $table->timestamps();
        });

        Schema::create('service_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('body', 200);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('service_technology', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->constrained()->cascadeOnDelete();
            $table->primary(['service_id', 'technology_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('service_technology');
        Schema::dropIfExists('service_items');
        Schema::dropIfExists('services');
    }
};
