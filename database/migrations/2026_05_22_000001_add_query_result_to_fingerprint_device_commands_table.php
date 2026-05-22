<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fingerprint_device_commands', function (Blueprint $table) {
            $table->json('reply_payload')->nullable()->after('raw_reply');
            $table->string('comparison_status')->nullable()->after('reply_payload')->index();
            $table->json('comparison_details')->nullable()->after('comparison_status');
        });
    }

    public function down(): void
    {
        Schema::table('fingerprint_device_commands', function (Blueprint $table) {
            $table->dropColumn([
                'reply_payload',
                'comparison_status',
                'comparison_details',
            ]);
        });
    }
};
