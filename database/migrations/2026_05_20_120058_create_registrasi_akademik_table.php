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
        Schema::create('registrasi_akademik', function (Blueprint $table) {
            $table->bigIncrements('id_registrasi');
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('tahun_id');
            $table->timestamps();

            $table->foreign('siswa_id')->references('id_siswa')->on('siswa')->cascadeOnDelete();
            $table->foreign('kelas_id')->references('id_kelas')->on('kelas')->cascadeOnDelete();
            $table->foreign('tahun_id')->references('id_tahun')->on('tahun_ajaran')->cascadeOnDelete();
            $table->unique(['siswa_id', 'tahun_id']);
            $table->index(['kelas_id', 'tahun_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrasi_akademik');
    }
};
