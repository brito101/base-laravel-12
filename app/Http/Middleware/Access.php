<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Access
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->first_access == 1 && ! $request->routeIs('admin.user.edit') && ! $request->routeIs('admin.users.update') && ! $request->routeIs('admin.user.google2fa')) {
            return redirect()->route('admin.user.edit')->with('warning', 'Por favor atualize sua senha de acesso.');
        }

        return $next($request);
    }
}
