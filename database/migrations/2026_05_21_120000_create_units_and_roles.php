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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->index();
            $table->string('campus')->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::table('units')->insert([
            ['code' => 'KB_PA', 'name' => 'KB', 'campus' => 'Pondok Aren', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'TK_PA', 'name' => 'TK', 'campus' => 'Pondok Aren', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'SD_PA', 'name' => 'SD', 'campus' => 'Pondok Aren', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'KB_KT', 'name' => 'KB', 'campus' => 'Karang Tengah', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'TK_KT', 'name' => 'TK', 'campus' => 'Karang Tengah', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'SD_KT', 'name' => 'SD', 'campus' => 'Karang Tengah', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'SMP_KT', 'name' => 'SMP', 'campus' => 'Karang Tengah', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'SMAKT', 'name' => 'SMA', 'campus' => 'Karang Tengah', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'SMKKT', 'name' => 'SMK', 'campus' => 'Karang Tengah', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin')->after('password')->index();
        });

        Schema::create('unit_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'unit_id']);
        });

        DB::statement('ALTER TABLE students MODIFY unit VARCHAR(50) NULL');

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('unit')->constrained()->nullOnDelete();
        });

        DB::table('students')
            ->join('units', 'students.unit', '=', 'units.code')
            ->update(['students.unit_id' => DB::raw('units.id')]);

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('position')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
        });

        Schema::dropIfExists('unit_user');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::dropIfExists('units');
    }
};
