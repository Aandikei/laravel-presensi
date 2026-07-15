<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auto Lock Grace Period
    |--------------------------------------------------------------------------
    |
    | Jeda waktu (dalam menit) setelah jam_selesai jadwal sebelum absensi
    | otomatis dikunci. Guru masih bisa edit absensi selama masa tenggang ini.
    |
    */
    'auto_lock_grace_minutes' => (int) env('ABSENSI_LOCK_GRACE', 30),
];
