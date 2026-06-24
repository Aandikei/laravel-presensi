<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, $request) {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Kalau ada halaman sebelumnya yang valid, balik ke sana
        $previous = url()->previous();
        $current  = url()->current();

        if ($previous && $previous !== $current && $previous !== url('/')) {
            $message = $e->getMessage();
            if (!$message || str_starts_with($message, 'User does not have')) {
                $message = 'Kamu tidak memiliki akses ke halaman tersebut.';
            }
            return redirect($previous)->with('error', $message);
        }

        // Kalau tidak ada previous, redirect ke dashboard sesuai role
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->hasRole('super_admin')) return redirect()->route('superadmin.dashboard');
            if ($user->hasRole('admin')) return redirect()->route('admin.dashboard');
            if ($user->hasRole('kepala_sekolah')) return redirect()->route('kepala-sekolah.dashboard');
            if ($user->hasRole('wakil_kepala_sekolah')) return redirect()->route('wakil-kepala-sekolah.dashboard');
            if ($user->hasRole('guru') || $user->hasRole('wali_kelas')) return redirect()->route('guru.dashboard');
            if ($user->hasRole('siswa')) return redirect()->route('siswa.dashboard');
            if ($user->hasRole('orang_tua')) return redirect()->route('orangtua.dashboard');
        }

        return redirect()->route('login');
    });
    })->create();
