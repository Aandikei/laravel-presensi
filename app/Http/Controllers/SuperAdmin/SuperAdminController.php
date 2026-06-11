<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Instansi;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;

class SuperAdminController extends Controller
{
    public function index()
    {
        $totalSekolah       = Instansi::count();
        $totalUsers         = User::count();
        $totalGuru          = Guru::count();
        $totalSiswa         = Siswa::count();
        $totalKelas         = Kelas::count();
        $sekolahBaruBulanIni = Instansi::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();
        $sekolahTerbaru     = Instansi::latest()->get();

        return view('superadmin.dashboard', compact(
            'totalSekolah', 'totalUsers', 'totalGuru', 'totalSiswa',
            'totalKelas', 'sekolahBaruBulanIni', 'sekolahTerbaru'
        ));
    }
}
