<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Display the login form.
     */
    public function showLoginForm(): View
    {
        // Get all institutions with their client users for dynamic credentials display
        $institutions = \App\Models\Institution::with(['users' => function ($query) {
            $query->where('is_active', true)
                ->whereNotNull('institution_id');
        }])
            ->orderBy('name')
            ->get()
            ->filter(function ($institution) {
                return $institution->users->isNotEmpty();
            });

        return view('auth.login', [
            'institutions' => $institutions,
        ]);
    }

    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Redirect berdasarkan role
            $user = Auth::user();

            if ($user->hasRole('admin')) {
                return redirect()->intended(route('dashboard-admin'));
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // TEMPORARY: Redirect to route('welcome') to show welcome instead of login for bypass authentication
        // TODO: Change back to route('login') when restoring authentication
        return redirect()->route('login');
        // ORIGINAL: return redirect()->route('login');
    }
}
