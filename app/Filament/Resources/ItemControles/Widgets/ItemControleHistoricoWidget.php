<?php

namespace App\Filament\Resources\ItemControles\Widgets;

use App\Models\Empresa;
use App\Models\ItemControle;
use App\Models\Responsavel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class ItemControleHistoricoWidget extends TableWidget
{
    public ?ItemControle $record = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Histórico do Item';

    /**
     * Cache local em memória da própria requisição
     * para evitar N+1 ao traduzir empresa_id e responsavel_id.
     *
     * @var array<int, string>
     */
    protected array $empresaNomeCache = [];

    /**
     * @var array<int, string>
     */
    protected array $responsavelNomeCache = [];

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25])
            ->striped()
            ->emptyStateHeading('Nenhum histórico encontrado')
            ->emptyStateDescription('As alterações deste item aparecerão aqui.')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('causer.name')
                    ->label('Usuário')
                    ->placeholder('Sistema')
                    ->sortable(),

                TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'created' => 'Criado',
                        'updated' => 'Atualizado',
                        'deleted' => 'Excluído',
                        'status_manual' => 'Conclusão manual',
                        'status_manual_em_lote' => 'Conclusão em lote',
                        'status_automatico' => 'Atualização automática',
                        'comentario' => 'Comentário',
                        'anexo_adicionado' => 'Anexo',
                        'lembrete_recorrente' => 'Lembrete recorrente',
                        default => (string) $state,
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        'status_manual' => 'warning',
                        'status_manual_em_lote' => 'warning',
                        'status_automatico' => 'gray',
                        'comentario' => 'info',
                        'anexo_adicionado' => 'warning',
                        'lembrete_recorrente' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->wrap()
                    ->formatStateUsing(fn (?string $state): string => $state ?: 'Sem descrição'),

                TextColumn::make('alteracoes')
                    ->label('Alterações')
                    ->html()
                    ->wrap()
                    ->getStateUsing(function (Activity $record): string {
                        $old = (array) data_get($record->properties, 'old', []);
                        $attributes = (array) data_get($record->properties, 'attributes', []);

                        $camposOcultos = [
                            'updated_at',
                            'created_at',
                            'deleted_at',
                            'ultimo_lembrete_enviado_em',
                            'qtd_lembretes_enviados',
                            'ultima_falha_notificacao_em',
                            'ultima_falha_notificacao_msg',
                            'notificado_3_dias',
                            'notificado_no_dia',
                            'notificado_vencido',
                        ];

                        if ($record->event === 'comentario') {
                            $comentario = trim((string) data_get($record->properties, 'attributes.comentario', '-'));

                            return '<div><strong>Comentário:</strong> ' . e($comentario !== '' ? $comentario : '-') . '</div>';
                        }

                        if ($record->event === 'anexo_adicionado') {
                            $arquivo = (string) data_get($record->properties, 'attributes.arquivo', '-');
                            $observacao = trim((string) data_get($record->properties, 'attributes.observacao', ''));

                            $html = '<div><strong>Arquivo:</strong> ' . e($arquivo) . '</div>';

                            if ($observacao !== '') {
                                $html .= '<div><strong>Observação:</strong> ' . e($observacao) . '</div>';
                            }

                            return $html;
                        }

                        $linhas = [];
                        $todosCampos = array_unique(array_merge(array_keys($old), array_keys($attributes)));

                        foreach ($todosCampos as $campo) {
                            if (in_array($campo, $camposOcultos, true)) {
                                continue;
                            }

                            $antes = $old[$campo] ?? null;
                            $depois = $attributes[$campo] ?? null;

                            if ($this->valoresSaoIguais($campo, $antes, $depois)) {
                                continue;
                            }

                            $linhas[] = sprintf(
                                '<div><strong>%s:</strong> %s → %s</div>',
                                e($this->traduzirCampo($campo)),
                                e($this->formatarValor($campo, $antes)),
                                e($this->formatarValor($campo, $depois))
                            );
                        }

                        if (! empty($linhas)) {
                            return implode('', $linhas);
                        }

                        return match ($record->event) {
                            'created' => '<span class="text-sm text-gray-500">Registro inicial do item.</span>',
                            'status_manual' => '<span class="text-sm text-gray-500">Item concluído manualmente.</span>',
                            'status_manual_em_lote' => '<span class="text-sm text-gray-500">Item concluído em ação de lote.</span>',
                            'status_automatico' => '<span class="text-sm text-gray-500">Atualização automática do sistema.</span>',
                            'lembrete_recorrente' => '<span class="text-sm text-gray-500">Lembrete recorrente enviado porque o item continua vencido.</span>',
                            default => '<span class="text-sm text-gray-500">Sem diferenças relevantes visíveis.</span>',
                        };
                    }),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        $itemId = $this->record?->id;

        if (! $itemId) {
            return Activity::query()->whereRaw('1 = 0');
        }

        return Activity::query()
            ->with('causer')
            ->where('subject_type', ItemControle::class)
            ->where('subject_id', $itemId);
    }

    protected function traduzirCampo(string $campo): string
    {
        return match ($campo) {
            'titulo' => 'Título',
            'descricao' => 'Descrição',
            'tipo' => 'Tipo',
            'status' => 'Status',
            'data_vencimento' => 'Data de vencimento',
            'data_conclusao' => 'Data de conclusão',
            'empresa_id' => 'Empresa',
            'responsavel_id' => 'Responsável',
            'arquivo' => 'Anexo',
            'observacao' => 'Observação',
            default => ucfirst(str_replace('_', ' ', $campo)),
        };
    }

    protected function formatarValor(string $campo, mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '-';
        }

        if ($campo === 'tipo') {
            return match ((string) $valor) {
                'contrato' => 'Contrato',
                'documento' => 'Documento',
                'licenca' => 'Licença',
                'acordo' => 'Acordo',
                default => (string) $valor,
            };
        }

        if ($campo === 'status') {
            return match ((string) $valor) {
                'pendente' => 'Pendente',
                'em_andamento' => 'Em andamento',
                'concluido' => 'Concluído',
                'cancelado' => 'Cancelado',
                'vencido' => 'Vencido',
                default => (string) $valor,
            };
        }

        if ($campo === 'empresa_id') {
            return $this->getEmpresaNomeById($valor);
        }

        if ($campo === 'responsavel_id') {
            return $this->getResponsavelNomeById($valor);
        }

        if ($campo === 'arquivo') {
            return Str::afterLast((string) $valor, '/');
        }

        if (in_array($campo, ['data_vencimento', 'data_conclusao'], true)) {
            try {
                return Carbon::parse($valor)->format('d/m/Y');
            } catch (\Throwable) {
                return (string) $valor;
            }
        }

        return (string) $valor;
    }

    protected function getEmpresaNomeById(mixed $valor): string
    {
        $id = (int) $valor;

        if ($id <= 0) {
            return (string) $valor;
        }

        if (array_key_exists($id, $this->empresaNomeCache)) {
            return $this->empresaNomeCache[$id];
        }

        $nome = Empresa::query()
            ->whereKey($id)
            ->value('razao_social');

        $this->empresaNomeCache[$id] = $nome ?: (string) $valor;

        return $this->empresaNomeCache[$id];
    }

    protected function getResponsavelNomeById(mixed $valor): string
    {
        $id = (int) $valor;

        if ($id <= 0) {
            return (string) $valor;
        }

        if (array_key_exists($id, $this->responsavelNomeCache)) {
            return $this->responsavelNomeCache[$id];
        }

        $nome = Responsavel::query()
            ->whereKey($id)
            ->value('nome');

        $this->responsavelNomeCache[$id] = $nome ?: (string) $valor;

        return $this->responsavelNomeCache[$id];
    }

    protected function valoresSaoIguais(string $campo, mixed $antes, mixed $depois): bool
    {
        if (in_array($campo, ['data_vencimento', 'data_conclusao'], true)) {
            return $this->normalizarDataComparacao($antes) === $this->normalizarDataComparacao($depois);
        }

        return (string) $antes === (string) $depois;
    }

    protected function normalizarDataComparacao(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        try {
            return Carbon::parse($valor)->format('Y-m-d');
        } catch (\Throwable) {
            return (string) $valor;
        }
    }
}