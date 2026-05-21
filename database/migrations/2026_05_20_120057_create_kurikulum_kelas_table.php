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
        Schema::create('kurikulum_kelas', function (Blueprint $table) {
            $table->bigIncrements('id_kurikulum');
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('mapel_id');
            $table->unsignedBigInteger('guru_id');
            $table->timestamps();

            $table->foreign('kelas_id')->references('id_kelas')->on('kelas')->cascadeOnDelete();
            $table->foreign('mapel_id')->references('id_mapel')->on('mata_pelajaran')->cascadeOnDelete();
            $table->foreign('guru_id')->references('id_guru')->on('guru')->cascadeOnDelete();
            $table->unique(['kelas_id', 'mapel_id']);
            $table->index(['kelas_id', 'guru_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kurikulum_kelas');
    }
};
