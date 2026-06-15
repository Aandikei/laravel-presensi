<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE instansi MODIFY COLUMN jenjang ENUM('SD', 'SMP', 'SMA') NOT NULL DEFAULT 'SMA'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE instansi MODIFY COLUMN jenjang ENUM('SD', 'SMP', 'SMA', 'SMK') NOT NULL DEFAULT 'SMA'");
    }
};
