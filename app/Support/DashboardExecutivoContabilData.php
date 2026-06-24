<?php

namespace App\Support;

use App\Filament\Pages\Atendimentos;
use App\Filament\Pages\CentralAprovacoes;
use App\Filament\Pages\CentroOperacional;
use App\Filament\Pages\ControleCobrancas;
use App\Filament\Pages\Documentos;
use App\Filament\Pages\Financeiro;
use App\Filament\Pages\PortalCliente;
use App\Filament\Pages\Relatorios;
use App\Filament\Pages\SlaPrazos;
use App\Filament\Resources\ItemControles\ItemControleResource;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DashboardExecutivoContabilData
{
    private const DONE_STATUSES = ['concluido', 'concluído', 'finalizado', 'finalizada', 'aprovado', 'aprovada', 'pago', 'paid'];
    private const OPEN_STATUSES = ['pendente', 'aberto', 'em_andamento', 'aguardando_cliente', 'aguardando_equipe', 'em_aprovacao'];

    public function data(): array
    {
        $cards = $this->cards();

        return [
            'updated_at' => now()->format('d/m/Y H:i'),
            'health' => $this->health($cards),
            'cards' => $cards,
            'decision_blocks' => $this->decisionBlocks(),
            'sections' => $this->sections(),
            'quick_actions' => $this->quickActions(),
        ];
    }

    private function cards(): array
    {
        $clientesAtivos = $this->countRows('empresas', function (Builder $query): void {
            if ($this->hasColumn('empresas', 'ativo')) {
                $query->where('ativo', 1);
                return;
            }

            if ($this->hasColumn('empresas', 'status')) {
                $query->whereIn('status', ['ativo', 'ativa', 'active']);
            }
        });

        $tarefasAbertas = $this->itemsBase()
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->count();

        $tarefasAtrasadas = $this->itemsBase()
            ->whereNotNull('item_controles.data_vencimento')
            ->whereDate('item_controles.data_vencimento', '<', now()->toDateString())
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->count();

        $vencendoHoje = $this->itemsBase()
            ->whereDate('item_controles.data_vencimento', now()->toDateString())
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->count();

        $slaRisco = $this->slaRiskCount();
        $documentosPendentes = $this->documentsPendingCount();
        $atendimentosAbertos = $this->openAttendancesCount();
        $cobrancasVencidas = $this->overdueBillingCount();
        $valorVencido = $this->overdueBillingValue();

        return [
            [
                'key' => 'clientes',
                'label' => 'Clientes ativos',
                'value' => $this->formatNumber($clientesAtivos),
                'hint' => 'Base atual do escritório',
                'tone' => 'info',
            ],
            [
                'key' => 'risco',
                'label' => 'Clientes em atenção',
                'value' => $this->formatNumber($this->criticalClientsCount()),
                'hint' => 'Com atraso, cobrança ou atendimento aberto',
                'tone' => $this->criticalClientsCount() > 0 ? 'warning' : 'success',
            ],
            [
                'key' => 'tarefas',
                'label' => 'Tarefas abertas',
                'value' => $this->formatNumber($tarefasAbertas),
                'hint' => 'Ainda não concluídas',
                'tone' => $tarefasAbertas > 0 ? 'info' : 'success',
            ],
            [
                'key' => 'atrasos',
                'label' => 'Atrasadas',
                'value' => $this->formatNumber($tarefasAtrasadas),
                'hint' => 'Exigem decisão rápida',
                'tone' => $tarefasAtrasadas > 0 ? 'danger' : 'success',
            ],
            [
                'key' => 'hoje',
                'label' => 'Vencem hoje',
                'value' => $this->formatNumber($vencendoHoje),
                'hint' => 'Prazos do dia',
                'tone' => $vencendoHoje > 0 ? 'warning' : 'success',
            ],
            [
                'key' => 'sla',
                'label' => 'SLA em risco',
                'value' => $this->formatNumber($slaRisco),
                'hint' => 'Próximas 12h ou já vencidos',
                'tone' => $slaRisco > 0 ? 'danger' : 'success',
            ],
            [
                'key' => 'documentos',
                'label' => 'Docs pendentes',
                'value' => $this->formatNumber($documentosPendentes),
                'hint' => 'Sem arquivo, revisão ou assinatura',
                'tone' => $documentosPendentes > 0 ? 'warning' : 'success',
            ],
            [
                'key' => 'atendimentos',
                'label' => 'Atendimentos abertos',
                'value' => $this->formatNumber($atendimentosAbertos),
                'hint' => 'Cliente aguardando retorno',
                'tone' => $atendimentosAbertos > 0 ? 'warning' : 'success',
            ],
            [
                'key' => 'cobrancas',
                'label' => 'Cobranças vencidas',
                'value' => $this->formatNumber($cobrancasVencidas),
                'hint' => 'Total: R$ ' . number_format($valorVencido, 2, ',', '.'),
                'tone' => $cobrancasVencidas > 0 ? 'danger' : 'success',
            ],
        ];
    }

    private function decisionBlocks(): array
    {
        $late = (int) str_replace('.', '', (string) ($this->cards()[3]['value'] ?? 0));
        $sla = (int) str_replace('.', '', (string) ($this->cards()[5]['value'] ?? 0));
        $billing = (int) str_replace('.', '', (string) ($this->cards()[8]['value'] ?? 0));

        return [
            [
                'title' => 'O que olhar primeiro',
                'value' => $late + $sla,
                'tone' => ($late + $sla) > 0 ? 'danger' : 'success',
                'description' => ($late + $sla) > 0
                    ? 'Comece por tarefas atrasadas e SLA em risco. São os pontos que mais geram multa, retrabalho e desgaste com cliente.'
                    : 'Nenhum atraso crítico encontrado agora. Use a tela para acompanhar vencimentos do dia e manter o ritmo.',
                'url' => $this->safeUrl(CentroOperacional::class),
                'action' => 'Abrir Centro Operacional',
            ],
            [
                'title' => 'Risco financeiro',
                'value' => $billing,
                'tone' => $billing > 0 ? 'warning' : 'success',
                'description' => $billing > 0
                    ? 'Existem cobranças vencidas. Trate antes que isso afete caixa, relacionamento ou acesso do cliente.'
                    : 'Sem cobrança vencida localizada no banco atual.',
                'url' => $this->safeUrl(ControleCobrancas::class) ?: $this->safeUrl(Financeiro::class),
                'action' => 'Ver cobranças',
            ],
            [
                'title' => 'Comunicação com cliente',
                'value' => $this->openAttendancesCount(),
                'tone' => $this->openAttendancesCount() > 0 ? 'warning' : 'success',
                'description' => $this->openAttendancesCount() > 0
                    ? 'Há atendimentos abertos. Priorize pedidos parados para reduzir ruído operacional.'
                    : 'Sem atendimento aberto localizado agora.',
                'url' => $this->safeUrl(Atendimentos::class) ?: $this->safeUrl(PortalCliente::class),
                'action' => 'Abrir atendimentos',
            ],
        ];
    }

    private function sections(): array
    {
        return [
            [
                'title' => 'Clientes que precisam de atenção',
                'description' => 'Empresas com sinais de atraso, cobrança, atendimento aberto ou documento pendente.',
                'items' => $this->criticalClientsRows(),
            ],
            [
                'title' => 'Prazos críticos',
                'description' => 'Obrigações vencidas, vencendo hoje ou com SLA em risco.',
                'items' => $this->criticalDeadlineRows(),
            ],
            [
                'title' => 'Operação por responsável',
                'description' => 'Distribuição simples para o gestor enxergar gargalos de equipe.',
                'items' => $this->responsibleRows(),
            ],
            [
                'title' => 'Documentos e aprovações',
                'description' => 'Itens que dependem de arquivo, revisão, assinatura ou decisão.',
                'items' => $this->documentApprovalRows(),
            ],
        ];
    }

    private function quickActions(): array
    {
        return array_values(array_filter([
            ['label' => 'Centro Operacional', 'url' => $this->safeUrl(CentroOperacional::class)],
            ['label' => 'Prazos e SLA', 'url' => $this->safeUrl(SlaPrazos::class)],
            ['label' => 'Tarefas', 'url' => $this->safeUrl(ItemControleResource::class)],
            ['label' => 'Documentos', 'url' => $this->safeUrl(Documentos::class)],
            ['label' => 'Cobranças', 'url' => $this->safeUrl(ControleCobrancas::class)],
            ['label' => 'Aprovações', 'url' => $this->safeUrl(CentralAprovacoes::class)],
            ['label' => 'Relatórios', 'url' => $this->safeUrl(Relatorios::class)],
        ], fn (array $action): bool => ! empty($action['url'])));
    }

    private function health(array $cards): array
    {
        $danger = collect($cards)->where('tone', 'danger')->count();
        $warning = collect($cards)->where('tone', 'warning')->count();
        $score = max(0, 100 - ($danger * 18) - ($warning * 8));

        if ($score < 60) {
            return ['score' => $score, 'label' => 'Atenção alta', 'tone' => 'danger', 'message' => 'Resolva atrasos, SLA e cobrança antes de abrir novas frentes.'];
        }

        if ($score < 82) {
            return ['score' => $score, 'label' => 'Precisa acompanhar', 'tone' => 'warning', 'message' => 'A operação está andando, mas existem pontos que podem virar problema.'];
        }

        return ['score' => $score, 'label' => 'Saudável', 'tone' => 'success', 'message' => 'A operação está sob controle. Acompanhe os vencimentos do dia.'];
    }

    private function criticalClientsRows(): array
    {
        if (! $this->hasTable('empresas')) {
            return [];
        }

        $rows = DB::table('empresas')
            ->select('id', 'razao_social', 'nome_fantasia', 'status')
            ->when($this->hasColumn('empresas', 'ativo'), fn (Builder $query) => $query->where('ativo', 1))
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return $rows->map(function ($empresa): array {
            $itemQuery = $this->itemsBase()->where('item_controles.empresa_id', $empresa->id);
            $late = (clone $itemQuery)->whereNotNull('item_controles.data_vencimento')->whereDate('item_controles.data_vencimento', '<', now()->toDateString())->whereNotIn('item_controles.status', self::DONE_STATUSES)->count();
            $open = (clone $itemQuery)->whereNotIn('item_controles.status', self::DONE_STATUSES)->count();
            $billing = $this->overdueBillingCount($empresa->id);
            $attendances = $this->openAttendancesCount($empresa->id);
            $score = ($late * 3) + ($billing * 2) + $attendances;

            return [
                'title' => $empresa->nome_fantasia ?: $empresa->razao_social,
                'status' => $score > 0 ? 'Atenção' : 'OK',
                'meta' => $open . ' tarefa(s) abertas • ' . $late . ' atrasada(s)',
                'description' => $billing . ' cobrança(s) vencida(s) e ' . $attendances . ' atendimento(s) aberto(s).',
                'tone' => $score >= 5 ? 'danger' : ($score > 0 ? 'warning' : 'success'),
                'url' => $this->safeUrl(CentroOperacional::class),
            ];
        })->filter(fn (array $row): bool => $row['tone'] !== 'success')->values()->take(8)->all();
    }

    private function criticalDeadlineRows(): array
    {
        if (! $this->hasTable('item_controles')) {
            return [];
        }

        return $this->itemsBase()
            ->whereNotNull('item_controles.data_vencimento')
            ->whereDate('item_controles.data_vencimento', '<=', now()->addDays(3)->toDateString())
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->orderBy('item_controles.data_vencimento')
            ->limit(10)
            ->get()
            ->map(fn ($item): array => $this->itemRow($item))
            ->all();
    }

    private function responsibleRows(): array
    {
        if (! $this->hasTable('item_controles') || ! $this->hasTable('responsaveis')) {
            return [];
        }

        return DB::table('responsaveis')
            ->leftJoin('item_controles', 'item_controles.responsavel_id', '=', 'responsaveis.id')
            ->select('responsaveis.nome', DB::raw("SUM(CASE WHEN item_controles.status NOT IN ('concluido','concluído','finalizado','finalizada','aprovado','aprovada','pago','paid') THEN 1 ELSE 0 END) as abertas"), DB::raw("SUM(CASE WHEN item_controles.data_vencimento < CURDATE() AND item_controles.status NOT IN ('concluido','concluído','finalizado','finalizada','aprovado','aprovada','pago','paid') THEN 1 ELSE 0 END) as atrasadas"))
            ->when($this->empresaId(), fn (Builder $query, int $empresaId) => $query->where('responsaveis.empresa_id', $empresaId))
            ->groupBy('responsaveis.id', 'responsaveis.nome')
            ->orderByDesc('atrasadas')
            ->orderByDesc('abertas')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => [
                'title' => $row->nome ?: 'Sem responsável',
                'status' => ((int) $row->atrasadas) > 0 ? 'Atraso' : 'Em dia',
                'meta' => ((int) $row->abertas) . ' tarefa(s) abertas',
                'description' => ((int) $row->atrasadas) . ' tarefa(s) atrasada(s).',
                'tone' => ((int) $row->atrasadas) > 0 ? 'danger' : (((int) $row->abertas) > 0 ? 'info' : 'success'),
                'url' => $this->safeUrl(ItemControleResource::class),
            ])->all();
    }

    private function documentApprovalRows(): array
    {
        if (! $this->hasTable('item_controles')) {
            return [];
        }

        $query = $this->itemsBase();
        $query->where(function (Builder $q): void {
            if ($this->hasColumn('item_controles', 'document_status')) {
                $q->orWhereIn('item_controles.document_status', ['pendente', 'aguardando', 'em_revisao', 'revisao']);
            }
            if ($this->hasColumn('item_controles', 'signature_status')) {
                $q->orWhereIn('item_controles.signature_status', ['pendente', 'aguardando', 'enviado']);
            }
            if ($this->hasColumn('item_controles', 'approval_status')) {
                $q->orWhereIn('item_controles.approval_status', ['pendente', 'aguardando', 'em_aprovacao']);
            }
            if ($this->hasColumn('item_controles', 'arquivo')) {
                $q->orWhereNull('item_controles.arquivo');
            }
        });

        return $query->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->orderByDesc('item_controles.updated_at')
            ->limit(10)
            ->get()
            ->map(fn ($item): array => $this->itemRow($item, 'Documento/Aprovação'))
            ->all();
    }

    private function itemRow(object $item, ?string $forcedStatus = null): array
    {
        $due = $item->data_vencimento ? Carbon::parse($item->data_vencimento) : null;
        $late = $due && $due->isPast() && ! $due->isToday();

        return [
            'title' => $item->titulo ?? 'Item sem título',
            'status' => $forcedStatus ?: ($late ? 'Vencido' : ($due && $due->isToday() ? 'Hoje' : 'Próximo')),
            'meta' => trim(($item->empresa_nome ?? 'Empresa') . ' • ' . ($item->responsavel_nome ?? 'Sem responsável')),
            'description' => 'Status atual: ' . ($item->status ?? '-') . ($due ? ' • Vencimento: ' . $due->format('d/m/Y') : ''),
            'tone' => $late ? 'danger' : ($due && $due->isToday() ? 'warning' : 'info'),
            'url' => $this->safeUrl(ItemControleResource::class),
        ];
    }

    private function criticalClientsCount(): int
    {
        return count($this->criticalClientsRows());
    }

    private function itemsBase(): Builder
    {
        $query = DB::table('item_controles');

        if ($this->hasTable('empresas')) {
            $query->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id');
        }

        if ($this->hasTable('responsaveis')) {
            $query->leftJoin('responsaveis', 'responsaveis.id', '=', 'item_controles.responsavel_id');
        }

        $query->select('item_controles.*');

        if ($this->hasTable('empresas')) {
            $query->addSelect(DB::raw('COALESCE(empresas.nome_fantasia, empresas.razao_social) as empresa_nome'));
        }

        if ($this->hasTable('responsaveis')) {
            $query->addSelect('responsaveis.nome as responsavel_nome');
        }

        if ($empresaId = $this->empresaId()) {
            $query->where('item_controles.empresa_id', $empresaId);
        }

        return $query;
    }

    private function documentsPendingCount(): int
    {
        if (! $this->hasTable('item_controles')) {
            return 0;
        }

        return $this->itemsBase()
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->where(function (Builder $q): void {
                if ($this->hasColumn('item_controles', 'document_status')) {
                    $q->orWhereIn('item_controles.document_status', ['pendente', 'aguardando', 'em_revisao', 'revisao']);
                }
                if ($this->hasColumn('item_controles', 'signature_status')) {
                    $q->orWhereIn('item_controles.signature_status', ['pendente', 'aguardando', 'enviado']);
                }
                if ($this->hasColumn('item_controles', 'arquivo')) {
                    $q->orWhereNull('item_controles.arquivo');
                }
            })
            ->count();
    }

    private function slaRiskCount(): int
    {
        if (! $this->hasTable('item_controles') || ! $this->hasColumn('item_controles', 'sla_limite_em')) {
            return 0;
        }

        return $this->itemsBase()
            ->whereNotNull('item_controles.sla_limite_em')
            ->where(function (Builder $query): void {
                $query->whereNull('item_controles.sla_concluido_em')
                    ->orWhere('item_controles.sla_concluido_em', '');
            })
            ->where('item_controles.sla_limite_em', '<=', now()->addHours(12))
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->count();
    }

    private function openAttendancesCount(?int $empresaId = null): int
    {
        if (! $this->hasTable('atendimentos')) {
            return 0;
        }

        return DB::table('atendimentos')
            ->when($empresaId, fn (Builder $query, int $id) => $query->where('empresa_id', $id))
            ->when(! $empresaId && $this->empresaId(), fn (Builder $query, int $id) => $query->where('empresa_id', $id))
            ->whereIn('status', ['aberto', 'em_andamento', 'aguardando_cliente', 'aguardando_suporte'])
            ->count();
    }

    private function overdueBillingCount(?int $empresaId = null): int
    {
        if (! $this->hasTable('pagamentos')) {
            return 0;
        }

        return DB::table('pagamentos')
            ->when($empresaId, fn (Builder $query, int $id) => $query->where('empresa_id', $id))
            ->when(! $empresaId && $this->empresaId(), fn (Builder $query, int $id) => $query->where('empresa_id', $id))
            ->whereNotNull('vencimento')
            ->whereDate('vencimento', '<', now()->toDateString())
            ->whereNotIn('status', ['pago', 'paid', 'recebido', 'confirmed', 'received'])
            ->count();
    }

    private function overdueBillingValue(): float
    {
        if (! $this->hasTable('pagamentos')) {
            return 0.0;
        }

        return (float) DB::table('pagamentos')
            ->when($this->empresaId(), fn (Builder $query, int $id) => $query->where('empresa_id', $id))
            ->whereNotNull('vencimento')
            ->whereDate('vencimento', '<', now()->toDateString())
            ->whereNotIn('status', ['pago', 'paid', 'recebido', 'confirmed', 'received'])
            ->sum('valor');
    }

    private function countRows(string $table, ?callable $callback = null): int
    {
        if (! $this->hasTable($table)) {
            return 0;
        }

        $query = DB::table($table);

        if ($table !== 'empresas' && $this->hasColumn($table, 'empresa_id') && $this->empresaId()) {
            $query->where('empresa_id', $this->empresaId());
        }

        if ($callback) {
            $callback($query);
        }

        return $query->count();
    }

    private function safeUrl(string $class): ?string
    {
        try {
            if (method_exists($class, 'canAccess') && ! $class::canAccess()) {
                return null;
            }

            return $class::getUrl();
        } catch (Throwable) {
            return null;
        }
    }

    private function empresaId(): ?int
    {
        $user = Filament::auth()->user();

        if (! $user || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            return null;
        }

        return $user->empresa_id ? (int) $user->empresa_id : null;
    }

    private function hasTable(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }

    private function hasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (Throwable) {
            return false;
        }
    }

    private function formatNumber(int|float $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
