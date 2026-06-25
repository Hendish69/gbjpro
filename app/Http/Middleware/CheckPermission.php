<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $menu, $action = 'view'): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Load relationship jika belum diload
        if (!$user->relationLoaded('role') || !$user->role->relationLoaded('permissions')) {
            $user->load('role.permissions.menu');
        }

        if (!$user->hasPermission($menu, $action)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized access.'], 403);
            }
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}