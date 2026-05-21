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
        Schema::create('absensi', function (Blueprint $table) {
            $table->bigIncrements('id_absen');
            $table->unsignedBigInteger('reg_id');
            $table->unsignedBigInteger('jadwal_id');
            $table->date('tanggal');
            $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Alpa', 'Cabut', 'Terlambat']);
            $table->text('keterangan')->nullable();
            $table->timestamp('waktu_input')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('reg_id')->references('id_registrasi')->on('registrasi_akademik')->cascadeOnDelete();
            $table->foreign('jadwal_id')->references('id_jadwal')->on('jadwal')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unique(['reg_id', 'jadwal_id', 'tanggal']);
            $table->index(['jadwal_id', 'tanggal']);
            $table->index('is_locked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
