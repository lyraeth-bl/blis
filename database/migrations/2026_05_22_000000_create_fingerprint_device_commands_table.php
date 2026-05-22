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
        Schema::create('fingerprint_device_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fingerprint_device_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('attendable');
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('command_id', 16)->unique();
            $table->string('action')->index();
            $table->text('command');
            $table->json('payload')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('return_code')->nullable();
            $table->text('raw_reply')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['fingerprint_device_id', 'status', 'created_at'], 'fd_commands_device_status_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fingerprint_device_commands');
    }
};
