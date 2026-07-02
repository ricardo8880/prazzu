<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/contabilidade-ux-lote6.css') }}?v={{ file_exists(public_path('css/contabilidade-ux-lote6.css')) ? filemtime(public_path('css/contabilidade-ux-lote6.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/relatorios-auditoria-lote5.css') }}">


    <div class="audit-admin">
        <section class="audit-hero">
            <div>
                <span><i class="bi bi-database-check"></i> Central de Logs</span>
                <h1>Logs técnicos e evidências administrativas</h1>
                <p>Consolida fontes de auditoria, activity log, timeline técnica, logs do sistema e permissões. Esta tela serve para rastrear e comprovar, não para executar rotinas.</p>
            </div>
            <div class="audit-actions">
                @if (! empty($links['auditoria']))<a href="{{ $links['auditoria'] }}"><i class="bi bi-shield-check"></i> Auditoria completa</a>@endif
                @if (! empty($links['detalhada']))<a href="{{ $links['detalhada'] }}"><i class="bi bi-search"></i> Investigação detalhada</a>@endif
                @if (! empty($links['permissoes']))<a href="{{ $links['permissoes'] }}"><i class="bi bi-key"></i> Auditoria de permissões</a>@endif
            </div>
        </section>

        <section class="audit-grid">
            <article class="audit-card"><span>Auditoria detalhada</span><strong>{{ number_format($resumo['auditoria_detalhada'] ?? 0, 0, ',', '.') }}</strong></article>
            <article class="audit-card"><span>Activity log</span><strong>{{ number_format($resumo['activity_log'] ?? 0, 0, ',', '.') }}</strong></article>
            <article class="audit-card"><span>Eventos hoje</span><strong>{{ number_format($resumo['eventos_hoje'] ?? 0, 0, ',', '.') }}</strong></article>
            <article class="audit-card"><span>Eventos críticos</span><strong>{{ number_format($resumo['eventos_criticos'] ?? 0, 0, ',', '.') }}</strong></article>
        </section>

        <section class="audit-sources">
            @foreach ($fontes as $fonte)
                <article class="audit-source">
                    <h3>{{ $fonte['nome'] }}</h3>
                    <p>{{ $fonte['descricao'] }}</p>
                    <span class="audit-badge {{ $fonte['ativo'] ? '' : 'off' }}">{{ $fonte['ativo'] ? number_format($fonte['eventos'], 0, ',', '.') . ' eventos' : 'Tabela ausente' }}</span>
                </article>
            @endforeach
        </section>

        <section class="audit-columns">
            <article class="audit-panel">
                <h2>Eventos recentes consolidados</h2>
                <p>Últimos registros vindos das principais fontes de auditoria.</p>
                <div class="audit-list">
                    @forelse ($eventosRecentes as $evento)
                        <div class="audit-row">
                            <div>
                                <h3>{{ $evento['titulo'] }}</h3>
                                <small>{{ $evento['detalhe'] }} · {{ $evento['usuario'] }} · IP {{ $evento['ip'] }}</small>
                                <span class="audit-chip">{{ $evento['fonte'] }} · {{ $evento['nivel'] }}</span>
                            </div>
                            <b>{{ $evento['data'] }}</b>
                        </div>
                    @empty
                        <div class="audit-empty">Nenhum evento de auditoria encontrado.</div>
                    @endforelse
                </div>
            </article>

            <div class="audit-panel">
                <h2>Maiores módulos</h2>
                <p>Concentração de eventos por módulo auditado.</p>
                <div class="audit-list">
                    @forelse ($eventosPorModulo as $row)
                        <div class="audit-row"><div><h3>{{ $row['label'] }}</h3><small>Eventos registrados</small></div><b>{{ $row['total'] }}</b></div>
                    @empty
                        <div class="audit-empty">Sem dados por módulo.</div>
                    @endforelse
                </div>

                <h2 style="margin-top:22px">Usuários mais auditados</h2>
                <div class="audit-list">
                    @forelse ($eventosPorUsuario as $row)
                        <div class="audit-row"><div><h3>{{ $row['label'] }}</h3><small>Eventos registrados</small></div><b>{{ $row['total'] }}</b></div>
                    @empty
                        <div class="audit-empty">Sem dados por usuário.</div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
