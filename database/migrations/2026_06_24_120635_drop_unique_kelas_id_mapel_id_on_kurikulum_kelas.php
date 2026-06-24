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
        Schema::table('kurikulum_kelas', function (Blueprint $table) {
            $table->dropUnique(['kelas_id', 'mapel_id']);
        });
    }

    public function down(): void
    {
        Schema::table('kurikulum_kelas', function (Blueprint $table) {
            $table->unique(['kelas_id', 'mapel_id']);
        });
    }
};
