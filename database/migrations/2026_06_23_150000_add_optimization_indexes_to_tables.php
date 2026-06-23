<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->index(['tanggal', 'status'], 'idx_absensi_tanggal_status');
        });

        Schema::table('jadwal', function (Blueprint $table) {
            $table->index(['hari'], 'idx_jadwal_hari');
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->index(['status'], 'idx_siswa_status');
        });

        Schema::table('guru', function (Blueprint $table) {
            $table->index(['status'], 'idx_guru_status');
        });

        Schema::table('registrasi_akademik', function (Blueprint $table) {
            $table->index(['status'], 'idx_registrasi_status');
        });

        Schema::table('log_poin_siswa', function (Blueprint $table) {
            $table->index(['tanggal'], 'idx_log_poin_tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropIndex('idx_absensi_tanggal_status');
        });

        Schema::table('jadwal', function (Blueprint $table) {
            $table->dropIndex('idx_jadwal_hari');
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->dropIndex('idx_siswa_status');
        });

        Schema::table('guru', function (Blueprint $table) {
            $table->dropIndex('idx_guru_status');
        });

        Schema::table('registrasi_akademik', function (Blueprint $table) {
            $table->dropIndex('idx_registrasi_status');
        });

        Schema::table('log_poin_siswa', function (Blueprint $table) {
            $table->dropIndex('idx_log_poin_tanggal');
        });
    }
};
