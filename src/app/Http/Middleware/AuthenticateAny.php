<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateAny
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
            Auth::shouldUse('admin');
            return $next($request);
        }

        if (Auth::guard('web')->check()) {
            Auth::shouldUse('web');
            return $next($request);
        }

        return redirect()->route('login');
    }
}
