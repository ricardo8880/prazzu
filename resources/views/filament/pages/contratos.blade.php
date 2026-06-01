<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/prazzu-fase2-pages.css') }}?v={{ filemtime(public_path('css/prazzu-fase2-pages.css')) }}">

    <div class="prazzu-page prazzu-docs-page">
        <div class="prazzu-hero prazzu-hero-docs">
            <div><span class="prazzu-kicker">DOCUMENTOS</span><h2>Contratos</h2><p>Acompanhe carteira, vigência, valor, vencimentos e acesso rápido ao contrato.</p></div>
            <div class="prazzu-hero-actions"><a class="prazzu-action primary" href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('create') }}">Novo contrato</a><a class="prazzu-action" href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('central-contratos') }}">Central</a></div>
        </div>

        <div class="prazzu-stats-grid">
            <div class="prazzu-stat-card"><span>Total</span><strong>{{ number_format($resumo['total'] ?? 0, 0, ',', '.') }}</strong><small>Contratos encontrados</small></div>
            <div class="prazzu-stat-card success"><span>Ativos</span><strong>{{ number_format($resumo['ativos'] ?? 0, 0, ',', '.') }}</strong><small>Vigentes/em vigor</small></div>
            <div class="prazzu-stat-card warning"><span>Vencem em 30 dias</span><strong>{{ number_format($resumo['vencendo'] ?? 0, 0, ',', '.') }}</strong><small>Renovação próxima</small></div>
            <div class="prazzu-stat-card"><span>Valor total</span><strong>R$ {{ number_format($resumo['valor'] ?? 0, 2, ',', '.') }}</strong><small>Somatório cadastrado</small></div>
        </div>

        <div class="prazzu-work-grid">
            <div class="prazzu-card"><div class="prazzu-card-header compact"><div><h3>Alertas da carteira</h3><p>Visão rápida para gestão de risco contratual.</p></div></div><div class="prazzu-mini-grid"><div class="prazzu-mini-card danger"><span>Vencidos</span><strong>{{ number_format($resumo['vencidos'] ?? 0, 0, ',', '.') }}</strong><p>Contratos com vigência encerrada.</p></div><div class="prazzu-mini-card"><span>Sem vigência final</span><strong>{{ number_format($resumo['semVigencia'] ?? 0, 0, ',', '.') }}</strong><p>Precisam de conferência cadastral.</p></div></div></div>
            <div class="prazzu-card"><div class="prazzu-card-header compact"><div><h3>Ações úteis</h3><p>Atalhos para operar contratos.</p></div></div><div class="prazzu-actions-list"><a href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('create') }}">Cadastrar contrato</a><a href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('central-contratos') }}">Abrir central de contratos</a><a href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('relatorios-internos') }}">Relatórios internos</a></div></div>
        </div>

        <div class="prazzu-card">
            <div class="prazzu-card-header"><div><h3>Carteira de contratos</h3><p>Clique na linha para ver resumo, partes, vigência, arquivo e botão para editar.</p></div></div>
            <div class="prazzu-table-wrap">
                <table class="prazzu-table prazzu-click-table">
                    <thead><tr><th>Contrato</th><th>Empresa</th><th>Parte</th><th>Valor</th><th>Vigência</th><th>Status</th><th class="prazzu-modal-head"></th></tr></thead>
                    <tbody>
                        @forelse ($contratos as $contrato)
                            @php
                                $empresa = $contrato['nome_fantasia'] ?: ($contrato['razao_social'] ?: '-');
                                $vencido = ! empty($contrato['contrato_fim_em']) && \Carbon\Carbon::parse($contrato['contrato_fim_em'])->isPast();
                            @endphp
                            <tr x-data="{ open: false }" @click="open = true">
                                <td><strong>{{ $contrato['titulo'] }}</strong><small>{{ $contrato['contrato_numero'] ?: 'Sem número' }}</small></td>
                                <td>{{ $empresa }}</td>
                                <td>{{ $contrato['contrato_parte_nome'] ?: '-' }}</td>
                                <td>R$ {{ number_format($contrato['contrato_valor'] ?? 0, 2, ',', '.') }}</td>
                                <td><span class="{{ $vencido ? 'prazzu-date-danger' : '' }}">{{ ! empty($contrato['contrato_inicio_em']) ? \Carbon\Carbon::parse($contrato['contrato_inicio_em'])->format('d/m/Y') : '-' }} até {{ ! empty($contrato['contrato_fim_em']) ? \Carbon\Carbon::parse($contrato['contrato_fim_em'])->format('d/m/Y') : '-' }}</span></td>
                                <td><span class="prazzu-badge {{ $vencido ? 'danger' : '' }}">{{ ucfirst(str_replace('_', ' ', $contrato['contrato_status'] ?? 'não informado')) }}</span></td>
                                <td class="prazzu-modal-cell" @click.stop><div class="prazzu-modal-backdrop" x-show="open" x-cloak @click.self="open = false" @keydown.escape.window="open = false"><div class="prazzu-modal-panel"><button type="button" class="prazzu-modal-close" @click="open = false">×</button><span class="prazzu-kicker dark-text">CONTRATO</span><h3>{{ $contrato['titulo'] }}</h3><p>{{ $contrato['descricao'] ?: 'Sem descrição cadastrada.' }}</p><div class="prazzu-detail-grid"><div><span>Empresa</span><strong>{{ $empresa }}</strong></div><div><span>Número</span><strong>{{ $contrato['contrato_numero'] ?: '-' }}</strong></div><div><span>Parte</span><strong>{{ $contrato['contrato_parte_nome'] ?: '-' }}</strong></div><div><span>Documento da parte</span><strong>{{ $contrato['contrato_parte_documento'] ?: '-' }}</strong></div><div><span>Valor</span><strong>R$ {{ number_format($contrato['contrato_valor'] ?? 0, 2, ',', '.') }}</strong></div><div><span>Status</span><strong>{{ ucfirst(str_replace('_', ' ', $contrato['contrato_status'] ?? '-')) }}</strong></div><div><span>Início</span><strong>{{ ! empty($contrato['contrato_inicio_em']) ? \Carbon\Carbon::parse($contrato['contrato_inicio_em'])->format('d/m/Y') : '-' }}</strong></div><div><span>Fim</span><strong>{{ ! empty($contrato['contrato_fim_em']) ? \Carbon\Carbon::parse($contrato['contrato_fim_em'])->format('d/m/Y') : '-' }}</strong></div></div><div class="prazzu-modal-actions">@if (! empty($contrato['arquivo_url']))<a href="{{ $contrato['arquivo_url'] }}" target="_blank">Abrir arquivo</a>@endif<a href="{{ $contrato['edit_url'] }}">Ir para o contrato</a></div></div></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="prazzu-empty">Nenhum contrato encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
