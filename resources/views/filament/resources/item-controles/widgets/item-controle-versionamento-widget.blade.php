<x-filament-widgets::widget>
    <link rel="stylesheet" href="{{ asset('css/item-controle-versionamento.css') }}?v={{ file_exists(public_path('css/item-controle-versionamento.css')) ? filemtime(public_path('css/item-controle-versionamento.css')) : time() }}">

    <section id="painel-versionamento" class="icv-card">
        <header class="icv-header">
            <div>
                <span>Versionamento documental</span>
                <h2>Versões do documento</h2>
                <p>Controle a versão atual, veja versões anteriores, compare alterações e restaure uma versão quando necessário.</p>
            </div>
        </header>

        @if (! $temTabelaVersionamento)
            <div class="icv-empty">
                <strong>Tabela de versionamento não encontrada.</strong>
                <p>Quando a tabela <code>prazzu_document_versions</code> existir, as versões deste item aparecerão aqui automaticamente.</p>
            </div>
        @else
            <div class="icv-current">
                <div>
                    <small>Versão atual</small>
                    <strong>{{ $versaoAtual['numero'] ?? 'Sem versão atual' }}</strong>
                    <span>{{ $versaoAtual['arquivo'] ?? 'Nenhum arquivo principal/versionado encontrado.' }}</span>
                </div>

                @if (! empty($versaoAtual['url']))
                    <a href="{{ $versaoAtual['url'] }}" target="_blank" rel="noopener">Abrir arquivo</a>
                @endif
            </div>

            @if ($comparacao)
                <div class="icv-compare">
                    <div>
                        <span>Comparação rápida</span>
                        <strong>{{ $comparacao['anterior']['numero'] }} × {{ $comparacao['atual']['numero'] }}</strong>
                    </div>
                    <ul>
                        @forelse ($comparacao['mudancas'] as $mudanca)
                            <li>{{ $mudanca }}</li>
                        @empty
                            <li>Sem diferença operacional nos metadados cadastrados.</li>
                        @endforelse
                    </ul>
                </div>
            @endif

            <div class="icv-list">
                @forelse ($versoes as $versao)
                    <article class="icv-version {{ $versao['status_tom'] }}">
                        <div class="icv-version-main">
                            <div>
                                <strong>{{ $versao['numero'] }} • {{ $versao['tipo'] }}</strong>
                                <span>{{ $versao['arquivo'] }}</span>
                            </div>
                            <b>{{ $versao['status_label'] }}</b>
                        </div>

                        <div class="icv-meta">
                            <span>Criada por: {{ $versao['criado_por'] }}</span>
                            <span>Data: {{ $versao['criado_em'] }}</span>
                            @if (! empty($versao['aprovado_por']))
                                <span>Aprovada por: {{ $versao['aprovado_por'] }}</span>
                            @endif
                        </div>

                        <p><strong>Motivo:</strong> {{ $versao['motivo'] }}</p>

                        <div class="icv-actions">
                            @if (! empty($versao['url']))
                                <a href="{{ $versao['url'] }}" target="_blank" rel="noopener">Visualizar</a>
                            @endif

                            @if ($podeGerenciar && ! empty($versao['id']) && ! $versao['is_atual'])
                                <button type="button" wire:click="restaurarVersao({{ $versao['id'] }})" wire:confirm="Restaurar esta versão como arquivo principal do item?">Restaurar versão</button>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="icv-empty">
                        <strong>Nenhuma versão cadastrada.</strong>
                        <p>Assim que houver registros em <code>prazzu_document_versions</code>, o histórico versionado será exibido aqui.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </section>
</x-filament-widgets::widget>
