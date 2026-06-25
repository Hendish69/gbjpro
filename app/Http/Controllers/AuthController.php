<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Simpan permissions di session
            $user = Auth::user();
            $user->load('role.permissions.menu');
            session(['user_permissions' => $user->getMenuPermissions()]);

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        // Default role: User (role_id = 3)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 3, // Default role: User
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Registration successful!');
    }
    public function checkSession(Request $request)
    {
        if (Auth::check()) {
            // Update last activity
            session(['last_activity' => time()]);
            
            return response()->json([
                'valid' => true,
                'user' => Auth::user()->name,
                'remaining_time' => $this->getRemainingSessionTime()
            ]);
        }

        return response()->json(['valid' => false], 401);
    }

    /**
     * Extend session manually
     */
    public function extendSession(Request $request)
    {
        if (Auth::check()) {
            session(['last_activity' => time()]);
            
            return response()->json([
                'success' => true,
                'message' => 'Session diperpanjang',
                'remaining_time' => $this->getRemainingSessionTime()
            ]);
        }

        return response()->json(['success' => false], 401);
    }

    /**
     * Calculate remaining session time
     */
    private function getRemainingSessionTime()
    {
        $lastActivity = session('last_activity', time());
        $timeout = config('session.lifetime') * 60;
        $remaining = $lastActivity + $timeout - time();
        
        return max(0, $remaining);
    }
}