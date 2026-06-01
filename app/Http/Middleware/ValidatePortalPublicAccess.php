<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidatePortalPublicAccess
{
    /**
     * Protege os portais públicos contra enumeração simples, parâmetros de debug e agentes vazios,
     * sem alterar a regra de negócio dos controllers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) $request->route('token', '');

        if ($token !== '' && ! preg_match('/\A[A-Za-z0-9]{32,128}\z/', $token)) {
            Log::warning('Acesso público bloqueado por token em formato inválido.', [
                'path' => $request->path(),
                'ip' => $request->ip(),
                'token_hash' => hash('sha256', $token),
            ]);

            abort(404);
        }

        if ($this->hasDebugParameter($request)) {
            Log::warning('Acesso público bloqueado por parâmetro de debug.', [
                'path' => $request->path(),
                'ip' => $request->ip(),
                'token_hash' => $token !== '' ? hash('sha256', $token) : null,
                'query_keys' => array_keys($request->query()),
            ]);

            abort(404);
        }

        if ($request->isMethod('post') && blank($request->userAgent())) {
            Log::warning('POST público bloqueado sem user-agent.', [
                'path' => $request->path(),
                'ip' => $request->ip(),
                'token_hash' => $token !== '' ? hash('sha256', $token) : null,
            ]);

            abort(403, 'Requisição inválida.');
        }

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    private function hasDebugParameter(Request $request): bool
    {
        foreach (['debug_sql', 'debug_sql_all', 'debug_performance', 'xdebug', 'phpinfo'] as $key) {
            if ($request->query->has($key)) {
                return true;
            }
        }

        return false;
    }
}
