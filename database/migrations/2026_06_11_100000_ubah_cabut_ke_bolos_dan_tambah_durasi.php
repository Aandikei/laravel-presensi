<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('Hadir','Sakit','Izin','Alpa','Terlambat','Bolos') NOT NULL");

        Schema::table('absensi', function (Blueprint $table) {
            $table->integer('durasi_terlambat')->nullable()->after('keterangan');
        });

        Schema::table('rekap_bulanan', function (Blueprint $table) {
            $table->renameColumn('cabut', 'bolos');
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('Hadir','Sakit','Izin','Alpa','Cabut','Terlambat') NOT NULL");

        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn('durasi_terlambat');
        });

        Schema::table('rekap_bulanan', function (Blueprint $table) {
            $table->renameColumn('bolos', 'cabut');
        });
    }
};
