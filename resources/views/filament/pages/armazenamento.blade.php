<x-filament-panels::page>
    <style>
        .storage-page { display: grid; gap: 1.25rem; }
        .storage-hero { position: relative; overflow: hidden; border: 1px solid rgba(148, 163, 184, .24); border-radius: 28px; padding: 1.5rem; background: linear-gradient(135deg, rgba(124, 58, 237, .12), rgba(14, 165, 233, .08)), var(--filament-panels-color-gray-50, #f8fafc); }
        .dark .storage-hero { background: linear-gradient(135deg, rgba(124, 58, 237, .18), rgba(14, 165, 233, .10)), rgba(15, 23, 42, .72); border-color: rgba(148, 163, 184, .18); }
        .storage-hero__grid { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(280px, .8fr); gap: 1rem; align-items: stretch; }
        .storage-kicker { display: inline-flex; align-items: center; gap: .4rem; font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: rgb(124, 58, 237); }
        .storage-hero h1 { margin: .35rem 0 .35rem; font-size: clamp(1.8rem, 4vw, 3rem); line-height: 1; font-weight: 900; letter-spacing: -.04em; color: rgb(15, 23, 42); }
        .dark .storage-hero h1 { color: #fff; }
        .storage-hero p { max-width: 720px; color: rgb(71, 85, 105); font-size: .98rem; line-height: 1.65; }
        .dark .storage-hero p { color: rgb(203, 213, 225); }
        .storage-hero__panel { border-radius: 24px; padding: 1rem; background: rgba(255, 255, 255, .76); border: 1px solid rgba(148, 163, 184, .24); box-shadow: 0 18px 40px rgba(15, 23, 42, .08); }
        .dark .storage-hero__panel { background: rgba(15, 23, 42, .58); }
        .storage-hero__panel strong { display: block; font-size: 2.15rem; line-height: 1; font-weight: 900; color: rgb(15, 23, 42); }
        .dark .storage-hero__panel strong { color: #fff; }
        .storage-hero__panel span { display: block; margin-top: .35rem; color: rgb(100, 116, 139); font-size: .85rem; }
        .storage-cards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .9rem; }
        .storage-card { display: block; text-decoration: none; border-radius: 22px; padding: 1rem; border: 1px solid rgba(148, 163, 184, .22); background: white; box-shadow: 0 12px 30px rgba(15, 23, 42, .05); transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease; }
        .storage-card:hover { transform: translateY(-2px); border-color: rgba(124, 58, 237, .35); box-shadow: 0 18px 38px rgba(15, 23, 42, .08); }
        .dark .storage-card { background: rgba(15, 23, 42, .78); border-color: rgba(148, 163, 184, .16); }
        .storage-card span { display: block; color: rgb(100, 116, 139); font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }
        .storage-card strong { display: block; margin-top: .45rem; font-size: 1.7rem; font-weight: 900; color: rgb(15, 23, 42); }
        .dark .storage-card strong { color: white; }
        .storage-card small { display: block; margin-top: .25rem; color: rgb(100, 116, 139); }
        .storage-card .storage-progress { margin-top: .7rem; height: .5rem; }
        .storage-mini-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; padding: 1rem 1.1rem; border-bottom: 1px solid rgba(148, 163, 184, .14); }
        .storage-mini-card { display: block; text-decoration: none; border-radius: 18px; padding: .85rem; background: rgba(248, 250, 252, .82); border: 1px solid rgba(148, 163, 184, .18); }
        .dark .storage-mini-card { background: rgba(30, 41, 59, .66); }
        .storage-mini-card strong { display: block; margin-top: .2rem; color: rgb(15, 23, 42); font-size: 1.15rem; font-weight: 900; }
        .dark .storage-mini-card strong { color: white; }
        .storage-mini-card p { margin: .3rem 0 0; color: rgb(100, 116, 139); font-size: .78rem; line-height: 1.45; }
        .storage-alert-list { display: grid; gap: .55rem; padding: 1rem 1.1rem; border-bottom: 1px solid rgba(148, 163, 184, .14); }
        .storage-alert-item { display: grid; grid-template-columns: auto 1fr auto; gap: .65rem; align-items: start; border-radius: 16px; padding: .75rem; background: rgba(248, 250, 252, .82); border: 1px solid rgba(148, 163, 184, .16); text-decoration: none; transition: transform .18s ease, border-color .18s ease; }
        .storage-alert-item:hover { transform: translateX(2px); border-color: currentColor; }
        .dark .storage-alert-item { background: rgba(30, 41, 59, .66); }
        .storage-alert-dot { width: .7rem; height: .7rem; border-radius: 999px; margin-top: .24rem; background: currentColor; }
        .storage-alert-item.success { color: rgb(34, 197, 94); }
        .storage-alert-item.warning { color: rgb(245, 158, 11); }
        .storage-alert-item.danger { color: rgb(239, 68, 68); }
        .storage-alert-item.primary { color: rgb(124, 58, 237); }
        .storage-alert-item strong { display: block; color: rgb(15, 23, 42); font-weight: 850; }
        .dark .storage-alert-item strong { color: white; }
        .storage-alert-item p { margin: .18rem 0 0; color: rgb(100, 116, 139); font-size: .8rem; line-height: 1.45; }
        .storage-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 1rem; align-items: start; }
        .storage-section { border-radius: 24px; border: 1px solid rgba(148, 163, 184, .22); background: white; overflow: hidden; box-shadow: 0 12px 30px rgba(15, 23, 42, .04); }
        .dark .storage-section { background: rgba(15, 23, 42, .78); border-color: rgba(148, 163, 184, .16); }
        .storage-section__header { display: flex; justify-content: space-between; gap: 1rem; align-items: start; padding: 1rem 1.1rem; border-bottom: 1px solid rgba(148, 163, 184, .18); }
        .storage-section__header h2 { margin: .15rem 0; font-size: 1.05rem; font-weight: 850; color: rgb(15, 23, 42); }
        .dark .storage-section__header h2 { color: #fff; }
        .storage-section__header p { margin: 0; color: rgb(100, 116, 139); font-size: .88rem; }
        .storage-list { display: grid; }
        .storage-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 1rem; padding: .95rem 1.1rem; border-bottom: 1px solid rgba(148, 163, 184, .14); }
        .storage-row--action { align-items: center; }
        .storage-row:last-child { border-bottom: 0; }
        .storage-row h3 { margin: 0; font-size: .95rem; font-weight: 800; color: rgb(15, 23, 42); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .dark .storage-row h3 { color: white; }
        .storage-row p { margin: .25rem 0 0; color: rgb(100, 116, 139); font-size: .82rem; }
        .storage-meta { display: flex; flex-wrap: wrap; gap: .45rem; margin-top: .55rem; }
        .storage-pill { display: inline-flex; align-items: center; border-radius: 999px; padding: .28rem .55rem; font-size: .72rem; font-weight: 800; background: rgba(148, 163, 184, .14); color: rgb(71, 85, 105); }
        .storage-pill.success { background: rgba(34, 197, 94, .12); color: rgb(21, 128, 61); }
        .storage-pill.warning { background: rgba(245, 158, 11, .14); color: rgb(180, 83, 9); }
        .storage-pill.danger { background: rgba(239, 68, 68, .13); color: rgb(185, 28, 28); }
        .storage-pill.primary { background: rgba(124, 58, 237, .12); color: rgb(109, 40, 217); }
        .storage-action-link { display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; padding: .42rem .75rem; font-size: .75rem; font-weight: 850; text-decoration: none; background: rgba(124, 58, 237, .10); color: rgb(109, 40, 217); border: 1px solid rgba(124, 58, 237, .18); white-space: nowrap; cursor: pointer; }
        .storage-action-link:hover { background: rgba(124, 58, 237, .16); }
        button.storage-action-link { font-family: inherit; }
        button.storage-action-link:disabled { opacity: .65; cursor: wait; }
        .storage-action-stack { display: grid; gap: .55rem; justify-items: end; }
        .storage-checklist { display: grid; gap: .5rem; padding: 1rem 1.1rem; border-top: 1px solid rgba(148, 163, 184, .14); background: rgba(248, 250, 252, .62); }
        .dark .storage-checklist { background: rgba(30, 41, 59, .38); }
        .storage-checklist strong { color: rgb(15, 23, 42); font-weight: 850; }
        .dark .storage-checklist strong { color: white; }
        .storage-checklist ol { margin: .2rem 0 0 1.1rem; color: rgb(100, 116, 139); font-size: .84rem; line-height: 1.55; }
        .storage-size { text-align: right; font-weight: 900; color: rgb(15, 23, 42); white-space: nowrap; }
        .dark .storage-size { color: white; }
        .storage-progress { height: .65rem; border-radius: 999px; background: rgba(148, 163, 184, .20); overflow: hidden; margin-top: .75rem; }
        .storage-progress span { display: block; height: 100%; border-radius: inherit; background: currentColor; max-width: 100%; }
        .storage-progress.success { color: rgb(34, 197, 94); }
        .storage-progress.warning { color: rgb(245, 158, 11); }
        .storage-progress.danger { color: rgb(239, 68, 68); }
        .storage-insights { display: grid; gap: .75rem; }
        .storage-insight { border-radius: 20px; padding: .9rem; background: rgba(248, 250, 252, .88); border: 1px solid rgba(148, 163, 184, .18); }
        .dark .storage-insight { background: rgba(30, 41, 59, .72); }
        .storage-insight strong { display: block; color: rgb(15, 23, 42); font-weight: 850; }
        .dark .storage-insight strong { color: white; }
        .storage-insight p { margin: .25rem 0 0; color: rgb(100, 116, 139); font-size: .84rem; line-height: 1.5; }
        .storage-empty { padding: 2rem; text-align: center; color: rgb(100, 116, 139); }

        .storage-form-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem; padding: 1rem 1.1rem; border-bottom: 1px solid rgba(148, 163, 184, .14); }
        .storage-field { display: grid; gap: .35rem; }
        .storage-field--wide { grid-column: 1 / -1; }
        .storage-field label { font-size: .72rem; font-weight: 850; text-transform: uppercase; letter-spacing: .08em; color: rgb(100, 116, 139); }
        .storage-input { width: 100%; border-radius: 14px; border: 1px solid rgba(148, 163, 184, .34); background: white; padding: .62rem .72rem; color: rgb(15, 23, 42); font-size: .88rem; }
        .dark .storage-input { background: rgba(15, 23, 42, .72); color: white; border-color: rgba(148, 163, 184, .22); }
        .storage-retention-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .75rem; padding: 1rem 1.1rem; border-bottom: 1px solid rgba(148, 163, 184, .14); }
        .storage-retention-box { border-radius: 18px; padding: .85rem; background: rgba(248, 250, 252, .82); border: 1px solid rgba(148, 163, 184, .18); }
        .dark .storage-retention-box { background: rgba(30, 41, 59, .66); }
        .storage-retention-box span { display: block; color: rgb(100, 116, 139); font-size: .72rem; font-weight: 800; text-transform: uppercase; }
        .storage-retention-box strong { display: block; margin-top: .25rem; color: rgb(15, 23, 42); font-size: 1.1rem; font-weight: 900; }
        .dark .storage-retention-box strong { color: white; }
        .storage-alert { border-radius: 20px; padding: .9rem 1rem; border: 1px solid rgba(245, 158, 11, .32); background: rgba(245, 158, 11, .10); color: rgb(120, 53, 15); font-size: .88rem; }
        .dark .storage-alert { color: rgb(253, 230, 138); }
        @media (max-width: 1100px) { .storage-hero__grid, .storage-grid { grid-template-columns: 1fr; } .storage-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); } .storage-form-grid, .storage-retention-summary { grid-template-columns: 1fr; } }
        @media (max-width: 700px) { .storage-cards, .storage-mini-grid { grid-template-columns: 1fr; } .storage-row { grid-template-columns: 1fr; } .storage-size { text-align: left; } .storage-action-stack { justify-items: start; } .storage-alert-item { grid-template-columns: auto 1fr; } .storage-alert-item .storage-action-link { grid-column: 2; width: fit-content; } }
    </style>

    <div class="storage-page">
        <section class="storage-hero">
            <div class="storage-hero__grid">
                <div>
                    <span class="storage-kicker">Governança documental</span>
                    <h1>Armazenamento</h1>
                    <p>Controle espaço usado por empresa, limites de plano, arquivos pesados e documentos expirados sem misturar operação documental com gestão de capacidade.</p>
                </div>
                <div class="storage-hero__panel">
                    <span>Uso geral identificado</span>
                    <strong>{{ $resumo['percentual_global'] }}%</strong>
                    <div class="storage-progress {{ $resumo['tom_global'] }}"><span style="width: {{ min(100, $resumo['percentual_global']) }}%"></span></div>
                    <span>{{ $resumo['total_formatado'] }} usados de {{ $resumo['total_limite_formatado'] }}</span>
                </div>
            </div>
        </section>

        @unless($temColunaLimite)
            <div class="storage-alert">
                <strong>Limite funcionando por padrão de plano.</strong>
                Para limites manuais por empresa, execute o SQL enviado no pacote: <code>database/sql/2026_06_19_armazenamento_limites.sql</code>.
            </div>
        @endunless

        <section class="storage-cards" aria-label="Resumo de armazenamento">
            <a class="storage-card" href="{{ \App\Filament\Pages\Armazenamento::getUrl(['aba' => 'limites']) }}"><span>Uso geral</span><strong>{{ $resumo['percentual_global'] }}%</strong><div class="storage-progress {{ $resumo['tom_global'] }}"><span style="width: {{ min(100, $resumo['percentual_global']) }}%"></span></div><small>{{ $resumo['total_formatado'] }} de {{ $resumo['total_limite_formatado'] }} · abrir limites</small></a>
            <a class="storage-card" href="{{ \App\Filament\Pages\Armazenamento::getUrl(['aba' => 'expirados']) }}"><span>Espaço recuperável</span><strong>{{ $resumo['recuperavel_formatado'] }}</strong><small>Estimativa com expirados/antigos · revisar limpeza</small></a>
            <a class="storage-card" href="{{ \App\Filament\Pages\Armazenamento::getUrl(['aba' => 'por-empresa']) }}"><span>Clientes/Empresas</span><strong>{{ number_format($resumo['empresas'], 0, ',', '.') }}</strong><small>Com arquivos vinculados · ver ranking</small></a>
            <a class="storage-card" href="{{ \App\Filament\Pages\Armazenamento::getUrl(['aba' => 'arquivos-pesados']) }}"><span>Alertas</span><strong>{{ count($alertas) }}</strong><small>Itens que pedem atenção operacional · agir agora</small></a>
            <a class="storage-card" href="{{ \App\Filament\Pages\Armazenamento::getUrl(['aba' => 'retencao']) }}"><span>Retenção</span><strong>{{ $retencao['counts']['policies'] ?? 0 }}</strong><small>Políticas ativas · arquivar, excluir ou manter</small></a>
        </section>

        <div class="storage-grid">
            <main class="storage-section">
                @if($aba === 'visao-geral')
                    <div class="storage-section__header"><div><span class="storage-kicker">Painel executivo</span><h2>Saúde do armazenamento</h2><p>Alertas, espaço recuperável e os maiores consumidores em uma leitura rápida.</p></div></div>
                    <div class="storage-mini-grid">
                        <a class="storage-mini-card" href="{{ \App\Filament\Pages\Armazenamento::getUrl(['aba' => 'expirados']) }}"><span class="storage-kicker">Recuperável</span><strong>{{ $resumo['recuperavel_formatado'] }}</strong><p>Baseado em arquivos expirados ou antigos encontrados. Clique para revisar.</p></a>
                        <a class="storage-mini-card" href="{{ \App\Filament\Pages\Armazenamento::getUrl(['aba' => 'arquivos-pesados']) }}"><span class="storage-kicker">Arquivos</span><strong>{{ number_format($resumo['total_arquivos'], 0, ',', '.') }}</strong><p>Total localizado em anexos, documentos e portal. Clique para auditar.</p></a>
                    </div>
                    <div class="storage-alert-list">
                        @foreach($alertas as $alerta)
                            <a class="storage-alert-item {{ $alerta['tom'] }}" href="{{ \App\Filament\Pages\Armazenamento::getUrl(['aba' => $alerta['aba'] ?? 'visao-geral']) }}">
                                <span class="storage-alert-dot"></span>
                                <div><strong>{{ $alerta['titulo'] }}</strong><p>{{ $alerta['texto'] }}</p></div>
                                <span class="storage-action-link">{{ $alerta['acao'] ?? 'Abrir' }}</span>
                            </a>
                        @endforeach
                    </div>
                    <div class="storage-section__header"><div><span class="storage-kicker">Top 5</span><h2>Maiores consumidores</h2><p>Clientes/empresas que mais ocupam espaço agora.</p></div></div>
                    <div class="storage-list">
                        @forelse($topConsumidores as $empresa)
                            <article class="storage-row storage-row--action" id="empresa-{{ $empresa['empresa_id'] ?? 'sem-empresa' }}">
                                <div>
                                    <h3>{{ $empresa['empresa_nome'] }}</h3>
                                    <p>{{ $empresa['arquivos'] }} arquivo(s) · Plano {{ $empresa['plano'] }}</p>
                                    <div class="storage-progress {{ $empresa['tom'] }}"><span style="width: {{ min(100, $empresa['percentual']) }}%"></span></div>
                                    <div class="storage-meta"><span class="storage-pill {{ $empresa['tom'] }}">{{ $empresa['percentual'] }}% do limite</span><span class="storage-pill">Limite {{ $empresa['limite_formatado'] }}</span><span class="storage-pill warning">{{ $empresa['expirados'] }} expirado(s)</span></div>
                                </div>
                                <div class="storage-action-stack">
                                    <div class="storage-size">{{ $empresa['total_formatado'] }}</div>
                                    @if(! empty($empresa['empresa_id']))
                                        <button type="button" class="storage-action-link" wire:click='mountAction("verCliente", @json(["empresaId" => (int) $empresa["empresa_id"]]))' wire:loading.attr="disabled" wire:target='mountAction("verCliente", @json(["empresaId" => (int) $empresa["empresa_id"]]))'>Ver cliente</button>
                                    @else
                                        <span class="storage-pill warning">Sem vínculo</span>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="storage-empty">Nenhum arquivo encontrado para análise.</div>
                        @endforelse
                    </div>
                @elseif($aba === 'por-empresa')
                    <div class="storage-section__header"><div><span class="storage-kicker">Empresas</span><h2>Uso de armazenamento por cliente/empresa</h2><p>Controle limite, percentual usado e acúmulo por cliente/empresa.</p></div><strong>{{ count($porEmpresa) }}</strong></div>
                    <div class="storage-list">
                        @forelse($porEmpresa as $empresa)
                            <article class="storage-row storage-row--action" id="empresa-{{ $empresa['empresa_id'] ?? 'sem-empresa' }}">
                                <div>
                                    <h3>{{ $empresa['empresa_nome'] }}</h3>
                                    <p>Maior arquivo: {{ $empresa['maior_arquivo']['nome'] ?? 'Não identificado' }}</p>
                                    <div class="storage-progress {{ $empresa['tom'] }}"><span style="width: {{ min(100, $empresa['percentual']) }}%"></span></div>
                                    <div class="storage-meta"><span class="storage-pill {{ $empresa['tom'] }}">{{ $empresa['percentual'] }}%</span><span class="storage-pill primary">{{ $empresa['arquivos'] }} arquivo(s)</span><span class="storage-pill">{{ $empresa['limite_formatado'] }} de limite</span></div>
                                </div>
                                <div class="storage-action-stack">
                                    <div class="storage-size">{{ $empresa['total_formatado'] }}</div>
                                    @if(! empty($empresa['empresa_id']))
                                        <button type="button" class="storage-action-link" wire:click='mountAction("verCliente", @json(["empresaId" => (int) $empresa["empresa_id"]]))' wire:loading.attr="disabled" wire:target='mountAction("verCliente", @json(["empresaId" => (int) $empresa["empresa_id"]]))'>Ver cliente</button>
                                    @else
                                        <span class="storage-pill warning">Sem vínculo</span>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="storage-empty">Nenhuma empresa com arquivos.</div>
                        @endforelse
                    </div>
                @elseif($aba === 'arquivos-pesados')
                    <div class="storage-section__header"><div><span class="storage-kicker">Peso</span><h2>Arquivos mais pesados</h2><p>Arquivos que mais impactam custo e limite.</p></div><strong>{{ count($arquivosPesados) }}</strong></div>
                    <div class="storage-list">
                        @forelse($arquivosPesados as $arquivo)
                            <article class="storage-row">
                                <div><h3 title="{{ $arquivo['nome'] }}">{{ $arquivo['nome'] }}</h3><p>{{ $arquivo['empresa_nome'] }} · {{ $arquivo['item_titulo'] }}</p><div class="storage-meta"><span class="storage-pill primary">{{ $arquivo['origem'] }}</span><span class="storage-pill">{{ $arquivo['mime_type'] ?: 'Tipo não informado' }}</span><span class="storage-pill {{ $arquivo['expirado'] ? 'warning' : 'success' }}">{{ $arquivo['expirado'] ? 'Expirado/antigo' : 'Ativo' }}</span></div></div>
                                <div class="storage-action-stack">
                                    <div class="storage-size">{{ $arquivo['tamanho_formatado'] }}</div>
                                    <a class="storage-action-link" href="{{ \App\Filament\Pages\Documentos::getUrl(['cluster' => 'fila']) }}">Revisar</a>
                                </div>
                            </article>
                        @empty
                            <div class="storage-empty">Nenhum arquivo pesado encontrado.</div>
                        @endforelse
                    </div>
                @elseif($aba === 'expirados')
                    <div class="storage-section__header"><div><span class="storage-kicker">Limpeza</span><h2>Arquivos expirados ou antigos</h2><p>Itens candidatos a revisão, arquivamento ou exclusão controlada.</p></div><strong>{{ count($arquivosExpirados) }}</strong></div>
                    <div class="storage-list">
                        @forelse($arquivosExpirados as $arquivo)
                            <article class="storage-row">
                                <div><h3 title="{{ $arquivo['nome'] }}">{{ $arquivo['nome'] }}</h3><p>{{ $arquivo['empresa_nome'] }} · {{ $arquivo['item_titulo'] }}</p><div class="storage-meta"><span class="storage-pill warning">{{ $arquivo['idade_dias'] }} dia(s)</span><span class="storage-pill">{{ $arquivo['data_vencimento'] ? 'Venceu em ' . \Carbon\Carbon::parse($arquivo['data_vencimento'])->format('d/m/Y') : 'Arquivo antigo' }}</span><span class="storage-pill primary">{{ $arquivo['origem'] }}</span></div></div>
                                <div class="storage-action-stack">
                                    <div class="storage-size">{{ $arquivo['tamanho_formatado'] }}</div>
                                    <a class="storage-action-link" href="{{ \App\Filament\Pages\Documentos::getUrl(['cluster' => 'fila']) }}">Revisar</a>
                                </div>
                            </article>
                        @empty
                            <div class="storage-empty">Nenhum arquivo expirado ou antigo encontrado.</div>
                        @endforelse
                    </div>
                    <div class="storage-checklist">
                        <strong>Fluxo recomendado de ação</strong>
                        <ol>
                            <li>Conferir se o documento ainda precisa ser retido por obrigação legal.</li>
                            <li>Registrar aprovação interna antes de excluir ou arquivar.</li>
                            <li>Remover somente arquivos sem pendência operacional e com rastreabilidade.</li>
                        </ol>
                    </div>
                @elseif($aba === 'retencao')
                    <div class="storage-section__header">
                        <div><span class="storage-kicker">Governança de arquivos</span><h2>Política de retenção</h2><p>Defina se arquivos são temporários, permanentes, arquivados ou excluídos automaticamente.</p></div>
                        <button type="button" class="storage-action-link" wire:click="processarRetencaoAgora" wire:loading.attr="disabled" wire:target="processarRetencaoAgora">Processar agora</button>
                    </div>

                    @if(! ($retencao['ready'] ?? false))
                        <div class="storage-alert" style="margin:1rem">As tabelas de retenção ainda não existem. Execute <strong>php artisan migrate</strong> para ativar cadastro, histórico e processamento automático.</div>
                    @endif

                    <div class="storage-retention-summary">
                        <div class="storage-retention-box"><span>Políticas ativas</span><strong>{{ $retencao['counts']['policies'] ?? 0 }}</strong></div>
                        <div class="storage-retention-box"><span>Arquivar agora</span><strong>{{ $retencao['counts']['due_archive'] ?? 0 }}</strong></div>
                        <div class="storage-retention-box"><span>Excluir agora</span><strong>{{ $retencao['counts']['due_delete'] ?? 0 }}</strong></div>
                        <div class="storage-retention-box"><span>Espaço elegível</span><strong>{{ $retencao['counts']['space'] ?? '0 B' }}</strong></div>
                    </div>

                    <form class="storage-form-grid" wire:submit.prevent="salvarPoliticaRetencao">
                        <div class="storage-field"><label>Nome da política</label><input class="storage-input" type="text" wire:model="retentionForm.name" placeholder="Ex: Temporários 7 dias"></div>
                        <div class="storage-field"><label>Escopo</label><select class="storage-input" wire:model.live="retentionForm.scope_type"><option value="global">Todos os arquivos</option><option value="empresa">Cliente específico</option><option value="origem">Origem do arquivo</option></select></div>
                        <div class="storage-field"><label>Tipo</label><select class="storage-input" wire:model="retentionForm.storage_type"><option value="temporario">Arquivo temporário</option><option value="permanente">Arquivo permanente</option></select></div>
                        @if(($retentionForm['scope_type'] ?? 'global') === 'empresa')
                            <div class="storage-field"><label>Cliente</label><select class="storage-input" wire:model="retentionForm.empresa_id"><option value="">Selecione</option>@foreach($empresasOptions as $empresaId => $empresaNome)<option value="{{ $empresaId }}">{{ $empresaNome }}</option>@endforeach</select></div>
                        @endif
                        @if(($retentionForm['scope_type'] ?? 'global') === 'origem')
                            <div class="storage-field"><label>Origem</label><select class="storage-input" wire:model="retentionForm.origin"><option value="Anexo">Anexos</option><option value="Documento">Documentos</option><option value="Portal">Portal do cliente</option></select></div>
                        @endif
                        <div class="storage-field"><label>Ação automática</label><select class="storage-input" wire:model.live="retentionForm.action"><option value="arquivar">Arquivar após prazo</option><option value="excluir">Excluir após prazo</option><option value="manter">Nunca excluir</option></select></div>
                        @if(($retentionForm['action'] ?? 'arquivar') !== 'manter')
                            <div class="storage-field"><label>Prazo</label><select class="storage-input" wire:model="retentionForm.retention_days"><option value="7">7 dias</option><option value="30">30 dias</option><option value="90">90 dias</option><option value="365">1 ano</option></select></div>
                        @endif
                        <div class="storage-field storage-field--wide"><label>Observação</label><input class="storage-input" type="text" wire:model="retentionForm.notes" placeholder="Ex: usar para arquivos enviados temporariamente pelo cliente."></div>
                        <div class="storage-field"><label>&nbsp;</label><button class="storage-action-link" type="submit">Salvar política</button></div>
                    </form>

                    <div class="storage-section__header"><div><span class="storage-kicker">Regras cadastradas</span><h2>Políticas em uso</h2><p>A regra mais específica vence: cliente, origem e depois global.</p></div><strong>{{ count($retencao['all_policies'] ?? []) }}</strong></div>
                    <div class="storage-list">
                        @forelse($retencao['all_policies'] ?? [] as $policy)
                            <article class="storage-row">
                                <div><h3>{{ $policy['name'] }}</h3><p>{{ $policy['scope_label'] }} · {{ ucfirst($policy['storage_type']) }} · {{ $policy['retention_label'] }}</p><div class="storage-meta"><span class="storage-pill {{ $policy['is_active'] ? 'success' : 'warning' }}">{{ $policy['is_active'] ? 'Ativa' : 'Pausada' }}</span>@if(! empty($policy['notes']))<span class="storage-pill">{{ $policy['notes'] }}</span>@endif</div></div>
                                <div class="storage-action-stack"><button type="button" class="storage-action-link" wire:click="alternarPoliticaRetencao({{ (int) $policy['id'] }})">{{ $policy['is_active'] ? 'Pausar' : 'Ativar' }}</button></div>
                            </article>
                        @empty
                            <div class="storage-empty">Nenhuma política cadastrada. Crie a primeira regra acima.</div>
                        @endforelse
                    </div>

                    <div class="storage-section__header"><div><span class="storage-kicker">Prévia automática</span><h2>Arquivos que entram na próxima execução</h2><p>Estes são os candidatos calculados agora pelas políticas ativas.</p></div><strong>{{ count($retencao['candidates'] ?? []) }}</strong></div>
                    <div class="storage-list">
                        @forelse($retencao['candidates'] ?? [] as $arquivo)
                            <article class="storage-row"><div><h3 title="{{ $arquivo['nome'] }}">{{ $arquivo['nome'] }}</h3><p>{{ $arquivo['empresa_nome'] }} · {{ $arquivo['policy_name'] }}</p><div class="storage-meta"><span class="storage-pill {{ $arquivo['action'] === 'excluir' ? 'danger' : 'warning' }}">{{ $arquivo['action'] === 'excluir' ? 'Excluir' : 'Arquivar' }}</span><span class="storage-pill">Venceu em {{ $arquivo['due_at'] }}</span><span class="storage-pill primary">{{ $arquivo['origem'] }}</span></div></div><div class="storage-size">{{ $arquivo['tamanho_formatado'] }}</div></article>
                        @empty
                            <div class="storage-empty">Nenhum arquivo elegível para arquivar ou excluir agora.</div>
                        @endforelse
                    </div>

                    <div class="storage-section__header"><div><span class="storage-kicker">Histórico</span><h2>Últimas execuções</h2><p>Rastro de auditoria para saber o que foi feito pela rotina.</p></div></div>
                    <div class="storage-list">
                        @forelse($retencao['recent_events'] ?? [] as $event)
                            <article class="storage-row"><div><h3>{{ $event['file_name'] ?? 'Arquivo' }}</h3><p>{{ $event['policy_name'] ?? 'Política removida' }} · {{ $event['message'] ?? '' }}</p><div class="storage-meta"><span class="storage-pill {{ ($event['status'] ?? '') === 'processado' ? 'success' : 'danger' }}">{{ $event['status'] ?? 'registro' }}</span><span class="storage-pill">{{ $event['action'] ?? '-' }}</span></div></div><div class="storage-size">{{ \Carbon\Carbon::parse($event['created_at'])->format('d/m/Y H:i') }}</div></article>
                        @empty
                            <div class="storage-empty">Ainda não existe histórico de processamento.</div>
                        @endforelse
                    </div>
                @elseif($aba === 'limites')
                    <div class="storage-section__header"><div><span class="storage-kicker">Capacidade</span><h2>Limites de armazenamento</h2><p>Ranking de empresas mais próximas do limite.</p></div><strong>{{ count($limites) }}</strong></div>
                    <div class="storage-list">
                        @forelse($limites as $empresa)
                            <article class="storage-row">
                                <div><h3>{{ $empresa['empresa_nome'] }}</h3><p>{{ $empresa['total_formatado'] }} usados de {{ $empresa['limite_formatado'] }}</p><div class="storage-progress {{ $empresa['tom'] }}"><span style="width: {{ min(100, $empresa['percentual']) }}%"></span></div><div class="storage-meta"><span class="storage-pill {{ $empresa['tom'] }}">{{ $empresa['percentual'] }}% usado</span><span class="storage-pill">Plano {{ $empresa['plano'] }}</span></div></div>
                                <div class="storage-action-stack">
                                    <div class="storage-size">{{ $empresa['limite_formatado'] }}</div>
                                    <a class="storage-action-link" href="{{ \App\Filament\Pages\Armazenamento::getUrl(['aba' => 'expirados']) }}">Limpar</a>
                                </div>
                            </article>
                        @empty
                            <div class="storage-empty">Nenhum limite para exibir.</div>
                        @endforelse
                    </div>
                @endif
            </main>

            <aside class="storage-insights" aria-label="Insights de armazenamento">
                @foreach($insights as $insight)
                    <article class="storage-insight">
                        <span class="storage-pill {{ $insight['tom'] }}">{{ ucfirst($insight['tom']) }}</span>
                        <strong>{{ $insight['titulo'] }}</strong>
                        <p>{{ $insight['texto'] }}</p>
                    </article>
                @endforeach

                <article class="storage-insight">
                    <strong>Como usar esta página</strong>
                    <p>Comece pelos limites, revise arquivos pesados e configure Política de Retenção para arquivar, excluir ou manter arquivos com auditoria.</p>
                </article>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
