<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE registrasi_akademik MODIFY COLUMN status ENUM('Aktif', 'Pindah', 'Alumni', 'Keluar') NOT NULL DEFAULT 'Aktif'");
    }

    public function down(): void
    {
        DB::statement("UPDATE registrasi_akademik SET status = 'Alumni' WHERE status = 'Keluar'");
        DB::statement("ALTER TABLE registrasi_akademik MODIFY COLUMN status ENUM('Aktif', 'Pindah', 'Alumni') NOT NULL DEFAULT 'Aktif'");
    }
};
