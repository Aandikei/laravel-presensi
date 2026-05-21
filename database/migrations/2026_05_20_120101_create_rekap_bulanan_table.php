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
        Schema::create('rekap_bulanan', function (Blueprint $table) {
            $table->bigIncrements('id_rekap');
            $table->unsignedBigInteger('reg_id');
            $table->integer('bulan');
            $table->integer('tahun');
            $table->integer('hadir')->default(0);
            $table->integer('sakit')->default(0);
            $table->integer('izin')->default(0);
            $table->integer('alpa')->default(0);
            $table->integer('cabut')->default(0);
            $table->integer('terlambat')->default(0);
            $table->integer('poin_akumulasi')->default(0);
            $table->timestamps();

            $table->foreign('reg_id')->references('id_registrasi')->on('registrasi_akademik')->cascadeOnDelete();
            $table->unique(['reg_id', 'bulan', 'tahun']);
            $table->index(['reg_id', 'bulan', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_bulanan');
    }
};
