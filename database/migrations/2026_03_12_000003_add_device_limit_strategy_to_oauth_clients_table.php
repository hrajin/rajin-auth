<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            // 'block'         → reject the new login (default)
            // 'evict_oldest'  → revoke the least recently active device and allow the new one
            $table->string('device_limit_strategy')
                ->default('block')
                ->after('max_devices_per_user');
        });
    }

    public function down(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropColumn('device_limit_strategy');
        });
    }
};
