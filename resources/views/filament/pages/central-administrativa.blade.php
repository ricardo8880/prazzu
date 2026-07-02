<x-filament-panels::page>
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
