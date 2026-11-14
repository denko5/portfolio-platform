<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('owner_name', 120);
            $table->string('headline', 160);
            $table->text('intro_text');
            $table->longText('bio_markdown')->nullable();
            $table->string('location', 120)->nullable();
            $table->string('email', 190)->default('');
            $table->string('phone', 40)->nullable();
            $table->string('whatsapp_number', 20)->nullable();
            $table->string('tiktok_handle', 60)->nullable();
            $table->string('tiktok_followers_label', 20)->nullable();
            $table->boolean('overcend_visible')->default(false);
            $table->string('overcend_heading', 120)->nullable();
            $table->text('overcend_body')->nullable();
            $table->string('default_seo_title', 70)->nullable();
            $table->string('default_seo_description', 170)->nullable();
            $table->string('contact_notification_email', 190)->default('');
            $table->timestamps();
        });

        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 30)->unique();
            $table->string('label', 60);
            $table->string('url', 500);
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('site_settings');
    }
};
