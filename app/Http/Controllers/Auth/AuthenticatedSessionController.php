<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        if (Auth::guard('owner')->attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
    
            return redirect()->intended(RouteServiceProvider::HOME);
        }
    
        // Attempt to authenticate using the web guard (default user)
        if (Auth::guard('web')->attempt($request->only('email', 'password'))) {
            session(['school_id' => user()->school_id]);
            $request->session()->regenerate();
    
            return redirect()->intended(RouteServiceProvider::HOME);
        }
    
        // If neither guard succeeds, return an error response
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if(isOwner()) {
            Auth::guard('owner')->logout();
        }
        if(isUser()) {
            Auth::guard('web')->logout();
        }

        session(['school_id' => null]);

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
