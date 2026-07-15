<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropUnique(['nama_kelas', 'tingkat', 'instansi_id']);
            $table->unique(['tingkat', 'jurusan_id', 'nomor_kelas', 'instansi_id'], 'kelas_unique_tingkat_jurusan_nomor');
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropUnique('kelas_unique_tingkat_jurusan_nomor');
            $table->unique(['nama_kelas', 'tingkat', 'instansi_id']);
        });
    }
};
