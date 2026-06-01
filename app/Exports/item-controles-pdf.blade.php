<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Itens de Controle</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
            margin: 20px;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 6px;
        }

        .subtitulo {
            font-size: 11px;
            color: #555;
            margin-bottom: 16px;
        }

        .resumo {
            margin-bottom: 16px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 6px;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
            text-align: left;
        }

        tr:nth-child(even) {
            background: #fafafa;
        }

        .small {
            font-size: 10px;
        }
    </style>
</head>
<body>
    <h1>Relatório de Itens de Controle</h1>

    <div class="subtitulo">
        Gerado em {{ now()->format('d/m/Y H:i') }}
    </div>

    <div class="resumo">
        <strong>Total de registros:</strong> {{ $items->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">ID</th>
                <th style="width: 16%;">Título</th>
                <th style="width: 8%;">Tipo</th>
                <th style="width: 9%;">Status</th>
                <th style="width: 14%;">Empresa</th>
                <th style="width: 12%;">Responsável</th>
                <th style="width: 8%;">Vencimento</th>
                <th style="width: 8%;">Conclusão</th>
                <th style="width: 10%;">Situação</th>
                <th style="width: 5%;">Anexo</th>
                <th style="width: 6%;">Anexos</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                @php
                    $statusExibicao = $item->status;

                    if (
                        $item->data_vencimento &&
                        $item->data_vencimento->copy()->startOfDay()->isPast() &&
                        ! in_array($item->status, ['concluido', 'cancelado', 'vencido'], true)
                    ) {
                        $statusExibicao = 'vencido';
                    }

                    $statusTraduzido = match ($statusExibicao) {
                        'pendente' => 'Pendente',
                        'em_andamento' => 'Em andamento',
                        'concluido' => 'Concluído',
                        'cancelado' => 'Cancelado',
                        'vencido' => 'Vencido',
                        default => (string) $statusExibicao,
                    };

                    $tipoTraduzido = match ($item->tipo) {
                        'contrato' => 'Contrato',
                        'documento' => 'Documento',
                        'licenca' => 'Licença',
                        'acordo' => 'Acordo',
                        default => (string) $item->tipo,
                    };

                    $situacaoPrazo = '-';

                    if ($item->data_vencimento) {
                        if (in_array($item->status, ['concluido', 'cancelado'], true)) {
                            $situacaoPrazo = ucfirst(str_replace('_', ' ', $item->status));
                        } else {
                            $dias = now()->startOfDay()->diffInDays($item->data_vencimento->copy()->startOfDay(), false);

                            $situacaoPrazo = match (true) {
                                $dias < 0 => 'Vencido',
                                $dias === 0 => 'Vence hoje',
                                $dias <= 7 => 'Próximo do vencimento',
                                default => 'No prazo',
                            };
                        }
                    }
                @endphp

                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->titulo }}</td>
                    <td>{{ $tipoTraduzido }}</td>
                    <td>{{ $statusTraduzido }}</td>
                    <td>{{ $item->empresa?->razao_social }}</td>
                    <td>{{ $item->responsavel?->nome }}</td>
                    <td>{{ optional($item->data_vencimento)?->format('d/m/Y') }}</td>
                    <td>{{ optional($item->data_conclusao)?->format('d/m/Y') }}</td>
                    <td>{{ $situacaoPrazo }}</td>
                    <td>{{ filled($item->arquivo) ? 'Sim' : 'Não' }}</td>
                    <td>{{ $item->anexos_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>