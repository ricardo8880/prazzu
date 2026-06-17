<?php

namespace App\Support;

use App\Models\Atendimento;
use App\Models\AtendimentoInteracao;
use App\Models\ItemControle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AtendimentoActionService
{
    public function __construct(private readonly AtendimentoWorkflowService $workflow)
    {
    }

    /**
     * @return array{changed: bool, message: string}
     */
    public function salvarDetalhe(Atendimento $atendimento, string $status, string $prioridade, ?int $responsavelId): array
    {
        $status = AtendimentoStatus::normalize($status, (string) $atendimento->status);
        $prioridade = array_key_exists($prioridade, AtendimentosData::PRIORIDADES)
            ? $prioridade
            : (string) $atendimento->prioridade;

        $responsavelId = $responsavelId && $this->workflow->usuarioResponsavelValido($responsavelId)
            ? $responsavelId
            : null;

        $payload = [
            'status' => $status,
            'prioridade' => $prioridade,
            'responsavel_id' => $responsavelId,
        ];

        $this->workflow->aplicarCamposStatus($atendimento, $status, $payload);

        if ($prioridade !== $atendimento->prioridade && ! AtendimentoStatus::isClosed($status)) {
            $slaHoras = AtendimentosData::slaHorasPorPrioridade($prioridade);
            $payload['sla_horas'] = $slaHoras;
            $payload['sla_limite_em'] = now()->addHours($slaHoras);
        }

        $mudancas = $this->mudancasDetalhe($atendimento, $status, $prioridade, $responsavelId);
        if (empty($mudancas)) {
            return ['changed' => false, 'message' => 'Nenhuma alteração pendente.'];
        }

        DB::transaction(function () use ($atendimento, $payload, $status, $mudancas): void {
            $atendimento->update($payload);
            $this->workflow->registrarInteracao(
                (int) $atendimento->id,
                'alteracao',
                'Atualização: ' . implode(', ', $mudancas) . '.',
                ['mudancas' => $mudancas]
            );
            $this->workflow->sincronizarPortalVinculado($atendimento->refresh(), $status);
        });

        return ['changed' => true, 'message' => 'Atendimento atualizado.'];
    }

    public function responderCliente(Atendimento $atendimento, string $mensagem, ?array $anexo = null): void
    {
        if (AtendimentoStatus::isClosed((string) $atendimento->status)) {
            throw new \RuntimeException('Este atendimento está finalizado. Reabra antes de responder ao cliente.');
        }

        DB::transaction(function () use ($atendimento, $mensagem, $anexo): void {
            $agora = now();
            $payload = [
                'status' => AtendimentoStatus::AGUARDANDO_CLIENTE,
                'updated_at' => $agora,
            ];

            if (! $atendimento->responsavel_id && auth()->id()) {
                $payload['responsavel_id'] = auth()->id();
            }

            if (! $atendimento->primeira_resposta_em) {
                $payload['primeira_resposta_em'] = $agora;
            }

            $atendimento->update($payload);

            $this->workflow->registrarInteracao(
                (int) $atendimento->id,
                'resposta',
                Str::limit($mensagem, 12000, ''),
                [
                    'origem_coluna' => 'suporte',
                    'origem' => 'painel_interno_suporte',
                    'visivel_cliente' => true,
                    'suporte_nome' => auth()->user()?->name,
                    'suporte_email' => auth()->user()?->email,
                    'anexos' => $anexo ? [$anexo] : [],
                ]
            );

            $this->workflow->sincronizarPortalVinculado($atendimento->refresh(), AtendimentoStatus::AGUARDANDO_CLIENTE);
        });
    }

    public function criarPendencia(Atendimento $atendimento): ItemControle
    {
        return DB::transaction(function () use ($atendimento): ItemControle {
            $item = $this->criarItemControleVinculado(
                $atendimento,
                'pendencia_compliance',
                'Pendência do atendimento #' . $atendimento->id . ' - ' . Str::limit((string) $atendimento->titulo, 120, ''),
                'Pendência criada a partir do atendimento #' . $atendimento->id . ".\n\n" . trim((string) ($atendimento->descricao ?: 'Sem descrição.')),
                false
            );

            if (! $atendimento->item_controle_id) {
                $atendimento->update(['item_controle_id' => $item->id, 'updated_at' => now()]);
            } else {
                $atendimento->touch();
            }

            $this->workflow->registrarInteracao(
                (int) $atendimento->id,
                'pendencia_criada',
                'Pendência criada a partir deste atendimento: #' . $item->id . ' - ' . $item->titulo . '.',
                ['item_controle_id' => $item->id, 'tipo' => 'pendencia_compliance']
            );

            return $item;
        });
    }

    public function solicitarDocumento(Atendimento $atendimento): ItemControle
    {
        return DB::transaction(function () use ($atendimento): ItemControle {
            $item = $this->criarItemControleVinculado(
                $atendimento,
                'documento',
                'Documento solicitado no atendimento #' . $atendimento->id,
                'Solicitação documental criada a partir do atendimento #' . $atendimento->id . '. ' . Str::limit(trim((string) $atendimento->titulo), 180, ''),
                true
            );

            $payload = ['status' => AtendimentoStatus::AGUARDANDO_CLIENTE, 'updated_at' => now()];
            if (! $atendimento->item_controle_id) {
                $payload['item_controle_id'] = $item->id;
            }
            if (! $atendimento->responsavel_id && auth()->id()) {
                $payload['responsavel_id'] = auth()->id();
            }
            if (! $atendimento->primeira_resposta_em) {
                $payload['primeira_resposta_em'] = now();
            }

            $atendimento->update($payload);

            $this->workflow->registrarInteracao(
                (int) $atendimento->id,
                'documento_solicitado',
                'Documento solicitado ao cliente e registrado em Documentos: #' . $item->id . ' - ' . $item->titulo . '.',
                ['item_controle_id' => $item->id, 'tipo' => 'documento', 'portal_ativo' => true]
            );

            $this->workflow->sincronizarPortalVinculado($atendimento->refresh(), AtendimentoStatus::AGUARDANDO_CLIENTE);

            return $item;
        });
    }

    public function adicionarComentario(Atendimento $atendimento, string $mensagem): void
    {
        DB::transaction(function () use ($atendimento, $mensagem): void {
            $this->workflow->registrarInteracao((int) $atendimento->id, 'comentario', $mensagem);

            if (! $atendimento->primeira_resposta_em && auth()->id()) {
                $atendimento->update(['primeira_resposta_em' => now()]);
            }
        });
    }

    /**
     * @return list<string>
     */
    private function mudancasDetalhe(Atendimento $atendimento, string $status, string $prioridade, ?int $responsavelId): array
    {
        $mudancas = [];

        if ($status !== $atendimento->status) {
            $mudancas[] = 'status de ' . AtendimentosData::statusLabel((string) $atendimento->status)
                . ' para ' . AtendimentosData::statusLabel($status);
        }

        if ($prioridade !== $atendimento->prioridade) {
            $mudancas[] = 'prioridade de ' . AtendimentosData::prioridadeLabel((string) $atendimento->prioridade)
                . ' para ' . AtendimentosData::prioridadeLabel($prioridade);
        }

        if ((int) $responsavelId !== (int) $atendimento->responsavel_id) {
            $mudancas[] = 'responsável atualizado';
        }

        return $mudancas;
    }

    private function criarItemControleVinculado(Atendimento $atendimento, string $tipo, string $titulo, string $descricao, bool $portalAtivo): ItemControle
    {
        if (! CachedSchema::hasTable('item_controles')) {
            throw new \RuntimeException('Tabela item_controles indisponível.');
        }

        $empresaId = (int) $atendimento->empresa_id;
        $responsavelId = ComplianceModuleData::resolveResponsavelId(null, $empresaId);

        if (! $empresaId || ! $responsavelId) {
            throw new \RuntimeException('Empresa ou responsável indisponível para criar item de controle.');
        }

        $payload = [];
        $this->setItemControlePayload($payload, 'titulo', Str::limit(trim($titulo), 255, ''));
        $this->setItemControlePayload($payload, 'descricao', Str::limit(trim($descricao), 5000, ''));
        $this->setItemControlePayload($payload, 'tipo', $tipo);
        $this->setItemControlePayload($payload, 'status', 'pendente');
        $this->setItemControlePayload($payload, 'prioridade', $this->prioridadeItemControle((string) $atendimento->prioridade));
        $this->setItemControlePayload($payload, 'empresa_id', $empresaId);
        $this->setItemControlePayload($payload, 'responsavel_id', $responsavelId);
        $this->setItemControlePayload($payload, 'data_vencimento', now()->addDays($portalAtivo ? 3 : 2)->toDateString());
        $this->setItemControlePayload($payload, 'portal_ativo', $portalAtivo);

        if ($portalAtivo) {
            $this->setItemControlePayload($payload, 'portal_cliente_nome', $this->workflow->nomeClienteAtendimento($atendimento));
            $this->setItemControlePayload($payload, 'portal_cliente_email', $this->workflow->emailClienteAtendimento($atendimento));
            $this->setItemControlePayload($payload, 'portal_expira_em', now()->addDays(7));
            $this->setItemControlePayload($payload, 'portal_status', 'pendente');
            $this->setItemControlePayload($payload, 'document_status', 'solicitado');
        }

        return ItemControle::query()->create($payload);
    }

    private function setItemControlePayload(array &$payload, string $column, mixed $value): void
    {
        if (CachedSchema::hasColumn('item_controles', $column)) {
            $payload[$column] = $value;
        }
    }

    private function prioridadeItemControle(string $prioridade): string
    {
        return in_array($prioridade, ['baixa', 'media', 'alta', 'urgente'], true) ? $prioridade : 'media';
    }
}
