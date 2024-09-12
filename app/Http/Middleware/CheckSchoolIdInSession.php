<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSchoolIdInSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!session('school_id')) {
            if(isOwner()) {
                return redirect()->route('schools');
            } else {
                Auth::guard('web')->logout();
                return redirect()->route('login');
            }
        }
        return $next($request);
    }
}
