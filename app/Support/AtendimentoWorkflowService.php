<?php

namespace App\Support;

use App\Models\Atendimento;
use App\Models\AtendimentoInteracao;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AtendimentoWorkflowService
{
    public function findAutorizado(int $id, bool $notify = true, ?callable $notifier = null): ?Atendimento
    {
        if (! CachedSchema::hasTable('atendimentos')) {
            $this->notify($notifier, 'danger', 'Tabela atendimentos não encontrada. Execute o SQL do Lote 1 antes de usar o módulo.');
            return null;
        }

        $atendimento = Atendimento::query()->find($id);
        if (! $atendimento || ! AtendimentosData::usuarioPodeAcessarEmpresa((int) $atendimento->empresa_id)) {
            if ($notify) {
                $this->notify($notifier, 'danger', 'Atendimento não encontrado ou sem permissão.');
            }
            return null;
        }

        return $atendimento;
    }

    public function assumir(Atendimento $atendimento): void
    {
        $novoStatus = $atendimento->status === AtendimentoStatus::ABERTO
            ? AtendimentoStatus::EM_ANDAMENTO
            : (string) $atendimento->status;

        $payload = [
            'responsavel_id' => auth()->id(),
            'status' => $novoStatus,
        ];

        if (! $atendimento->primeira_resposta_em) {
            $payload['primeira_resposta_em'] = now();
        }

        DB::transaction(function () use ($atendimento, $payload): void {
            $atendimento->update($payload);
            $this->registrarInteracao(
                (int) $atendimento->id,
                'responsavel',
                'Atendimento assumido por ' . (auth()->user()?->name ?: 'usuário interno') . '.'
            );
        });
    }

    public function aplicarStatus(Atendimento $atendimento, string $status, ?string $mensagemOperacional = null): void
    {
        $payload = ['status' => $status];
        $this->aplicarCamposStatus($atendimento, $status, $payload);

        if ($status === AtendimentoStatus::EM_ANDAMENTO && ! $atendimento->responsavel_id && auth()->id()) {
            $payload['responsavel_id'] = auth()->id();
        }

        if (AtendimentoStatus::isActive($status) && ! $atendimento->sla_limite_em) {
            $slaHoras = AtendimentosData::slaHorasPorPrioridade($atendimento->prioridade ?: 'media');
            $payload['sla_horas'] = $slaHoras;
            $payload['sla_limite_em'] = now()->addHours($slaHoras);
        }

        DB::transaction(function () use ($atendimento, $payload, $status, $mensagemOperacional): void {
            $statusAnterior = (string) $atendimento->status;
            $atendimento->update($payload);

            $tipo = $status === AtendimentoStatus::RESOLVIDO
                ? 'resolucao'
                : ($status === AtendimentoStatus::EM_ANDAMENTO ? 'reabertura' : 'alteracao');

            $mensagem = 'Status alterado de ' . AtendimentosData::statusLabel($statusAnterior)
                . ' para ' . AtendimentosData::statusLabel($status) . '.';

            if ($mensagemOperacional) {
                $mensagem .= "\n\nResumo: " . $mensagemOperacional;
            }

            $this->registrarInteracao((int) $atendimento->id, $tipo, $mensagem);
            $this->sincronizarPortalVinculado($atendimento->refresh(), $status, $mensagemOperacional);
        });
    }

    public function aplicarCamposStatus(Atendimento $atendimento, string $status, array &$payload): void
    {
        if ($status === AtendimentoStatus::RESOLVIDO && ! $atendimento->resolvido_em) {
            $payload['resolvido_em'] = now();
        }

        if ($status === AtendimentoStatus::FECHADO && ! $atendimento->fechado_em) {
            $payload['fechado_em'] = now();
        }

        if (AtendimentoStatus::isActive($status)) {
            $payload['resolvido_em'] = null;
            $payload['fechado_em'] = null;
        }
    }

    public function sincronizarPortalVinculado(Atendimento $atendimento, string $status, ?string $mensagemOperacional = null): void
    {
        if (! $atendimento->portal_solicitacao_id || ! CachedSchema::hasTable('portal_solicitacoes')) {
            return;
        }

        $portalStatus = AtendimentoStatus::toPortalStatus($status);
        $payload = ['status' => $portalStatus];

        if (in_array($status, [AtendimentoStatus::RESOLVIDO, AtendimentoStatus::FECHADO], true)) {
            if (CachedSchema::hasColumn('portal_solicitacoes', 'resposta')) {
                $payload['resposta'] = $mensagemOperacional ?: 'Atendimento marcado como resolvido pela equipe interna.';
            }
            if (CachedSchema::hasColumn('portal_solicitacoes', 'respondido_por')) {
                $payload['respondido_por'] = auth()->id();
            }
            if (CachedSchema::hasColumn('portal_solicitacoes', 'respondido_em')) {
                $payload['respondido_em'] = now();
            }
        }

        DB::table('portal_solicitacoes')
            ->where('id', $atendimento->portal_solicitacao_id)
            ->where('empresa_id', $atendimento->empresa_id)
            ->update($payload);
    }

    public function registrarInteracao(int $atendimentoId, string $tipo, string $mensagem, ?array $metadata = null): void
    {
        if (! CachedSchema::hasTable('atendimento_interacoes')) {
            return;
        }

        $origem = 'interno';
        if (is_array($metadata ?? null) && isset($metadata['origem_coluna'])) {
            $origemInformada = (string) $metadata['origem_coluna'];
            if (in_array($origemInformada, ['interno', 'suporte', 'sistema', 'cliente', 'portal', 'publico'], true)) {
                $origem = $origemInformada;
            }
        }

        $payload = [
            'atendimento_id' => $atendimentoId,
            'user_id' => auth()->id(),
            'origem' => $origem,
            'tipo' => $tipo,
            'mensagem' => $mensagem,
        ];

        if ($metadata !== null) {
            unset($metadata['origem_coluna']);
            $payload['metadata'] = $metadata;
        }

        AtendimentoInteracao::query()->create($payload);
    }

    public function usuarioResponsavelValido(int $userId): bool
    {
        if (! $userId || ! CachedSchema::hasTable('users')) {
            return false;
        }

        $query = DB::table('users')->where('id', $userId);
        $user = auth()->user();
        if ($user && ! $user->isSuperAdmin()) {
            $query->where('empresa_id', $user->empresa_id);
        }

        return $query->exists();
    }

    public function emailClienteAtendimento(Atendimento $atendimento): ?string
    {
        return AtendimentoClienteResolver::emailPorAtendimento($atendimento);
    }

    public function nomeClienteAtendimento(Atendimento $atendimento): ?string
    {
        return AtendimentoClienteResolver::nomePorAtendimento($atendimento);
    }

    public function enviarEmailSeguro(string $to, string $subject, string $body): void
    {
        try {
            Mail::raw($body, function ($message) use ($to, $subject): void {
                $message->to($to)->subject($subject);
            });
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function notify(?callable $notifier, string $type, string $message): void
    {
        if ($notifier) {
            $notifier($type, $message);
        }
    }
}
