<x-filament-panels::page>
    @php
        $resumo = $resumo ?? [];
        $hub = $hub ?? [];
        $documentos = $documentos ?? [];
        $categoriasDocumentais = $categoriasDocumentais ?? [];
        $empresasFiltro = $empresasFiltro ?? [];
        $statusFiltroOptions = $statusFiltroOptions ?? [];
        $documentoResolucaoEmEdicao = $documentoResolucaoEmEdicao ?? null;
        $statusResolucaoOptions = $statusResolucaoOptions ?? [];
        $total = (int) ($resumo['total'] ?? 0);
        $comArquivo = (int) ($resumo['comArquivo'] ?? 0);
        $semArquivo = (int) ($resumo['semArquivo'] ?? 0);
        $vencidos = (int) ($resumo['vencidos'] ?? 0);
        $vencem30 = (int) ($resumo['vencem30'] ?? 0);
        $portal = (int) ($resumo['portal'] ?? 0);
        $score = (int) ($hub['score'] ?? 100);
        $statusHub = $hub['status'] ?? 'Organizado';
    @endphp

    <style>
        .doc-center { display: grid; gap: 1rem; }
        .doc-hero { position: relative; overflow: hidden; border-radius: 24px; padding: 24px; background: linear-gradient(135deg, rgba(15, 23, 42, .96), rgba(30, 64, 175, .88)); color: white; box-shadow: 0 18px 45px rgba(15, 23, 42, .18); }
        .doc-hero::after { content: ''; position: absolute; inset: -30% -10% auto auto; width: 360px; height: 360px; border-radius: 999px; background: rgba(255, 255, 255, .10); }
        .doc-hero__content { position: relative; z-index: 1; display: grid; gap: 18px; grid-template-columns: minmax(0, 1fr) auto; align-items: center; }
        .doc-eyebrow { display: inline-flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; opacity: .82; }
        .doc-hero h2 { margin: 6px 0 8px; font-size: clamp(1.55rem, 3vw, 2.25rem); line-height: 1.08; font-weight: 900; }
        .doc-hero p { max-width: 760px; margin: 0; color: rgba(255, 255, 255, .82); }
        .doc-score { min-width: 145px; padding: 18px; border-radius: 20px; background: rgba(255, 255, 255, .12); border: 1px solid rgba(255, 255, 255, .18); text-align: center; backdrop-filter: blur(10px); }
        .doc-score strong { display: block; font-size: 2.3rem; line-height: 1; font-weight: 950; }
        .doc-score span { display: block; margin-top: 8px; font-size: 12px; color: rgba(255, 255, 255, .76); }
        .doc-cards { display: grid; gap: 12px; grid-template-columns: repeat(6, minmax(0, 1fr)); }
        .doc-card { border-radius: 18px; padding: 16px; background: white; border: 1px solid rgba(148, 163, 184, .25); box-shadow: 0 10px 28px rgba(15, 23, 42, .06); }
        .dark .doc-card { background: rgba(15, 23, 42, .72); border-color: rgba(148, 163, 184, .20); }
        .doc-card span { display: block; color: rgb(100, 116, 139); font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; }
        .dark .doc-card span { color: rgb(203, 213, 225); }
        .doc-card strong { display: block; margin-top: 8px; font-size: 1.6rem; font-weight: 950; color: rgb(15, 23, 42); }
        .dark .doc-card strong { color: white; }
        .doc-card small { display: block; margin-top: 4px; color: rgb(100, 116, 139); }
        .doc-filters { display: grid; gap: 12px; grid-template-columns: minmax(220px, 2fr) repeat(4, minmax(145px, 1fr)) auto; align-items: end; border-radius: 22px; padding: 16px; background: white; border: 1px solid rgba(148, 163, 184, .25); box-shadow: 0 10px 28px rgba(15, 23, 42, .05); }
        .dark .doc-filters { background: rgba(15, 23, 42, .72); border-color: rgba(148, 163, 184, .20); }
        .doc-field { display: grid; gap: 6px; }
        .doc-field span { font-size: 12px; font-weight: 850; color: rgb(71, 85, 105); }
        .dark .doc-field span { color: rgb(203, 213, 225); }
        .doc-field input, .doc-field select, .documentos-resolver-field input, .documentos-resolver-field select, .documentos-resolver-field textarea { width: 100%; border-radius: 14px; border: 1px solid rgba(148, 163, 184, .45); background: white; padding: 10px 12px; color: rgb(15, 23, 42); }
        .dark .doc-field input, .dark .doc-field select, .dark .documentos-resolver-field input, .dark .documentos-resolver-field select, .dark .documentos-resolver-field textarea { background: rgba(15, 23, 42, .80); color: white; border-color: rgba(148, 163, 184, .35); }
        .doc-clear { border: 1px solid rgba(148, 163, 184, .35); border-radius: 14px; padding: 10px 14px; font-weight: 850; color: rgb(51, 65, 85); background: rgb(248, 250, 252); }
        .dark .doc-clear { background: rgba(30, 41, 59, .82); color: white; }
        .doc-board { display: grid; grid-template-columns: 1fr; gap: 12px; }
        .doc-list-head { display: flex; justify-content: space-between; gap: 12px; align-items: end; }
        .doc-list-head h3 { margin: 0; font-size: 1.2rem; font-weight: 950; color: rgb(15, 23, 42); }
        .dark .doc-list-head h3 { color: white; }
        .doc-list-head p { margin: 4px 0 0; color: rgb(100, 116, 139); }
        .doc-grid { display: grid; gap: 12px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .doc-item { display: grid; gap: 14px; border-radius: 22px; padding: 18px; background: white; border: 1px solid rgba(148, 163, 184, .25); box-shadow: 0 10px 28px rgba(15, 23, 42, .05); }
        .dark .doc-item { background: rgba(15, 23, 42, .72); border-color: rgba(148, 163, 184, .20); }
        .doc-item__top { display: flex; justify-content: space-between; gap: 14px; align-items: flex-start; }
        .doc-item h4 { margin: 8px 0 0; font-size: 1.03rem; font-weight: 950; color: rgb(15, 23, 42); }
        .dark .doc-item h4 { color: white; }
        .doc-company { margin-top: 5px; color: rgb(100, 116, 139); font-size: 13px; }
        .doc-badges { display: flex; flex-wrap: wrap; gap: 6px; }
        .doc-badge { display: inline-flex; align-items: center; gap: 6px; border-radius: 999px; padding: 5px 9px; font-size: 11px; font-weight: 900; background: rgb(241, 245, 249); color: rgb(51, 65, 85); }
        .doc-badge.danger { background: rgb(254, 226, 226); color: rgb(153, 27, 27); }
        .doc-badge.warning { background: rgb(254, 243, 199); color: rgb(146, 64, 14); }
        .doc-badge.success { background: rgb(220, 252, 231); color: rgb(22, 101, 52); }
        .doc-badge.primary { background: rgb(219, 234, 254); color: rgb(30, 64, 175); }
        .doc-meta { display: grid; gap: 8px; grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .doc-meta div { border-radius: 14px; padding: 10px; background: rgb(248, 250, 252); }
        .dark .doc-meta div { background: rgba(30, 41, 59, .72); }
        .doc-meta span { display: block; font-size: 11px; color: rgb(100, 116, 139); font-weight: 800; text-transform: uppercase; }
        .doc-meta strong { display: block; margin-top: 4px; color: rgb(15, 23, 42); font-size: 13px; }
        .dark .doc-meta strong { color: white; }
        .doc-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: flex-end; border-top: 1px solid rgba(148, 163, 184, .20); padding-top: 12px; }
        .doc-link { display: inline-flex; align-items: center; justify-content: center; gap: 7px; border-radius: 12px; padding: 8px 12px; font-weight: 850; font-size: 13px; border: 1px solid rgba(148, 163, 184, .34); color: rgb(30, 64, 175); background: rgb(248, 250, 252); }
        .doc-empty { border-radius: 22px; padding: 28px; text-align: center; background: white; border: 1px dashed rgba(148, 163, 184, .55); color: rgb(100, 116, 139); }
        .dark .doc-empty { background: rgba(15, 23, 42, .72); }
        .documentos-modal-backdrop { position: fixed; inset: 0; z-index: 60; display: grid; place-items: center; padding: 24px; background: rgba(15, 23, 42, .62); backdrop-filter: blur(10px); }
        .documentos-modal-card { width: min(980px, 100%); max-height: 92vh; overflow: auto; border-radius: 28px; background: rgb(248, 250, 252); box-shadow: 0 34px 90px rgba(15, 23, 42, .34); }
        .dark .documentos-modal-card { background: rgb(15, 23, 42); color: white; }
        .documentos-resolver-form { display: grid; gap: 18px; padding: 26px; }
        .documentos-modal-header { display: flex; justify-content: space-between; gap: 18px; align-items: flex-start; padding-bottom: 2px; }
        .documentos-modal-title-block h2 { margin: 6px 0 4px; font-size: clamp(1.35rem, 2.5vw, 1.9rem); line-height: 1.1; font-weight: 950; color: rgb(15, 23, 42); }
        .dark .documentos-modal-title-block h2 { color: white; }
        .documentos-modal-title-block p { margin: 0; color: rgb(71, 85, 105); font-weight: 700; }
        .dark .documentos-modal-title-block p { color: rgb(203, 213, 225); }
        .documentos-modal-close { display: grid; place-items: center; flex: 0 0 auto; width: 42px; height: 42px; border-radius: 999px; background: white; border: 1px solid rgba(148, 163, 184, .28); color: rgb(15, 23, 42); font-size: 26px; line-height: 1; box-shadow: 0 10px 24px rgba(15, 23, 42, .08); }
        .dark .documentos-modal-close { background: rgba(30, 41, 59, .92); color: white; border-color: rgba(148, 163, 184, .24); }
        .documentos-focus-card { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; gap: 14px; align-items: center; border-radius: 22px; padding: 16px; border: 1px solid rgba(148, 163, 184, .22); background: white; }
        .documentos-focus-card.danger { background: rgb(254, 242, 242); border-color: rgba(248, 113, 113, .42); }
        .documentos-focus-card.warning { background: rgb(255, 251, 235); border-color: rgba(251, 191, 36, .42); }
        .documentos-focus-card.success { background: rgb(240, 253, 244); border-color: rgba(74, 222, 128, .38); }
        .dark .documentos-focus-card { background: rgba(30, 41, 59, .82); border-color: rgba(148, 163, 184, .22); }
        .documentos-focus-icon { display: grid; place-items: center; width: 42px; height: 42px; border-radius: 16px; background: rgba(15, 23, 42, .08); font-weight: 950; }
        .documentos-focus-card span, .documentos-current-file span { display: block; font-size: 11px; font-weight: 900; letter-spacing: .07em; text-transform: uppercase; color: rgb(100, 116, 139); }
        .documentos-focus-card strong { display: block; margin-top: 3px; font-size: 1rem; color: rgb(15, 23, 42); }
        .dark .documentos-focus-card strong { color: white; }
        .documentos-focus-card p { margin: 4px 0 0; color: rgb(71, 85, 105); }
        .dark .documentos-focus-card p { color: rgb(203, 213, 225); }
        .documentos-focus-card em { justify-self: end; border-radius: 999px; padding: 7px 12px; background: rgba(15, 23, 42, .08); font-style: normal; font-size: 12px; font-weight: 900; color: rgb(15, 23, 42); }
        .dark .documentos-focus-card em { color: white; background: rgba(255, 255, 255, .10); }
        .documentos-modal-section { display: grid; gap: 14px; border-radius: 24px; padding: 18px; background: white; border: 1px solid rgba(148, 163, 184, .24); box-shadow: 0 12px 30px rgba(15, 23, 42, .055); }
        .dark .documentos-modal-section { background: rgba(30, 41, 59, .76); border-color: rgba(148, 163, 184, .20); }
        .documentos-section-heading { display: flex; gap: 12px; align-items: flex-start; }
        .documentos-section-heading > span { display: grid; place-items: center; width: 30px; height: 30px; border-radius: 11px; background: rgb(219, 234, 254); color: rgb(29, 78, 216); font-weight: 950; }
        .dark .documentos-section-heading > span { background: rgba(59, 130, 246, .20); color: rgb(147, 197, 253); }
        .documentos-section-heading h3 { margin: 0; font-size: 1.04rem; font-weight: 950; color: rgb(15, 23, 42); }
        .dark .documentos-section-heading h3 { color: white; }
        .documentos-section-heading p { margin: 3px 0 0; color: rgb(100, 116, 139); font-size: .9rem; }
        .documentos-summary-list { display: grid; gap: 10px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .documentos-summary-list div { border-radius: 16px; padding: 12px; background: rgb(248, 250, 252); border: 1px solid rgba(148, 163, 184, .18); min-height: 74px; }
        .dark .documentos-summary-list div { background: rgba(15, 23, 42, .58); border-color: rgba(148, 163, 184, .16); }
        .documentos-summary-list span, .documentos-resolver-field span { display: block; font-size: 12px; font-weight: 900; color: rgb(71, 85, 105); }
        .dark .documentos-summary-list span, .dark .documentos-resolver-field span { color: rgb(203, 213, 225); }
        .documentos-summary-list strong { display: block; margin-top: 5px; color: rgb(15, 23, 42); font-weight: 900; overflow-wrap: anywhere; }
        .dark .documentos-summary-list strong { color: white; }
        .documentos-form-stack { display: grid; gap: 14px; }
        .documentos-form-row { display: grid; gap: 14px; }
        .documentos-form-row--two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .documentos-resolver-field { display: grid; gap: 7px; }
        .documentos-resolver-field input, .documentos-resolver-field select, .documentos-resolver-field textarea { min-height: 44px; border-radius: 15px; }
        .documentos-resolver-field textarea { resize: vertical; line-height: 1.45; }
        .documentos-file-drop { border: 1px dashed rgba(59, 130, 246, .45); border-radius: 20px; padding: 14px; background: rgb(248, 250, 252); }
        .dark .documentos-file-drop { background: rgba(15, 23, 42, .46); border-color: rgba(96, 165, 250, .35); }
        .documentos-file-drop small, .documentos-portal-option small { color: rgb(100, 116, 139); }
        .documentos-portal-option { display: flex; gap: 12px; align-items: flex-start; border-radius: 18px; padding: 14px; background: rgb(239, 246, 255); border: 1px solid rgba(59, 130, 246, .22); }
        .dark .documentos-portal-option { background: rgba(30, 64, 175, .16); border-color: rgba(96, 165, 250, .25); }
        .documentos-portal-option input { margin-top: 4px; }
        .documentos-portal-option strong { display: block; color: rgb(15, 23, 42); }
        .dark .documentos-portal-option strong { color: white; }
        .documentos-resolver-footer { position: sticky; bottom: -26px; display: flex; justify-content: space-between; gap: 14px; align-items: center; margin: 0 -26px -26px; padding: 16px 26px; border-top: 1px solid rgba(148, 163, 184, .25); background: rgba(248, 250, 252, .96); backdrop-filter: blur(8px); }
        .dark .documentos-resolver-footer { background: rgba(15, 23, 42, .96); border-color: rgba(148, 163, 184, .20); }
        .documentos-current-file strong, .documentos-current-file a { display: inline-block; margin-top: 4px; font-weight: 900; }
        .documentos-current-file a { color: rgb(37, 99, 235); }
        .documentos-modal-actions { display: flex; gap: 10px; align-items: center; }
        .documentos-field-error { color: rgb(185, 28, 28); font-weight: 800; }
        @media (max-width: 1100px) { .doc-cards { grid-template-columns: repeat(3, minmax(0, 1fr)); } .doc-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); } .doc-grid { grid-template-columns: 1fr; } }
        @media (max-width: 760px) { .doc-hero__content { grid-template-columns: 1fr; } .doc-cards, .doc-meta, .documentos-summary-list, .documentos-form-row--two { grid-template-columns: 1fr; } .doc-actions, .documentos-resolver-footer { justify-content: flex-start; flex-direction: column; align-items: stretch; } .documentos-focus-card { grid-template-columns: 1fr; } .documentos-focus-card em { justify-self: start; } .documentos-modal-backdrop { padding: 10px; align-items: end; } .documentos-modal-card { max-height: 96vh; border-radius: 24px 24px 0 0; } .documentos-resolver-form { padding: 18px; } .documentos-resolver-footer { position: static; margin: 0 -18px -18px; padding: 14px 18px; } .documentos-modal-actions { flex-direction: column-reverse; align-items: stretch; } }
    </style>

    <div class="doc-center">
        <section class="doc-hero" aria-label="Resumo do centro de documentos">
            <div class="doc-hero__content">
                <div>
                    <span class="doc-eyebrow">Centro de Documentos</span>
                    <h2>Documentos por empresa, categoria e responsabilidade.</h2>
                    <p>O usuário comum vê somente os documentos liberados pela regra de acesso. Gestores e administradores acompanham a base permitida do escritório, com foco em vencidos, arquivos pendentes e itens publicados no portal.</p>
                </div>
                <div class="doc-score">
                    <strong>{{ $score }}%</strong>
                    <span>{{ $statusHub }}</span>
                </div>
            </div>
        </section>

        <section class="doc-cards" aria-label="Indicadores documentais">
            <article class="doc-card"><span>Total</span><strong>{{ number_format($total, 0, ',', '.') }}</strong><small>documentos visíveis</small></article>
            <article class="doc-card"><span>Com arquivo</span><strong>{{ number_format($comArquivo, 0, ',', '.') }}</strong><small>prontos para consulta</small></article>
            <article class="doc-card"><span>Sem arquivo</span><strong>{{ number_format($semArquivo, 0, ',', '.') }}</strong><small>precisam de anexo</small></article>
            <article class="doc-card"><span>Vencidos</span><strong>{{ number_format($vencidos, 0, ',', '.') }}</strong><small>risco imediato</small></article>
            <article class="doc-card"><span>Vencem em 30 dias</span><strong>{{ number_format($vencem30, 0, ',', '.') }}</strong><small>monitoramento</small></article>
            <article class="doc-card"><span>No portal</span><strong>{{ number_format($portal, 0, ',', '.') }}</strong><small>visíveis ao cliente</small></article>
        </section>

        <section class="doc-filters" aria-label="Filtros de documentos">
            <label class="doc-field">
                <span>Buscar</span>
                <input type="search" wire:model.live.debounce.400ms="buscaDocumento" placeholder="Documento, empresa, status ou arquivo">
            </label>
            <label class="doc-field">
                <span>Situação</span>
                <select wire:model.live="filtroSituacao">
                    <option value="todos">Todas</option>
                    <option value="sem_arquivo">Sem arquivo</option>
                    <option value="vencidos">Vencidos</option>
                    <option value="vence_30">Vencem em 30 dias</option>
                    <option value="portal">Liberados no portal</option>
                </select>
            </label>
            <label class="doc-field">
                <span>Categoria</span>
                <select wire:model.live="filtroCategoria">
                    <option value="todas">Todas</option>
                    @foreach ($categoriasDocumentais as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="doc-field">
                <span>Empresa</span>
                <select wire:model.live="filtroEmpresa">
                    <option value="todas">Todas</option>
                    @foreach ($empresasFiltro as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="doc-field">
                <span>Status</span>
                <select wire:model.live="filtroStatus">
                    <option value="todos">Todos</option>
                    @foreach ($statusFiltroOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button type="button" class="doc-clear" wire:click="limparFiltrosDocumentos">Limpar</button>
        </section>

        <section class="doc-board" aria-label="Lista de documentos">
            <div class="doc-list-head">
                <div>
                    <h3>Documentos encontrados</h3>
                    <p>Lista limpa para consultar, baixar e regularizar documentos com propósito claro.</p>
                </div>
                <strong>{{ number_format(count($documentos), 0, ',', '.') }} item(ns)</strong>
            </div>

            <div class="doc-grid">
                @forelse ($documentos as $documento)
                    @php
                        $empresa = $documento['nome_fantasia'] ?: ($documento['razao_social'] ?: 'Empresa não informada');
                        $prioridadeOperacional = $documento['prioridade_operacional'] ?? ['label' => 'Estável', 'motivo' => 'Sem sinal crítico.', 'tom' => 'success', 'prazo' => '-'];
                        $tom = $prioridadeOperacional['tom'] ?? 'success';
                        $status = ucfirst(str_replace('_', ' ', (string) ($documento['status'] ?? '-')));
                    @endphp
                    <article class="doc-item">
                        <div class="doc-item__top">
                            <div>
                                <div class="doc-badges">
                                    <span class="doc-badge {{ $tom }}">{{ $prioridadeOperacional['label'] ?? 'Estável' }}</span>
                                    <span class="doc-badge">{{ $documento['categoria_documental_label'] ?? 'Geral' }}</span>
                                    @if (! empty($documento['portal_ativo']))
                                        <span class="doc-badge primary">Portal</span>
                                    @endif
                                </div>
                                <h4>{{ $documento['titulo'] }}</h4>
                                <div class="doc-company">{{ $empresa }}</div>
                            </div>
                            <span class="doc-badge {{ ! empty($documento['arquivo']) ? 'success' : 'warning' }}">{{ ! empty($documento['arquivo']) ? 'Com arquivo' : 'Sem arquivo' }}</span>
                        </div>

                        <div class="doc-meta">
                            <div><span>Status</span><strong>{{ $status }}</strong></div>
                            <div><span>Competência</span><strong>{{ $documento['competencia'] ?? '-' }}</strong></div>
                            <div><span>Vencimento</span><strong>{{ ! empty($documento['data_vencimento']) ? \Carbon\Carbon::parse($documento['data_vencimento'])->format('d/m/Y') : '-' }}</strong></div>
                            <div><span>Prazo</span><strong>{{ $prioridadeOperacional['prazo'] ?? '-' }}</strong></div>
                        </div>

                        @if (! empty($prioridadeOperacional['motivo']))
                            <p class="doc-company">{{ $prioridadeOperacional['motivo'] }}</p>
                        @endif

                        <div class="doc-actions">
                            @if (! empty($documento['arquivo_url']))
                                <a class="doc-link" href="{{ $documento['arquivo_url'] }}" target="_blank" rel="noopener noreferrer">Baixar / abrir</a>
                            @endif

                            @include('filament.pages.partials.documento-resolver-modal', [
                                'documento' => $documento,
                                'empresa' => $empresa,
                                'prioridadeOperacional' => $prioridadeOperacional,
                                'statusResolucaoOptions' => $statusResolucaoOptions,
                            ])
                        </div>
                    </article>
                @empty
                    <div class="doc-empty">
                        <strong>Nenhum documento encontrado.</strong>
                        <p>Ajuste os filtros ou cadastre documentos vinculados às empresas/clientes para alimentar este centro.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    @if ($documentoResolucaoEmEdicao)
        @include('filament.pages.partials.documento-resolver-dialog', [
            'documento' => $documentoResolucaoEmEdicao,
            'statusResolucaoOptions' => $statusResolucaoOptions,
        ])
    @endif
</x-filament-panels::page>
