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
        Schema::create('log_poin_siswa', function (Blueprint $table) {
            $table->bigIncrements('id_log_poin');
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('absen_id')->nullable();
            $table->unsignedBigInteger('poin_id');
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('siswa_id')->references('id_siswa')->on('siswa')->cascadeOnDelete();
            $table->foreign('absen_id')->references('id_absen')->on('absensi')->nullOnDelete();
            $table->foreign('poin_id')->references('id_poin')->on('master_poin')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['siswa_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_poin_siswa');
    }
};
