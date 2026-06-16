<?php

namespace App\Support;

use App\Models\Atendimento;
use App\Models\AtendimentoInteracao;
use App\Models\PortalMensagem;
use App\Models\PortalSolicitacao;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AtendimentoPortalService
{
    public function registrarMensagem(PortalMensagem $mensagem): ?Atendimento
    {
        if (! $this->tabelasDisponiveis()) {
            $this->registrarAlertaEstruturaAusente('registrarMensagem');
            return null;
        }

        try {
            return DB::transaction(function () use ($mensagem): Atendimento {
                $atendimento = Atendimento::query()
                    ->where('portal_mensagem_id', $mensagem->id)
                    ->first();

                if (! $atendimento) {
                    $atendimento = Atendimento::query()->create([
                        'empresa_id' => (int) $mensagem->empresa_id,
                        'crm_cliente_id' => $this->crmClienteId((int) $mensagem->empresa_id),
                        'portal_mensagem_id' => (int) $mensagem->id,
                        'item_controle_id' => $mensagem->item_controle_id ? (int) $mensagem->item_controle_id : null,
                        'responsavel_id' => null,
                        'criado_por' => null,
                        'titulo' => $this->tituloMensagem($mensagem),
                        'descricao' => trim((string) $mensagem->mensagem),
                        'status' => AtendimentoStatus::ABERTO,
                        'prioridade' => 'media',
                        'origem' => 'portal',
                        'canal' => 'portal',
                        'sla_horas' => AtendimentosData::slaHorasPorPrioridade('media'),
                        'sla_limite_em' => now()->addHours(AtendimentosData::slaHorasPorPrioridade('media')),
                    ]);

                    $this->registrarInteracao($atendimento, 'abertura', $this->textoMensagem($mensagem), [
                        'portal_mensagem_id' => (int) $mensagem->id,
                        'nome' => $mensagem->nome,
                        'email' => $mensagem->email,
                    ]);
                }

                return $atendimento;
            });
        } catch (Throwable $exception) {
            Log::error('Falha ao gerar atendimento a partir de mensagem do portal.', [
                'portal_mensagem_id' => $mensagem->id,
                'empresa_id' => $mensagem->empresa_id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return null;
        }
    }

    public function registrarSolicitacao(PortalSolicitacao $solicitacao): ?Atendimento
    {
        if (! $this->tabelasDisponiveis()) {
            $this->registrarAlertaEstruturaAusente('registrarSolicitacao');
            return null;
        }

        try {
            return DB::transaction(function () use ($solicitacao): Atendimento {
                $atendimento = Atendimento::query()
                    ->where('portal_solicitacao_id', $solicitacao->id)
                    ->first();

                if (! $atendimento) {
                    $prioridade = $this->prioridadeValida((string) $solicitacao->prioridade);
                    $slaHoras = AtendimentosData::slaHorasPorPrioridade($prioridade);

                    $atendimento = Atendimento::query()->create([
                        'empresa_id' => (int) $solicitacao->empresa_id,
                        'crm_cliente_id' => $this->crmClienteId((int) $solicitacao->empresa_id),
                        'portal_solicitacao_id' => (int) $solicitacao->id,
                        'item_controle_id' => $solicitacao->item_controle_id ? (int) $solicitacao->item_controle_id : null,
                        'responsavel_id' => null,
                        'criado_por' => $solicitacao->user_id ? (int) $solicitacao->user_id : null,
                        'titulo' => trim((string) $solicitacao->titulo),
                        'descricao' => trim((string) $solicitacao->descricao),
                        'status' => $this->statusAtendimentoPorSolicitacao((string) $solicitacao->status),
                        'prioridade' => $prioridade,
                        'origem' => 'portal',
                        'canal' => 'portal',
                        'sla_horas' => $slaHoras,
                        'sla_limite_em' => now()->addHours($slaHoras),
                    ]);

                    $this->registrarInteracao($atendimento, 'abertura', $this->textoSolicitacao($solicitacao), [
                        'portal_solicitacao_id' => (int) $solicitacao->id,
                        'portal_status' => $solicitacao->status,
                    ]);
                }

                return $atendimento;
            });
        } catch (Throwable $exception) {
            Log::error('Falha ao gerar atendimento a partir de solicitação do portal.', [
                'portal_solicitacao_id' => $solicitacao->id,
                'empresa_id' => $solicitacao->empresa_id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return null;
        }
    }

    public function registrarRespostaSolicitacao(PortalSolicitacao $solicitacao, PortalMensagem $mensagem): ?Atendimento
    {
        if (! $this->tabelasDisponiveis()) {
            $this->registrarAlertaEstruturaAusente('registrarRespostaSolicitacao');
            return null;
        }

        try {
            return DB::transaction(function () use ($solicitacao, $mensagem): Atendimento {
                $atendimento = Atendimento::query()
                    ->where('portal_solicitacao_id', $solicitacao->id)
                    ->first();

                if (! $atendimento) {
                    $atendimento = $this->registrarSolicitacao($solicitacao);
                }

                if (! $atendimento) {
                    $atendimento = $this->registrarMensagem($mensagem);
                }

                if (! $atendimento) {
                    throw new \RuntimeException('Não foi possível localizar ou criar atendimento para resposta do portal.');
                }

                $payload = [];
                if ($atendimento->status === AtendimentoStatus::AGUARDANDO_CLIENTE) {
                    $payload['status'] = AtendimentoStatus::EM_ANDAMENTO;
                }
                if (! $atendimento->primeira_resposta_em) {
                    $payload['primeira_resposta_em'] = now();
                }
                if (! empty($payload)) {
                    $atendimento->forceFill($payload)->save();
                }

                $this->registrarInteracao($atendimento, 'resposta', $this->textoMensagem($mensagem), [
                    'portal_solicitacao_id' => (int) $solicitacao->id,
                    'portal_mensagem_id' => (int) $mensagem->id,
                    'nome' => $mensagem->nome,
                    'email' => $mensagem->email,
                ]);

                return $atendimento->refresh();
            });
        } catch (Throwable $exception) {
            Log::error('Falha ao registrar resposta de solicitação do portal no atendimento.', [
                'portal_solicitacao_id' => $solicitacao->id,
                'portal_mensagem_id' => $mensagem->id,
                'empresa_id' => $solicitacao->empresa_id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return null;
        }
    }


    public function sincronizarPendentes(?int $empresaId = null): array
    {
        if (! $this->tabelasDisponiveis()) {
            $this->registrarAlertaEstruturaAusente('sincronizarPendentes');
            return ['solicitacoes' => 0, 'mensagens' => 0];
        }

        $criadasSolicitacoes = 0;
        $criadasMensagens = 0;

        if (CachedSchema::hasTable('portal_solicitacoes')) {
            PortalSolicitacao::query()
                ->when($empresaId, fn ($query) => $query->where('empresa_id', $empresaId))
                ->whereNotIn('status', ['concluido', 'concluida', 'finalizado', 'finalizada', 'cancelado', 'cancelada'])
                ->whereDoesntHave('atendimento')
                ->orderBy('id')
                ->chunkById(100, function ($solicitacoes) use (&$criadasSolicitacoes): void {
                    foreach ($solicitacoes as $solicitacao) {
                        if ($this->registrarSolicitacao($solicitacao)) {
                            $criadasSolicitacoes++;
                        }
                    }
                });
        }

        if (CachedSchema::hasTable('portal_mensagens')) {
            PortalMensagem::query()
                ->when($empresaId, fn ($query) => $query->where('empresa_id', $empresaId))
                ->where('origem', 'cliente')
                ->whereDoesntHave('atendimento')
                ->orderBy('id')
                ->chunkById(100, function ($mensagens) use (&$criadasMensagens): void {
                    foreach ($mensagens as $mensagem) {
                        if ($this->registrarMensagem($mensagem)) {
                            $criadasMensagens++;
                        }
                    }
                });
        }

        return ['solicitacoes' => $criadasSolicitacoes, 'mensagens' => $criadasMensagens];
    }

    private function tabelasDisponiveis(): bool
    {
        return CachedSchema::hasTable('atendimentos') && CachedSchema::hasTable('atendimento_interacoes');
    }

    private function registrarAlertaEstruturaAusente(string $acao): void
    {
        Log::warning('Integração Portal do Cliente x Atendimentos indisponível por estrutura ausente.', [
            'acao' => $acao,
            'has_atendimentos' => CachedSchema::hasTable('atendimentos'),
            'has_atendimento_interacoes' => CachedSchema::hasTable('atendimento_interacoes'),
        ]);
    }

    private function crmClienteId(int $empresaId): ?int
    {
        if (! CachedSchema::hasTable('crm_clientes')) {
            return null;
        }

        $id = DB::table('crm_clientes')->where('empresa_id', $empresaId)->value('id');

        return $id ? (int) $id : null;
    }

    private function registrarInteracao(Atendimento $atendimento, string $tipo, string $mensagem, array $metadata = []): void
    {
        AtendimentoInteracao::query()->create([
            'atendimento_id' => (int) $atendimento->id,
            'user_id' => null,
            'origem' => 'cliente',
            'tipo' => $tipo,
            'mensagem' => trim($mensagem),
            'metadata' => empty($metadata) ? null : $metadata,
        ]);
    }

    private function tituloMensagem(PortalMensagem $mensagem): string
    {
        $nome = trim((string) ($mensagem->nome ?: 'Cliente'));
        $base = 'Mensagem do portal';

        if ($nome !== '') {
            $base .= ' - ' . $nome;
        }

        return mb_substr($base, 0, 180);
    }

    private function textoMensagem(PortalMensagem $mensagem): string
    {
        $partes = [];
        $nome = trim((string) $mensagem->nome);
        $email = trim((string) $mensagem->email);

        if ($nome !== '' || $email !== '') {
            $partes[] = 'Cliente: ' . trim($nome . ($email !== '' ? ' <' . $email . '>' : ''));
        }

        $partes[] = trim((string) $mensagem->mensagem);

        return implode("\n\n", array_filter($partes));
    }

    private function textoSolicitacao(PortalSolicitacao $solicitacao): string
    {
        return trim(sprintf(
            "Solicitação aberta pelo portal.\n\nPrioridade: %s\n\n%s",
            AtendimentosData::prioridadeLabel($this->prioridadeValida((string) $solicitacao->prioridade)),
            (string) $solicitacao->descricao
        ));
    }

    private function prioridadeValida(string $prioridade): string
    {
        return array_key_exists($prioridade, AtendimentosData::PRIORIDADES) ? $prioridade : 'media';
    }

    private function statusAtendimentoPorSolicitacao(string $status): string
    {
        return AtendimentoStatus::fromPortalStatus($status);
    }
}
