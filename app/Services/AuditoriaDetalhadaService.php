<?php

namespace App\Services;


use App\Support\CachedSchema;
use App\Models\AuditoriaDetalhada;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuditoriaDetalhadaService
{
    public static function registrar(Model $model, string $evento, array $antigos = [], array $novos = []): void
    {
        if (! CachedSchema::hasTable('auditoria_detalhada')) {
            return;
        }

        $user = Auth::user();
        $empresaId = self::resolverEmpresaId($model, $user);
        $camposIgnorados = ['updated_at', 'created_at', 'remember_token', 'password'];

        try {
            if ($evento === 'updated') {
                foreach ($novos as $campo => $valorNovo) {
                    if (in_array($campo, $camposIgnorados, true)) {
                        continue;
                    }

                    $valorAntigo = Arr::get($antigos, $campo);

                    if ((string) $valorAntigo === (string) $valorNovo) {
                        continue;
                    }

                    self::criarLinha($model, $evento, $empresaId, $user?->id, $campo, $valorAntigo, $valorNovo);
                }

                return;
            }

            $base = $evento === 'deleted' ? $antigos : $novos;

            foreach ($base as $campo => $valor) {
                if (in_array($campo, $camposIgnorados, true)) {
                    continue;
                }

                self::criarLinha(
                    $model,
                    $evento,
                    $empresaId,
                    $user?->id,
                    $campo,
                    $evento === 'deleted' ? $valor : null,
                    $evento === 'created' ? $valor : null,
                );
            }
        } catch (Throwable $exception) {
            // Auditoria nunca deve quebrar o fluxo operacional principal, mas precisa deixar rastro técnico.
            Log::warning('Falha ao registrar auditoria detalhada.', [
                'auditable_type' => $model::class,
                'auditable_id' => $model->getKey(),
                'evento' => $evento,
                'empresa_id' => $empresaId,
                'user_id' => $user?->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
        }
    }

    protected static function criarLinha(Model $model, string $evento, ?int $empresaId, ?int $userId, string $campo, mixed $valorAntigo, mixed $valorNovo): void
    {
        AuditoriaDetalhada::query()->create([
            'empresa_id' => $empresaId,
            'user_id' => $userId,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'evento' => $evento,
            'campo' => $campo,
            'valor_anterior' => self::normalizarValor($valorAntigo),
            'valor_novo' => self::normalizarValor($valorNovo),
            'ip' => request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 500),
        ]);
    }

    protected static function resolverEmpresaId(Model $model, $user): ?int
    {
        if (isset($model->empresa_id)) {
            return (int) $model->empresa_id;
        }

        if (isset($user->empresa_id)) {
            return (int) $user->empresa_id;
        }

        return null;
    }

    protected static function normalizarValor(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        if (is_bool($valor)) {
            return $valor ? '1' : '0';
        }

        if (is_array($valor) || is_object($valor)) {
            return json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return mb_substr((string) $valor, 0, 4000);
    }
}
