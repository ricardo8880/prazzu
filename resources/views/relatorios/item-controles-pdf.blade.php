<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
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
    
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: {{ $marcaSecundaria }}; }
        h1 { font-size: 18px; margin-bottom: 8px; }
        .meta { margin-bottom: 12px; }
        .meta div { margin-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
    </style>
</head>
<body>

    <div class="brand-header">
        <table class="brand-table">
            <tr>
                <td class="brand-left">
                    <div class="brand-kicker">{{ $marcaNome }}</div>
                    <h1>Relatório de Itens de Controle</h1>
                    <div class="brand-meta">Gerado em {{ $geradoEm->format('d/m/Y H:i') }}</div>
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
    <div class="meta">
        <div><strong>Total de registros:</strong> {{ $registros->count() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Empresa</th>
                <th>Responsável</th>
                <th>Vencimento</th>
                <th>Conclusão</th>
                <th>Situação</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($registros as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->titulo }}</td>
                    <td>{{ \App\Support\ItemControleRelatorioExportador::traduzirTipo($item->tipo) }}</td>
                    <td>{{ \App\Support\ItemControleRelatorioExportador::traduzirStatus($item->status) }}</td>
                    <td>{{ $item->empresa?->razao_social }}</td>
                    <td>{{ $item->responsavel?->nome }}</td>
                    <td>{{ optional($item->data_vencimento)?->format('d/m/Y') }}</td>
                    <td>{{ optional($item->data_conclusao)?->format('d/m/Y') }}</td>
                    <td>{{ \App\Support\ItemControleRelatorioExportador::situacaoPrazo($item) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">Nenhum registro encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="brand-footer">Documento gerado por {{ $marcaNome }}.</div>
</body>
</html>