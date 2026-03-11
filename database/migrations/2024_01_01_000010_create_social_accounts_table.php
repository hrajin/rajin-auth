<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider');
            $table->string('provider_id');
            $table->string('access_token')->nullable();
            $table->string('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->unique(['provider', 'provider_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
