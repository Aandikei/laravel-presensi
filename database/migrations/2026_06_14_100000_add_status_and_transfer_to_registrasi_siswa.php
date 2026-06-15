<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrasi_akademik', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'pindah', 'alumni'])->default('aktif')->after('tahun_id');
            $table->text('alasan_mutasi')->nullable()->after('status');
            $table->date('tanggal_mutasi')->nullable()->after('alasan_mutasi');
            $table->index('status');
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->string('transfer_token', 6)->nullable()->after('foto');
            $table->index('transfer_token');
        });
    }

    public function down(): void
    {
        Schema::table('registrasi_akademik', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'alasan_mutasi', 'tanggal_mutasi']);
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->dropIndex(['transfer_token']);
            $table->dropColumn('transfer_token');
        });
    }
};
