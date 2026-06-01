<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEmpresaPagamento
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $empresa = $user->empresa;

        if (! $empresa) {
            return $next($request);
        }

        if ($empresa->isAtivo()) {
            return $next($request);
        }

        if ($request->routeIs('billing.bloqueado') || $request->routeIs('billing.pagar') || $request->routeIs('filament.admin.auth.logout')) {
            return $next($request);
        }

        return redirect()->route('billing.bloqueado');
    }
}
