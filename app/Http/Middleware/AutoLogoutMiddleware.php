<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AutoLogoutMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek jika user sudah login
        if (Auth::check()) {
            $lastActivity = Session::get('last_activity');
            $currentTime = time();
            $timeout = config('session.lifetime') * 60; // Convert menit ke detik

            // Jika last activity ada dan sudah timeout
            if ($lastActivity && ($currentTime - $lastActivity > $timeout)) {
                Auth::logout();
                Session::flush();
                
                return $this->redirectToLogin($request);
            }

            // Update last activity time
            Session::put('last_activity', $currentTime);
        }

        return $next($request);
    }

    /**
     * Redirect ke halaman login dengan pesan timeout
     */
    private function redirectToLogin(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Session telah berakhir. Silakan login kembali.',
                'redirect' => route('login')
            ], 401);
        }

        return redirect()->route('login')
            ->with('timeout', 'Session Anda telah berakhir karena tidak ada aktivitas. Silakan login kembali.');
    }
}