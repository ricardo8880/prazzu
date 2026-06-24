<?php

namespace App\Services;

use App\Models\AuditoriaDetalhada;
use App\Support\CachedSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuditoriaManualService
{
    /**
     * Registra eventos que não são CRUD de model: login, logout, exportação,
     * download, webhook, visualização, reset de senha etc.
     *
     * A tabela atual exige auditable_type/auditable_id/campo, então os eventos
     * manuais são gravados como uma linha única com campo = evento_manual.
     */
    public static function registrarEvento(
        string $evento,
        array $dados = [],
        ?Model $auditable = null,
        ?int $empresaId = null,
        ?int $userId = null,
        string $nivel = 'info'
    ): void {
        if (! (bool) config('auditoria.manual_events_enabled', true)) {
            return;
        }

        if (! CachedSchema::hasTable('auditoria_detalhada')) {
            return;
        }

        try {
            $user = Auth::user();

            $empresaId ??= self::resolverEmpresaId($auditable, $user, $dados);
            $userId ??= self::resolverUserId($user, $dados);

            DB::table('auditoria_detalhada')->insert([
                'empresa_id' => $empresaId,
                'user_id' => $userId,
                'auditable_type' => $auditable ? $auditable::class : AuditoriaDetalhada::class,
                'auditable_id' => $auditable?->getKey() ?: 0,
                'evento' => mb_substr($evento, 0, 50),
                'nivel' => mb_substr($nivel, 0, 50),
                'campo' => 'evento_manual',
                'valor_anterior' => null,
                'valor_novo' => self::normalizarDados($dados),
                'ip' => request()?->ip(),
                'user_agent' => mb_substr((string) request()?->userAgent(), 0, 500),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Falha ao registrar evento manual de auditoria.', [
                'evento' => $evento,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    protected static function resolverEmpresaId(?Model $auditable, $user, array $dados): ?int
    {
        foreach (['empresa_id', 'empresaId'] as $campo) {
            if (isset($dados[$campo]) && is_numeric($dados[$campo])) {
                return (int) $dados[$campo];
            }
        }

        if ($auditable && isset($auditable->empresa_id)) {
            return (int) $auditable->empresa_id;
        }

        if ($user && isset($user->empresa_id)) {
            return (int) $user->empresa_id;
        }

        return null;
    }

    protected static function resolverUserId($user, array $dados): ?int
    {
        if (isset($dados['user_id']) && is_numeric($dados['user_id'])) {
            return (int) $dados['user_id'];
        }

        return $user?->id ? (int) $user->id : null;
    }

    protected static function normalizarDados(array $dados): string
    {
        $dados = self::mascararSensivel($dados);

        $json = json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return mb_substr((string) $json, 0, (int) config('auditoria.max_value_length', 4000));
    }

    protected static function mascararSensivel(array $dados): array
    {
        foreach ($dados as $campo => $valor) {
            if (is_array($valor)) {
                $dados[$campo] = self::mascararSensivel($valor);
                continue;
            }

            if (self::campoSensivel((string) $campo)) {
                $dados[$campo] = config('auditoria.protected_value', '[valor protegido]');
            }
        }

        return $dados;
    }

    protected static function campoSensivel(string $campo): bool
    {
        $campoNormalizado = mb_strtolower($campo);

        foreach (config('auditoria.sensitive_fields', []) as $sensivel) {
            $sensivel = mb_strtolower((string) $sensivel);

            if ($campoNormalizado === $sensivel || str_contains($campoNormalizado, $sensivel)) {
                return true;
            }
        }

        return false;
    }
}
