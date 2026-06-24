<?php

namespace App\Services;

use App\Models\AuditoriaDetalhada;
use App\Support\CachedSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuditoriaDetalhadaService
{
    public static function registrar(Model $model, string $evento, array $antigos = [], array $novos = []): void
    {
        if (! CachedSchema::hasTable('auditoria_detalhada')) {
            return;
        }

        if (self::modelExcluido($model)) {
            return;
        }

        $user = Auth::user();
        $empresaId = self::resolverEmpresaId($model, $user);

        try {
            if ($evento === 'updated') {
                self::registrarUpdated($model, $evento, $empresaId, $user?->id, $antigos, $novos);

                return;
            }

            $base = $evento === 'deleted' ? $antigos : $novos;

            foreach ($base as $campo => $valor) {
                if (self::campoIgnorado($campo)) {
                    continue;
                }

                self::criarLinha(
                    $model,
                    $evento,
                    $empresaId,
                    $user?->id,
                    $campo,
                    $evento === 'deleted' ? $valor : null,
                    $evento !== 'deleted' ? $valor : null,
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

    protected static function registrarUpdated(Model $model, string $evento, ?int $empresaId, ?int $userId, array $antigos, array $novos): void
    {
        foreach ($novos as $campo => $valorNovo) {
            if (self::campoIgnorado($campo)) {
                continue;
            }

            $valorAntigo = Arr::get($antigos, $campo);

            if (self::valoresIguais($valorAntigo, $valorNovo)) {
                continue;
            }

            self::criarLinha($model, $evento, $empresaId, $userId, $campo, $valorAntigo, $valorNovo);
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
            'nivel' => config('auditoria.default_level', 'info'),
            'campo' => $campo,
            'valor_anterior' => self::normalizarValor($valorAntigo, $campo),
            'valor_novo' => self::normalizarValor($valorNovo, $campo),
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

    protected static function modelExcluido(Model $model): bool
    {
        if (in_array($model::class, config('auditoria.excluded_models', []), true)) {
            return true;
        }

        return in_array($model->getTable(), config('auditoria.excluded_tables', []), true);
    }

    protected static function campoIgnorado(string $campo): bool
    {
        return in_array($campo, config('auditoria.ignored_fields', []), true);
    }

    protected static function campoSensivel(string $campo): bool
    {
        $campoNormalizado = mb_strtolower($campo);

        foreach (config('auditoria.sensitive_fields', []) as $sensivel) {
            if ($campoNormalizado === mb_strtolower((string) $sensivel)) {
                return true;
            }

            if (str_contains($campoNormalizado, mb_strtolower((string) $sensivel))) {
                return true;
            }
        }

        return false;
    }

    protected static function valoresIguais(mixed $valorAntigo, mixed $valorNovo): bool
    {
        if ($valorAntigo === $valorNovo) {
            return true;
        }

        return self::normalizarValor($valorAntigo) === self::normalizarValor($valorNovo);
    }

    protected static function normalizarValor(mixed $valor, ?string $campo = null): ?string
    {
        if ($campo !== null && self::campoSensivel($campo)) {
            return config('auditoria.protected_value', '[valor protegido]');
        }

        if ($valor === null) {
            return null;
        }

        if (is_bool($valor)) {
            return $valor ? '1' : '0';
        }

        if (is_array($valor) || is_object($valor)) {
            $valor = json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return mb_substr((string) $valor, 0, (int) config('auditoria.max_value_length', 4000));
    }
}
