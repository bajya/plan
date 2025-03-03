<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        //dd(auth()->user());
        if (Auth::guard($guard)->check()) {
            $user = Auth::user();
            if ($user->is_admin == 'Yes') {
                return redirect()->route('admins');
            }else{
                return redirect(RouteServiceProvider::HOME);
            }
        }
        return $next($request);
    }
}
