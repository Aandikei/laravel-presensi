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
        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->bigIncrements('id_mapel');
            $table->unsignedBigInteger('instansi_id');
            $table->string('nama_mapel');
            $table->string('kode_mapel', 20);
            $table->enum('kelompok', ['Umum', 'Jurusan', 'Muatan Lokal']);
            $table->timestamps();

            $table->foreign('instansi_id')->references('id_instansi')->on('instansi')->cascadeOnDelete();
            $table->unique(['kode_mapel', 'instansi_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mata_pelajaran');
    }
};
