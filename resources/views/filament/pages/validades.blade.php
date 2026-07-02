<x-filament-panels::page>

    <div class="prazzu-page prazzu-docs-page">
        <div class="prazzu-hero prazzu-hero-docs">
            <div><span class="prazzu-kicker">DOCUMENTOS</span><h2>Validades</h2><p>Controle de vencimentos com alertas, priorização e acesso rápido ao item.</p></div>
            <div class="prazzu-hero-actions"><a class="prazzu-action primary" href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('create') }}">Novo vencimento</a><a class="prazzu-action" href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('dashboard-tabelas') }}">Dashboard</a></div>
        </div>

        <div class="prazzu-stats-grid">
            <div class="prazzu-stat-card"><span>Com validade</span><strong>{{ number_format($resumo['total'] ?? 0, 0, ',', '.') }}</strong><small>Itens com data</small></div>
            <div class="prazzu-stat-card danger"><span>Vencidos</span><strong>{{ number_format($resumo['vencidos'] ?? 0, 0, ',', '.') }}</strong><small>Ação imediata</small></div>
            <div class="prazzu-stat-card warning"><span>7 dias</span><strong>{{ number_format($resumo['seteDias'] ?? 0, 0, ',', '.') }}</strong><small>Urgente</small></div>
            <div class="prazzu-stat-card"><span>30 dias</span><strong>{{ number_format($resumo['trintaDias'] ?? 0, 0, ',', '.') }}</strong><small>Planejamento</small></div>
        </div>

        <div class="prazzu-work-grid"><div class="prazzu-card"><div class="prazzu-card-header compact"><div><h3>Saúde dos vencimentos</h3><p>Dados para organizar a rotina documental.</p></div></div><div class="prazzu-mini-grid"><div class="prazzu-mini-card"><span>Sem data</span><strong>{{ number_format($resumo['semData'] ?? 0, 0, ',', '.') }}</strong><p>Itens que precisam de validade.</p></div><div class="prazzu-mini-card success"><span>Concluídos</span><strong>{{ number_format($resumo['concluidos'] ?? 0, 0, ',', '.') }}</strong><p>Encerrados/finalizados.</p></div></div></div><div class="prazzu-card"><div class="prazzu-card-header compact"><div><h3>Ações úteis</h3><p>Atalhos para gestão de vencimentos.</p></div></div><div class="prazzu-actions-list"><a href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('create') }}">Cadastrar validade</a><a href="{{ \App\Filament\Pages\Pendencias::getUrl() }}">Pendências</a><a href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('dashboard-graficos') }}">Ver gráficos</a></div></div></div>

        <div class="prazzu-card"><div class="prazzu-card-header"><div><h3>Agenda de vencimentos</h3><p>Clique para consultar detalhe, lembretes e ir para o item.</p></div></div><div class="prazzu-table-wrap"><table class="prazzu-table prazzu-click-table"><thead><tr><th>Item</th><th>Empresa</th><th>Tipo</th><th>Prioridade</th><th>Status</th><th>Vencimento</th><th class="prazzu-modal-head"></th></tr></thead><tbody>
            @forelse ($validades as $validade)
                @php
                    $vencido = ! empty($validade['data_vencimento']) && \Carbon\Carbon::parse($validade['data_vencimento'])->isPast();
                    $empresa = $validade['nome_fantasia'] ?: ($validade['razao_social'] ?: '-');
                    $dias = ! empty($validade['data_vencimento']) ? now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($validade['data_vencimento'])->startOfDay(), false) : null;
                @endphp
                <tr x-data="{ open: false }" @click="open = true"><td><strong>{{ $validade['titulo'] }}</strong><small>{{ \Illuminate\Support\Str::limit($validade['descricao'] ?? 'Sem descrição cadastrada', 55) }}</small></td><td>{{ $empresa }}</td><td>{{ ucfirst(str_replace('_', ' ', $validade['tipo'] ?? '-')) }}</td><td><span class="prazzu-pill">{{ ucfirst(str_replace('_', ' ', $validade['prioridade'] ?? '-')) }}</span></td><td><span class="prazzu-badge {{ $vencido ? 'danger' : '' }}">{{ ucfirst(str_replace('_', ' ', $validade['status'] ?? '-')) }}</span></td><td><span class="{{ $vencido ? 'prazzu-date-danger' : '' }}">{{ \Carbon\Carbon::parse($validade['data_vencimento'])->format('d/m/Y') }}</span><small>{{ $dias === null ? '' : ($dias < 0 ? abs((int) $dias) . ' dia(s) vencido' : 'faltam ' . (int) $dias . ' dia(s)') }}</small></td><td class="prazzu-modal-cell" @click.stop><div class="prazzu-modal-backdrop" x-show="open" x-cloak @click.self="open = false" @keydown.escape.window="open = false"><div class="prazzu-modal-panel"><button type="button" class="prazzu-modal-close" @click="open = false">×</button><span class="prazzu-kicker dark-text">VALIDADE</span><h3>{{ $validade['titulo'] }}</h3><p>{{ $validade['descricao'] ?: 'Sem descrição cadastrada.' }}</p><div class="prazzu-detail-grid"><div><span>Empresa</span><strong>{{ $empresa }}</strong></div><div><span>Tipo</span><strong>{{ ucfirst(str_replace('_', ' ', $validade['tipo'] ?? '-')) }}</strong></div><div><span>Status</span><strong>{{ ucfirst(str_replace('_', ' ', $validade['status'] ?? '-')) }}</strong></div><div><span>Prioridade</span><strong>{{ ucfirst(str_replace('_', ' ', $validade['prioridade'] ?? '-')) }}</strong></div><div><span>Vencimento</span><strong>{{ \Carbon\Carbon::parse($validade['data_vencimento'])->format('d/m/Y') }}</strong></div><div><span>Situação</span><strong>{{ $dias < 0 ? 'Vencido há ' . abs((int) $dias) . ' dia(s)' : 'Faltam ' . (int) $dias . ' dia(s)' }}</strong></div><div><span>Lembretes enviados</span><strong>{{ number_format($validade['qtd_lembretes_enviados'] ?? 0, 0, ',', '.') }}</strong></div><div><span>Último lembrete</span><strong>{{ ! empty($validade['ultimo_lembrete_enviado_em']) ? \Carbon\Carbon::parse($validade['ultimo_lembrete_enviado_em'])->format('d/m/Y H:i') : '-' }}</strong></div></div><div class="prazzu-modal-actions"><a href="{{ $validade['edit_url'] }}">Ir para o item</a></div></div></div></td></tr>
            @empty
                <tr><td colspan="6" class="prazzu-empty">Nenhuma validade encontrada.</td></tr>
            @endforelse
        </tbody></table></div></div>
    </div>
</x-filament-panels::page>
