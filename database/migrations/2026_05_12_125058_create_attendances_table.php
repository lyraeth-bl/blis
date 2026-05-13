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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->morphs('attendable');
            $table->foreignId('fingerprint_device_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date')->index();
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'permitted']);
            $table->enum('source', ['fingerprint', 'manual'])->default('manual');
            $table->string('reason')->nullable();
            $table->text('description')->nullable();
            $table->string('edited_by')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();

            $table->index(['attendable_type', 'attendable_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
