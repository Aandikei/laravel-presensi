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
        Schema::create('kelas', function (Blueprint $table) {
            $table->bigIncrements('id_kelas');
            $table->unsignedBigInteger('instansi_id');
            $table->unsignedBigInteger('tahun_id');
            $table->unsignedBigInteger('guru_wali_id')->nullable();
            $table->string('nama_kelas');
            $table->integer('tingkat');
            $table->timestamps();

            $table->foreign('instansi_id')->references('id_instansi')->on('instansi')->cascadeOnDelete();
            $table->foreign('tahun_id')->references('id_tahun')->on('tahun_ajaran')->cascadeOnDelete();
            $table->foreign('guru_wali_id')->references('id_guru')->on('guru')->nullOnDelete();
            $table->index(['instansi_id', 'tahun_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
