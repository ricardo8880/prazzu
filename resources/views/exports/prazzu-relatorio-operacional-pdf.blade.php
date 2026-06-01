<!doctype html>
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
<title>{{ $titulo }}</title>
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
    
        body { font-family: DejaVu Sans, sans-serif; color: {{ $marcaSecundaria }}; font-size: 11px; }
        h1 { margin: 0 0 4px; font-size: 20px; }
        p { margin: 0; color: #4b5563; }
        .header { border-bottom: 2px solid {{ $marcaPrimaria }}; padding-bottom: 12px; margin-bottom: 14px; }
        .cards { width: 100%; margin-bottom: 14px; }
        .cards td { border: 1px solid #e5e7eb; border-radius: 8px; padding: 9px; }
        .cards strong { display: block; font-size: 18px; color: {{ $marcaSecundaria }}; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #f3f4f6; font-size: 10px; text-transform: uppercase; color: #374151; }
        th, td { border: 1px solid #e5e7eb; padding: 7px; vertical-align: top; }
        .muted { color: #6b7280; font-size: 10px; }
    </style>
</head>
<body>

    <div class="brand-header">
        <table class="brand-table">
            <tr>
                <td class="brand-left">
                    <div class="brand-kicker">{{ $marcaNome }}</div>
                    <h1>{{ $titulo }}</h1>
                    <div class="brand-meta">Gerado em {{ $geradoEm }}</div>
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
    <table class="cards">
        <tr>
            @foreach (($data['cards'] ?? []) as $card)
                <td>
                    <span>{{ $card['label'] }}</span>
                    <strong>{{ $card['value'] }}</strong>
                    <small>{{ $card['hint'] }}</small>
                </td>
            @endforeach
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Título</th>
                <th>Status</th>
                <th>Responsável</th>
                <th>Prazo</th>
                <th>Indicador</th>
                <th>Prioridade</th>
            </tr>
        </thead>
        <tbody>
            @forelse (($data['linhas'] ?? []) as $row)
                <tr>
                    <td>{{ $row['cliente'] }}</td>
                    <td>{{ $row['titulo'] }}<br><span class="muted">{{ $row['observacao'] }}</span></td>
                    <td>{{ $row['status'] }}</td>
                    <td>{{ $row['responsavel'] }}</td>
                    <td>{{ $row['vencimento'] }}</td>
                    <td>{{ $row['indicador'] }}</td>
                    <td>{{ $row['prioridade'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7">Nenhum registro encontrado.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="brand-footer">Documento gerado por {{ $marcaNome }}.</div>
</body>
</html>
