<?php

namespace App\Models;


use App\Support\CachedSchema;
use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ItemControle extends Model
{
    use Loggable;

    protected $table = 'item_controles';

    protected $fillable = [
        'titulo',
        'descricao',
        'tipo',
        'categoria_id',
        'status',
        'prioridade',
        'arquivo',
        'data_vencimento',
        'data_conclusao',
        'empresa_id',
        'responsavel_id',
        'observacao',
        'portal_ativo',
        'portal_token',
        'portal_cliente_nome',
        'portal_cliente_email',
        'portal_expira_em',
        'sla_horas',
        'sla_inicio_em',
        'sla_limite_em',
        'sla_concluido_em',
        'sla_status',
        'sla_prazo_alvo_em',
        'fluxo_operacional_id',
        'contrato_numero',
        'contrato_parte_nome',
        'contrato_parte_documento',
        'contrato_valor',
        'contrato_inicio_em',
        'contrato_fim_em',
        'contrato_status',
        'view_type',
        'automation_status',
        'risco_score',
        'bloqueado_por_dependencia',
        'kanban_order',
        'blocked_by_dependency',
        'estimated_minutes',
        'actual_minutes',
        'custom_payload',
        'template_id',
        'approval_required',
        'approval_status',
        'document_status',
        'portal_status',
        'ultima_interacao_cliente_em',
        'risk_probability',
        'risk_impact',
        'risk_score',
        'urgencia',
        'valor_tarefa',
        'bloqueado',
        'faturado_em',
        'pago_em',
        'status_operacional_at',
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'data_conclusao' => 'date',
        'categoria_id' => 'integer',
        'portal_ativo' => 'boolean',
        'portal_expira_em' => 'datetime',
        'sla_horas' => 'integer',
        'sla_inicio_em' => 'datetime',
        'sla_limite_em' => 'datetime',
        'sla_concluido_em' => 'datetime',
        'sla_prazo_alvo_em' => 'datetime',
        'fluxo_operacional_id' => 'integer',
        'contrato_valor' => 'decimal:2',
        'contrato_inicio_em' => 'date',
        'contrato_fim_em' => 'date',
        'risco_score' => 'integer',
        'bloqueado_por_dependencia' => 'boolean',
        'kanban_order' => 'integer',
        'blocked_by_dependency' => 'boolean',
        'estimated_minutes' => 'integer',
        'actual_minutes' => 'integer',
        'custom_payload' => 'array',
        'template_id' => 'integer',
        'approval_required' => 'boolean',
        'risk_probability' => 'integer',
        'risk_impact' => 'integer',
        'risk_score' => 'integer',
        'valor_tarefa' => 'decimal:2',
        'bloqueado' => 'boolean',
        'faturado_em' => 'datetime',
        'pago_em' => 'datetime',
        'status_operacional_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ItemControle $itemControle): void {
            $empresa = $itemControle->empresa_id ? Empresa::query()->find($itemControle->empresa_id) : null;

            if (! $empresa || ! $empresa->isAtivo()) {
                throw ValidationException::withMessages([
                    'empresa_id' => 'Empresa inativa ou não vinculada. Não é possível criar itens de controle.',
                ]);
            }

            if ($empresa->atingiuLimiteItens()) {
                throw ValidationException::withMessages([
                    'empresa_id' => 'Limite de itens de controle do plano da empresa atingido. Altere o plano antes de cadastrar novos itens.',
                ]);
            }
        });
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function responsavel()
    {
        return $this->belongsTo(Responsavel::class);
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaItemControle::class, 'categoria_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }

    public function comentariosKanban()
    {
        return $this->hasMany(ItemControleComentario::class, 'item_controle_id')
            ->latest('created_at');
    }

    public function anexos()
    {
        return $this->hasMany(ItemControleAnexo::class, 'item_controle_id')
            ->latest('id');
    }

    public function documentVersions()
    {
        return $this->hasMany(PrazzuDocumentVersion::class, 'item_controle_id')
            ->latest('version_number')
            ->latest('id');
    }

    public function clientPortalMessages()
    {
        return $this->hasMany(PrazzuClientPortalMessage::class, 'item_controle_id')
            ->latest('id');
    }

    public function dependencies()
    {
        return $this->hasMany(PrazzuDependency::class, 'item_controle_id')
            ->latest('id');
    }

    public function blockers()
    {
        return $this->hasMany(PrazzuDependency::class, 'depends_on_item_controle_id')
            ->latest('id');
    }

    public function checklists()
    {
        return $this->hasMany(ItemControleChecklist::class, 'item_controle_id')
            ->orderBy('ordem')
            ->orderBy('id');
    }

    public function tags()
    {
        return $this->belongsToMany(
            ItemControleTag::class,
            'item_controle_tag_relations',
            'item_controle_id',
            'item_controle_tag_id'
        )->withTimestamps();
    }

    public function assinaturas()
    {
        return $this->hasMany(ItemControleAssinatura::class, 'item_controle_id')
            ->latest('assinado_em');
    }

    public function ultimaAssinatura()
    {
        return $this->hasOne(ItemControleAssinatura::class, 'item_controle_id')
            ->latestOfMany('assinado_em');
    }

    public function aprovacoes()
    {
        return $this->hasMany(ItemControleAprovacao::class, 'item_controle_id')
            ->latest('id');
    }

    public function ultimaAprovacao()
    {
        return $this->hasOne(ItemControleAprovacao::class, 'item_controle_id')
            ->latestOfMany('id');
    }

    public function timelines()
    {
        return $this->hasMany(ItemControleTimeline::class, 'item_controle_id')
            ->latest('id');
    }

    public function notificacoesInternas()
    {
        return $this->hasMany(NotificacaoInterna::class, 'item_controle_id')
            ->latest('id');
    }

    public function etapasOperacionais()
    {
        return $this->hasMany(FluxoOperacionalExecucao::class, 'item_controle_id')
            ->with('etapa')
            ->orderBy('id');
    }

    public function fluxoOperacional()
    {
        return $this->belongsTo(FluxoOperacional::class, 'fluxo_operacional_id');
    }


    public function fluxoExecucoes()
    {
        return $this->hasMany(FluxoOperacionalExecucao::class, 'item_controle_id')
            ->orderBy('id');
    }

    public function auditoriasDetalhadas()
    {
        return $this->morphMany(AuditoriaDetalhada::class, 'auditable', 'auditable_type', 'auditable_id');
    }

    public function foiAssinado(): bool
    {
        if (array_key_exists('assinaturas_count', $this->attributes)) {
            return (int) $this->attributes['assinaturas_count'] > 0;
        }

        return $this->assinaturas()->exists();
    }

    public function getAssinaturaResumo(): string
    {
        if ($this->foiAssinado()) {
            $ultima = $this->relationLoaded('ultimaAssinatura')
                ? $this->ultimaAssinatura
                : $this->ultimaAssinatura()->first();

            return $ultima?->nome
                ? 'Assinado por ' . $ultima->nome
                : 'Assinado';
        }

        if ($this->portal_ativo && filled($this->portal_token)) {
            return 'Aguardando assinatura';
        }

        return 'Não enviado';
    }

    public function getAssinaturaColor(): string
    {
        if ($this->foiAssinado()) {
            return 'success';
        }

        if ($this->portal_ativo && filled($this->portal_token)) {
            return 'warning';
        }

        return 'gray';
    }

    public function getAssinaturaEnviadaEm(): ?\Illuminate\Support\Carbon
    {
        $payload = is_array($this->custom_payload) ? $this->custom_payload : [];
        $enviadoEm = $payload['assinatura']['enviado_em'] ?? null;

        if (blank($enviadoEm)) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($enviadoEm);
        } catch (\Throwable) {
            return null;
        }
    }

    public function gerarPortalTokenSeNecessario(): string
    {
        if (filled($this->portal_token)) {
            return (string) $this->portal_token;
        }

        do {
            $token = Str::random(64);
        } while (
            self::query()
                ->where('portal_token', $token)
                ->exists()
        );

        $this->forceFill([
            'portal_token' => $token,
        ])->save();

        return $token;
    }

    public function ativarPortalCliente(): bool
    {
        $this->gerarPortalTokenSeNecessario();

        $resultado = $this->update([
            'portal_ativo' => true,
        ]);

        if ($resultado) {
            $this->registrarTimeline(
                'atualizacao',
                'Portal do cliente ativado',
                'O acesso externo do cliente foi ativado para este item.'
            );
        }

        return $resultado;
    }

    public function desativarPortalCliente(): bool
    {
        $resultado = $this->update([
            'portal_ativo' => false,
        ]);

        if ($resultado) {
            $this->registrarTimeline(
                'atualizacao',
                'Portal do cliente desativado',
                'O acesso externo do cliente foi desativado para este item.'
            );
        }

        return $resultado;
    }

    public function portalEstaDisponivel(): bool
    {
        if (! $this->portal_ativo) {
            return false;
        }

        if (blank($this->portal_token)) {
            return false;
        }

        if ($this->portal_expira_em && $this->portal_expira_em->isPast()) {
            return false;
        }

        return true;
    }

    public function getPortalUrl(): ?string
    {
        if (blank($this->portal_token)) {
            return null;
        }

        return route('portal.item-controles.show', [
            'token' => $this->portal_token,
        ]);
    }

    public function getTipoOuCategoria(): string
    {
        if ($this->categoria?->nome) {
            return $this->categoria->nome;
        }

        return match ($this->tipo) {
            'contrato' => 'Contrato',
            'documento' => 'Documento',
            'licenca' => 'Licença',
            'acordo' => 'Acordo',
            default => ucfirst((string) $this->tipo),
        };
    }

    public function getTagsResumo(): string
    {
        if (! $this->relationLoaded('tags')) {
            return '-';
        }

        if ($this->tags->isEmpty()) {
            return '-';
        }

        return $this->tags->pluck('nome')->join(', ');
    }

    public function getPrioridadeExibicao(): string
    {
        return match ($this->prioridade) {
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            'critica' => 'Crítica',
            'urgente' => 'Urgente',
            default => 'Média',
        };
    }

    public function getPrioridadeColor(): string
    {
        return match ($this->prioridade) {
            'baixa' => 'gray',
            'media' => 'info',
            'alta' => 'warning',
            'critica' => 'danger',
            'urgente' => 'danger',
            default => 'info',
        };
    }


    public function getUrgenciaExibicao(): string
    {
        return match ($this->urgencia ?: $this->prioridade) {
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            'critica', 'urgente' => 'Crítica',
            default => 'Média',
        };
    }

    public function getUrgenciaColor(): string
    {
        return match ($this->urgencia ?: $this->prioridade) {
            'baixa' => 'gray',
            'media' => 'info',
            'alta' => 'warning',
            'critica', 'urgente' => 'danger',
            default => 'info',
        };
    }

    public function estaBloqueadoOperacionalmente(): bool
    {
        return (bool) ($this->bloqueado ?? false)
            || (bool) ($this->blocked_by_dependency ?? false)
            || (bool) ($this->bloqueado_por_dependencia ?? false);
    }

    public function getValorOperacional(): float
    {
        return (float) ($this->valor_tarefa ?? $this->contrato_valor ?? 0);
    }

    public function getStatusExibicao(): string
    {
        return match ($this->status) {
            'pendente' => 'Pendente',
            'pronto' => 'Pronto',
            'em_revisao' => 'Em revisão',
            'aguardando_aprovacao' => 'Aguardando aprovação',
            'em_aprovacao' => 'Em aprovação',
            'correcao_necessaria' => 'Correção necessária',
            'aprovado' => 'Aprovado',
            'reprovado' => 'Reprovado',
            'em_andamento' => 'Em andamento',
            'assinado' => 'Assinado',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
            'vencido' => 'Vencido',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusExibicaoFormatado(): string
    {
        return $this->getStatusExibicao();
    }

    public function getStatusExibicaoColor(): string
    {
        return match ($this->status) {
            'pendente' => 'warning',
            'pronto' => 'info',
            'em_revisao' => 'warning',
            'aguardando_aprovacao' => 'warning',
            'em_aprovacao' => 'warning',
            'correcao_necessaria' => 'danger',
            'aprovado' => 'success',
            'reprovado' => 'danger',
            'em_andamento' => 'info',
            'assinado' => 'success',
            'concluido' => 'success',
            'cancelado' => 'gray',
            'vencido' => 'danger',
            default => 'secondary',
        };
    }

    public function isVencido(): bool
    {
        if (! $this->data_vencimento) {
            return false;
        }

        return $this->data_vencimento < now()
            && ! in_array((string) $this->status, ['concluido', 'cancelado'], true);
    }

    public function isConcluido(): bool
    {
        return $this->status === 'concluido';
    }

    public function getDiasRestantes(): ?int
    {
        if (! $this->data_vencimento) {
            return null;
        }

        return now()->diffInDays($this->data_vencimento, false);
    }

    public function getDiasRestantesColor(): string
    {
        if (! $this->data_vencimento) {
            return 'gray';
        }

        if ($this->isVencido()) {
            return 'danger';
        }

        $dias = $this->getDiasRestantes();

        if ($dias === 0 || $dias <= 3) {
            return 'warning';
        }

        return 'success';
    }

    public function getSituacaoPrazo(): string
    {
        if (! $this->data_vencimento) {
            return 'Sem prazo';
        }

        if ($this->isVencido()) {
            return 'Vencido';
        }

        if ($this->data_vencimento->isToday()) {
            return 'Vence hoje';
        }

        if ($this->data_vencimento->diffInDays(now()) <= 3) {
            return 'Próximo do vencimento';
        }

        return 'No prazo';
    }

    public function getSituacaoPrazoColor(): string
    {
        if (! $this->data_vencimento) {
            return 'gray';
        }

        if ($this->isVencido()) {
            return 'danger';
        }

        if ($this->data_vencimento->isToday()) {
            return 'warning';
        }

        if ($this->data_vencimento->diffInDays(now()) <= 3) {
            return 'warning';
        }

        return 'success';
    }

    public function hasAnexoPrincipal(): bool
    {
        if (! empty($this->arquivo)) {
            return true;
        }

        return $this->anexos()->exists();
    }

    public function getTotalChecklist(): int
    {
        if (array_key_exists('checklists_count', $this->attributes)) {
            return (int) $this->attributes['checklists_count'];
        }

        return (int) $this->checklists()->count();
    }

    public function getChecklistConcluidos(): int
    {
        if (array_key_exists('checklists_concluidos_count', $this->attributes)) {
            return (int) $this->attributes['checklists_concluidos_count'];
        }

        return (int) $this->checklists()
            ->where('concluido', true)
            ->count();
    }

    public function getChecklistResumo(): string
    {
        $total = $this->getTotalChecklist();

        if ($total <= 0) {
            return '-';
        }

        return $this->getChecklistConcluidos() . '/' . $total;
    }

    public function getChecklistPercentual(): int
    {
        $total = $this->getTotalChecklist();

        if ($total <= 0) {
            return 0;
        }

        return (int) round(($this->getChecklistConcluidos() / $total) * 100);
    }

    public function getChecklistColor(): string
    {
        $total = $this->getTotalChecklist();

        if ($total <= 0) {
            return 'gray';
        }

        $percentual = $this->getChecklistPercentual();

        if ($percentual >= 100) {
            return 'success';
        }

        if ($percentual >= 50) {
            return 'warning';
        }

        return 'danger';
    }

    public function getAprovacaoResumo(): string
    {
        if (! $this->ultimaAprovacao) {
            return 'Sem aprovação';
        }

        return $this->ultimaAprovacao->getStatusExibicao();
    }

    public function getAprovacaoColor(): string
    {
        if (! $this->ultimaAprovacao) {
            return 'gray';
        }

        return $this->ultimaAprovacao->getStatusColor();
    }

    public function possuiAprovacaoPendente(): bool
    {
        if ($this->relationLoaded('ultimaAprovacao') && $this->ultimaAprovacao) {
            return $this->ultimaAprovacao->status === 'pendente';
        }

        return $this->aprovacoes()
            ->where('status', 'pendente')
            ->exists();
    }

    public function podeSolicitarAprovacao(): bool
    {
        return ! in_array((string) $this->status, [
            'em_aprovacao',
            'aprovado',
            'concluido',
            'cancelado',
            'vencido',
        ], true);
    }

    public function solicitarAprovacao(?User $user = null, ?string $observacao = null): ItemControleAprovacao
    {
        $user ??= Auth::user();

        $aprovacao = $this->aprovacoes()->create([
            'empresa_id' => $this->empresa_id,
            'solicitante_id' => $user?->id,
            'status' => 'pendente',
            'observacao_solicitacao' => $observacao,
            'solicitado_em' => now(),
        ]);

        $this->update([
            'status' => 'em_aprovacao',
        ]);

        $this->registrarTimeline(
            'aprovacao_solicitada',
            'Aprovação solicitada',
            $observacao ?: 'O item foi enviado para aprovação.',
            [
                'aprovacao_id' => $aprovacao->id,
                'solicitante_id' => $user?->id,
            ]
        );

        $this->gerarNotificacaoInterna(
            'Aprovação solicitada',
            'O item "' . $this->titulo . '" foi enviado para aprovação.',
            null,
            'aprovacao'
        );

        return $aprovacao;
    }

    public function aprovar(?User $user = null, ?string $observacao = null, ?int $aprovacaoId = null): bool
    {
        $user ??= Auth::user();

        $aprovacaoQuery = $this->aprovacoes()->where('status', 'pendente');

        if ($aprovacaoId) {
            $aprovacaoQuery->whereKey($aprovacaoId);
        }

        $aprovacao = $aprovacaoQuery->latest('id')->first();

        if (! $aprovacao && $aprovacaoId) {
            return false;
        }

        if (! $aprovacao) {
            $aprovacao = $this->aprovacoes()->create([
                'empresa_id' => $this->empresa_id,
                'solicitante_id' => null,
                'status' => 'pendente',
                'solicitado_em' => now(),
            ]);
        }

        $aprovacao->update([
            'aprovador_id' => $user?->id,
            'status' => 'aprovado',
            'observacao_resposta' => $observacao,
            'respondido_em' => now(),
        ]);

        $payload = [
            'status' => 'aprovado',
        ];

        if (CachedSchema::hasColumn($this->getTable(), 'approval_status')) {
            $payload['approval_status'] = 'aprovado';
        }

        if (CachedSchema::hasColumn($this->getTable(), 'approval_required')) {
            $payload['approval_required'] = false;
        }

        $resultado = $this->update($payload);

        if ($resultado) {
            $this->registrarTimeline(
                'aprovacao_aprovada',
                'Item aprovado',
                $observacao ?: 'O item foi aprovado.',
                [
                    'aprovacao_id' => $aprovacao->id,
                    'aprovador_id' => $user?->id,
                ]
            );
        }

        return $resultado;
    }

    public function reprovar(?User $user = null, ?string $observacao = null, ?int $aprovacaoId = null): bool
    {
        $user ??= Auth::user();

        $aprovacaoQuery = $this->aprovacoes()->where('status', 'pendente');

        if ($aprovacaoId) {
            $aprovacaoQuery->whereKey($aprovacaoId);
        }

        $aprovacao = $aprovacaoQuery->latest('id')->first();

        if (! $aprovacao && $aprovacaoId) {
            return false;
        }

        if (! $aprovacao) {
            $aprovacao = $this->aprovacoes()->create([
                'empresa_id' => $this->empresa_id,
                'solicitante_id' => null,
                'status' => 'pendente',
                'solicitado_em' => now(),
            ]);
        }

        $aprovacao->update([
            'aprovador_id' => $user?->id,
            'status' => 'reprovado',
            'observacao_resposta' => $observacao,
            'motivo_reprovacao' => $observacao,
            'respondido_em' => now(),
        ]);

        $payload = [
            'status' => 'reprovado',
        ];

        if (CachedSchema::hasColumn($this->getTable(), 'approval_status')) {
            $payload['approval_status'] = 'reprovado';
        }

        if (CachedSchema::hasColumn($this->getTable(), 'approval_required')) {
            $payload['approval_required'] = true;
        }

        $resultado = $this->update($payload);

        if ($resultado) {
            $this->registrarTimeline(
                'aprovacao_reprovada',
                'Item reprovado',
                $observacao ?: 'O item foi reprovado.',
                [
                    'aprovacao_id' => $aprovacao->id,
                    'aprovador_id' => $user?->id,
                ]
            );
        }

        return $resultado;
    }

    public function registrarTimeline(
        string $tipo,
        string $titulo,
        ?string $descricao = null,
        ?array $dados = null,
        ?User $user = null
    ): ?ItemControleTimeline {
        $user ??= Auth::user();

        if (! $this->exists) {
            return null;
        }

        return $this->timelines()->create([
            'empresa_id' => $this->empresa_id,
            'user_id' => $user?->id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'dados' => $dados,
        ]);
    }

    public function gerarNotificacaoInterna(
        string $titulo,
        ?string $mensagem = null,
        ?int $userId = null,
        string $tipo = 'manual'
    ): NotificacaoInterna {
        $destinatarioId = $userId ?: $this->responsavel?->user_id;

        $notificacao = $this->notificacoesInternas()->create([
            'empresa_id' => $this->empresa_id,
            'user_id' => $destinatarioId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensagem' => $mensagem,
            'lida' => false,
        ]);

        $this->registrarTimeline(
            'notificacao',
            'Alerta interno criado',
            $titulo,
            [
                'notificacao_id' => $notificacao->id,
                'destinatario_id' => $destinatarioId,
                'tipo' => $tipo,
            ]
        );

        return $notificacao;
    }

    public function iniciarSla(?int $horas = null): bool
    {
        $this->syncOriginal();

        unset($this->attributes['oldAttributes']);
        unset($this->original['oldAttributes']);
        unset($this->changes['oldAttributes']);

        $horas = $horas ?: $this->getSlaHorasPadrao();
        $inicio = now();

        $dadosSla = [
            'sla_horas' => $horas,
            'sla_inicio_em' => $inicio,
            'sla_limite_em' => $inicio->copy()->addHours($horas),
            'sla_status' => 'em_andamento',
        ];

        if (CachedSchema::hasColumn($this->getTable(), 'sla_prazo_alvo_em')) {
            $dadosSla['sla_prazo_alvo_em'] = $inicio->copy()->addHours($horas);
        }

        $resultado = $this->forceFill($dadosSla)->save();

        if ($resultado) {
            $this->registrarTimeline(
                'sla',
                'SLA iniciado',
                'SLA iniciado com limite de ' . $horas . ' hora(s).'
            );
        }

        return $resultado;
    }

    public function concluirSla(): bool
    {
        unset($this->attributes['oldAttributes']);
        unset($this->original['oldAttributes']);
        unset($this->changes['oldAttributes']);

        $status = $this->calcularSlaStatusFinal();

        $resultado = $this->forceFill([
            'sla_concluido_em' => now(),
            'sla_status' => $status,
        ])->save();

        if ($resultado) {
            $this->registrarTimeline(
                'sla',
                'SLA concluído',
                'SLA finalizado com status: ' . $this->getSlaStatusExibicao($status) . '.'
            );
        }

        return $resultado;
    }

    public function atualizarSlaStatus(): bool
    {
        if (! $this->sla_limite_em) {
            return false;
        }

        if ($this->sla_concluido_em) {
            return false;
        }

        $status = now()->greaterThan($this->sla_limite_em)
            ? 'atrasado'
            : 'em_andamento';

        if ($this->sla_status === $status) {
            return true;
        }

        unset($this->attributes['oldAttributes']);
        unset($this->original['oldAttributes']);
        unset($this->changes['oldAttributes']);

        return $this->forceFill([
            'sla_status' => $status,
        ])->save();
    }

    public function getSlaHorasPadrao(): int
    {
        return match ($this->prioridade) {
            'urgente' => 4,
            'alta' => 12,
            'media' => 24,
            'baixa' => 72,
            default => 24,
        };
    }

    public function calcularSlaStatusFinal(): string
    {
        if (! $this->sla_limite_em) {
            return 'sem_sla';
        }

        return now()->greaterThan($this->sla_limite_em)
            ? 'concluido_atrasado'
            : 'concluido_no_prazo';
    }

    public function getSlaResumo(): string
    {
        if (! $this->sla_status) {
            return 'Sem SLA';
        }

        return $this->getSlaStatusExibicao($this->sla_status);
    }

    public function getSlaStatusExibicao(?string $status = null): string
    {
        return match ($status ?: $this->sla_status) {
            'em_andamento' => 'Em andamento',
            'atrasado' => 'Atrasado',
            'concluido_no_prazo' => 'Concluído no prazo',
            'concluido_atrasado' => 'Concluído com atraso',
            'sem_sla' => 'Sem SLA',
            default => 'Sem SLA',
        };
    }

    public function getSlaColor(): string
    {
        return match ($this->sla_status) {
            'em_andamento' => 'info',
            'atrasado' => 'danger',
            'concluido_no_prazo' => 'success',
            'concluido_atrasado' => 'warning',
            default => 'gray',
        };
    }

    public function getSlaPrazoAlvo(): ?string
    {
        $prazo = $this->sla_prazo_alvo_em ?: $this->sla_limite_em;

        return $prazo ? $prazo->format('d/m/Y H:i') : null;
    }

    public function getSlaPercentualConsumido(): int
    {
        if (! $this->sla_inicio_em || ! $this->sla_limite_em) {
            return 0;
        }

        $total = max(1, (int) $this->sla_inicio_em->diffInMinutes($this->sla_limite_em));
        $usado = max(0, (int) $this->sla_inicio_em->diffInMinutes($this->sla_concluido_em ?: now()));

        return min(100, (int) round(($usado / $total) * 100));
    }

    public function getContextoOperacionalIa(): array
    {
        return [
            'item' => [
                'id' => $this->id,
                'titulo' => $this->titulo,
                'descricao' => $this->descricao,
                'status' => $this->getStatusExibicao(),
                'prioridade' => $this->getPrioridadeExibicao(),
                'vencimento' => $this->data_vencimento?->format('d/m/Y'),
                'situacao_prazo' => $this->getSituacaoPrazo(),
            ],
            'empresa' => [
                'id' => $this->empresa_id,
                'nome' => $this->empresa?->razao_social ?: $this->empresa?->nome_fantasia,
            ],
            'responsavel' => [
                'id' => $this->responsavel_id,
                'nome' => $this->responsavel?->nome,
                'email' => $this->responsavel?->email,
            ],
            'checklist' => [
                'total' => $this->getTotalChecklist(),
                'concluidos' => $this->getChecklistConcluidos(),
                'percentual' => $this->getChecklistPercentual(),
            ],
            'sla' => [
                'status' => $this->getSlaResumo(),
                'prazo_alvo' => $this->getSlaPrazoAlvo(),
                'tempo_restante' => $this->getSlaTempoRestanteResumo(),
                'percentual_consumido' => $this->getSlaPercentualConsumido(),
            ],
            'contrato' => [
                'numero' => $this->contrato_numero,
                'parte' => $this->contrato_parte_nome,
                'fim' => $this->contrato_fim_em?->format('d/m/Y'),
                'status' => $this->getContratoStatusResumo(),
            ],
        ];
    }

    public function getSlaTempoRestanteResumo(): string
    {
        if (! $this->sla_limite_em) {
            return '-';
        }

        if ($this->sla_concluido_em) {
            return 'Finalizado';
        }

        if (now()->greaterThan($this->sla_limite_em)) {
            return 'Atrasado';
        }

        $minutos = (int) now()->diffInMinutes($this->sla_limite_em, false);

        if ($minutos < 60) {
            return $minutos . ' min';
        }

        $horas = intdiv($minutos, 60);
        $restoMinutos = $minutos % 60;

        return $horas . 'h' . ($restoMinutos > 0 ? ' ' . $restoMinutos . 'min' : '');
    }

    public function isContrato(): bool
    {
        if ($this->tipo === 'contrato') {
            return true;
        }

        if ($this->relationLoaded('categoria') && $this->categoria?->nome) {
            return mb_strtolower($this->categoria->nome) === 'contrato';
        }

        return $this->categoria()
            ->whereRaw('LOWER(nome) = ?', ['contrato'])
            ->exists();
    }

    public function getContratoStatusResumo(): string
    {
        if ($this->contrato_status) {
            return $this->getContratoStatusExibicao();
        }

        if (! $this->contrato_fim_em) {
            return 'Sem vencimento';
        }

        if ($this->contrato_fim_em->isPast()) {
            return 'Vencido';
        }

        if ($this->contrato_fim_em->diffInDays(now()) <= 30) {
            return 'Vence em breve';
        }

        return 'Vigente';
    }

    public function getContratoStatusExibicao(): string
    {
        return match ($this->contrato_status) {
            'rascunho' => 'Rascunho',
            'vigente' => 'Vigente',
            'vencendo' => 'Vencendo',
            'vencido' => 'Vencido',
            'encerrado' => 'Encerrado',
            'cancelado' => 'Cancelado',
            default => ucfirst((string) $this->contrato_status),
        };
    }

    public function getContratoStatusColor(): string
    {
        $status = $this->contrato_status ?: null;

        if (! $status && $this->contrato_fim_em) {
            if ($this->contrato_fim_em->isPast()) {
                return 'danger';
            }

            if ($this->contrato_fim_em->diffInDays(now()) <= 30) {
                return 'warning';
            }

            return 'success';
        }

        return match ($status) {
            'vigente' => 'success',
            'vencendo' => 'warning',
            'vencido' => 'danger',
            'encerrado' => 'gray',
            'cancelado' => 'danger',
            'rascunho' => 'info',
            default => 'gray',
        };
    }

    public function atualizarStatusContrato(): bool
    {
        if (! $this->isContrato()) {
            return false;
        }

        if (in_array((string) $this->contrato_status, ['encerrado', 'cancelado'], true)) {
            return true;
        }

        if (! $this->contrato_fim_em) {
            return $this->update([
                'contrato_status' => $this->contrato_status ?: 'rascunho',
            ]);
        }

        $novoStatus = match (true) {
            $this->contrato_fim_em->isPast() => 'vencido',
            $this->contrato_fim_em->diffInDays(now()) <= 30 => 'vencendo',
            default => 'vigente',
        };

        if ($this->contrato_status === $novoStatus) {
            return true;
        }

        $resultado = $this->update([
            'contrato_status' => $novoStatus,
        ]);

        if ($resultado) {
            $this->registrarTimeline(
                'contrato',
                'Status do contrato atualizado',
                'Novo status: ' . $this->getContratoStatusExibicao() . '.'
            );
        }

        return $resultado;
    }

    public function scopeVisibleForUser(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if (! $user->empresa_id) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdminEmpresa() || $user->isGestor()) {
            return $query->where('empresa_id', $user->empresa_id);
        }

        if ($user->isUser()) {
            return $query->where('responsavel_id', $user->responsavel?->id);
        }

        return $query;
    }

    public function canBeAccessedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isUser()) {
            return $this->responsavel_id === $user->responsavel?->id;
        }

        return $user->empresa_id === $this->empresa_id;
    }

    public function canBeModifiedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdminEmpresa() || $user->isGestor()) {
            return $user->empresa_id === $this->empresa_id;
        }

        if ($user->isUser()) {
            return $this->responsavel_id === $user->responsavel?->id;
        }

        return false;
    }

    public function canBeDeletedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdminEmpresa()) {
            return $user->empresa_id === $this->empresa_id;
        }

        if ($user->isUser()) {
            return $this->responsavel_id === $user->responsavel?->id;
        }

        return false;
    }

    public function canBeApprovedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->hasEmpresaVinculada()) {
            return false;
        }

        if ((int) $user->empresa_id !== (int) $this->empresa_id) {
            return false;
        }

        return $user->isAdminEmpresa() || $user->isGestor();
    }
}
