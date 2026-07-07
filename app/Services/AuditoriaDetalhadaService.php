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

            self::registrarEventosCriticosDoModel($model, $evento, $empresaId, $user?->id, $antigos, $novos);
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
        $alteracoesCriticas = [];

        foreach ($novos as $campo => $valorNovo) {
            if (self::campoIgnorado($campo)) {
                continue;
            }

            $valorAntigo = Arr::get($antigos, $campo);

            if (self::valoresIguais($valorAntigo, $valorNovo)) {
                continue;
            }

            self::criarLinha($model, $evento, $empresaId, $userId, $campo, $valorAntigo, $valorNovo);

            $eventoCritico = self::eventoCriticoParaCampo($model->getTable(), $campo);

            if ($eventoCritico !== null) {
                $alteracoesCriticas[] = [
                    'evento' => $eventoCritico,
                    'campo' => $campo,
                    'valor_anterior' => self::normalizarValor($valorAntigo, $campo),
                    'valor_novo' => self::normalizarValor($valorNovo, $campo),
                ];
            }
        }

        foreach ($alteracoesCriticas as $alteracao) {
            self::registrarEventoCritico(
                $model,
                $alteracao['evento'],
                $empresaId,
                $userId,
                [
                    'acao' => 'updated',
                    'tabela' => $model->getTable(),
                    'registro_id' => $model->getKey(),
                    'campo' => $alteracao['campo'],
                    'valor_anterior' => $alteracao['valor_anterior'],
                    'valor_novo' => $alteracao['valor_novo'],
                ]
            );
        }
    }


    /**
     * Gera trilhas semânticas para ações críticas de negócio. A auditoria CRUD
     * continua gravando campo a campo; estes eventos facilitam investigação de
     * status, prazos, responsáveis, aprovações, documentos, permissões e financeiro.
     */
    protected static function registrarEventosCriticosDoModel(Model $model, string $evento, ?int $empresaId, ?int $userId, array $antigos, array $novos): void
    {
        $tabela = $model->getTable();
        $eventoCritico = null;

        if ($evento === 'created') {
            $eventoCritico = match ($tabela) {
                'item_controle_anexos' => 'item_controle.anexo.uploaded',
                'portal_documentos' => 'portal_documento.uploaded',
                'prazzu_permissions', 'prazzu_permission_rules', 'prazzu_user_permissions', 'prazzu_roles', 'prazzu_user_roles' => 'permissao.changed',
                'pagamentos' => 'financeiro.pagamento.created',
                'assinaturas' => 'financeiro.assinatura.created',
                'item_controle_aprovacoes' => 'item_controle.aprovacao.created',
                default => null,
            };
        }

        if ($evento === 'deleted') {
            $eventoCritico = match ($tabela) {
                'item_controle_anexos' => 'item_controle.anexo.deleted',
                'portal_documentos' => 'portal_documento.deleted',
                'prazzu_permissions', 'prazzu_permission_rules', 'prazzu_user_permissions', 'prazzu_roles', 'prazzu_user_roles' => 'permissao.changed',
                'pagamentos' => 'financeiro.pagamento.deleted',
                'assinaturas' => 'financeiro.assinatura.deleted',
                default => null,
            };
        }

        if ($eventoCritico === null) {
            return;
        }

        self::registrarEventoCritico($model, $eventoCritico, $empresaId, $userId, [
            'acao' => $evento,
            'tabela' => $tabela,
            'registro_id' => $model->getKey(),
            'snapshot' => self::normalizarSnapshot($evento === 'deleted' ? $antigos : $novos),
        ]);
    }

    protected static function eventoCriticoParaCampo(string $tabela, string $campo): ?string
    {
        return match ($tabela . '.' . $campo) {
            'item_controles.status' => 'item_controle.status.changed',
            'item_controles.data_vencimento' => 'item_controle.vencimento.changed',
            'item_controles.responsavel_id' => 'item_controle.responsavel.changed',
            'item_controles.sla_inicio_em',
            'item_controles.sla_limite_em',
            'item_controles.sla_concluido_em',
            'item_controles.sla_status',
            'item_controles.sla_prazo_alvo_em' => 'item_controle.sla.changed',
            'item_controles.approval_status' => 'item_controle.approval_status.changed',
            'item_controle_aprovacoes.status' => 'item_controle.aprovacao.status.changed',
            'item_controle_aprovacoes.aprovador_id',
            'item_controle_aprovacoes.respondido_em',
            'item_controle_aprovacoes.motivo_reprovacao' => 'item_controle.aprovacao.response.changed',
            'prazzu_permissions.name',
            'prazzu_permissions.module',
            'prazzu_permissions.action',
            'prazzu_permissions.scope',
            'prazzu_permission_rules.effect',
            'prazzu_permission_rules.context_type',
            'prazzu_permission_rules.resource_type',
            'prazzu_user_permissions.permission_id',
            'prazzu_user_permissions.expires_at',
            'prazzu_roles.name',
            'prazzu_roles.guard_name',
            'prazzu_user_roles.role_id' => 'permissao.changed',
            'pagamentos.status' => 'financeiro.pagamento.status.changed',
            'pagamentos.pago_em' => 'financeiro.pagamento.paid_at.changed',
            'pagamentos.valor' => 'financeiro.pagamento.valor.changed',
            'assinaturas.status' => 'financeiro.assinatura.status.changed',
            'assinaturas.cancelado_em' => 'financeiro.assinatura.cancelled_at.changed',
            'assinaturas.plano' => 'financeiro.assinatura.plan.changed',
            'assinaturas.valor' => 'financeiro.assinatura.valor.changed',
            default => null,
        };
    }

    protected static function registrarEventoCritico(Model $model, string $evento, ?int $empresaId, ?int $userId, array $dados): void
    {
        AuditoriaManualService::registrarEvento(
            $evento,
            [
                'dominio' => 'acao_critica',
                'origem' => 'auditoria_global_observer',
                'dados' => $dados,
            ],
            $model,
            $empresaId,
            $userId,
            in_array($evento, config('auditoria.high_risk_events', []), true) ? 'warning' : 'info'
        );
    }

    protected static function normalizarSnapshot(array $dados): array
    {
        $snapshot = [];

        foreach ($dados as $campo => $valor) {
            if (self::campoIgnorado((string) $campo)) {
                continue;
            }

            $snapshot[$campo] = self::normalizarValor($valor, (string) $campo);
        }

        return $snapshot;
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
