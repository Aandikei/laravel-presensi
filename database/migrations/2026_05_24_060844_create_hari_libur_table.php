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
        Schema::create('hari_libur', function (Blueprint $table) {
            $table->bigIncrements('id_libur');
            $table->unsignedBigInteger('instansi_id')->nullable();
            $table->date('tanggal');
            $table->string('nama_libur');
            $table->boolean('is_nasional')->default(false);
            $table->timestamps();

            $table->foreign('instansi_id')->references('id_instansi')->on('instansi')->cascadeOnDelete();
            $table->index(['instansi_id', 'tanggal']);
            $table->unique(['instansi_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hari_libur');
    }
};
