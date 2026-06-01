<?php

namespace App\Console\Commands;


use App\Support\CachedSchema;
use App\Models\Configuracao;
use App\Models\ItemControle;
use App\Models\ItemControleNotificacaoLog;
use App\Models\User;
use App\Notifications\ItemControleVencimentoNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NotificarVencimentoItensControle extends Command
{
    protected $signature = 'item-controle:notificar-vencimentos';

    protected $description = 'Envia notificações automáticas por empresa para itens próximos do vencimento, no dia, vencidos e lembretes recorrentes.';

    public function handle(): int
    {
        if (! CachedSchema::hasTable('item_controles') || ! CachedSchema::hasTable('configuracoes') || ! CachedSchema::hasTable('item_controle_notificacao_logs')) {
            $this->error('Tabelas obrigatórias não encontradas.');

            return self::FAILURE;
        }

        $hoje = Carbon::today();

        $adminsPorEmpresa = User::query()
            ->where('role', 'admin')
            ->whereNotNull('empresa_id')
            ->get(['id', 'name', 'email', 'role', 'empresa_id'])
            ->groupBy('empresa_id');

        $enviadas = 0;
        $ignoradas = 0;
        $erros = 0;

        ItemControle::query()
            ->with([
                'empresa:id,razao_social',
                'responsavel:id,nome,email,user_id,gestor_user_id,empresa_id',
                'responsavel.user:id,name,email,role,empresa_id',
                'responsavel.gestor:id,name,email,role,empresa_id',
            ])
            ->whereNotNull('empresa_id')
            ->whereNotNull('responsavel_id')
            ->whereNotNull('data_vencimento')
            ->whereNotIn('status', ['concluido', 'cancelado'])
            ->orderBy('id')
            ->chunkById(200, function ($itens) use ($hoje, $adminsPorEmpresa, &$enviadas, &$ignoradas, &$erros): void {
                foreach ($itens as $item) {
            try {
                $empresaId = (int) $item->empresa_id;

                if (! $item->responsavel || (int) $item->responsavel->empresa_id !== $empresaId) {
                    $ignoradas++;
                    continue;
                }

                $configuracao = Configuracao::forEmpresaId($empresaId);

                if (! $configuracao->enviar_email && ! $configuracao->enviar_sistema) {
                    $ignoradas++;
                    continue;
                }

                $diasAlerta = max(0, (int) $configuracao->dias_alerta);
                $diasLembrete = max(1, (int) $configuracao->dias_lembrete);

                $dataVencimento = $item->data_vencimento->copy()->startOfDay();
                $diasRestantes = $hoje->diffInDays($dataVencimento, false);

                $tipo = null;

                if ($diasRestantes === $diasAlerta && ! $item->notificado_3_dias) {
                    $tipo = '3_dias';
                } elseif ($diasRestantes === 0 && ! $item->notificado_no_dia) {
                    $tipo = 'hoje';
                } elseif ($diasRestantes < 0 && ! $item->notificado_vencido) {
                    $tipo = 'vencido';
                } elseif (
                    $diasRestantes < 0
                    && $item->notificado_vencido
                    && (
                        ! $item->ultimo_lembrete_enviado_em
                        || Carbon::parse($item->ultimo_lembrete_enviado_em)->startOfDay()->lte($hoje->copy()->subDays($diasLembrete))
                    )
                ) {
                    $tipo = 'lembrete_recorrente';
                }

                if (! $tipo) {
                    $ignoradas++;
                    continue;
                }

                $destinatarios = collect();

                if ($item->responsavel->user && (int) $item->responsavel->user->empresa_id === $empresaId) {
                    $destinatarios->push($item->responsavel->user);
                }

                if ($item->responsavel->gestor && (int) $item->responsavel->gestor->empresa_id === $empresaId) {
                    $destinatarios->push($item->responsavel->gestor);
                }

                if (in_array($tipo, ['vencido', 'lembrete_recorrente'], true)) {
                    foreach ($adminsPorEmpresa->get($empresaId, collect()) as $admin) {
                        $destinatarios->push($admin);
                    }
                }

                $destinatarios = $destinatarios
                    ->filter(fn ($usuario) => $usuario && filled($usuario->email))
                    ->unique('id')
                    ->values();

                if ($destinatarios->isEmpty()) {
                    $ignoradas++;
                    continue;
                }

                foreach ($destinatarios as $destinatario) {
                    $destinatario->notify(new ItemControleVencimentoNotification($item, $tipo, $configuracao));

                    ItemControleNotificacaoLog::query()->create([
                        'item_controle_id' => $item->id,
                        'empresa_id' => $empresaId,
                        'responsavel_id' => $item->responsavel_id,
                        'user_id' => $destinatario->id,
                        'tipo' => $tipo,
                        'canal' => 'mail,database',
                        'destinatario' => $destinatario->email,
                        'sucesso' => true,
                        'erro' => null,
                        'payload' => [
                            'item_id' => $item->id,
                            'titulo' => $item->titulo,
                            'tipo' => $tipo,
                            'data_vencimento' => optional($item->data_vencimento)->format('Y-m-d'),
                        ],
                    ]);

                    $enviadas++;
                }

                $updates = [
                    'ultima_falha_notificacao_em' => null,
                    'ultima_falha_notificacao_msg' => null,
                ];

                if ($tipo === '3_dias') {
                    $updates['notificado_3_dias'] = true;
                    $updates['ultimo_alerta_enviado_em'] = now();
                }

                if ($tipo === 'hoje') {
                    $updates['notificado_no_dia'] = true;
                    $updates['ultimo_alerta_enviado_em'] = now();
                }

                if ($tipo === 'vencido') {
                    $updates['notificado_vencido'] = true;
                    $updates['ultimo_alerta_enviado_em'] = now();
                    $updates['ultimo_lembrete_enviado_em'] = now();
                    $updates['qtd_lembretes_enviados'] = ((int) $item->qtd_lembretes_enviados) + 1;

                    if ($item->status !== 'vencido') {
                        $updates['status'] = 'vencido';
                    }
                }

                if ($tipo === 'lembrete_recorrente') {
                    $updates['ultimo_lembrete_enviado_em'] = now();
                    $updates['qtd_lembretes_enviados'] = ((int) $item->qtd_lembretes_enviados) + 1;
                }

                $item->update($updates);

                activity('item_controle')
                    ->performedOn($item)
                    ->event($tipo === 'lembrete_recorrente' ? 'lembrete_recorrente' : 'status_automatico')
                    ->log($tipo === 'lembrete_recorrente'
                        ? 'Lembrete recorrente enviado para item ainda vencido'
                        : 'Notificação automática de vencimento enviada'
                    );
            } catch (\Throwable $e) {
                $erros++;

                Log::error('Erro ao notificar vencimento de item de controle.', [
                    'item_id' => $item->id ?? null,
                    'erro' => $e->getMessage(),
                ]);

                if (isset($item)) {
                    $item->update([
                        'ultima_falha_notificacao_em' => now(),
                        'ultima_falha_notificacao_msg' => mb_substr($e->getMessage(), 0, 1000),
                    ]);
                }
            }
                }
            });

        $this->info("Notificações enviadas: {$enviadas}");
        $this->info("Itens ignorados: {$ignoradas}");
        $this->info("Erros: {$erros}");

        return $erros > 0 ? self::FAILURE : self::SUCCESS;
    }
}
