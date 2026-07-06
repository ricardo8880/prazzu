<?php

namespace App\Console\Commands;

use App\Models\Configuracao;
use App\Models\ItemControle;
use App\Models\ItemControleNotificacaoLog;
use App\Models\User;
use App\Notifications\ItemControleVencimentoNotification;
use App\Support\CachedSchema;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class NotificarVencimentoItensControle extends Command
{
    protected $signature = 'item-controle:notificar-vencimentos
        {--empresa_id= : Processa somente uma empresa específica}
        {--limit=0 : Limite máximo de itens processados nesta execução; 0 processa todos}
        {--dry-run : Simula a execução sem enviar notificações e sem gravar alterações}';

    protected $description = 'Envia notificações automáticas por empresa para itens próximos do vencimento, no dia, vencidos e lembretes recorrentes.';

    public function handle(): int
    {
        if (! $this->schemaPronto()) {
            $this->error('Tabelas obrigatórias não encontradas.');

            return self::FAILURE;
        }

        $hoje = Carbon::today();
        $empresaFiltro = $this->option('empresa_id') ? (int) $this->option('empresa_id') : null;
        $limit = max(0, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');

        $adminsPorEmpresa = User::query()
            ->where('role', 'admin')
            ->whereNotNull('empresa_id')
            ->when($empresaFiltro, fn ($query) => $query->where('empresa_id', $empresaFiltro))
            ->get(['id', 'name', 'email', 'role', 'empresa_id'])
            ->groupBy('empresa_id');

        $enviadas = 0;
        $ignoradas = 0;
        $erros = 0;
        $itensProcessados = 0;

        $query = ItemControle::query()
            ->with([
                'empresa:id,razao_social',
                'responsavel:id,nome,email,user_id,gestor_user_id,empresa_id',
                'responsavel.user:id,name,email,role,empresa_id',
                'responsavel.gestor:id,name,email,role,empresa_id',
            ])
            ->whereNotNull('empresa_id')
            ->whereNotNull('responsavel_id')
            ->whereNotNull('data_vencimento')
            ->whereNotIn('status', ['concluido', 'finalizado', 'cancelado'])
            ->when($empresaFiltro, fn ($builder) => $builder->where('empresa_id', $empresaFiltro))
            ->orderBy('id');

        $query->chunkById(200, function ($itens) use ($hoje, $adminsPorEmpresa, $limit, $dryRun, &$enviadas, &$ignoradas, &$erros, &$itensProcessados): bool {
            foreach ($itens as $item) {
                if ($limit > 0 && $itensProcessados >= $limit) {
                    return false;
                }

                $itensProcessados++;

                try {
                    $resultado = $this->processarItem($item, $hoje, $adminsPorEmpresa, $dryRun);

                    $enviadas += $resultado['enviadas'];
                    $ignoradas += $resultado['ignoradas'];
                } catch (\Throwable $e) {
                    $erros++;

                    Log::error('Erro ao notificar vencimento de item de controle.', [
                        'item_id' => $item->id ?? null,
                        'erro' => $e->getMessage(),
                    ]);

                    if (! $dryRun && isset($item)) {
                        $item->forceFill([
                            'ultima_falha_notificacao_em' => now(),
                            'ultima_falha_notificacao_msg' => substr($e->getMessage(), 0, 1000),
                        ])->save();
                    }
                }
            }

            return true;
        });

        $prefixo = $dryRun ? '[DRY-RUN] ' : '';
        $this->info($prefixo."Itens processados: {$itensProcessados}");
        $this->info($prefixo."Notificações/canais disparados: {$enviadas}");
        $this->info($prefixo."Itens ignorados: {$ignoradas}");
        $this->info($prefixo."Erros: {$erros}");

        return $erros > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function schemaPronto(): bool
    {
        return CachedSchema::hasTable('item_controles')
            && CachedSchema::hasTable('configuracoes')
            && CachedSchema::hasTable('item_controle_notificacao_logs')
            && CachedSchema::hasTable('notifications');
    }

    /**
     * @return array{enviadas:int,ignoradas:int}
     */
    private function processarItem(ItemControle $item, Carbon $hoje, $adminsPorEmpresa, bool $dryRun): array
    {
        $empresaId = (int) $item->empresa_id;

        if (! $item->responsavel || (int) $item->responsavel->empresa_id !== $empresaId) {
            return ['enviadas' => 0, 'ignoradas' => 1];
        }

        $configuracao = Configuracao::forEmpresaId($empresaId);

        if (! $configuracao->enviar_email && ! $configuracao->enviar_sistema) {
            return ['enviadas' => 0, 'ignoradas' => 1];
        }

        $tipo = $this->determinarTipo($item, $configuracao, $hoje);

        if (! $tipo) {
            return ['enviadas' => 0, 'ignoradas' => 1];
        }

        $destinatarios = $this->destinatarios($item, $adminsPorEmpresa, $tipo);

        if ($destinatarios->isEmpty()) {
            return ['enviadas' => 0, 'ignoradas' => 1];
        }

        $notification = new ItemControleVencimentoNotification($item, $tipo, $configuracao);
        $canais = $notification->canaisAtivos();

        if ($canais === []) {
            return ['enviadas' => 0, 'ignoradas' => 1];
        }

        $enviadas = 0;
        $duplicadas = 0;

        foreach ($destinatarios as $destinatario) {
            if (! $dryRun && $this->notificacaoJaRegistrada($item, $destinatario, $tipo)) {
                $duplicadas++;
                continue;
            }

            if (! $dryRun) {
                $destinatario->notify($notification);

                $this->registrarLog($item, $destinatario, $tipo, $canais);
            }

            $enviadas += count($canais);
        }

        if (! $dryRun && ($enviadas > 0 || $duplicadas > 0)) {
            $this->atualizarMarcadoresDoItem($item, $tipo);

            activity('item_controle')
                ->performedOn($item)
                ->event($tipo === 'lembrete_recorrente' ? 'lembrete_recorrente' : 'status_automatico')
                ->withProperties([
                    'tipo_notificacao' => $tipo,
                    'canais' => $canais,
                    'destinatarios' => $destinatarios->pluck('id')->values()->all(),
                ])
                ->log($tipo === 'lembrete_recorrente'
                    ? 'Lembrete recorrente enviado para item ainda vencido'
                    : 'Notificação automática de vencimento enviada'
                );
        }

        return ['enviadas' => $enviadas, 'ignoradas' => 0];
    }

    private function determinarTipo(ItemControle $item, Configuracao $configuracao, Carbon $hoje): ?string
    {
        $diasAlerta = max(0, (int) $configuracao->dias_alerta);
        $diasLembrete = max(1, (int) $configuracao->dias_lembrete);
        $dataVencimento = $item->data_vencimento->copy()->startOfDay();
        $diasRestantes = $hoje->diffInDays($dataVencimento, false);

        if ($diasRestantes === $diasAlerta && ! (bool) $item->notificado_3_dias) {
            return '3_dias';
        }

        if ($diasRestantes === 0 && ! (bool) $item->notificado_no_dia) {
            return 'hoje';
        }

        if ($diasRestantes < 0 && ! (bool) $item->notificado_vencido) {
            return 'vencido';
        }

        if (
            $diasRestantes < 0
            && (bool) $item->notificado_vencido
            && (
                ! $item->ultimo_lembrete_enviado_em
                || Carbon::parse($item->ultimo_lembrete_enviado_em)->startOfDay()->lte($hoje->copy()->subDays($diasLembrete))
            )
        ) {
            return 'lembrete_recorrente';
        }

        return null;
    }

    private function destinatarios(ItemControle $item, $adminsPorEmpresa, string $tipo)
    {
        $empresaId = (int) $item->empresa_id;
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

        return $destinatarios
            ->filter(fn ($usuario) => $usuario && filled($usuario->email))
            ->unique('id')
            ->values();
    }


    private function notificacaoJaRegistrada(ItemControle $item, User $destinatario, string $tipo): bool
    {
        return ItemControleNotificacaoLog::query()
            ->where('item_controle_id', $item->id)
            ->where('user_id', $destinatario->id)
            ->where('tipo_notificacao', $tipo)
            ->where('status', 'enviado')
            ->when($tipo === 'lembrete_recorrente', fn ($query) => $query->whereDate('enviado_em', now()->toDateString()))
            ->exists();
    }

    private function registrarLog(ItemControle $item, User $destinatario, string $tipo, array $canais): void
    {
        ItemControleNotificacaoLog::query()->create([
            'item_controle_id' => $item->id,
            'responsavel_id' => $item->responsavel_id,
            'user_id' => $destinatario->id,
            'tipo_notificacao' => $tipo,
            'canal' => implode(',', $canais),
            'mensagem' => sprintf(
                'Notificação %s enviada para %s <%s> sobre o item #%d.',
                $tipo,
                $destinatario->name ?: 'usuário',
                $destinatario->email,
                $item->id
            ),
            'status' => 'enviado',
            'enviado_em' => now(),
        ]);
    }

    private function atualizarMarcadoresDoItem(ItemControle $item, string $tipo): void
    {
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

        $item->forceFill($updates)->save();
    }
}
