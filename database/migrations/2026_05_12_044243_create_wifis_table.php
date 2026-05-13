<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wifis', function (Blueprint $table) {
            $table->id();
            $table->string('ssid')->index();
            $table->string('location')->index();
            $table->string('ip_address');
            $table->string('password')->nullable();
            $table->string('router_type');
            $table->string('admin_username')->nullable();
            $table->string('admin_password')->nullable();
            $table->string('link')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wifis');
    }
};
