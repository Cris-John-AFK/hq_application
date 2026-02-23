<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $roleLabel = $user->role === 'admin' ? 'Admin' : 'Employee';
            \App\Utils\AuditLogger::log('Authentication', 'Login', "{$roleLabel} {$user->name} logged in successfully.");

            return response()->json(['message' => 'Login successful', 'user' => $user]);
        }

        \App\Utils\AuditLogger::log('Authentication', 'Failed Login', "Failed login attempt for email: {$credentials['email']}");

        return response()->json([
            'message' => 'The provided credentials do not match our records.',
        ], 401);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $roleLabel = $user->role === 'admin' ? 'Admin' : 'Employee';
            \App\Utils\AuditLogger::log('Authentication', 'Logout (Manual)', "{$roleLabel} {$user->name} logged out manually.");
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Log a session expiry event from the frontend.
     */
    public function logExpiry(Request $request)
    {
        $email = $request->input('email');
        $name = $request->input('name');

        // Try to get role from DB if possible, or just use naming convention
        $user = \App\Models\User::where('email', $email)->first();
        $roleLabel = ($user && $user->role === 'admin') ? 'Admin' : 'Employee';

        \App\Utils\AuditLogger::log('Authentication', 'Session Expired', "Session for {$roleLabel} {$name} ({$email}) timed out or token expired.");

        return response()->json(['status' => 'logged']);
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request)
    {
        return $request->user();
    }
}
