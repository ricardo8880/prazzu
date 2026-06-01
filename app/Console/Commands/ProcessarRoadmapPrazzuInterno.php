<?php

namespace App\Console\Commands;


use App\Support\CachedSchema;
use App\Models\ItemControle;
use App\Services\PrazzuAutomationEngine;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProcessarRoadmapPrazzuInterno extends Command
{
    protected $signature = 'prazzu:processar-roadmap-interno {--silent-notifications : Não cria notificações internas de risco/SLA}';

    protected $description = 'Processa SLA, dependências, risco operacional e automações internas do Prazzu sem depender de APIs externas.';

    public function handle(PrazzuAutomationEngine $automationEngine): int
    {
        if (! CachedSchema::hasTable('item_controles')) {
            $this->warn('Tabela item_controles não encontrada.');
            return self::SUCCESS;
        }

        $this->info('Processando roadmap interno do Prazzu...');

        $dependencies = $this->processDependencies();
        $sla = $this->processSla();
        $risk = $this->processRiskScores();
        $automationEngine->primeRules();
        $automations = $this->processAutomations($automationEngine);

        $this->info("Dependências atualizadas: {$dependencies}");
        $this->info("SLA atualizado: {$sla}");
        $this->info("Score de risco atualizado: {$risk}");
        $this->info("Itens avaliados pelas automações: {$automations}");
        $this->info('Processamento concluído.');

        return self::SUCCESS;
    }

    private function processDependencies(): int
    {
        if (! CachedSchema::hasTable('prazzu_dependencies') || ! CachedSchema::hasColumn('item_controles', 'blocked_by_dependency')) {
            return 0;
        }

        $doneStatuses = ['concluido', 'concluído', 'finalizado', 'cancelado'];
        $blockedIds = DB::table('prazzu_dependencies')
            ->leftJoin('item_controles as blocker', 'blocker.id', '=', 'prazzu_dependencies.depends_on_item_controle_id')
            ->where('prazzu_dependencies.blocked_until_resolved', 1)
            ->where(function ($query) use ($doneStatuses): void {
                $query->whereNull('blocker.id')
                    ->orWhereNotIn('blocker.status', $doneStatuses);
            })
            ->pluck('prazzu_dependencies.item_controle_id')
            ->filter()
            ->unique()
            ->values();

        $resetPayload = [
            'blocked_by_dependency' => 0,
            'updated_at' => now(),
        ];

        if (CachedSchema::hasColumn('item_controles', 'bloqueado_por_dependencia')) {
            $resetPayload['bloqueado_por_dependencia'] = 0;
        }

        DB::table('item_controles')->where('blocked_by_dependency', 1)->update($resetPayload);

        if ($blockedIds->isEmpty()) {
            return 0;
        }

        $payload = [
            'blocked_by_dependency' => 1,
            'updated_at' => now(),
        ];

        if (CachedSchema::hasColumn('item_controles', 'bloqueado_por_dependencia')) {
            $payload['bloqueado_por_dependencia'] = 1;
        }

        return DB::table('item_controles')->whereIn('id', $blockedIds)->update($payload);
    }

    private function processSla(): int
    {
        if (! CachedSchema::hasColumn('item_controles', 'sla_limite_em') || ! CachedSchema::hasColumn('item_controles', 'sla_status')) {
            return 0;
        }

        $total = 0;
        $now = now();

        $total += DB::table('item_controles')
            ->whereNotNull('sla_concluido_em')
            ->whereColumn('sla_concluido_em', '<=', 'sla_limite_em')
            ->update(['sla_status' => 'concluido_no_prazo', 'updated_at' => $now]);

        $total += DB::table('item_controles')
            ->whereNotNull('sla_concluido_em')
            ->whereColumn('sla_concluido_em', '>', 'sla_limite_em')
            ->update(['sla_status' => 'concluido_atrasado', 'updated_at' => $now]);

        $total += DB::table('item_controles')
            ->whereNull('sla_concluido_em')
            ->whereNotNull('sla_limite_em')
            ->where('sla_limite_em', '<', $now)
            ->update(['sla_status' => 'vencido', 'updated_at' => $now]);

        $total += DB::table('item_controles')
            ->whereNull('sla_concluido_em')
            ->whereNotNull('sla_limite_em')
            ->whereBetween('sla_limite_em', [$now, $now->copy()->addHours(8)])
            ->update(['sla_status' => 'risco', 'updated_at' => $now]);

        $total += DB::table('item_controles')
            ->whereNull('sla_concluido_em')
            ->whereNotNull('sla_limite_em')
            ->where('sla_limite_em', '>', $now->copy()->addHours(8))
            ->update(['sla_status' => 'ok', 'updated_at' => $now]);

        if (! $this->option('silent-notifications')) {
            $this->createSlaNotifications();
        }

        return $total;
    }

    private function processRiskScores(): int
    {
        if (! CachedSchema::hasColumn('item_controles', 'risk_score') && ! CachedSchema::hasColumn('item_controles', 'risco_score')) {
            return 0;
        }

        $updated = 0;
        ItemControle::query()
            ->select('id', 'prioridade', 'data_vencimento', 'sla_limite_em', 'sla_concluido_em', 'blocked_by_dependency', 'bloqueado_por_dependencia')
            ->chunkById(200, function ($items) use (&$updated): void {
                foreach ($items as $item) {
                    $score = 0;
                    $prioridade = strtolower((string) $item->prioridade);

                    $score += match ($prioridade) {
                        'critica', 'crítica' => 45,
                        'alta' => 30,
                        'media', 'média' => 15,
                        default => 5,
                    };

                    if ($item->data_vencimento && Carbon::parse($item->data_vencimento)->isPast()) {
                        $score += 25;
                    }

                    if ($item->sla_limite_em && ! $item->sla_concluido_em && Carbon::parse($item->sla_limite_em)->isPast()) {
                        $score += 25;
                    }

                    if ((bool) ($item->blocked_by_dependency ?? false) || (bool) ($item->bloqueado_por_dependencia ?? false)) {
                        $score += 15;
                    }

                    $score = min(100, $score);
                    $payload = ['updated_at' => now()];

                    if (CachedSchema::hasColumn('item_controles', 'risk_score')) {
                        $payload['risk_score'] = $score;
                    }

                    if (CachedSchema::hasColumn('item_controles', 'risco_score')) {
                        $payload['risco_score'] = $score;
                    }

                    DB::table('item_controles')->where('id', $item->id)->update($payload);
                    $updated++;
                }
            });

        return $updated;
    }

    private function processAutomations(PrazzuAutomationEngine $automationEngine): int
    {
        if (! CachedSchema::hasTable('prazzu_automation_rules')) {
            return 0;
        }

        $count = 0;
        $select = array_values(array_filter([
            'id',
            'titulo',
            'tipo',
            'status',
            'prioridade',
            'empresa_id',
            'responsavel_id',
            'data_vencimento',
            CachedSchema::hasColumn('item_controles', 'approval_status') ? 'approval_status' : null,
            CachedSchema::hasColumn('item_controles', 'signature_status') ? 'signature_status' : null,
            CachedSchema::hasColumn('item_controles', 'sla_status') ? 'sla_status' : null,
            CachedSchema::hasColumn('item_controles', 'portal_status') ? 'portal_status' : null,
            CachedSchema::hasColumn('item_controles', 'document_status') ? 'document_status' : null,
        ]));

        DB::table('item_controles')
            ->select($select)
            ->orderBy('id')
            ->chunkById(200, function ($items) use ($automationEngine, &$count): void {
                foreach ($items as $item) {
                    $automationEngine->runForItem($item);
                    $count++;
                }
            });

        return $count;
    }

    private function createSlaNotifications(): void
    {
        if (! CachedSchema::hasTable('notificacoes_internas')) {
            return;
        }

        DB::table('item_controles')
            ->select('id', 'empresa_id', 'responsavel_id', 'titulo', 'sla_status')
            ->whereIn('sla_status', ['risco', 'vencido'])
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get()
            ->each(function ($item): void {
                $exists = DB::table('notificacoes_internas')
                    ->where('item_controle_id', $item->id)
                    ->where('tipo', 'sla')
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();

                if ($exists) {
                    return;
                }

                DB::table('notificacoes_internas')->insert([
                    'item_controle_id' => $item->id,
                    'empresa_id' => $item->empresa_id,
                    'user_id' => $item->responsavel_id,
                    'tipo' => 'sla',
                    'titulo' => $item->sla_status === 'vencido' ? 'SLA vencido' : 'SLA em risco',
                    'mensagem' => 'O item "' . $item->titulo . '" está com SLA ' . str_replace('_', ' ', $item->sla_status) . '.',
                    'lida' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }
}
