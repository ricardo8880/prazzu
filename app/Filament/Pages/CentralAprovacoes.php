<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\ItemControle;
use App\Support\CachedSchema;
use App\Support\PrazzuAccessControl;
use BackedEnum;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use UnitEnum;

class CentralAprovacoes extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-inbox-stack';
    protected static string | UnitEnum | null $navigationGroup = 'Governança';
    protected static ?string $navigationLabel = 'Central de Aprovações';
    protected static ?string $title = 'Central de Aprovações';
    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.central-aprovacoes';

    public string $statusFiltro = 'pendente';
    public string $prioridadeFiltro = 'todas';
    public string $busca = '';
    public ?int $reprovacaoSelecionada = null;
    public ?int $confirmacaoAprovacaoSelecionada = null;
    public string $motivoReprovacao = '';
    public bool $aprovacaoRevisada = false;
    public ?int $detalhesSelecionado = null;

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return PrazzuAccessControl::canUseAprovacoes(PrazzuAccessControl::user());
    }

    protected function getViewData(): array
    {
        $resumo = $this->resumo();
        $fila = $this->filaPriorizada();

        return [
            'resumo' => $resumo,
            'diagnostico' => $this->diagnostico($resumo),
            'kanban' => $this->kanban(),
            'fila' => $fila,
            'atencaoAgora' => $this->atencaoAgora($fila),
            'responsaveis' => $this->responsaveis(),
            'recentes' => $this->recentes(),
            'historico' => $this->historicoAprovacoes(),
            'atalhos' => $this->atalhos(),
            'aprovacaoEmConfirmacao' => $this->aprovacaoEmConfirmacao(),
            'reprovacaoEmEdicao' => $this->reprovacaoEmEdicao(),
            'detalhesEmVisualizacao' => $this->detalhesEmVisualizacao(),
            'temTabelaAprovacoes' => CachedSchema::hasTable('item_controle_aprovacoes'),
        ];
    }

    public function limparFiltros(): void
    {
        $this->statusFiltro = 'pendente';
        $this->prioridadeFiltro = 'todas';
        $this->busca = '';
    }

    public function abrirDetalhesItem(int $aprovacaoId): void
    {
        if (! $this->usuarioPodeVisualizarAprovacao($aprovacaoId)) {
            Notification::make()
                ->title('Item indisponível no seu escopo de acesso.')
                ->body('A aprovação pode ter sido removida, concluída ou pertencer a outro escopo.')
                ->danger()
                ->send();

            return;
        }

        $this->detalhesSelecionado = $aprovacaoId;
        $this->confirmacaoAprovacaoSelecionada = null;
        $this->reprovacaoSelecionada = null;
        $this->aprovacaoRevisada = false;
        $this->motivoReprovacao = '';
    }

    public function fecharDetalhesItem(): void
    {
        $this->detalhesSelecionado = null;
    }

    public function abrirConfirmacaoAprovacao(int $aprovacaoId): void
    {
        if (! $this->usuarioPodeDecidirAprovacao($aprovacaoId)) {
            Notification::make()
                ->title('Você não tem permissão para decidir esta aprovação.')
                ->body('A aprovação pode ter sido concluída, pertencer a outro escopo ou não estar mais disponível.')
                ->danger()
                ->send();

            return;
        }

        $this->confirmacaoAprovacaoSelecionada = $aprovacaoId;
        $this->detalhesSelecionado = null;
        $this->aprovacaoRevisada = false;
    }

    public function cancelarConfirmacaoAprovacao(): void
    {
        $this->confirmacaoAprovacaoSelecionada = null;
        $this->aprovacaoRevisada = false;
    }

    public function confirmarAprovacao(): void
    {
        if (! $this->confirmacaoAprovacaoSelecionada) {
            Notification::make()->title('Selecione uma aprovação para confirmar.')->warning()->send();
            return;
        }

        if (! $this->aprovacaoRevisada) {
            Notification::make()
                ->title('Confirme que você revisou o contexto antes de aprovar.')
                ->body('Essa etapa evita aprovações acidentais e mantém a decisão rastreável.')
                ->warning()
                ->send();
            return;
        }

        $this->decidir($this->confirmacaoAprovacaoSelecionada, 'aprovado', 'Aprovado pela Central de Aprovações após revisão do contexto.');
        $this->cancelarConfirmacaoAprovacao();
    }

    public function aprovar(int $aprovacaoId): void
    {
        $this->abrirConfirmacaoAprovacao($aprovacaoId);
    }

    public function abrirReprovacao(int $aprovacaoId): void
    {
        if (! $this->usuarioPodeDecidirAprovacao($aprovacaoId)) {
            Notification::make()
                ->title('Você não tem permissão para reprovar esta aprovação.')
                ->body('A aprovação pode ter sido concluída, pertencer a outro escopo ou não estar mais disponível.')
                ->danger()
                ->send();

            return;
        }

        $this->reprovacaoSelecionada = $aprovacaoId;
        $this->detalhesSelecionado = null;
        $this->motivoReprovacao = '';
    }

    public function cancelarReprovacao(): void
    {
        $this->reprovacaoSelecionada = null;
        $this->motivoReprovacao = '';
    }

    public function reprovarComComentario(): void
    {
        $motivo = trim($this->motivoReprovacao);

        if (! $this->reprovacaoSelecionada) {
            Notification::make()->title('Selecione uma aprovação para reprovar.')->warning()->send();
            return;
        }

        if ($motivo === '') {
            Notification::make()
                ->title('Informe o motivo da reprovação.')
                ->body('O comentário ajuda o responsável a corrigir o documento sem retrabalho.')
                ->danger()
                ->send();
            return;
        }

        $this->decidir($this->reprovacaoSelecionada, 'reprovado', $motivo);
        $this->cancelarReprovacao();
    }

    private function decidir(int $aprovacaoId, string $status, string $observacao): void
    {
        if (! CachedSchema::hasTable('item_controle_aprovacoes') || ! CachedSchema::hasTable('item_controles')) {
            Notification::make()->title('Estrutura de aprovações não encontrada.')->danger()->send();
            return;
        }

        $aprovacao = $this->queryAprovacoes()
            ->where('item_controle_aprovacoes.id', $aprovacaoId)
            ->first();

        if (! $aprovacao) {
            Notification::make()->title('Aprovação não encontrada no seu escopo de acesso.')->danger()->send();
            return;
        }

        if (($aprovacao->status ?? null) !== 'pendente') {
            Notification::make()->title('Esta aprovação já foi decidida.')->warning()->send();
            return;
        }

        $item = ItemControle::query()
            ->visibleForUser($this->usuarioAtual())
            ->whereKey($aprovacao->item_controle_id)
            ->first();

        if (! $this->usuarioPodeDecidirItem($item)) {
            Notification::make()
                ->title('Você não tem permissão para decidir este item.')
                ->body('Seu usuário pode visualizar este registro, mas não possui permissão operacional para aprovar ou reprovar esta solicitação.')
                ->danger()
                ->send();
            return;
        }

        try {
            $resultado = DB::transaction(function () use ($item, $aprovacaoId, $status, $observacao): bool {
                if ($status === 'aprovado') {
                    return $item->aprovar($this->usuarioAtual(), $observacao, $aprovacaoId);
                }

                return $item->reprovar($this->usuarioAtual(), $observacao, $aprovacaoId);
            });

            if (! $resultado) {
                Notification::make()
                    ->title('A aprovação não pôde ser sincronizada.')
                    ->body('O registro pode ter sido decidido por outro usuário ou não está mais pendente. Atualize a página antes de tentar novamente.')
                    ->warning()
                    ->send();

                return;
            }
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Não foi possível registrar a decisão.')
                ->body('Tente novamente ou revise o item de controle antes de decidir.')
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title($status === 'aprovado' ? 'Aprovação concluída com segurança.' : 'Ajuste solicitado com sucesso.')
            ->body($status === 'aprovado' ? 'O item foi aprovado e o status foi sincronizado.' : 'O responsável recebeu uma decisão rastreável para correção.')
            ->success()
            ->send();
    }

    private function usuarioPodeDecidirAprovacao(int $aprovacaoId): bool
    {
        if (! CachedSchema::hasTable('item_controle_aprovacoes') || ! CachedSchema::hasTable('item_controles')) {
            return false;
        }

        $aprovacao = $this->queryAprovacoes()
            ->where('item_controle_aprovacoes.id', $aprovacaoId)
            ->where('item_controle_aprovacoes.status', 'pendente')
            ->first();

        if (! $aprovacao || empty($aprovacao->item_controle_id)) {
            return false;
        }

        $item = ItemControle::query()
            ->visibleForUser($this->usuarioAtual())
            ->whereKey($aprovacao->item_controle_id)
            ->first();

        return $this->usuarioPodeDecidirItem($item);
    }

    private function usuarioPodeVisualizarAprovacao(int $aprovacaoId): bool
    {
        if (! CachedSchema::hasTable('item_controle_aprovacoes') || ! CachedSchema::hasTable('item_controles')) {
            return false;
        }

        return $this->queryAprovacoes()
            ->where('item_controle_aprovacoes.id', $aprovacaoId)
            ->exists();
    }

    private function usuarioPodeDecidirItem(?ItemControle $item): bool
    {
        $user = $this->usuarioAtual();

        if (! $item || ! $user) {
            return false;
        }

        if (! PrazzuAccessControl::canUseAprovacoes($user)) {
            return false;
        }

        return $item->canBeModifiedBy($user);
    }

    private function resumo(): array
    {
        if (! CachedSchema::hasTable('item_controle_aprovacoes') || ! CachedSchema::hasTable('item_controles')) {
            return [
                'total' => 0,
                'pendentes' => 0,
                'aprovadas' => 0,
                'reprovadas' => 0,
                'hoje' => 0,
                'atrasadas' => 0,
                'criticas' => 0,
                'tempoMedio' => '0h',
                'taxaResolucao' => 0,
            ];
        }

        $base = $this->baseAprovacoesQuery();
        $total = (clone $base)->count();
        $aprovadas = (clone $base)->whereIn('item_controle_aprovacoes.status', ['aprovado', 'aprovada'])->count();
        $reprovadas = (clone $base)->whereIn('item_controle_aprovacoes.status', ['reprovado', 'reprovada'])->count();
        $decididas = $aprovadas + $reprovadas;

        return [
            'total' => $total,
            'pendentes' => (clone $base)->where('item_controle_aprovacoes.status', 'pendente')->count(),
            'aprovadas' => $aprovadas,
            'reprovadas' => $reprovadas,
            'hoje' => (clone $base)->whereDate('item_controle_aprovacoes.solicitado_em', now()->toDateString())->count(),
            'atrasadas' => (clone $base)
                ->where('item_controle_aprovacoes.status', 'pendente')
                ->whereNotNull('item_controles.data_vencimento')
                ->whereDate('item_controles.data_vencimento', '<', now()->toDateString())
                ->count(),
            'criticas' => (clone $base)
                ->where('item_controle_aprovacoes.status', 'pendente')
                ->whereIn('item_controles.prioridade', ['critica', 'crítica', 'alta'])
                ->count(),
            'tempoMedio' => $this->tempoMedioResposta(),
            'taxaResolucao' => $total > 0 ? (int) round(($decididas / $total) * 100) : 0,
        ];
    }

    private function diagnostico(array $resumo): array
    {
        $atrasadas = (int) ($resumo['atrasadas'] ?? 0);
        $criticas = (int) ($resumo['criticas'] ?? 0);
        $pendentes = (int) ($resumo['pendentes'] ?? 0);

        if ($atrasadas > 0) {
            return [
                'tom' => 'danger',
                'titulo' => 'Atenção imediata necessária',
                'descricao' => 'Existem aprovações pendentes com item vencido. Priorize esses registros antes de analisar a fila normal.',
                'acao' => 'Comece pelas aprovações atrasadas',
            ];
        }

        if ($criticas > 0) {
            return [
                'tom' => 'warning',
                'titulo' => 'Fila com prioridade alta',
                'descricao' => 'Há aprovações críticas ou de alta prioridade aguardando decisão. Resolva primeiro os itens destacados.',
                'acao' => 'Revise os itens críticos',
            ];
        }

        if ($pendentes > 0) {
            return [
                'tom' => 'info',
                'titulo' => 'Fila controlada, mas ativa',
                'descricao' => 'Ainda existem aprovações abertas. Acompanhe a fila priorizada para manter o fluxo sem gargalos.',
                'acao' => 'Decida a próxima pendência',
            ];
        }

        return [
            'tom' => 'success',
            'titulo' => 'Tudo em dia',
            'descricao' => 'Não há aprovações pendentes no seu escopo de acesso neste momento.',
            'acao' => 'Acompanhe o histórico',
        ];
    }

    private function kanban(): array
    {
        if (! CachedSchema::hasTable('item_controle_aprovacoes') || ! CachedSchema::hasTable('item_controles')) {
            return [
                'pendente' => ['titulo' => 'Pendente', 'descricao' => 'Entrada de aprovações para decidir agora.', 'tom' => 'warning', 'items' => []],
                'aprovado' => ['titulo' => 'Aprovado', 'descricao' => 'Decisões positivas mais recentes.', 'tom' => 'success', 'items' => []],
                'reprovado' => ['titulo' => 'Ajuste solicitado', 'descricao' => 'Itens devolvidos para correção.', 'tom' => 'danger', 'items' => []],
            ];
        }

        return [
            'pendente' => [
                'titulo' => 'Pendente',
                'descricao' => 'Entrada de aprovações para decidir agora.',
                'tom' => 'warning',
                'items' => $this->queryAprovacoes()->where('item_controle_aprovacoes.status', 'pendente')->limit(6)->get()->map(fn ($item) => $this->formatarAprovacao($item))->all(),
            ],
            'aprovado' => [
                'titulo' => 'Aprovado',
                'descricao' => 'Decisões positivas mais recentes.',
                'tom' => 'success',
                'items' => $this->queryAprovacoes()->whereIn('item_controle_aprovacoes.status', ['aprovado', 'aprovada'])->limit(6)->get()->map(fn ($item) => $this->formatarAprovacao($item))->all(),
            ],
            'reprovado' => [
                'titulo' => 'Ajuste solicitado',
                'descricao' => 'Itens devolvidos para correção.',
                'tom' => 'danger',
                'items' => $this->queryAprovacoes()->whereIn('item_controle_aprovacoes.status', ['reprovado', 'reprovada'])->limit(6)->get()->map(fn ($item) => $this->formatarAprovacao($item))->all(),
            ],
        ];
    }

    private function filaPriorizada(): array
    {
        if (! CachedSchema::hasTable('item_controle_aprovacoes') || ! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        $query = $this->queryAprovacoes();

        if ($this->statusFiltro !== 'todos') {
            if ($this->statusFiltro === 'aprovado') {
                $query->whereIn('item_controle_aprovacoes.status', ['aprovado', 'aprovada']);
            } elseif ($this->statusFiltro === 'reprovado') {
                $query->whereIn('item_controle_aprovacoes.status', ['reprovado', 'reprovada']);
            } else {
                $query->where('item_controle_aprovacoes.status', $this->statusFiltro);
            }
        }

        if ($this->prioridadeFiltro !== 'todas') {
            $query->where('item_controles.prioridade', $this->prioridadeFiltro);
        }

        if (trim($this->busca) !== '') {
            $busca = '%' . Str::lower(trim($this->busca)) . '%';
            $query->where(function ($q) use ($busca): void {
                $q->whereRaw("LOWER(COALESCE(item_controles.titulo, '')) LIKE ?", [$busca])
                    ->orWhereRaw("LOWER(COALESCE(item_controles.descricao, '')) LIKE ?", [$busca])
                    ->orWhereRaw("LOWER(COALESCE(empresas.nome_fantasia, '')) LIKE ?", [$busca])
                    ->orWhereRaw("LOWER(COALESCE(empresas.razao_social, '')) LIKE ?", [$busca]);
            });
        }

        return $query->limit(30)->get()->map(fn ($item) => $this->formatarAprovacao($item))->all();
    }

    private function atencaoAgora(array $fila): array
    {
        return collect($fila)
            ->filter(fn (array $item): bool => $item['status'] === 'pendente' && ($item['atrasado'] || in_array($item['prioridade_raw'], ['critica', 'crítica', 'alta'], true)))
            ->take(4)
            ->values()
            ->all();
    }

    private function responsaveis(): array
    {
        if (! CachedSchema::hasTable('item_controle_aprovacoes') || ! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        return $this->baseAprovacoesQuery()
            ->leftJoin('responsaveis', 'responsaveis.id', '=', 'item_controles.responsavel_id')
            ->where('item_controle_aprovacoes.status', 'pendente')
            ->selectRaw("COALESCE(responsaveis.nome, 'Sem responsável') as nome_responsavel, COUNT(*) as total")
            ->groupByRaw("COALESCE(responsaveis.nome, 'Sem responsável')")
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row) => ['nome' => $row->nome_responsavel, 'total' => (int) $row->total])
            ->all();
    }

    private function recentes(): array
    {
        if (! CachedSchema::hasTable('item_controle_aprovacoes') || ! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        return $this->queryAprovacoes()
            ->whereNotNull('item_controle_aprovacoes.respondido_em')
            ->limit(8)
            ->get()
            ->map(fn ($item) => $this->formatarAprovacao($item))
            ->all();
    }

    private function historicoAprovacoes(): array
    {
        if (! CachedSchema::hasTable('item_controle_aprovacoes') || ! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        return $this->queryAprovacoes()
            ->where(function ($query): void {
                $query->whereNotNull('item_controle_aprovacoes.respondido_em')
                    ->orWhereIn('item_controle_aprovacoes.status', ['aprovado', 'aprovada', 'reprovado', 'reprovada']);
            })
            ->limit(12)
            ->get()
            ->map(fn ($item) => $this->formatarAprovacao($item))
            ->all();
    }

    private function queryAprovacoes(): Builder
    {
        return $this->baseAprovacoesQuery()
            ->leftJoin('responsaveis', 'responsaveis.id', '=', 'item_controles.responsavel_id')
            ->leftJoin('users as solicitantes', 'solicitantes.id', '=', 'item_controle_aprovacoes.solicitante_id')
            ->leftJoin('users as aprovadores', 'aprovadores.id', '=', 'item_controle_aprovacoes.aprovador_id')
            ->select(
                'item_controle_aprovacoes.id',
                'item_controle_aprovacoes.item_controle_id',
                'item_controle_aprovacoes.status',
                'item_controle_aprovacoes.observacao_solicitacao',
                'item_controle_aprovacoes.observacao_resposta',
                'item_controle_aprovacoes.motivo_reprovacao',
                'item_controle_aprovacoes.solicitado_em',
                'item_controle_aprovacoes.respondido_em',
                'item_controle_aprovacoes.solicitante_id',
                'item_controle_aprovacoes.aprovador_id',
                'item_controles.titulo',
                'item_controles.descricao',
                'item_controles.tipo',
                'item_controles.prioridade',
                'item_controles.data_vencimento',
                'item_controles.status as item_status',
                'item_controles.observacao as item_observacao',
                'item_controles.approval_status',
                'item_controles.document_status',
                'item_controles.signature_status',
                'item_controles.risk_probability',
                'item_controles.risk_impact',
                'item_controles.risk_score',
                'item_controles.created_at as item_created_at',
                'item_controles.updated_at as item_updated_at',
                'empresas.nome_fantasia',
                'empresas.razao_social',
                'responsaveis.nome as responsavel_nome',
                'solicitantes.name as solicitante_nome',
                'aprovadores.name as aprovador_nome'
            )
            ->orderByRaw("CASE WHEN item_controle_aprovacoes.status = 'pendente' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN item_controles.data_vencimento IS NOT NULL AND item_controles.data_vencimento < ? AND item_controle_aprovacoes.status = 'pendente' THEN 0 ELSE 1 END", [now()->toDateString()])
            ->orderByRaw("CASE WHEN item_controles.prioridade IN ('critica', 'crítica', 'alta') THEN 0 ELSE 1 END")
            ->orderByRaw('CASE WHEN item_controles.data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('item_controles.data_vencimento')
            ->orderByDesc('item_controle_aprovacoes.solicitado_em');
    }

    private function baseAprovacoesQuery(): Builder
    {
        $user = $this->usuarioAtual();
        $itensVisiveis = ItemControle::query()
            ->select('item_controles.id')
            ->visibleForUser($user);

        return DB::table('item_controle_aprovacoes')
            ->join('item_controles', 'item_controles.id', '=', 'item_controle_aprovacoes.item_controle_id')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controle_aprovacoes.empresa_id')
            ->whereIn('item_controle_aprovacoes.item_controle_id', $itensVisiveis);
    }

    private function formatarAprovacao(object $item): array
    {
        $status = (string) ($item->status ?? 'pendente');
        $empresa = $item->nome_fantasia ?: ($item->razao_social ?: 'Sem empresa');
        $solicitado = $item->solicitado_em ? Carbon::parse($item->solicitado_em) : null;
        $vencimento = $item->data_vencimento ? Carbon::parse($item->data_vencimento) : null;
        $atrasado = $status === 'pendente' && $vencimento && $vencimento->isPast() && ! $vencimento->isToday();
        $idadeHoras = $solicitado ? max(0, (int) $solicitado->diffInHours(now())) : 0;
        $prioridadeRaw = (string) ($item->prioridade ?: 'normal');
        $critico = in_array($prioridadeRaw, ['critica', 'crítica', 'alta'], true);

        return [
            'id' => (int) $item->id,
            'item_controle_id' => $item->item_controle_id,
            'titulo' => $item->titulo ?: 'Solicitação sem item vinculado',
            'descricao' => Str::limit((string) ($item->descricao ?: $item->observacao_solicitacao ?: 'Sem descrição cadastrada.'), 180),
            'descricao_completa' => (string) ($item->descricao ?: $item->observacao_solicitacao ?: 'Sem descrição cadastrada.'),
            'empresa' => $empresa,
            'responsavel' => $item->responsavel_nome ?: 'Sem responsável',
            'solicitante' => $item->solicitante_nome ?: 'Sistema',
            'aprovador' => $item->aprovador_nome ?: ($status === 'pendente' ? 'Aguardando decisão' : 'Sistema'),
            'tipo' => ucfirst(str_replace('_', ' ', (string) ($item->tipo ?: 'aprovação'))),
            'prioridade' => ucfirst(str_replace('_', ' ', $prioridadeRaw)),
            'prioridade_raw' => $prioridadeRaw,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'tom' => $this->statusTone($status, $atrasado, $critico),
            'solicitado_em' => $solicitado?->format('d/m/Y H:i') ?: '-',
            'respondido_em' => ! empty($item->respondido_em) ? Carbon::parse($item->respondido_em)->format('d/m/Y H:i') : '-',
            'vencimento' => $vencimento?->format('d/m/Y') ?: '-',
            'idade' => $idadeHoras < 24 ? $idadeHoras . 'h' : floor($idadeHoras / 24) . 'd',
            'atrasado' => $atrasado,
            'critico' => $critico,
            'observacao' => $item->observacao_solicitacao ?: 'Sem observação cadastrada.',
            'resposta' => $item->observacao_resposta ?: ($item->motivo_reprovacao ?: null),
            'item_status' => $this->statusLabel((string) ($item->item_status ?: 'pendente')),
            'item_status_raw' => (string) ($item->item_status ?: 'pendente'),
            'item_observacao' => $item->item_observacao ?: 'Sem observação operacional cadastrada.',
            'approval_status' => $item->approval_status ?: 'Não informado',
            'document_status' => $item->document_status ?: 'Não informado',
            'signature_status' => $item->signature_status ?: 'Não informado',
            'risk_probability' => $item->risk_probability !== null ? (int) $item->risk_probability : null,
            'risk_impact' => $item->risk_impact !== null ? (int) $item->risk_impact : null,
            'risk_score' => $item->risk_score !== null ? (int) $item->risk_score : null,
            'criado_em' => ! empty($item->item_created_at) ? Carbon::parse($item->item_created_at)->format('d/m/Y H:i') : '-',
            'atualizado_em' => ! empty($item->item_updated_at) ? Carbon::parse($item->item_updated_at)->format('d/m/Y H:i') : '-',
            'url' => ! empty($item->item_controle_id) ? ItemControleResource::getUrl('edit', ['record' => $item->item_controle_id]) : null,
            'decisao_alerta' => $this->alertaDecisao($status, $atrasado, $critico, $vencimento),
            'decisao_checklist' => $this->checklistDecisao($status, $atrasado, $critico, $vencimento),
        ];
    }


    private function alertaDecisao(string $status, bool $atrasado, bool $critico, ?Carbon $vencimento): string
    {
        if ($status !== 'pendente') {
            return 'Esta solicitação já foi decidida. Use o histórico para auditoria e rastreabilidade.';
        }

        if ($atrasado) {
            return 'Este item está vencido. Antes de aprovar, confirme se o responsável corrigiu o risco de atraso ou se a decisão ainda faz sentido.';
        }

        if ($critico) {
            return 'Este item possui prioridade alta. Revise empresa, responsável, observação e vencimento antes de confirmar a decisão.';
        }

        if ($vencimento && $vencimento->isToday()) {
            return 'Este item vence hoje. Decida com atenção para evitar gargalo operacional no fim do dia.';
        }

        return 'Revise o contexto do item antes de decidir. A ação será registrada no histórico e sincronizada com o item de controle.';
    }

    private function checklistDecisao(string $status, bool $atrasado, bool $critico, ?Carbon $vencimento): array
    {
        $checklist = [
            'Conferir se a empresa e o responsável estão corretos.',
            'Ler a descrição e a observação da solicitação.',
            'Confirmar que a decisão está alinhada com o status atual do item.',
        ];

        if ($status === 'pendente' && $atrasado) {
            array_unshift($checklist, 'Validar o motivo do atraso antes de aprovar.');
        } elseif ($status === 'pendente' && $critico) {
            array_unshift($checklist, 'Revisar o impacto da prioridade alta antes de concluir.');
        } elseif ($status === 'pendente' && $vencimento && $vencimento->isToday()) {
            array_unshift($checklist, 'Tratar a decisão como prioridade do dia.');
        }

        return $checklist;
    }

    private function detalhesEmVisualizacao(): ?array
    {
        if (! $this->detalhesSelecionado || ! CachedSchema::hasTable('item_controle_aprovacoes') || ! CachedSchema::hasTable('item_controles')) {
            return null;
        }

        $aprovacao = $this->queryAprovacoes()
            ->where('item_controle_aprovacoes.id', $this->detalhesSelecionado)
            ->first();

        return $aprovacao ? $this->formatarAprovacao($aprovacao) : null;
    }

    private function aprovacaoEmConfirmacao(): ?array
    {
        if (! $this->confirmacaoAprovacaoSelecionada || ! CachedSchema::hasTable('item_controle_aprovacoes') || ! CachedSchema::hasTable('item_controles')) {
            return null;
        }

        $aprovacao = $this->queryAprovacoes()->where('item_controle_aprovacoes.id', $this->confirmacaoAprovacaoSelecionada)->first();

        return $aprovacao ? $this->formatarAprovacao($aprovacao) : null;
    }

    private function reprovacaoEmEdicao(): ?array
    {
        if (! $this->reprovacaoSelecionada || ! CachedSchema::hasTable('item_controle_aprovacoes') || ! CachedSchema::hasTable('item_controles')) {
            return null;
        }

        $aprovacao = $this->queryAprovacoes()->where('item_controle_aprovacoes.id', $this->reprovacaoSelecionada)->first();

        return $aprovacao ? $this->formatarAprovacao($aprovacao) : null;
    }

    private function tempoMedioResposta(): string
    {
        if (! CachedSchema::hasTable('item_controle_aprovacoes') || ! CachedSchema::hasTable('item_controles')) {
            return '0h';
        }

        $rows = $this->baseAprovacoesQuery()
            ->whereNotNull('item_controle_aprovacoes.solicitado_em')
            ->whereNotNull('item_controle_aprovacoes.respondido_em')
            ->limit(200)
            ->get(['item_controle_aprovacoes.solicitado_em', 'item_controle_aprovacoes.respondido_em']);

        if ($rows->isEmpty()) {
            return '0h';
        }

        $media = (int) round($rows->avg(fn ($row) => Carbon::parse($row->solicitado_em)->diffInHours(Carbon::parse($row->respondido_em))));

        return $media < 24 ? $media . 'h' : floor($media / 24) . 'd';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pendente' => 'Pendente',
            'aprovado', 'aprovada' => 'Aprovado',
            'reprovado', 'reprovada' => 'Ajuste',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    private function statusTone(string $status, bool $atrasado = false, bool $critico = false): string
    {
        if ($atrasado) {
            return 'danger';
        }

        if ($critico && $status === 'pendente') {
            return 'critical';
        }

        return match ($status) {
            'pendente' => 'warning',
            'aprovado', 'aprovada' => 'success',
            'reprovado', 'reprovada' => 'danger',
            default => 'info',
        };
    }

    private function atalhos(): array
    {
        return collect([
            ['label' => 'Nova solicitação', 'page' => 'create', 'primary' => true],
            ['label' => 'Todos os itens', 'page' => 'index', 'primary' => false],
            ['label' => 'Fluxo de aprovações do item', 'page' => 'aprovacoes', 'primary' => false],
            ['label' => 'Pendências', 'page' => 'pendencias', 'primary' => false],
            ['label' => 'Relatórios', 'page' => 'relatorios-internos', 'primary' => false],
        ])
            ->map(function (array $atalho): ?array {
                $url = $this->urlItemControlePage($atalho['page']);

                if (! $url) {
                    return null;
                }

                return [
                    'label' => $atalho['label'],
                    'url' => $url,
                    'primary' => (bool) ($atalho['primary'] ?? false),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function urlItemControlePage(string $page): ?string
    {
        if ($page === 'pendencias') {
            return Pendencias::getUrl();
        }

        try {
            if (! array_key_exists($page, ItemControleResource::getPages())) {
                return null;
            }

            return ItemControleResource::getUrl($page);
        } catch (Throwable) {
            return null;
        }
    }

    private function usuarioAtual()
    {
        return Filament::auth()->user() ?: Auth::user();
    }
}
