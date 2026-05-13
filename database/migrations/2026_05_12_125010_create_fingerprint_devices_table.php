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
        Schema::create('fingerprint_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->string('ip_address');
            $table->unsignedSmallInteger('port')->default(80);
            $table->string('comm_key')->default('0');
            $table->enum('type', ['student', 'employee']);
            $table->time('check_in_start')->default('05:00');
            $table->time('check_in_end')->default('07:00');
            $table->time('check_out_start')->default('15:00');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fingerprint_devices');
    }
};
