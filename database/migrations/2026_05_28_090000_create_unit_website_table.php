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
        Schema::create('unit_website', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['unit_id', 'website_id']);
        });

        DB::table('unit_website')->insertUsing(
            ['website_id', 'unit_id', 'created_at', 'updated_at'],
            DB::table('websites')
                ->select([
                    'id',
                    'unit_id',
                    DB::raw('CURRENT_TIMESTAMP'),
                    DB::raw('CURRENT_TIMESTAMP'),
                ])
                ->whereNotNull('unit_id'),
        );

        Schema::table('websites', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('description')->constrained()->nullOnDelete();
        });

        DB::statement(<<<'SQL'
            UPDATE websites
            SET unit_id = (
                SELECT unit_website.unit_id
                FROM unit_website
                WHERE unit_website.website_id = websites.id
                ORDER BY unit_website.unit_id
                LIMIT 1
            )
        SQL);

        Schema::dropIfExists('unit_website');
    }
};
