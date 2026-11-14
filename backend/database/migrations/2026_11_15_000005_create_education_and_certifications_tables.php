<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education', function (Blueprint $table) {
            $table->id();
            $table->string('institution', 180);
            $table->string('qualification', 180);
            $table->unsignedSmallInteger('start_year');
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('publication_status', 20)->default('draft');
            $table->timestamps();
        });

        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('issuer', 160);
            $table->date('issued_on');
            $table->string('credential_url', 500)->nullable();
            $table->string('note', 160)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('publication_status', 20)->default('draft');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('certifications');
        Schema::dropIfExists('education');
    }
};
