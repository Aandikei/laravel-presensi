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
        Schema::create('master_poin', function (Blueprint $table) {
            $table->bigIncrements('id_poin');
            $table->unsignedBigInteger('instansi_id');
            $table->string('nama_pelanggaran');
            $table->text('deskripsi')->nullable();
            $table->integer('jumlah_poin');
            $table->timestamps();

            $table->foreign('instansi_id')->references('id_instansi')->on('instansi')->cascadeOnDelete();
            $table->index('instansi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_poin');
    }
};
