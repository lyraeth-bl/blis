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
        Schema::create('attendance_fetch_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('sync_id')->unique();
            $table->foreignId('fingerprint_device_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_name')->nullable();
            $table->string('device_type')->nullable()->index();
            $table->string('device_ip_address')->nullable();
            $table->unsignedInteger('device_port')->nullable();
            $table->string('status')->default('running')->index();
            $table->unsignedInteger('fetched')->default(0);
            $table->unsignedInteger('inserted')->default(0);
            $table->unsignedInteger('updated')->default(0);
            $table->unsignedInteger('skipped')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->timestamp('first_log_at')->nullable();
            $table->timestamp('last_log_at')->nullable();
            $table->unsignedInteger('elapsed_ms')->nullable();
            $table->json('raw_rows_sample')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_fetch_logs');
    }
};
