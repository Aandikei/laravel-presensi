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
        Schema::create('ortu_siswa', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ortu_id');
            $table->unsignedBigInteger('siswa_id');
            $table->enum('hubungan', ['Ayah', 'Ibu', 'Wali']);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('ortu_id')->references('id_ortu')->on('orang_tua')->cascadeOnDelete();
            $table->foreign('siswa_id')->references('id_siswa')->on('siswa')->cascadeOnDelete();
            $table->unique(['ortu_id', 'siswa_id']);
            $table->index('siswa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ortu_siswa');
    }
};
