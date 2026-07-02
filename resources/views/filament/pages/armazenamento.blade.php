<x-filament-panels::page>
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
