<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GlobalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request, GlobalSearchService $service): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        try {
            return response()->json(
                $service->search($request->user(), (string) $request->query('q', ''))
            );
        } catch (Throwable $exception) {
            Log::error('Falha crítica na busca global.', [
                'user_id' => $request->user()?->id,
                'query' => (string) $request->query('q', ''),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return response()->json([
                'message' => 'Não foi possível concluir a busca agora. Tente novamente em instantes.',
                'term' => (string) $request->query('q', ''),
                'total' => 0,
                'groups' => [],
                'recent_groups' => [],
                'quick_links' => [],
                'search_shortcuts' => [],
            ], 500);
        }
    }
}
