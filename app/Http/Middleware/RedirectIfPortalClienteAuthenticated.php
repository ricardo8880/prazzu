<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfPortalClienteAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('portal_cliente')->check()) {
            return redirect()->route('portal.cliente.dashboard');
        }

        return $next($request);
    }
}
