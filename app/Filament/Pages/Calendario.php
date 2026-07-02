<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\ItemControle;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class Calendario extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string | UnitEnum | null $navigationGroup = 'Pendências e Prazos';

    protected static ?string $navigationLabel = 'Calendário Operacional';

    protected static ?string $title = 'Calendário Operacional';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.calendario';

    public int $mesOffset = 0;

    public ?int $itemSelecionadoId = null;

    public bool $modalAberto = false;

    protected array $calendarioCache = [];

    protected function baseQuery(bool $incluirDetalhes = false): Builder
    {
        $query = ItemControle::query()
            ->select([
                'id',
                'titulo',
                'descricao',
                'observacao',
                'tipo',
                'status',
                'prioridade',
                'empresa_id',
                'responsavel_id',
                'categoria_id',
                'data_vencimento',
                'data_conclusao',
                'sla_horas',
                'sla_limite_em',
                'sla_concluido_em',
                'sla_status',
                'created_at',
                'updated_at',
            ])
            ->with([
                'empresa:id,razao_social',
                'responsavel:id,nome',
            ])
            ->visibleForUser(Filament::auth()->user());

        if ($incluirDetalhes) {
            $query->with([
                'categoria:id,nome',
                'checklists:id,item_controle_id,titulo,concluido,ordem',
            ]);
        }

        return $query;
    }

    protected function limparCacheCalendario(): void
    {
        $this->calendarioCache = [];
    }

    protected function dataBaseMes()
    {
        return now()->addMonths($this->mesOffset)->startOfMonth();
    }

    public function mesAnterior(): void
    {
        $this->mesOffset--;
        $this->limparCacheCalendario();
    }

    public function proximoMes(): void
    {
        $this->mesOffset++;
        $this->limparCacheCalendario();
    }

    public function voltarParaHoje(): void
    {
        $this->mesOffset = 0;
        $this->limparCacheCalendario();
    }

    public function abrirItem(int $itemId): void
    {
        $this->itemSelecionadoId = $itemId;
        $this->modalAberto = true;
    }

    public function fecharModal(): void
    {
        $this->modalAberto = false;
        $this->itemSelecionadoId = null;
    }

    public function alterarStatusSelecionado(string $status): void
    {
        $item = $this->getItemSelecionadoModel();

        if (! $item) {
            return;
        }

        $dados = ['status' => $status];

        if ($status === 'concluido') {
            $dados['data_conclusao'] = now()->toDateString();
            $dados['sla_concluido_em'] = now();
            $dados['sla_status'] = 'concluido';
        }

        if ($status !== 'concluido') {
            $dados['data_conclusao'] = null;
            $dados['sla_concluido_em'] = null;
        }

        $item->update($dados);
        $this->limparCacheCalendario();

        Notification::make()
            ->title('Status atualizado')
            ->body('O item foi atualizado diretamente pelo calendário.')
            ->success()
            ->send();
    }

    public function getDias(): array
    {
        $inicioMes = $this->dataBaseMes();
        $inicioGrade = $inicioMes->copy()->startOfWeek();
        $fimGrade = $inicioMes->copy()->endOfMonth()->endOfWeek();

        $itens = $this->baseQuery()
            ->whereBetween('data_vencimento', [$inicioGrade->toDateString(), $fimGrade->toDateString()])
            ->orderBy('data_vencimento')
            ->orderByRaw("CASE prioridade WHEN 'critica' THEN 1 WHEN 'urgente' THEN 2 WHEN 'alta' THEN 3 WHEN 'media' THEN 4 WHEN 'baixa' THEN 5 ELSE 99 END")
            ->get()
            ->groupBy(fn (ItemControle $item): string => $item->data_vencimento?->format('Y-m-d') ?? 'sem_data');

        $dias = [];
        $cursor = $inicioGrade->copy();

        while ($cursor->lte($fimGrade)) {
            $chave = $cursor->format('Y-m-d');
            $itensDia = $itens->get($chave, collect());

            $dias[] = [
                'dia' => $cursor->day,
                'data_chave' => $chave,
                'data' => $cursor->format('d/m/Y'),
                'semana' => ucfirst($cursor->translatedFormat('D')),
                'hoje' => $cursor->isToday(),
                'fora_mes' => ! $cursor->isSameMonth($inicioMes),
                'atrasado' => $cursor->isPast() && ! $cursor->isToday() && $itensDia->whereNotIn('status', ['concluido', 'cancelado'])->isNotEmpty(),
                'total' => $itensDia->count(),
                'itens' => $itensDia->take(5)->map(fn (ItemControle $item): array => $this->formatarItemCalendario($item))->all(),
            ];

            $cursor->addDay();
        }

        return $dias;
    }

    public function getResumo(): array
    {
        $inicio = $this->dataBaseMes();
        $fim = $inicio->copy()->endOfMonth();
        $cacheKey = 'resumo_' . $inicio->format('Y_m');

        if (isset($this->calendarioCache[$cacheKey])) {
            return $this->calendarioCache[$cacheKey];
        }

        $query = $this->baseQuery()
            ->whereBetween('data_vencimento', [$inicio->toDateString(), $fim->toDateString()]);

        $hoje = now();

        return $this->calendarioCache[$cacheKey] = [
            'total' => (clone $query)->count(),
            'atrasados' => (clone $query)
                ->whereDate('data_vencimento', '<', $hoje->toDateString())
                ->whereNotIn('status', ['concluido', 'concluida', 'cancelado'])
                ->count(),
            'hoje' => (clone $query)->whereDate('data_vencimento', $hoje->toDateString())->count(),
            'semana' => (clone $query)
                ->whereBetween('data_vencimento', [$hoje->copy()->startOfWeek()->toDateString(), $hoje->copy()->endOfWeek()->toDateString()])
                ->count(),
            'concluidos' => (clone $query)->whereIn('status', ['concluido', 'concluida'])->count(),
        ];
    }

    public function getProximos(): array
    {
        return $this->baseQuery()
            ->whereDate('data_vencimento', '>=', now()->toDateString())
            ->whereNotIn('status', ['concluido', 'cancelado'])
            ->orderBy('data_vencimento')
            ->orderByRaw("CASE prioridade WHEN 'critica' THEN 1 WHEN 'urgente' THEN 2 WHEN 'alta' THEN 3 WHEN 'media' THEN 4 WHEN 'baixa' THEN 5 ELSE 99 END")
            ->limit(12)
            ->get()
            ->map(fn (ItemControle $item): array => $this->formatarItemCalendario($item))
            ->all();
    }

    public function getAtrasados(): array
    {
        return $this->baseQuery()
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->whereNotIn('status', ['concluido', 'cancelado'])
            ->orderBy('data_vencimento')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->formatarItemCalendario($item))
            ->all();
    }

    public function getSemData(): array
    {
        return $this->baseQuery()
            ->whereNull('data_vencimento')
            ->whereNotIn('status', ['concluido', 'cancelado'])
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->formatarItemCalendario($item))
            ->all();
    }

    public function getItemSelecionado(): ?array
    {
        $item = $this->getItemSelecionadoModel();

        if (! $item) {
            return null;
        }

        $checklists = $item->checklists;
        $totalChecklist = $checklists->count();
        $concluidosChecklist = $checklists->where('concluido', true)->count();

        return array_merge($this->formatarItemCalendario($item), [
            'descricao' => $item->descricao ?: 'Sem descrição cadastrada.',
            'observacao' => $item->observacao ?: null,
            'categoria' => $item->categoria?->nome ?? '-',
            'sla' => $this->formatarSla($item),
            'checklists' => $checklists->map(fn ($checklist): array => [
                'titulo' => $checklist->titulo,
                'concluido' => (bool) $checklist->concluido,
            ])->all(),
            'checklist_resumo' => $totalChecklist > 0 ? $concluidosChecklist . '/' . $totalChecklist . ' concluídos' : 'Nenhum checklist cadastrado',
        ]);
    }

    protected function getItemSelecionadoModel(): ?ItemControle
    {
        if (! $this->itemSelecionadoId) {
            return null;
        }

        return $this->baseQuery(true)->find($this->itemSelecionadoId);
    }

    protected function formatarItemCalendario(ItemControle $item): array
    {
        return [
            'id' => $item->id,
            'titulo' => $item->titulo,
            'tipo' => ucfirst(str_replace('_', ' ', (string) $item->tipo)),
            'status' => ucfirst(str_replace('_', ' ', (string) $item->status)),
            'status_raw' => (string) $item->status,
            'prioridade' => ucfirst((string) ($item->prioridade ?: 'Normal')),
            'prioridade_raw' => (string) ($item->prioridade ?: 'normal'),
            'data' => $item->data_vencimento?->format('d/m/Y') ?? 'Sem data',
            'data_humana' => $this->formatarDataHumana($item),
            'empresa' => $item->empresa?->razao_social ?? '-',
            'responsavel' => $item->responsavel?->nome ?? '-',
            'atrasado' => $this->itemAtrasado($item),
            'url' => ItemControleResource::getUrl('edit', ['record' => $item]),
        ];
    }

    protected function itemAtrasado(ItemControle $item): bool
    {
        return $item->data_vencimento
            && $item->data_vencimento->isPast()
            && ! $item->data_vencimento->isToday()
            && ! in_array($item->status, ['concluido', 'cancelado'], true);
    }

    protected function formatarDataHumana(ItemControle $item): string
    {
        if (! $item->data_vencimento) {
            return 'Sem vencimento definido';
        }

        if ($item->data_vencimento->isToday()) {
            return 'Vence hoje';
        }

        if ($this->itemAtrasado($item)) {
            return 'Atrasado há ' . $item->data_vencimento->diffInDays(now()) . ' dia(s)';
        }

        return 'Vence em ' . now()->diffInDays($item->data_vencimento) . ' dia(s)';
    }

    protected function formatarSla(ItemControle $item): string
    {
        if (! $item->sla_limite_em) {
            return 'SLA não configurado';
        }

        if ($item->sla_status === 'concluido') {
            return 'SLA concluído';
        }

        if ($item->sla_limite_em->isPast()) {
            return 'SLA vencido em ' . $item->sla_limite_em->format('d/m/Y H:i');
        }

        return 'Limite em ' . $item->sla_limite_em->format('d/m/Y H:i');
    }

    public function getMesAtual(): string
    {
        return ucfirst($this->dataBaseMes()->translatedFormat('F/Y'));
    }
}
