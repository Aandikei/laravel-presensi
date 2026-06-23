<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // Jika ada intended URL (misal dari middleware verified/guest), lanjutkan ke sana
        if ($intended = session()->pull('url.intended')) {
            return redirect($intended);
        }

        // User belum verifikasi email → skip validasi status aktif,
        // nanti dicegah middleware 'verified' di dashboard route
        if (! $user->hasVerifiedEmail()) {
            return redirect('/');
        }

        if ($user->hasRole('super_admin')) {
            return redirect()->route('superadmin.dashboard');
        } elseif ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('kepala_sekolah')) {
            if (! $user->guru || ! $user->getInstansi() || ! $user->guru->isAktif()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $msg = ! $user->guru?->isAktif()
                    ? 'Akun kepala sekolah sudah tidak aktif.'
                    : 'Akun kepala sekolah tidak terdaftar di sekolah ini.';
                throw ValidationException::withMessages(['email' => $msg]);
            }

            return redirect()->route('kepala-sekolah.dashboard');
        } elseif ($user->hasRole('wakil_kepala_sekolah')) {
            if (! $user->guru || ! $user->getInstansi() || ! $user->guru->isAktif()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $msg = ! $user->guru?->isAktif()
                    ? 'Akun wakil kepala sekolah sudah tidak aktif.'
                    : 'Akun wakil kepala sekolah tidak terdaftar di sekolah ini.';
                throw ValidationException::withMessages(['email' => $msg]);
            }

            return redirect()->route('wakil-kepala-sekolah.dashboard');
        } elseif ($user->hasRole('guru') || $user->hasRole('wali_kelas')) {
            if (! $user->guru || ! $user->getInstansi() || ! $user->guru->isAktif()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $msg = ! $user->guru?->isAktif()
                    ? 'Akun guru sudah tidak aktif.'
                    : 'Akun guru tidak terdaftar di sekolah ini.';
                throw ValidationException::withMessages(['email' => $msg]);
            }

            return redirect()->route('guru.dashboard');
        } elseif ($user->hasRole('siswa')) {
            // Validasi siswa harus punya registrasi aktif di sekolah ini
            $instansi = $user->getInstansi();
            $hasActiveReg = $user->siswa?->registrasiAktif()
                ->whereHas('kelas', fn ($q) => $q->where('instansi_id', $instansi?->id_instansi))
                ->exists();

            if (! $hasActiveReg) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                throw ValidationException::withMessages([
                    'email' => 'Siswa tidak terdaftar aktif di sekolah ini.',
                ]);
            }

            if ($user->siswa && !$user->siswa->isAktif()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                throw ValidationException::withMessages([
                    'email' => 'Akun siswa sudah tidak aktif.',
                ]);
            }

            return redirect()->route('siswa.dashboard');
        } elseif ($user->hasRole('orang_tua')) {
            return redirect()->route('orangtua.dashboard');
        }

        // User tanpa role valid
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        throw ValidationException::withMessages([
            'email' => 'Akun tidak memiliki akses ke sistem ini.',
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
