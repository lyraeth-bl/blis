<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fingerprint_devices', function (Blueprint $table) {
            $table->string('location')->nullable()->change();
            $table->string('ip_address')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('fingerprint_devices')
            ->whereNull('location')
            ->update(['location' => '']);

        DB::table('fingerprint_devices')
            ->whereNull('ip_address')
            ->update(['ip_address' => '']);

        Schema::table('fingerprint_devices', function (Blueprint $table) {
            $table->string('location')->nullable(false)->change();
            $table->string('ip_address')->nullable(false)->change();
        });
    }
};
