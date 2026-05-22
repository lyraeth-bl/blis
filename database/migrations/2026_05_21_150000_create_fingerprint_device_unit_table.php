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
        Schema::create('fingerprint_device_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fingerprint_device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['fingerprint_device_id', 'unit_id']);
        });

        if (Schema::hasColumn('fingerprint_devices', 'unit_id')) {
            DB::table('fingerprint_devices')
                ->whereNotNull('unit_id')
                ->select(['id', 'unit_id'])
                ->orderBy('id')
                ->get()
                ->each(function (object $device): void {
                    DB::table('fingerprint_device_unit')->insertOrIgnore([
                        'fingerprint_device_id' => $device->id,
                        'unit_id' => $device->unit_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });

            Schema::table('fingerprint_devices', function (Blueprint $table) {
                $table->dropConstrainedForeignId('unit_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fingerprint_devices', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('type')->constrained()->nullOnDelete();
        });

        DB::table('fingerprint_device_unit')
            ->select(['fingerprint_device_id', 'unit_id'])
            ->orderBy('id')
            ->each(function (object $pivot): void {
                DB::table('fingerprint_devices')
                    ->where('id', $pivot->fingerprint_device_id)
                    ->whereNull('unit_id')
                    ->update(['unit_id' => $pivot->unit_id]);
            });

        Schema::dropIfExists('fingerprint_device_unit');
    }
};
