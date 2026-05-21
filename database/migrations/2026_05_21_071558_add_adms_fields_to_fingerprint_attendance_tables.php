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
        Schema::table('fingerprint_devices', function (Blueprint $table) {
            $table->string('serial_number')->nullable()->unique()->after('id');
            $table->timestamp('last_seen_at')->nullable()->index()->after('type');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->string('adms_pin')->nullable()->index()->after('fingerprint_device_id');
            $table->timestamp('adms_punch_time')->nullable()->index()->after('adms_pin');
            $table->string('adms_status1')->nullable()->after('adms_punch_time');
            $table->string('adms_status2')->nullable()->after('adms_status1');
            $table->string('adms_status3')->nullable()->after('adms_status2');
            $table->string('adms_status4')->nullable()->after('adms_status3');
            $table->string('adms_status5')->nullable()->after('adms_status4');
            $table->text('adms_raw_payload')->nullable()->after('adms_status5');
        });

        Schema::create('device_raw_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fingerprint_device_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_serial_number')->nullable()->index();
            $table->string('method', 10);
            $table->string('endpoint');
            $table->json('query_payload')->nullable();
            $table->longText('body_payload')->nullable();
            $table->string('table_name')->nullable()->index();
            $table->unsignedInteger('processed_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_raw_logs');

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['adms_pin']);
            $table->dropIndex(['adms_punch_time']);
            $table->dropColumn([
                'adms_pin',
                'adms_punch_time',
                'adms_status1',
                'adms_status2',
                'adms_status3',
                'adms_status4',
                'adms_status5',
                'adms_raw_payload',
            ]);
        });

        Schema::table('fingerprint_devices', function (Blueprint $table) {
            $table->dropUnique(['serial_number']);
            $table->dropIndex(['last_seen_at']);
            $table->dropColumn(['serial_number', 'last_seen_at']);
        });
    }
};
