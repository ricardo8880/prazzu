<x-filament-panels::page>
<div class="pz-page">
        <section class="pz-hero">
            <div>
                <div class="pz-kicker">GANTT · ESTRATÉGIA · INTERDEPENDÊNCIA</div>
                <h1>Cronograma Gantt</h1>
                <p>Visualização de planejamento da operação: mostra dependências, bloqueios, linha de base e impacto no prazo final. A execução do trabalho permanece na Central Operacional.</p>
            </div>
            <div class="pz-actions">
                <button class="pz-btn primary" wire:click="saveBaseline" wire:loading.attr="disabled">Salvar baseline atual</button>
                <button class="pz-btn" wire:click="syncBlocked" wire:loading.attr="disabled">Recalcular bloqueios</button>
            </div>
        </section>

        <section class="pz-grid four">
            <article class="pz-card pz-stat"><span>Itens no cronograma</span><strong>{{ $stats['items'] }}</strong><small>{{ $range['start'] }} até {{ $range['end'] }}</small></article>
            <article class="pz-card pz-stat"><span>Caminho crítico</span><strong>{{ $stats['critical'] }}</strong><small>Vermelho = afeta a entrega final</small></article>
            <article class="pz-card pz-stat"><span>Bloqueados</span><strong>{{ $stats['blocked'] }}</strong><small>Dependência aberta ou predecessora pendente</small></article>
            <article class="pz-card pz-stat"><span>Progresso geral</span><strong>{{ $stats['progress'] }}%</strong><small>Média dos itens carregados</small></article>
        </section>

        <section class="pz-card pz-help">
            <strong>Como usar sem treinamento</strong>
            <div class="pz-flow">
                <span>1. Filtre projeto/responsável</span><span>2. Veja vermelho/laranja</span><span>3. Ajuste período ou mova dias</span><span>4. Crie dependência</span><span>5. Salve baseline antes de iniciar</span>
            </div>
        </section>

        <section class="pz-card">
            <div class="pz-filter">
                <input class="pz-input" wire:model.live.debounce.400ms="search" placeholder="Buscar tarefa, empresa, responsável ou descrição...">
                <select class="pz-select" wire:model.live="statusFilter"><option value="todos">Todos</option><option value="abertos">Abertos</option><option value="atrasados">Atrasados</option><option value="concluidos">Concluídos</option></select>
                <select class="pz-select" wire:model.live="empresaFilter"><option value="">Todas as empresas</option>@foreach($options['empresas'] as $empresa)<option value="{{ $empresa['id'] }}">{{ $empresa['nome'] }}</option>@endforeach</select>
                <select class="pz-select" wire:model.live="responsavelFilter"><option value="">Todos responsáveis</option>@foreach($options['responsaveis'] as $responsavel)<option value="{{ $responsavel['id'] }}">{{ $responsavel['nome'] }}</option>@endforeach</select>
            </div>
        </section>

        <section class="pz-grid two">
            <article class="pz-card">
                <h3 class="pz-section-title">Multi-projeto / empresa</h3>
                @forelse($spaces as $space)
                    <div class="pz-space">
                        <div><strong>{{ $space['name'] }}</strong><div class="pz-meta">{{ $space['total'] }} itens · {{ $space['late'] }} atrasados · {{ $space['critical'] }} críticos</div></div>
                        <div class="pz-spacebar"><i style="width:{{ $space['progress'] }}%"></i></div>
                        <strong>{{ $space['progress'] }}%</strong>
                    </div>
                @empty
                    <div class="pz-empty">Nenhuma empresa encontrada para os filtros atuais.</div>
                @endforelse
            </article>

            <article class="pz-card">
                <h3 class="pz-section-title">Alterar período de uma tarefa</h3>
                <div class="pz-grid two">
                    <select class="pz-select" wire:model="windowItemId"><option value="">Tarefa</option>@foreach($options['items'] as $item)<option value="{{ $item['id'] }}">#{{ $item['id'] }} · {{ $item['titulo'] }}</option>@endforeach</select>
                    <div></div>
                    <input class="pz-input" type="date" wire:model="windowStart">
                    <input class="pz-input" type="date" wire:model="windowEnd">
                </div>
                <div class="pz-actions" style="margin-top:10px"><button class="pz-btn primary" wire:click="updateWindow">Aplicar período</button></div>
                <p class="pz-meta">Atualiza início/fim, vencimento e empurra dependentes se necessário.</p>
            </article>
        </section>

        <section class="pz-card">
            <h3 class="pz-section-title">Criar dependência entre tarefas</h3>
            <div class="pz-filter">
                <select class="pz-select" wire:model="dependencyItemId"><option value="">Tarefa bloqueada</option>@foreach($options['items'] as $item)<option value="{{ $item['id'] }}">#{{ $item['id'] }} · {{ $item['titulo'] }}</option>@endforeach</select>
                <select class="pz-select" wire:model="dependencyDependsOnId"><option value="">Depende de</option>@foreach($options['items'] as $item)<option value="{{ $item['id'] }}">#{{ $item['id'] }} · {{ $item['titulo'] }}</option>@endforeach</select>
                <select class="pz-select" wire:model="dependencyType"><option value="finish_to_start">Fim → início</option><option value="start_to_start">Início → início</option><option value="finish_to_finish">Fim → fim</option><option value="bloqueia">Bloqueia</option></select>
                <input class="pz-input" wire:model="dependencyNotes" placeholder="Observação">
            </div>
            <div class="pz-actions" style="margin-top:10px"><button class="pz-btn primary" wire:click="createDependency">Criar dependência funcional</button></div>
        </section>

        <section class="pz-card">
            <h3 class="pz-section-title">Cronograma</h3>
            <p class="pz-meta" style="margin-bottom:12px">Barra cinza fina = baseline. Barra colorida = cronograma atual. Vermelho = caminho crítico. Laranja lateral = bloqueio por dependência.</p>
            <div class="pz-grid">
                @forelse($rows as $row)
                    <div class="pz-row {{ $row['critical'] ? 'critical' : '' }} {{ $row['is_blocked'] ? 'blocked' : '' }}">
                        <div>
                            <div class="pz-task-title">@if($row['is_milestone'])<span class="diamond">◆</span>@endif <span>#{{ $row['id'] }} · {{ $row['titulo'] }}</span></div>
                            <div class="pz-meta">{{ $row['empresa'] }} · {{ $row['responsavel'] }} · {{ $row['gantt_start'] }} → {{ $row['gantt_end'] }}</div>
                            <div class="pz-pills">
                                @if($row['critical'])<span class="pz-pill red">Caminho crítico</span>@endif
                                @if($row['is_blocked'])<span class="pz-pill orange">Bloqueado</span>@endif
                                @if($row['done'])<span class="pz-pill green">Concluído</span>@endif
                                @if($row['is_milestone'])<span class="pz-pill purple">Marco</span>@endif
                                <span class="pz-pill">Folga: {{ $row['slack_days'] }}d</span>
                                <span class="pz-pill">{{ $row['progress'] }}%</span>
                            </div>
                        </div>
                        <div class="pz-track" title="{{ $row['gantt_start'] }} até {{ $row['gantt_end'] }}">
                            @if(! is_null($row['baseline_left_percent']))<div class="pz-baseline" style="left:{{ $row['baseline_left_percent'] }}%;width:{{ $row['baseline_width_percent'] }}%"></div>@endif
                            <div class="pz-bar {{ $row['critical'] ? 'critical' : '' }} {{ $row['done'] ? 'done' : '' }}" style="left:{{ $row['left_percent'] }}%;width:{{ $row['width_percent'] }}%"><div class="pz-progress" style="width:{{ $row['progress'] }}%"></div></div>
                        </div>
                        <div class="pz-actions">
                            <button class="pz-btn small" wire:click="moveTask({{ $row['id'] }}, -1)">Voltar 1d</button>
                            <button class="pz-btn small" wire:click="moveTask({{ $row['id'] }}, 1)">Adiantar 1d</button>
                            <button class="pz-btn small warn" wire:click="moveTask({{ $row['id'] }}, 7)">Empurrar 7d</button>
                            <button class="pz-btn small" wire:click="toggleMilestone({{ $row['id'] }})">{{ $row['is_milestone'] ? 'Remover marco' : 'Virar marco' }}</button>
                        </div>
                    </div>
                @empty
                    <div class="pz-empty">Nenhuma tarefa encontrada.</div>
                @endforelse
            </div>
        </section>

        <section class="pz-card">
            <h3 class="pz-section-title">Dependências ativas</h3>
            @forelse($dependencies as $dependency)
                <div class="pz-dep">
                    <div><strong>#{{ $dependency['item_controle_id'] }} {{ $dependency['atual'] }}</strong><div class="pz-meta">depende de #{{ $dependency['depends_on_item_controle_id'] }} {{ $dependency['depende'] }} · {{ $dependency['type'] }}</div></div>
                    <button class="pz-btn small danger" wire:click="removeDependency({{ $dependency['id'] }})">Remover</button>
                </div>
            @empty
                <div class="pz-empty">Ainda não existem dependências cadastradas.</div>
            @endforelse
        </section>
    </div>
</x-filament-panels::page>
