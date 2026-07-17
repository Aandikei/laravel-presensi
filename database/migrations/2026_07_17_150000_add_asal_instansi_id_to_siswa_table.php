<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->foreignId('asal_instansi_id')->nullable()->after('instansi_id')
                ->constrained('instansi', 'id_instansi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropForeign(['asal_instansi_id']);
            $table->dropColumn('asal_instansi_id');
        });
    }
};
