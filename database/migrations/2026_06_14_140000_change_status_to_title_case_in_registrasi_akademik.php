<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE registrasi_akademik SET status = 'Aktif' WHERE status = 'aktif'");
        DB::statement("UPDATE registrasi_akademik SET status = 'Pindah' WHERE status = 'pindah'");
        DB::statement("UPDATE registrasi_akademik SET status = 'Alumni' WHERE status = 'alumni'");

        DB::statement("ALTER TABLE registrasi_akademik MODIFY COLUMN status ENUM('Aktif', 'Pindah', 'Alumni') NOT NULL DEFAULT 'Aktif'");
    }

    public function down(): void
    {
        DB::statement("UPDATE registrasi_akademik SET status = 'aktif' WHERE status = 'Aktif'");
        DB::statement("UPDATE registrasi_akademik SET status = 'pindah' WHERE status = 'Pindah'");
        DB::statement("UPDATE registrasi_akademik SET status = 'alumni' WHERE status = 'Alumni'");

        DB::statement("ALTER TABLE registrasi_akademik MODIFY COLUMN status ENUM('aktif', 'pindah', 'alumni') NOT NULL DEFAULT 'aktif'");
    }
};
