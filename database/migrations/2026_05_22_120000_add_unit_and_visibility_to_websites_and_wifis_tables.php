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
        Schema::table('websites', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('description')->constrained()->nullOnDelete();
        });

        Schema::table('wifis', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('description')->constrained()->nullOnDelete();
            $table->boolean('is_private')->default(false)->after('unit_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wifis', function (Blueprint $table) {
            $table->dropColumn('is_private');
            $table->dropConstrainedForeignId('unit_id');
        });

        Schema::table('websites', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
        });
    }
};
