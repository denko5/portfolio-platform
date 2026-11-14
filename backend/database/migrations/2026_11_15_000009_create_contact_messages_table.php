<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->char('reference', 26)->unique();
            $table->string('name', 120);
            $table->string('email', 190);
            $table->string('organisation', 160)->nullable();
            $table->string('enquiry_type', 30);
            $table->string('subject', 190);
            $table->text('message');
            $table->string('budget_range', 40)->nullable();
            $table->timestamp('privacy_accepted_at');
            $table->string('status', 20)->default('new');
            $table->text('admin_notes')->nullable();
            $table->char('ip_hash', 64)->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
