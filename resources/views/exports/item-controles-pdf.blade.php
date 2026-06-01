<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    @php
    $whiteLabel = $whiteLabel ?? \App\Support\WhiteLabelSettings::make();
    $usarMarcaDocumento = $whiteLabel->documentBrandingEnabled();
    $marcaNome = $whiteLabel->brandName();
    $marcaLogo = $whiteLabel->documentLogoPath();
    $marcaPrimaria = $usarMarcaDocumento ? $whiteLabel->primaryColor() : '#111827';
    $marcaSecundaria = $usarMarcaDocumento ? $whiteLabel->secondaryColor() : '#111827';
    $marcaDestaque = $usarMarcaDocumento ? $whiteLabel->accentColor() : '#22c55e';
@endphp
<title>Relatório de Itens de Controle</title>
    <style>
        .brand-header { width: 100%; border-bottom: 3px solid {{ $marcaPrimaria }}; padding-bottom: 12px; margin-bottom: 14px; }
        .brand-table { width: 100%; border-collapse: collapse; }
        .brand-left { vertical-align: top; }
        .brand-right { width: 190px; vertical-align: top; text-align: right; }
        .brand-logo { max-width: 150px; max-height: 54px; margin-bottom: 6px; }
        .brand-name { font-size: 10px; font-weight: bold; color: {{ $marcaSecundaria }}; }
        .brand-kicker { color: {{ $marcaPrimaria }}; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
        .brand-meta { color: #6b7280; font-size: 10px; }
        .brand-footer { color: #6b7280; font-size: 9px; margin-top: 14px; border-top: 1px solid #e5e7eb; padding-top: 6px; }
        .brand-accent { color: {{ $marcaPrimaria }}; }
    
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: {{ $marcaSecundaria }};
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

    <div class="brand-header">
        <table class="brand-table">
            <tr>
                <td class="brand-left">
                    <div class="brand-kicker">{{ $marcaNome }}</div>
                    <h1>Relatório de Itens de Controle</h1>
                    <div class="brand-meta">Gerado em {{ now()->format('d/m/Y H:i') }}</div>
                </td>
                <td class="brand-right">
                    @if($usarMarcaDocumento && $marcaLogo)
                        <img src="{{ $marcaLogo }}" alt="{{ $marcaNome }}" class="brand-logo">
                    @endif
                    @if($usarMarcaDocumento)
                        <div class="brand-name">{{ $marcaNome }}</div>
                    @endif
                </td>
            </tr>
        </table>
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
    <div class="brand-footer">Documento gerado por {{ $marcaNome }}.</div>
</body>
</html>