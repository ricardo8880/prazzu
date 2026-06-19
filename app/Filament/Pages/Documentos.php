<?php

namespace App\Filament\Pages;


use App\Support\CachedSchema;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Filament\Pages\Contratos;
use App\Filament\Pages\GestaoDocumentalEnterprise;
use App\Filament\Pages\Pendencias;
use App\Filament\Pages\Validades;
use App\Models\ItemControle;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use UnitEnum;

class Documentos extends Page
{
    use WithFileUploads;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document';
    protected static string | UnitEnum | null $navigationGroup = 'Documentos';
    protected static ?string $navigationLabel = 'Documentos';
    protected static ?string $title = 'Documentos';
    protected static ?int $navigationSort = 1;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected string $view = 'filament.pages.documentos';

    public string $clusterDocumentos = 'visao-geral';
    public ?int $documentoResolucaoSelecionado = null;
    public bool $abrirProximoAutomaticamente = true;
    public ?int $ultimoDocumentoResolvido = null;
    public ?array $feedbackFluxoContinuo = null;

    public array $resolverStatus = [];
    public array $resolverDataVencimento = [];
    public array $resolverPortalAtivo = [];
    public array $resolverObservacao = [];
    public array $resolverArquivos = [];

    public function mount(): void
    {
        $cluster = request()->query('cluster');

        if (is_string($cluster) && $this->isClusterDocumentosValido($cluster)) {
            $this->clusterDocumentos = $cluster;
        }
    }


    public function abrirResolucaoDocumento(int $documentoId): void
    {
        if (! $this->prepararResolucaoDocumento($documentoId)) {
            return;
        }

        $this->documentoResolucaoSelecionado = $documentoId;
    }

    public function prepararResolucaoDocumento(int $documentoId): bool
    {
        $documento = $this->documentoVisivel($documentoId);

        if (! $documento) {
            Notification::make()
                ->title('Documento não encontrado')
                ->body('O item pode ter sido removido ou você não possui permissão para acessá-lo.')
                ->danger()
                ->send();

            return false;
        }

        $this->resolverStatus[$documentoId] = (string) ($this->value($documento, 'status') ?? '');
        $this->resolverDataVencimento[$documentoId] = filled($this->value($documento, 'data_vencimento'))
            ? \Carbon\Carbon::parse($this->value($documento, 'data_vencimento'))->format('Y-m-d')
            : null;
        $this->resolverPortalAtivo[$documentoId] = (bool) $this->value($documento, 'portal_ativo');
        $this->resolverObservacao[$documentoId] = '';
        unset($this->resolverArquivos[$documentoId]);

        return true;
    }

    public function fecharResolucaoDocumento(): void
    {
        $this->documentoResolucaoSelecionado = null;
    }

    public function resolverDocumentoRapido(int $documentoId): void
    {
        $documento = $this->documentoVisivel($documentoId);

        if (! $documento) {
            Notification::make()
                ->title('Documento não encontrado')
                ->body('O item pode ter sido removido ou você não possui permissão para acessá-lo.')
                ->danger()
                ->send();

            return;
        }

        $rules = [];

        if ($this->hasColumn('status')) {
            $rules["resolverStatus.{$documentoId}"] = ['nullable', 'string', Rule::in(array_keys($this->statusResolucaoOptions((string) ($this->value($documento, 'status') ?? ''))))];
        }

        if ($this->hasColumn('data_vencimento')) {
            $rules["resolverDataVencimento.{$documentoId}"] = ['nullable', 'date'];
        }

        if ($this->hasColumn('portal_ativo')) {
            $rules["resolverPortalAtivo.{$documentoId}"] = ['nullable', 'boolean'];
        }

        if ($this->hasColumn('descricao')) {
            $rules["resolverObservacao.{$documentoId}"] = ['nullable', 'string', 'max:1200'];
        }

        if ($this->hasColumn('arquivo')) {
            $rules["resolverArquivos.{$documentoId}"] = ['nullable', 'file', 'max:10240'];
        }

        if ($rules !== []) {
            $this->validate($rules, [], [
                "resolverStatus.{$documentoId}" => 'status',
                "resolverDataVencimento.{$documentoId}" => 'data de vencimento',
                "resolverPortalAtivo.{$documentoId}" => 'portal',
                "resolverObservacao.{$documentoId}" => 'observação',
                "resolverArquivos.{$documentoId}" => 'arquivo',
            ]);
        }

        $updates = [];

        if ($this->hasColumn('status')) {
            $status = trim((string) ($this->resolverStatus[$documentoId] ?? ''));

            if ($status !== '') {
                $updates['status'] = $status;
            }
        }

        if ($this->hasColumn('data_vencimento')) {
            $dataVencimento = $this->resolverDataVencimento[$documentoId] ?? null;
            $updates['data_vencimento'] = filled($dataVencimento) ? \Carbon\Carbon::parse($dataVencimento)->toDateString() : null;
        }

        if ($this->hasColumn('portal_ativo')) {
            $updates['portal_ativo'] = (bool) ($this->resolverPortalAtivo[$documentoId] ?? false);
        }

        if ($this->hasColumn('descricao')) {
            $observacao = trim((string) ($this->resolverObservacao[$documentoId] ?? ''));

            if ($observacao !== '') {
                $descricaoAtual = trim((string) ($this->value($documento, 'descricao') ?? ''));
                $registro = '[' . now()->format('d/m/Y H:i') . '] Resolução rápida: ' . $observacao;
                $updates['descricao'] = $descricaoAtual !== '' ? $descricaoAtual . PHP_EOL . PHP_EOL . $registro : $registro;
            }
        }

        if ($this->hasColumn('arquivo') && isset($this->resolverArquivos[$documentoId])) {
            $arquivo = $this->resolverArquivos[$documentoId];

            if ($arquivo) {
                $updates['arquivo'] = $arquivo->store('item-controles', 'public');
            }
        }

        if ($updates === []) {
            Notification::make()
                ->title('Nenhuma alteração para salvar')
                ->body('Revise os campos do pop-up e tente novamente.')
                ->warning()
                ->send();

            return;
        }

        $documento->forceFill($updates)->save();
        unset($this->resolverArquivos[$documentoId], $this->resolverObservacao[$documentoId]);

        $this->ultimoDocumentoResolvido = $documentoId;
        $proximoDocumento = $this->proximoDocumentoAcionavel($documentoId);

        if ($this->abrirProximoAutomaticamente && $proximoDocumento) {
            $this->feedbackFluxoContinuo = [
                'tipo' => 'proximo_aberto',
                'titulo' => (string) ($proximoDocumento['titulo'] ?? 'Próximo documento'),
                'mensagem' => 'Documento salvo. O próximo item prioritário já foi aberto para manter o fluxo de resolução.',
            ];

            Notification::make()
                ->title('Documento atualizado')
                ->body('O próximo item prioritário foi aberto automaticamente.')
                ->success()
                ->send();

            $this->abrirResolucaoDocumento((int) $proximoDocumento['id']);

            return;
        }

        $this->feedbackFluxoContinuo = [
            'tipo' => 'fila_concluida',
            'titulo' => 'Fila prioritária revisada',
            'mensagem' => 'Documento salvo e nenhum outro item crítico ou de alta prioridade foi encontrado na fila atual.',
        ];

        Notification::make()
            ->title('Documento atualizado')
            ->body('A resolução foi salva sem sair da página.')
            ->success()
            ->send();

        $this->fecharResolucaoDocumento();
    }

    public function getSubNavigation(): array
    {
        return collect($this->clustersDocumentos())
            ->map(fn (array $item): NavigationItem => NavigationItem::make($item['label'])
                ->icon($item['icon'])
                ->url($this->clusterDocumentosUrl($item['key']))
                ->isActiveWhen(fn (): bool => $this->clusterDocumentos === $item['key'])
                ->sort($item['sort']))
            ->all();
    }

    protected function getViewData(): array
    {
        $documentos = $this->documentos();

        return [
            'resumo' => $this->resumo(),
            'documentos' => $documentos,
            'documentosPorCluster' => $this->documentosPorCluster($documentos),
            'hub' => $this->hub(),
            'atalhos' => $this->atalhos(),
            'acoesInteligentes' => $this->acoesInteligentes(),
            'indicadoresPrioridade' => $this->indicadoresPrioridade($documentos),
            'integracaoEnterprise' => $this->integracaoEnterprise(),
            'clusterDocumentos' => $this->clusterDocumentos,
            'clusterAtivo' => $this->clusterDocumentosAtivo(),
            'clustersDocumentos' => $this->clustersDocumentos(),
            'statusResolucaoOptions' => $this->statusResolucaoOptions(),
            'documentoResolucaoEmEdicao' => $this->documentoResolucaoEmEdicao(),
            'prioridadeInteligente' => $this->prioridadeInteligente($documentos),
            'fluxoContinuo' => $this->fluxoContinuo($documentos),
        ];
    }


    /** @return array<int, array<string, mixed>> */
    private function clustersDocumentos(): array
    {
        $resumo = $this->resumo();
        $hub = $this->hub();

        return [
            [
                'key' => 'visao-geral',
                'label' => 'Visão Geral',
                'icon' => 'heroicon-o-squares-2x2',
                'sort' => 1,
                'tone' => $hub['tom'] ?? 'primary',
                'count' => (int) ($resumo['total'] ?? 0),
                'hint' => 'Saúde, atalhos e ação recomendada',
                'description' => 'Comece por aqui para entender a situação documental sem rolar a página inteira.',
                'next_action' => $hub['proximaAcao'] ?? 'Revisar fila documental',
            ],
            [
                'key' => 'pendencias',
                'label' => 'Pendências',
                'icon' => 'heroicon-o-exclamation-triangle',
                'sort' => 2,
                'tone' => ((int) ($hub['criticos'] ?? 0)) > 0 ? 'danger' : 'success',
                'count' => (int) ($hub['pendentes'] ?? 0),
                'hint' => 'Sem arquivo, vencidos e itens críticos',
                'description' => 'Use este cluster para enxergar o que precisa de ação operacional primeiro.',
                'next_action' => 'Tratar críticos antes de avançar para itens estáveis',
            ],
            [
                'key' => 'vencimentos',
                'label' => 'Vencimentos',
                'icon' => 'heroicon-o-calendar-days',
                'sort' => 3,
                'tone' => ((int) ($resumo['vencidos'] ?? 0)) > 0 ? 'danger' : (((int) ($resumo['vencem30'] ?? 0)) > 0 ? 'warning' : 'success'),
                'count' => (int) ($resumo['vencidos'] ?? 0) + (int) ($resumo['vencem30'] ?? 0),
                'hint' => 'Vencidos e próximos 30 dias',
                'description' => 'Acompanhe risco por prazo e abra a regularização pelo próprio item.',
                'next_action' => 'Regularizar vencidos e monitorar próximos prazos',
            ],
            [
                'key' => 'enterprise',
                'label' => 'Detalhes',
                'icon' => 'heroicon-o-rocket-launch',
                'sort' => 4,
                'tone' => 'primary',
                'count' => (int) ($resumo['total'] ?? 0),
                'hint' => 'Visão completa para consulta',
                'description' => 'Mantenha a consulta completa disponível sem atrapalhar a regularização rápida.',
                'next_action' => 'Consultar detalhes quando a regularização rápida não for suficiente',
            ],
            [
                'key' => 'fila',
                'label' => 'Fila',
                'icon' => 'heroicon-o-list-bullet',
                'sort' => 5,
                'tone' => 'neutral',
                'count' => (int) ($resumo['total'] ?? 0),
                'hint' => 'Lista objetiva dos documentos',
                'description' => 'Veja a fila de documentos sem misturar com os painéis de resumo.',
                'next_action' => 'Abrir detalhes ou resolver pelo item selecionado',
            ],
        ];
    }

    private function clusterDocumentosAtivo(): array
    {
        return collect($this->clustersDocumentos())
            ->firstWhere('key', $this->clusterDocumentos) ?? $this->clustersDocumentos()[0];
    }

    private function isClusterDocumentosValido(string $cluster): bool
    {
        return collect($this->clustersDocumentos())->contains(fn (array $item): bool => $item['key'] === $cluster);
    }

    private function clusterDocumentosUrl(string $cluster): string
    {
        return static::getUrl(['cluster' => $cluster]);
    }

    private function resumo(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return ['total' => 0, 'comArquivo' => 0, 'vencidos' => 0, 'portal' => 0, 'semArquivo' => 0, 'vencem30' => 0];
        }

        $query = $this->baseQuery();

        return [
            'total' => (clone $query)->count(),
            'comArquivo' => $this->hasColumn('arquivo') ? (clone $query)->whereNotNull('arquivo')->where('arquivo', '<>', '')->count() : 0,
            'semArquivo' => $this->hasColumn('arquivo') ? (clone $query)->where(function (Builder $subQuery): void {
                $subQuery->whereNull('arquivo')->orWhere('arquivo', '');
            })->count() : 0,
            'vencidos' => $this->hasColumn('data_vencimento') ? (clone $query)
                ->whereNotNull('data_vencimento')
                ->whereDate('data_vencimento', '<', now()->toDateString())
                ->when($this->hasColumn('status'), fn (Builder $builder): Builder => $builder->whereNotIn('status', $this->statusFinalizados()))
                ->count() : 0,
            'vencem30' => $this->hasColumn('data_vencimento') ? (clone $query)
                ->whereBetween('data_vencimento', [now()->toDateString(), now()->addDays(30)->toDateString()])
                ->count() : 0,
            'portal' => $this->hasColumn('portal_ativo') ? (clone $query)->where('portal_ativo', true)->count() : 0,
        ];
    }


    private function hub(): array
    {
        $resumo = $this->resumo();
        $total = max((int) ($resumo['total'] ?? 0), 0);
        $vencidos = max((int) ($resumo['vencidos'] ?? 0), 0);
        $semArquivo = max((int) ($resumo['semArquivo'] ?? 0), 0);
        $vencem30 = max((int) ($resumo['vencem30'] ?? 0), 0);
        $comArquivo = max((int) ($resumo['comArquivo'] ?? 0), 0);
        $portal = max((int) ($resumo['portal'] ?? 0), 0);
        $regularizados = $this->regularizados();
        $pendentes = $this->pendentes();
        $criticos = $vencidos + $semArquivo;

        $score = $total > 0
            ? max(0, min(100, (int) round(100 - (($criticos / $total) * 100))))
            : 100;

        $status = match (true) {
            $total === 0 => 'Base vazia',
            $vencidos > 0 || $semArquivo > 0 => 'Atenção necessária',
            $vencem30 > 0 => 'Monitorar prazos',
            default => 'Organizado',
        };

        $tom = match (true) {
            $total === 0 => 'muted',
            $vencidos > 0 || $semArquivo > 0 => 'danger',
            $vencem30 > 0 => 'warning',
            default => 'success',
        };

        return [
            'score' => $score,
            'status' => $status,
            'tom' => $tom,
            'pendentes' => $pendentes,
            'regularizados' => $regularizados,
            'criticos' => $criticos,
            'comArquivoPercentual' => $total > 0 ? (int) round(($comArquivo / $total) * 100) : 0,
            'portalPercentual' => $total > 0 ? (int) round(($portal / $total) * 100) : 0,
            'mensagem' => $this->mensagemHub($total, $vencidos, $semArquivo, $vencem30),
            'proximaAcao' => $this->proximaAcao($total, $vencidos, $semArquivo, $vencem30),
        ];
    }

    private function regularizados(): int
    {
        if (! CachedSchema::hasTable('item_controles') || ! $this->hasColumn('status')) {
            return 0;
        }

        return (clone $this->baseQuery())->whereIn('status', $this->statusFinalizados())->count();
    }

    private function pendentes(): int
    {
        if (! CachedSchema::hasTable('item_controles') || (! $this->hasColumn('arquivo') && ! $this->hasColumn('data_vencimento'))) {
            return 0;
        }

        $query = clone $this->baseQuery();

        $query->where(function (Builder $builder): void {
            $temCondicao = false;

            if ($this->hasColumn('arquivo')) {
                $builder->where(function (Builder $subQuery): void {
                    $subQuery->whereNull('arquivo')->orWhere('arquivo', '');
                });
                $temCondicao = true;
            }

            if ($this->hasColumn('data_vencimento')) {
                $method = $temCondicao ? 'orWhere' : 'where';
                $builder->{$method}(function (Builder $subQuery): void {
                    $subQuery->whereNotNull('data_vencimento')
                        ->whereDate('data_vencimento', '<', now()->toDateString());
                });
            }
        });

        if ($this->hasColumn('status')) {
            $query->whereNotIn('status', $this->statusFinalizados());
        }

        return $query->count();
    }


    /**
     * @param array<int, array<string, mixed>> $documentos
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function documentosPorCluster(array $documentos): array
    {
        $colecao = collect($documentos);

        $pendencias = $colecao
            ->filter(fn (array $documento): bool => in_array((string) ($documento['prioridade_operacional']['nivel'] ?? 'estavel'), ['critica', 'alta'], true))
            ->sortByDesc(fn (array $documento): int => (int) ($documento['prioridade_score'] ?? 0))
            ->take(8)
            ->values()
            ->all();

        $vencimentos = $colecao
            ->filter(fn (array $documento): bool => filled($documento['data_vencimento'] ?? null))
            ->sortBy(function (array $documento): int {
                try {
                    return now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($documento['data_vencimento'])->startOfDay(), false);
                } catch (\Throwable) {
                    return 9999;
                }
            })
            ->take(8)
            ->values()
            ->all();

        $enterprise = $colecao
            ->filter(fn (array $documento): bool => in_array((string) ($documento['prioridade_operacional']['nivel'] ?? 'estavel'), ['critica', 'alta', 'monitorar'], true))
            ->sortByDesc(fn (array $documento): int => (int) ($documento['prioridade_score'] ?? 0))
            ->take(6)
            ->values()
            ->all();

        return [
            'visao-geral' => $colecao->take(6)->values()->all(),
            'pendencias' => $pendencias,
            'vencimentos' => $vencimentos,
            'enterprise' => $enterprise,
            'fila' => $colecao->take(24)->values()->all(),
        ];
    }

    /** @return array<int, array<string, string>> */
    private function atalhos(): array
    {
        return [
            [
                'label' => 'Gestão documental',
                'descricao' => 'Abrir painel completo de documentos, prioridades e filtros.',
                'url' => $this->enterpriseUrl(),
                'tom' => 'primary',
            ],
            [
                'label' => 'Novo documento',
                'descricao' => 'Cadastrar documento, contrato, anexo ou evidência.',
                'url' => ItemControleResource::getUrl('create'),
                'tom' => 'success',
            ],
            [
                'label' => 'Validades',
                'descricao' => 'Ver vencidos, prazos próximos e itens sem data.',
                'url' => $this->enterpriseUrl(['situacao' => 'vence_30']),
                'tom' => 'warning',
            ],
            [
                'label' => 'Contratos',
                'descricao' => 'Acompanhar vigência, valores e partes contratuais.',
                'url' => Contratos::getUrl(),
                'tom' => 'neutral',
            ],
            [
                'label' => 'Pendências',
                'descricao' => 'Tratar itens que exigem ação operacional.',
                'url' => Pendencias::getUrl(),
                'tom' => 'danger',
            ],
        ];
    }



    /**
     * @param array<int, array<string, mixed>> $documentos
     * @return array<string, mixed>
     */
    private function prioridadeInteligente(array $documentos): array
    {
        $colecao = collect($documentos);
        $criticos = $colecao->filter(fn (array $documento): bool => (string) ($documento['prioridade_operacional']['nivel'] ?? 'estavel') === 'critica')->values();
        $altos = $colecao->filter(fn (array $documento): bool => (string) ($documento['prioridade_operacional']['nivel'] ?? 'estavel') === 'alta')->values();
        $monitorar = $colecao->filter(fn (array $documento): bool => (string) ($documento['prioridade_operacional']['nivel'] ?? 'estavel') === 'monitorar')->values();

        $item = $colecao
            ->filter(fn (array $documento): bool => ! in_array((string) ($documento['prioridade_operacional']['nivel'] ?? 'estavel'), ['estavel'], true))
            ->sortByDesc(fn (array $documento): int => (int) ($documento['prioridade_score'] ?? 0))
            ->first();

        if (! $item) {
            return [
                'temAcao' => false,
                'tom' => 'success',
                'titulo' => 'Base documental sob controle',
                'mensagem' => 'Nenhum documento crítico foi identificado na fila atual. Continue monitorando os prazos e mantendo os anexos atualizados.',
                'acao' => 'Abrir fila de documentos',
                'documento' => null,
                'criticos' => $criticos->count(),
                'altos' => $altos->count(),
                'monitorar' => $monitorar->count(),
                'totalAcionavel' => 0,
                'clusterUrl' => $this->clusterDocumentosUrl('fila'),
            ];
        }

        $prioridade = $item['prioridade_operacional'] ?? [];
        $nivel = (string) ($prioridade['nivel'] ?? 'monitorar');
        $tom = (string) ($prioridade['tom'] ?? 'primary');
        $empresa = $item['nome_fantasia'] ?: ($item['razao_social'] ?: 'Empresa não informada');
        $totalAcionavel = $criticos->count() + $altos->count();

        return [
            'temAcao' => true,
            'tom' => $tom,
            'titulo' => match ($nivel) {
                'critica' => 'Ação imediata necessária',
                'alta' => 'Prioridade alta aguardando resolução',
                default => 'Item importante para monitorar',
            },
            'mensagem' => trim(($prioridade['motivo'] ?? 'Documento exige atenção operacional.') . ' Empresa: ' . $empresa . '.'),
            'acao' => $nivel === 'monitorar' ? 'Revisar agora' : 'Resolver agora',
            'documento' => $item,
            'criticos' => $criticos->count(),
            'altos' => $altos->count(),
            'monitorar' => $monitorar->count(),
            'totalAcionavel' => $totalAcionavel,
            'clusterUrl' => $this->clusterDocumentosUrl($nivel === 'monitorar' ? 'vencimentos' : 'pendencias'),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $documentos
     * @return array<string, mixed>
     */
    private function fluxoContinuo(array $documentos): array
    {
        $acionaveis = collect($documentos)
            ->filter(fn (array $documento): bool => in_array((string) ($documento['prioridade_operacional']['nivel'] ?? 'estavel'), ['critica', 'alta'], true))
            ->sortByDesc(fn (array $documento): int => (int) ($documento['prioridade_score'] ?? 0))
            ->values();

        $proximo = $acionaveis->first();
        $restantes = $acionaveis->count();

        return [
            'ativo' => $restantes > 0,
            'automatico' => $this->abrirProximoAutomaticamente,
            'total' => $restantes,
            'proximo' => $proximo,
            'feedback' => $this->feedbackFluxoContinuo,
            'ultimoResolvido' => $this->ultimoDocumentoResolvido,
            'mensagem' => $restantes > 0
                ? 'Modo produtividade ativo: resolva o item atual e o sistema já prepara o próximo documento prioritário.'
                : 'Nenhum item crítico ou de alta prioridade está pendente na fila atual.',
        ];
    }

    /** @return array<string, mixed>|null */
    private function proximoDocumentoAcionavel(?int $ignorarDocumentoId = null): ?array
    {
        return collect($this->documentos())
            ->filter(function (array $documento) use ($ignorarDocumentoId): bool {
                if ($ignorarDocumentoId !== null && (int) ($documento['id'] ?? 0) === $ignorarDocumentoId) {
                    return false;
                }

                return in_array((string) ($documento['prioridade_operacional']['nivel'] ?? 'estavel'), ['critica', 'alta'], true);
            })
            ->sortByDesc(fn (array $documento): int => (int) ($documento['prioridade_score'] ?? 0))
            ->first();
    }

    /** @return array<int, array<string, mixed>> */
    private function acoesInteligentes(): array
    {
        $resumo = $this->resumo();
        $hub = $this->hub();
        $total = (int) ($resumo['total'] ?? 0);
        $vencidos = (int) ($resumo['vencidos'] ?? 0);
        $semArquivo = (int) ($resumo['semArquivo'] ?? 0);
        $vencem30 = (int) ($resumo['vencem30'] ?? 0);

        if ($total === 0) {
            return [
                [
                    'titulo' => 'Cadastrar o primeiro documento',
                    'descricao' => 'Inicie a base documental cadastrando o primeiro item com empresa, vencimento e arquivo principal.',
                    'url' => ItemControleResource::getUrl('create'),
                    'botao' => 'Cadastrar documento',
                    'tom' => 'success',
                    'prioridade' => 'Primeiro passo',
                ],
                [
                    'titulo' => 'Consultar documentos em detalhe',
                    'descricao' => 'Use a visão completa apenas quando precisar pesquisar ou auditar a base documental.',
                    'url' => $this->clusterDocumentosUrl('fila'),
                    'botao' => 'Ver lista documental',
                    'tom' => 'primary',
                    'prioridade' => 'Estrutura',
                ],
            ];
        }

        $acoes = [];

        if ($vencidos > 0) {
            $acoes[] = [
                'titulo' => 'Regularizar documentos vencidos',
                'descricao' => 'Resolva primeiro os documentos fora do prazo para reduzir risco operacional e melhorar a saúde documental.',
                'url' => $this->clusterDocumentosUrl('pendencias'),
                'botao' => 'Abrir vencidos',
                'tom' => 'danger',
                'prioridade' => $vencidos . ' vencido(s)',
            ];
        }

        if ($semArquivo > 0) {
            $acoes[] = [
                'titulo' => 'Anexar arquivos pendentes',
                'descricao' => 'Itens sem arquivo principal prejudicam consulta, auditoria e atendimento ao cliente.',
                'url' => $this->clusterDocumentosUrl('pendencias'),
                'botao' => 'Abrir sem anexo',
                'tom' => 'warning',
                'prioridade' => $semArquivo . ' sem arquivo',
            ];
        }

        if ($vencem30 > 0) {
            $acoes[] = [
                'titulo' => 'Antecipar próximos vencimentos',
                'descricao' => 'Revise documentos que vencem nos próximos 30 dias antes que virem urgência.',
                'url' => $this->clusterDocumentosUrl('vencimentos'),
                'botao' => 'Ver prazos',
                'tom' => 'warning',
                'prioridade' => $vencem30 . ' em 30 dias',
            ];
        }

        $acoes[] = [
            'titulo' => 'Consultar lista documental',
            'descricao' => 'Veja todos os documentos e abra a regularização pelo próprio item.',
            'url' => $this->clusterDocumentosUrl('fila'),
            'botao' => 'Ver lista',
            'tom' => $hub['tom'] === 'success' ? 'success' : 'primary',
            'prioridade' => (string) ($hub['status'] ?? 'Gestão'),
        ];

        return array_slice($acoes, 0, 4);
    }


    /** @return array<string, mixed> */
    private function integracaoEnterprise(): array
    {
        $resumo = $this->resumo();
        $vencidos = (int) ($resumo['vencidos'] ?? 0);
        $semArquivo = (int) ($resumo['semArquivo'] ?? 0);
        $vencem30 = (int) ($resumo['vencem30'] ?? 0);
        $total = (int) ($resumo['total'] ?? 0);

        $fluxos = [
            [
                'titulo' => 'Vencidos',
                'descricao' => 'Abre a Gestão Documental Enterprise já filtrada para documentos vencidos.',
                'total' => $vencidos,
                'url' => $this->enterpriseUrl(['situacao' => 'vencido']),
                'tom' => 'danger',
            ],
            [
                'titulo' => 'Sem anexo',
                'descricao' => 'Leva direto para itens sem arquivo/evidência dentro do painel completo.',
                'total' => $semArquivo,
                'url' => $this->enterpriseUrl(['situacao' => 'sem_arquivo']),
                'tom' => 'warning',
            ],
            [
                'titulo' => 'Vencem em 30 dias',
                'descricao' => 'Mostra a fila preventiva antes que os documentos virem urgência.',
                'total' => $vencem30,
                'url' => $this->enterpriseUrl(['situacao' => 'vence_30']),
                'tom' => 'primary',
            ],
            [
                'titulo' => 'Visão completa',
                'descricao' => 'Mantém o hub como entrada rápida e usa a Enterprise como tela operacional principal.',
                'total' => $total,
                'url' => $this->enterpriseUrl(),
                'tom' => 'success',
            ],
        ];

        return [
            'url' => $this->enterpriseUrl(),
            'descricao' => 'O hub orienta a decisão inicial e a Gestão Documental Enterprise assume a operação detalhada com filtros, fila, aprovação e cadastros.',
            'fluxos' => $fluxos,
        ];
    }

    /** @return array<string, string> */
    private function enterpriseQueryForItem(ItemControle $item, array $prioridadeOperacional): array
    {
        $nivel = (string) ($prioridadeOperacional['nivel'] ?? 'estavel');
        $query = [];

        if ($nivel === 'critica') {
            $motivo = mb_strtolower((string) ($prioridadeOperacional['motivo'] ?? ''));
            $query['situacao'] = str_contains($motivo, 'sem arquivo') ? 'sem_arquivo' : 'vencido';
        } elseif ($nivel === 'alta') {
            $query['situacao'] = 'vence_7';
        } elseif ($nivel === 'monitorar') {
            $query['situacao'] = 'vence_30';
        }

        $empresaId = $this->value($item, 'empresa_id');

        if (filled($empresaId)) {
            $query['empresa_id'] = (string) $empresaId;
        }

        $titulo = $this->value($item, 'titulo');

        if (filled($titulo)) {
            $query['busca'] = (string) $titulo;
        }

        return $query;
    }

    /** @param array<string, string|null> $query */
    private function enterpriseUrl(array $query = []): string
    {
        $url = GestaoDocumentalEnterprise::getUrl();
        $query = array_filter($query, fn ($value): bool => filled($value));

        if ($query === []) {
            return $url;
        }

        return $url . '?' . http_build_query($query);
    }

    private function mensagemHub(int $total, int $vencidos, int $semArquivo, int $vencem30): string
    {
        if ($total === 0) {
            return 'Nenhum documento cadastrado ainda. Comece criando o primeiro item documental para alimentar o painel.';
        }

        if ($vencidos > 0) {
            return 'Existem documentos vencidos. Priorize a regularização antes de cadastrar novos itens.';
        }

        if ($semArquivo > 0) {
            return 'Existem documentos sem arquivo principal. Complete os anexos para melhorar a rastreabilidade.';
        }

        if ($vencem30 > 0) {
            return 'Há documentos vencendo nos próximos 30 dias. Antecipe renovações e revisões.';
        }

        return 'A base documental está organizada. Continue acompanhando prazos e publicações no portal.';
    }

    private function proximaAcao(int $total, int $vencidos, int $semArquivo, int $vencem30): string
    {
        if ($total === 0) {
            return 'Cadastrar novo documento';
        }

        if ($vencidos > 0) {
            return 'Regularizar vencidos';
        }

        if ($semArquivo > 0) {
            return 'Anexar arquivos pendentes';
        }

        if ($vencem30 > 0) {
            return 'Revisar próximos vencimentos';
        }

        return 'Abrir gestão documental';
    }

    private function documentos(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        $query = $this->baseQuery()
            ->select($this->selectColumns())
            ->with($this->withRelations());

        if ($this->hasColumn('data_vencimento')) {
            $query->orderByRaw('CASE WHEN data_vencimento IS NULL THEN 1 ELSE 0 END')
                ->orderBy('data_vencimento');
        }

        $query->orderByDesc($this->hasColumn('updated_at') ? 'updated_at' : 'id');

        return $query
            ->limit(80)
            ->get()
            ->map(fn (ItemControle $item): array => $this->documentoParaArray($item))
            ->sortByDesc('prioridade_score')
            ->take(24)
            ->values()
            ->all();
    }


    /** @return array<string, mixed> */
    private function documentoParaArray(ItemControle $item): array
    {
        $empresa = $item->relationLoaded('empresa') ? $item->empresa : null;
        $arquivo = $this->value($item, 'arquivo');
        $prioridadeOperacional = $this->prioridadeOperacional($item);

        return [
            'id' => $item->id,
            'titulo' => $this->value($item, 'titulo') ?: 'Documento sem título',
            'descricao' => $this->value($item, 'descricao'),
            'tipo' => $this->value($item, 'tipo'),
            'status' => $this->value($item, 'status'),
            'prioridade' => $this->value($item, 'prioridade'),
            'data_vencimento' => $this->value($item, 'data_vencimento'),
            'arquivo' => $arquivo,
            'portal_ativo' => (bool) $this->value($item, 'portal_ativo'),
            'portal_cliente_nome' => $this->value($item, 'portal_cliente_nome'),
            'portal_cliente_email' => $this->value($item, 'portal_cliente_email'),
            'portal_expira_em' => $this->value($item, 'portal_expira_em'),
            'created_at' => $this->value($item, 'created_at'),
            'updated_at' => $this->value($item, 'updated_at'),
            'nome_fantasia' => $empresa?->nome_fantasia,
            'razao_social' => $empresa?->razao_social,
            'empresa_id' => $this->value($item, 'empresa_id'),
            'enterprise_url' => $this->enterpriseUrl($this->enterpriseQueryForItem($item, $prioridadeOperacional)),
            'edit_url' => ItemControleResource::getUrl('edit', ['record' => $item->id]),
            'arquivo_url' => filled($arquivo) ? asset('storage/' . ltrim((string) $arquivo, '/')) : null,
            'prioridade_operacional' => $prioridadeOperacional,
            'prioridade_score' => $prioridadeOperacional['score'],
            'status_resolucao_options' => $this->statusResolucaoOptions((string) ($this->value($item, 'status') ?? '')),
        ];
    }

    /** @return array<string, mixed>|null */
    private function documentoResolucaoEmEdicao(): ?array
    {
        if (! $this->documentoResolucaoSelecionado) {
            return null;
        }

        $documento = $this->documentoVisivel($this->documentoResolucaoSelecionado);

        if (! $documento) {
            $this->documentoResolucaoSelecionado = null;
            return null;
        }

        return $this->documentoParaArray($documento);
    }


    /**
     * @param array<int, array<string, mixed>> $documentos
     * @return array<string, array<string, mixed>>
     */
    private function indicadoresPrioridade(array $documentos): array
    {
        $contadores = [
            'critica' => ['label' => 'Crítica', 'descricao' => 'Vencido, sem arquivo ou exige correção imediata.', 'total' => 0, 'tom' => 'danger'],
            'alta' => ['label' => 'Alta', 'descricao' => 'Prazo curto ou risco operacional em aberto.', 'total' => 0, 'tom' => 'warning'],
            'monitorar' => ['label' => 'Monitorar', 'descricao' => 'Acompanhar para evitar nova urgência.', 'total' => 0, 'tom' => 'primary'],
            'estavel' => ['label' => 'Estável', 'descricao' => 'Sem sinal crítico na leitura atual.', 'total' => 0, 'tom' => 'success'],
        ];

        foreach ($documentos as $documento) {
            $nivel = (string) ($documento['prioridade_operacional']['nivel'] ?? 'estavel');

            if (! array_key_exists($nivel, $contadores)) {
                $nivel = 'estavel';
            }

            $contadores[$nivel]['total']++;
        }

        return $contadores;
    }

    /** @return array{nivel: string, label: string, motivo: string, tom: string, score: int, prazo: string} */
    private function prioridadeOperacional(ItemControle $item): array
    {
        $status = mb_strtolower(trim((string) ($this->value($item, 'status') ?? '')));
        $prioridade = mb_strtolower(trim((string) ($this->value($item, 'prioridade') ?? '')));
        $arquivo = trim((string) ($this->value($item, 'arquivo') ?? ''));
        $dataVencimento = $this->value($item, 'data_vencimento');
        $finalizado = in_array($status, $this->statusFinalizados(), true);
        $semArquivo = $this->hasColumn('arquivo') && $arquivo === '';
        $diasParaVencer = null;

        if ($this->hasColumn('data_vencimento') && filled($dataVencimento)) {
            try {
                $diasParaVencer = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($dataVencimento)->startOfDay(), false);
            } catch (\Throwable) {
                $diasParaVencer = null;
            }
        }

        if (! $finalizado && $diasParaVencer !== null && $diasParaVencer < 0) {
            return [
                'nivel' => 'critica',
                'label' => 'Crítica',
                'motivo' => 'Documento vencido há ' . abs($diasParaVencer) . ' dia(s).',
                'tom' => 'danger',
                'score' => 100 + abs($diasParaVencer),
                'prazo' => 'Vencido',
            ];
        }

        if (! $finalizado && $semArquivo) {
            return [
                'nivel' => 'critica',
                'label' => 'Crítica',
                'motivo' => 'Sem arquivo principal anexado.',
                'tom' => 'danger',
                'score' => 92,
                'prazo' => $diasParaVencer !== null ? $this->textoPrazo($diasParaVencer) : 'Sem arquivo',
            ];
        }

        if (! $finalizado && $diasParaVencer !== null && $diasParaVencer <= 7) {
            return [
                'nivel' => 'alta',
                'label' => 'Alta',
                'motivo' => 'Vence em até 7 dias.',
                'tom' => 'warning',
                'score' => 82 - max($diasParaVencer, 0),
                'prazo' => $this->textoPrazo($diasParaVencer),
            ];
        }

        if (! $finalizado && in_array($prioridade, ['alta', 'urgente', 'critica', 'crítica'], true)) {
            return [
                'nivel' => 'alta',
                'label' => 'Alta',
                'motivo' => 'Marcado com prioridade alta no cadastro.',
                'tom' => 'warning',
                'score' => 74,
                'prazo' => $diasParaVencer !== null ? $this->textoPrazo($diasParaVencer) : 'Prioridade alta',
            ];
        }

        if (! $finalizado && $diasParaVencer !== null && $diasParaVencer <= 30) {
            return [
                'nivel' => 'monitorar',
                'label' => 'Monitorar',
                'motivo' => 'Vence nos próximos 30 dias.',
                'tom' => 'primary',
                'score' => 55 - min($diasParaVencer, 30),
                'prazo' => $this->textoPrazo($diasParaVencer),
            ];
        }

        return [
            'nivel' => 'estavel',
            'label' => 'Estável',
            'motivo' => $finalizado ? 'Status finalizado ou aprovado.' : 'Sem sinal crítico no momento.',
            'tom' => 'success',
            'score' => $finalizado ? 12 : 20,
            'prazo' => $diasParaVencer !== null ? $this->textoPrazo($diasParaVencer) : 'Sem prazo crítico',
        ];
    }

    private function textoPrazo(int $dias): string
    {
        if ($dias < 0) {
            return 'Vencido há ' . abs($dias) . ' dia(s)';
        }

        if ($dias === 0) {
            return 'Vence hoje';
        }

        return 'Vence em ' . $dias . ' dia(s)';
    }


    private function documentoVisivel(int $documentoId): ?ItemControle
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return null;
        }

        return $this->baseQuery()->whereKey($documentoId)->first();
    }

    /** @return array<string, string> */
    private function statusResolucaoOptions(?string $statusAtual = null): array
    {
        $options = [
            'pendente' => 'Pendente',
            'em_analise' => 'Em análise',
            'aprovado' => 'Aprovado',
            'regularizado' => 'Regularizado',
            'concluido' => 'Concluído',
            'finalizado' => 'Finalizado',
            'cancelado' => 'Cancelado',
        ];

        $statusAtual = trim((string) $statusAtual);

        if ($statusAtual !== '' && ! array_key_exists($statusAtual, $options)) {
            $options[$statusAtual] = ucfirst(str_replace('_', ' ', $statusAtual));
        }

        return $options;
    }

    private function baseQuery(): Builder
    {
        return ItemControle::query()->visibleForUser(auth()->user());
    }

    /** @return array<int, string> */
    private function selectColumns(): array
    {
        $columns = ['id'];

        foreach ([
            'titulo', 'descricao', 'tipo', 'status', 'prioridade', 'data_vencimento', 'arquivo',
            'portal_ativo', 'portal_cliente_nome', 'portal_cliente_email', 'portal_expira_em',
            'empresa_id', 'created_at', 'updated_at',
        ] as $column) {
            if ($this->hasColumn($column)) {
                $columns[] = $column;
            }
        }

        return array_values(array_unique($columns));
    }

    /** @return array<int, string> */
    private function withRelations(): array
    {
        return CachedSchema::hasTable('empresas') && $this->hasColumn('empresa_id')
            ? ['empresa:id,razao_social,nome_fantasia']
            : [];
    }

    private function value(ItemControle $item, string $column): mixed
    {
        return $this->hasColumn($column) ? $item->getAttribute($column) : null;
    }

    private function hasColumn(string $column): bool
    {
        return CachedSchema::hasColumn('item_controles', $column);
    }

    /** @return array<int, string> */
    private function statusFinalizados(): array
    {
        return ['concluido', 'concluído', 'finalizado', 'cancelado', 'aprovado'];
    }
}
