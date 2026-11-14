<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('title', 160);
            $table->foreignId('category_id')->constrained('project_categories')->restrictOnDelete();
            $table->string('summary', 500);
            $table->text('problem')->nullable();
            $table->text('objectives')->nullable();
            $table->text('role')->nullable();
            $table->text('approach')->nullable();
            $table->text('challenges')->nullable();
            $table->text('outcomes')->nullable();
            $table->text('lessons')->nullable();
            $table->longText('description_markdown')->nullable();
            $table->string('status_label', 40)->nullable();
            $table->text('public_note')->nullable();
            $table->string('link_note', 120)->nullable();
            $table->string('publication_status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('seo_title', 70)->nullable();
            $table->string('seo_description', 170)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['publication_status', 'sort_order']);
            $table->index('is_featured');
        });

        Schema::create('project_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('body', 300);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['project_id', 'sort_order']);
        });

        Schema::create('project_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('label', 80);
            $table->string('url', 500);
            $table->string('type', 20)->default('other');
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_technology', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['project_id', 'technology_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('project_technology');
        Schema::dropIfExists('project_links');
        Schema::dropIfExists('project_features');
        Schema::dropIfExists('projects');
    }
};
