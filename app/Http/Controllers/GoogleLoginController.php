<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Owner;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;

class GoogleLoginController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }


    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $guard = 'owner';
        $user = Owner::where('email', $googleUser->email)->first();
        if(!$user) {
            $user = User::where('email', $googleUser->email)->first();
            $guard = 'web';
        }
        if(!$user)
        {
            return redirect()->back()->with('error', 'User not found');
            // $user = User::create(['name' => $googleUser->name, 'email' => $googleUser->email, 'password' => \Hash::make(rand(100000,999999))]);
        }

        Auth::guard($guard)->login($user);

        if(isUser()) {
            session(['school_id' => user()->school_id]);
        }

        return redirect(RouteServiceProvider::HOME);
    }
}