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
        Schema::create('picket_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week')->index();
            $table->time('starts_at');
            $table->time('ends_at');
            $table->date('effective_from');
            $table->date('effective_until');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['unit_id', 'day_of_week', 'is_active']);
            $table->index(['effective_from', 'effective_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('picket_schedules');
    }
};
