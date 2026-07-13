<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Kelas sekarang jadi entitas permanen yang hanya terikat ke instansi.
     * Hubungan siswa + kelas + tahun ajaran dikelola lewat registrasi_akademik.
     * Ini menghilangkan keharusan admin membuat kelas baru setiap ganti semester.
     */
    public function up(): void
    {
        // Index instansi_id sudah dibuat pada run sebelumnya yang gagal,
        // jadi kita skip pembuatan indexnya di sini.

        // Gunakan raw DB statement agar bisa try-catch tiap perintah
        // jika terjadi partial migration sebelumnya.
        try {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE kelas DROP FOREIGN KEY kelas_tahun_id_foreign');
        } catch (\Exception $e) {}

        try {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE kelas DROP INDEX kelas_instansi_id_tahun_id_index');
        } catch (\Exception $e) {}

        try {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE kelas DROP INDEX kelas_tahun_id_foreign');
        } catch (\Exception $e) {}

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('tahun_id');
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->unique(['nama_kelas', 'tingkat', 'instansi_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropUnique(['nama_kelas', 'tingkat', 'instansi_id']);
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->unsignedBigInteger('tahun_id')->nullable()->after('instansi_id');
            $table->foreign('tahun_id')->references('id_tahun')->on('tahun_ajaran')->cascadeOnDelete();
            $table->index(['instansi_id', 'tahun_id']);
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropIndex(['instansi_id']);
        });
    }
};
