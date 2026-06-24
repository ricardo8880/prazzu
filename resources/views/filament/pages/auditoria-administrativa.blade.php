<x-filament-panels::page>
    <style>
        .audit-admin{display:grid;gap:20px}.audit-hero{border-radius:26px;padding:26px;background:linear-gradient(135deg,#111827,#374151);color:#fff;display:flex;justify-content:space-between;gap:20px}.audit-hero span{display:inline-flex;border-radius:999px;background:rgba(255,255,255,.12);padding:6px 10px;font-size:12px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}.audit-hero h1{font-size:30px;font-weight:900;margin:12px 0 0}.audit-hero p{color:#d1d5db;max-width:860px;margin:10px 0 0}.audit-actions{display:flex;flex-wrap:wrap;gap:10px;align-content:flex-start}.audit-actions a,.audit-link{display:inline-flex;align-items:center;justify-content:center;border-radius:12px;padding:10px 14px;background:#fff;color:#111827;font-weight:800;text-decoration:none}.audit-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.audit-card,.audit-panel{border:1px solid #e5e7eb;border-radius:22px;background:#fff;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.05)}.audit-card span{display:block;color:#64748b;font-size:13px}.audit-card strong{display:block;margin-top:6px;font-size:28px;font-weight:900;color:#111827}.audit-sources{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px}.audit-source{border:1px solid #e5e7eb;border-radius:18px;padding:14px;background:#fff}.audit-source h3{font-size:15px;font-weight:900;margin:0}.audit-source p{color:#64748b;font-size:13px;margin:8px 0}.audit-badge{display:inline-flex;border-radius:999px;padding:5px 9px;background:#ecfdf5;color:#065f46;font-size:12px;font-weight:800}.audit-badge.off{background:#f3f4f6;color:#64748b}.audit-columns{display:grid;grid-template-columns:2fr 1fr;gap:16px}.audit-panel h2{font-size:18px;font-weight:900;margin:0;color:#111827}.audit-panel p{color:#64748b;margin:6px 0 0}.audit-list{display:grid;gap:10px;margin-top:14px}.audit-row{display:flex;justify-content:space-between;gap:14px;border:1px solid #e5e7eb;border-radius:16px;padding:12px;background:#f8fafc}.audit-row h3{font-size:14px;font-weight:900;margin:0;color:#111827}.audit-row small{display:block;color:#64748b;margin-top:4px}.audit-row b{white-space:nowrap;color:#111827}.audit-empty{border:1px dashed #cbd5e1;border-radius:16px;padding:18px;text-align:center;color:#64748b;background:#f8fafc}.audit-chip{display:inline-flex;border-radius:999px;background:#eef2ff;color:#3730a3;padding:4px 8px;font-size:11px;font-weight:800;margin-top:7px}@media(max-width:1150px){.audit-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.audit-sources{grid-template-columns:repeat(2,minmax(0,1fr))}.audit-columns{grid-template-columns:1fr}.audit-hero{display:block}.audit-actions{margin-top:16px}}@media(max-width:720px){.audit-grid,.audit-sources{grid-template-columns:1fr}.audit-hero h1{font-size:24px}}
    </style>

    <div class="audit-admin">
        <section class="audit-hero">
            <div>
                <span>Central Administrativa</span>
                <h1>Auditoria Administrativa</h1>
                <p>Uma visão única para logs, trilha de alterações, eventos sensíveis, auditoria de permissões e investigação detalhada. As telas antigas continuam funcionando.</p>
            </div>
            <div class="audit-actions">
                @if (! empty($links['auditoria']))<a href="{{ $links['auditoria'] }}">Abrir auditoria completa</a>@endif
                @if (! empty($links['detalhada']))<a href="{{ $links['detalhada'] }}">Investigação detalhada</a>@endif
                @if (! empty($links['permissoes']))<a href="{{ $links['permissoes'] }}">Auditoria de permissões</a>@endif
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
