<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogLivewireUtf8Error
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (Throwable $exception) {
            if (str_contains($exception->getMessage(), 'Malformed UTF-8')) {
                $rawContent = $request->getContent();
                $json = json_decode($rawContent, true);

                $debug = [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'route' => optional($request->route())->getName(),
                    'user_id' => optional($request->user())->id,
                    'exception' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'request_all_keys' => array_keys($request->all()),
                    'raw_json_valid' => json_last_error() === JSON_ERROR_NONE,
                    'livewire_components' => $this->extractLivewireComponents($json),
                    'raw_content_preview' => mb_substr($rawContent, 0, 3000),
                ];

                Log::error('LIVEWIRE UTF8 DEBUG FORCADO', $debug);

                file_put_contents(
                    storage_path('logs/livewire-utf8-debug.log'),
                    '[' . now()->format('Y-m-d H:i:s') . '] ' . print_r($debug, true) . PHP_EOL . PHP_EOL,
                    FILE_APPEND
                );
            }

            throw $exception;
        }
    }

    private function extractLivewireComponents(?array $json): array
    {
        if (! is_array($json)) {
            return [];
        }

        $components = $json['components'] ?? [];

        if (! is_array($components)) {
            return [];
        }

        return collect($components)
            ->map(function ($component): array {
                return [
                    'snapshot_name' => data_get($component, 'snapshot.memo.name'),
                    'snapshot_id' => data_get($component, 'snapshot.memo.id'),
                    'snapshot_path' => data_get($component, 'snapshot.memo.path'),
                    'calls' => data_get($component, 'calls'),
                ];
            })
            ->values()
            ->toArray();
    }
}
