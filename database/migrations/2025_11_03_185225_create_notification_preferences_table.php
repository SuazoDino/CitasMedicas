<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('email_opt_in')->default(true);
            $table->boolean('sms_opt_in')->default(false);
            $table->string('sms_number', 30)->nullable();
            $table->timestamp('sms_opted_in_at')->nullable();
            $table->boolean('push_opt_in')->default(false);
            $table->string('push_token', 255)->nullable();
            $table->string('push_platform', 50)->nullable();
            $table->json('push_metadata')->nullable();
            $table->timestamp('push_opted_in_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};