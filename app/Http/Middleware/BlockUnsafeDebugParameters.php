<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BlockUnsafeDebugParameters
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldBlock($request)) {
            return $next($request);
        }

        Log::warning('Requisição bloqueada por parâmetro de debug em ambiente protegido.', [
            'path' => $request->path(),
            'ip' => $request->ip(),
            'query_keys' => array_keys($request->query()),
        ]);

        abort(404);
    }

    private function shouldBlock(Request $request): bool
    {
        if (! app()->environment('production')) {
            return false;
        }

        if ((bool) config('prazzu_security.allow_debug_query_parameters', false)) {
            return false;
        }

        if (! (bool) config('prazzu_security.block_debug_query_parameters_in_production', true)) {
            return false;
        }

        foreach ((array) config('prazzu_security.debug_query_parameters', []) as $parameter) {
            if ($request->query->has((string) $parameter)) {
                return true;
            }
        }

        return false;
    }
}
