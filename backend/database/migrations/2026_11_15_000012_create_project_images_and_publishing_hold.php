<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Alt text and the placeholder flag are real columns: Filament's core upload has no per-file metadata.
        Schema::create('project_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('alt', 300);
            $table->boolean('is_placeholder')->default(false);
            $table->string('caption', 200)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['project_id', 'sort_order']);
        });

        // Internal reason that blocks publication (for example, a screenshot that needs correcting).
        Schema::table('projects', function (Blueprint $table) {
            $table->string('publish_block_reason', 300)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('publish_block_reason');
        });

        Schema::dropIfExists('project_images');
    }
};
