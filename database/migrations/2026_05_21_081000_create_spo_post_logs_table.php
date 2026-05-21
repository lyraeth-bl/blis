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
        Schema::create('spo_post_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_fetch_log_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fingerprint_device_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('attendable');
            $table->string('endpoint_type')->index();
            $table->string('field')->nullable()->index();
            $table->string('status')->index();
            $table->string('url')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->json('payload')->nullable();
            $table->longText('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->string('skipped_reason')->nullable();
            $table->timestamp('attempted_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spo_post_logs');
    }
};
