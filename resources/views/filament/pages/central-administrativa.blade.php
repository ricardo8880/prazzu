<x-filament-panels::page>
    <style>
        .prazzu-central-admin{display:grid;gap:20px}.prazzu-central-admin *{box-sizing:border-box}.prazzu-ca-hero{border-radius:26px;padding:28px;background:linear-gradient(135deg,#111827,#1f2937);color:#fff;display:flex;justify-content:space-between;gap:24px;align-items:flex-start}.prazzu-ca-eyebrow{display:inline-flex;align-items:center;gap:8px;border-radius:999px;background:rgba(255,255,255,.10);padding:6px 10px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}.prazzu-ca-hero h1{font-size:30px;line-height:1.1;font-weight:900;margin:12px 0 0}.prazzu-ca-hero p{margin:10px 0 0;color:#d1d5db;max-width:760px}.prazzu-ca-note{border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.08);border-radius:18px;padding:16px;min-width:260px}.prazzu-ca-note strong{display:block;font-size:24px}.prazzu-ca-note span,.prazzu-ca-note p{color:#d1d5db}.prazzu-ca-note p{margin:6px 0 0}.prazzu-ca-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.prazzu-ca-stat,.prazzu-ca-card,.prazzu-ca-section{border:1px solid #e5e7eb;border-radius:22px;background:#fff;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.05)}.prazzu-ca-stat{display:flex;align-items:center;gap:12px}.prazzu-ca-stat-icon,.prazzu-ca-icon,.prazzu-ca-action-icon,.prazzu-ca-status-icon{display:grid;place-items:center;flex:none}.prazzu-ca-stat-icon{width:42px;height:42px;border-radius:15px;background:#f3f4f6;color:#111827;font-size:20px}.prazzu-ca-stat span{display:block;color:#64748b;font-size:13px}.prazzu-ca-stat strong{display:block;margin-top:3px;font-size:24px;font-weight:900;color:#111827}.prazzu-ca-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.prazzu-ca-card{display:flex;flex-direction:column;gap:14px;min-height:220px}.prazzu-ca-card-top{display:flex;gap:12px;align-items:flex-start}.prazzu-ca-icon{width:46px;height:46px;border-radius:16px;background:#f3f4f6;color:#111827;font-size:22px}.prazzu-ca-card h2,.prazzu-ca-section h2{font-size:18px;font-weight:900;margin:0;color:#111827}.prazzu-ca-card p,.prazzu-ca-section p{margin:5px 0 0;color:#64748b;line-height:1.45}.prazzu-ca-shortcuts{display:flex;flex-wrap:wrap;gap:8px;margin-top:2px}.prazzu-ca-shortcut{display:inline-flex;align-items:center;border:1px solid #e5e7eb;background:#f8fafc;color:#111827;border-radius:999px;padding:6px 10px;font-size:12px;font-weight:800;text-decoration:none}.prazzu-ca-shortcut:hover{background:#eef2ff;color:#111827}.prazzu-ca-card-footer{margin-top:auto}.prazzu-ca-link{display:inline-flex;align-items:center;gap:8px;justify-content:center;border-radius:12px;padding:10px 14px;background:#111827;color:#fff;font-weight:800;text-decoration:none}.prazzu-ca-link:hover{color:#fff}.prazzu-ca-disabled{display:inline-flex;align-items:center;justify-content:center;border-radius:12px;padding:10px 14px;background:#f3f4f6;color:#64748b;font-weight:800}.prazzu-ca-two-columns{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px}.prazzu-ca-list{margin:14px 0 0;padding:0;display:grid;gap:10px;list-style:none}.prazzu-ca-list li{display:flex;gap:10px;align-items:flex-start;color:#374151}.prazzu-ca-status-icon{width:28px;height:28px;border-radius:999px;background:#f0fdf4;color:#15803d;margin-top:-2px}.prazzu-ca-status-icon.warning{background:#fffbeb;color:#b45309}.prazzu-ca-actions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:14px}.prazzu-ca-action{display:flex;gap:10px;align-items:center;border:1px solid #e5e7eb;border-radius:16px;padding:12px;background:#f8fafc;color:#111827;text-decoration:none;font-weight:800}.prazzu-ca-action:hover{background:#eef2ff;color:#111827}.prazzu-ca-action-icon{width:34px;height:34px;border-radius:12px;background:#fff;color:#111827}.prazzu-ca-activity{margin:14px 0 0;display:grid;gap:10px}.prazzu-ca-activity-item{border:1px solid #e5e7eb;background:#f8fafc;border-radius:16px;padding:12px}.prazzu-ca-activity-item strong{display:block;color:#111827}.prazzu-ca-activity-item span{display:block;color:#64748b;font-size:13px;margin-top:3px}@media(max-width:1100px){.prazzu-ca-grid,.prazzu-ca-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.prazzu-ca-hero,.prazzu-ca-two-columns{display:block}.prazzu-ca-note,.prazzu-ca-two-columns .prazzu-ca-section+.prazzu-ca-section{margin-top:16px}}@media(max-width:760px){.prazzu-ca-grid,.prazzu-ca-stats,.prazzu-ca-actions{grid-template-columns:1fr}.prazzu-ca-hero h1{font-size:24px}}
    </style>

    <div class="prazzu-central-admin">
        <section class="prazzu-ca-hero">
            <div>
                <span class="prazzu-ca-eyebrow"><i class="bi bi-gear"></i> Configurações</span>
                <h1>Central Administrativa</h1>
                <p>
                    Ajuste os dados da empresa, controle usuários e revise permissões em um só lugar.
                    Use esta área apenas quando precisar configurar o escritório ou revisar acessos.
                </p>
            </div>
            <aside class="prazzu-ca-note">
                <span>Resumo da conta</span>
                <strong>{{ collect($this->resumoConta())->sum('value') }}</strong>
                <p>registros principais acompanhados nesta tela.</p>
            </aside>
        </section>

        <section class="prazzu-ca-stats" aria-label="Resumo da conta">
            @foreach ($this->resumoConta() as $stat)
                <article class="prazzu-ca-stat">
                    <div class="prazzu-ca-stat-icon"><i class="bi {{ $stat['icon'] }}"></i></div>
                    <div>
                        <span>{{ $stat['label'] }}</span>
                        <strong>{{ $stat['value'] }}</strong>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="prazzu-ca-grid" aria-label="Áreas de configuração">
            @foreach ($this->modulos() as $modulo)
                <article class="prazzu-ca-card" wire:key="central-admin-{{ $modulo['key'] }}">
                    <div class="prazzu-ca-card-top">
                        <div class="prazzu-ca-icon"><i class="bi {{ $modulo['icone'] }}"></i></div>
                        <div>
                            <h2>{{ $modulo['titulo'] }}</h2>
                            <p>{{ $modulo['descricao'] }}</p>
                        </div>
                    </div>

                    @if ($modulo['disponivel'] && ! empty($modulo['atalhos']))
                        <div class="prazzu-ca-shortcuts">
                            @foreach ($modulo['atalhos'] as $atalho)
                                @if (filled($atalho['url'] ?? null))
                                    <a class="prazzu-ca-shortcut" href="{{ $atalho['url'] }}">{{ $atalho['label'] }}</a>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <div class="prazzu-ca-card-footer">
                        @if ($modulo['disponivel'] && filled($modulo['url']))
                            <a class="prazzu-ca-link" href="{{ $modulo['url'] }}">
                                {{ $modulo['acao'] }} <i class="bi bi-arrow-right-short"></i>
                            </a>
                        @else
                            <span class="prazzu-ca-disabled">Sem acesso para este perfil</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        <section class="prazzu-ca-two-columns">
            <article class="prazzu-ca-section">
                <h2>Saúde da conta</h2>
                <p>Veja rapidamente se o básico da administração está pronto para uso.</p>
                <ul class="prazzu-ca-list">
                    @foreach ($this->saudeConta() as $item)
                        <li>
                            <span class="prazzu-ca-status-icon {{ $item['ok'] ? '' : 'warning' }}">
                                <i class="bi {{ $item['ok'] ? 'bi-check2' : 'bi-exclamation-triangle' }}"></i>
                            </span>
                            <span>
                                <strong>{{ $item['label'] }}</strong><br>
                                {{ $item['texto'] }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </article>

            <article class="prazzu-ca-section">
                <h2>Ações rápidas</h2>
                <p>Atalhos para as configurações mais usadas pelo gestor do escritório.</p>
                <div class="prazzu-ca-actions">
                    @foreach ($this->acoesRapidas() as $acao)
                        <a class="prazzu-ca-action" href="{{ $acao['url'] }}">
                            <span class="prazzu-ca-action-icon"><i class="bi {{ $acao['icon'] }}"></i></span>
                            <span>{{ $acao['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="prazzu-ca-section">
            <h2>Atividade recente</h2>
            <p>Últimas alterações administrativas registradas no sistema.</p>
            <div class="prazzu-ca-activity">
                @foreach ($this->atividadeRecente() as $atividade)
                    <div class="prazzu-ca-activity-item">
                        <strong>{{ $atividade['titulo'] }}</strong>
                        <span>{{ $atividade['descricao'] }} · {{ $atividade['quando'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-filament-panels::page>
