<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurusan', function (Blueprint $table) {
            $table->bigIncrements('id_jurusan');
            $table->unsignedBigInteger('instansi_id');
            $table->string('kode_jurusan', 50);
            $table->string('nama_jurusan');
            $table->timestamps();

            $table->foreign('instansi_id')->references('id_instansi')->on('instansi')->cascadeOnDelete();
            $table->unique(['instansi_id', 'kode_jurusan']);
            $table->unique(['instansi_id', 'nama_jurusan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurusan');
    }
};
