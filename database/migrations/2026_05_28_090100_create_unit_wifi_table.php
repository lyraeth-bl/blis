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
        Schema::create('unit_wifi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wifi_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['unit_id', 'wifi_id']);
        });

        DB::table('unit_wifi')->insertUsing(
            ['wifi_id', 'unit_id', 'created_at', 'updated_at'],
            DB::table('wifis')
                ->select([
                    'id',
                    'unit_id',
                    DB::raw('CURRENT_TIMESTAMP'),
                    DB::raw('CURRENT_TIMESTAMP'),
                ])
                ->whereNotNull('unit_id'),
        );

        Schema::table('wifis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wifis', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('description')->constrained()->nullOnDelete();
        });

        DB::statement(<<<'SQL'
            UPDATE wifis
            SET unit_id = (
                SELECT unit_wifi.unit_id
                FROM unit_wifi
                WHERE unit_wifi.wifi_id = wifis.id
                ORDER BY unit_wifi.unit_id
                LIMIT 1
            )
        SQL);

        Schema::dropIfExists('unit_wifi');
    }
};
