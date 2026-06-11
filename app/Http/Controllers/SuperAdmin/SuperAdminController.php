<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Instansi;
use App\Models\Siswa;
use App\Models\User;

class SuperAdminController extends Controller
{
    public function index()
    {
        $totalSekolah = Instansi::count();
        $totalUsers   = User::count();
        $totalGuru    = Guru::count();
        $totalSiswa   = Siswa::count();

        return view('superadmin.dashboard', compact(
            'totalSekolah', 'totalUsers', 'totalGuru', 'totalSiswa'
        ));
    }
}
