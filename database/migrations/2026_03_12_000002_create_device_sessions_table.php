<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->uuid('client_id')->index();
            $table->string('token_id', 100)->nullable()->index();
            $table->string('device_fingerprint', 64); // sha256 of user-agent
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            // One row per device (user + client + fingerprint combo)
            $table->unique(['user_id', 'client_id', 'device_fingerprint']);
            $table->index(['user_id', 'client_id']);

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_sessions');
    }
};
