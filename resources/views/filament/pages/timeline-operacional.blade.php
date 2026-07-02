<x-filament-panels::page>

    <div class="tl-page">
        <section class="contabilidade-lote3-scope" aria-label="Propósito da Timeline Operacional">
            <div class="contabilidade-lote3-scope__top">
                <div>
                    <span class="contabilidade-lote3-eyebrow"><i class="bi bi-calendar2-week"></i> Timeline</span>
                    <h2>Planejamento temporal de execução e capacidade</h2>
                    <p>A Timeline organiza agenda, responsáveis e conflitos. Ela não deve repetir a fila inteira de Pendências; deve ajudar a planejar quando e por quem será executado.</p>
                </div>
                <div class="contabilidade-lote3-actions">
                    <a class="contabilidade-lote3-action primary" href="{{ \App\Filament\Pages\CentroOperacional::getUrl() }}"><i class="bi bi-command"></i> Mesa Operacional</a>
                    <a class="contabilidade-lote3-action" href="{{ \App\Filament\Pages\SlaPrazos::getUrl() }}"><i class="bi bi-clock-history"></i> SLA</a>
                </div>
            </div>
        </section>

        <div class="tp-action-loading" wire:loading.flex wire:target="scheduleSelectedTask,schedulePreset,quickMove,toggleMilestone,updateStatus">
            <span class="tp-spinner"></span>
            <span>Atualizando timeline...</span>
        </div>
        <section class="tl-hero">
            <div class="tl-kicker">TIMELINE · EXECUÇÃO · CAPACIDADE</div>
            <h1>Timeline Operacional</h1>
            <p>Visualização de apoio da operação: quem faz o quê, sobrecarga, tarefas sem agenda e conflitos de horário. A Central Operacional continua sendo a mesa principal de execução.</p>
        </section>

        <section class="tl-grid four">
            <article class="tl-card tl-stat"><span>Tarefas visíveis</span><strong>{{ $stats['items'] }}</strong><small>Depois dos filtros</small></article>
            <article class="tl-card tl-stat"><span>Responsáveis</span><strong>{{ $stats['responsaveis'] }}</strong><small>Swimlanes ativas</small></article>
            <article class="tl-card tl-stat"><span>Conflitos</span><strong>{{ $stats['overlaps'] }}</strong><small>Sobreposições por responsável</small></article>
            <article class="tl-card tl-stat"><span>Atrasadas</span><strong>{{ $stats['late'] ?? 0 }}</strong><small>Precisam de ação</small></article>
        </section>

        <section class="tl-card tl-help">
            <strong>Fluxo operacional</strong>
            <div class="tl-flow"><span>1. Veja não agendadas</span><span>2. Agende hoje/amanhã</span><span>3. Confira conflitos em laranja</span><span>4. Ajuste +1/-1 dia</span><span>5. Conclua ou marque como marco</span></div>
        </section>

        <section class="tl-card">
            <div class="tl-filter">
                <input class="tl-input" wire:model.live.debounce.400ms="search" placeholder="Buscar tarefa, cliente, responsável ou descrição...">
                <select class="tl-select" wire:model.live="statusFilter"><option value="todos">Todos</option><option value="abertos">Abertos</option><option value="atrasados">Atrasados</option><option value="concluidos">Concluídos</option></select>
                <select class="tl-select" wire:model.live="responsavelFilter"><option value="">Todos responsáveis</option>@foreach($options['responsaveis'] as $responsavel)<option value="{{ $responsavel['id'] }}">{{ $responsavel['nome'] }}</option>@endforeach</select>
                <select class="tl-select" wire:model.live="zoom"><option value="dia">Hoje por hora</option><option value="semana">Semana</option><option value="mes">Mês</option></select>
                <label style="display:flex;gap:8px;align-items:center;font-weight:800"><input type="checkbox" wire:model.live="hideDone"> Ocultar concluídas</label>
            </div>
            <p class="tl-meta">Janela atual: {{ $range['start'] }} até {{ $range['end'] }}</p>
        </section>

        <section class="tl-grid two">
            <article class="tl-card">
                <h3 class="tl-section-title">Agendamento manual</h3>
                <div class="tl-schedule">
                    <select class="tl-select" wire:model="scheduleItemId" wire:loading.attr="disabled" wire:target="scheduleSelectedTask"><option value="">Selecione uma tarefa</option>@foreach($options['items'] as $item)<option value="{{ $item['id'] }}">#{{ $item['id'] }} · {{ $item['titulo'] }}</option>@endforeach</select>
                    <input class="tl-input" type="datetime-local" wire:model="scheduleStart" wire:loading.attr="disabled" wire:target="scheduleSelectedTask">
                    <input class="tl-input" type="datetime-local" wire:model="scheduleEnd" wire:loading.attr="disabled" wire:target="scheduleSelectedTask">
                    <button class="tl-btn primary" wire:click="scheduleSelectedTask" wire:loading.attr="disabled" wire:target="scheduleSelectedTask">Agendar</button>
                </div>
                <p class="tl-meta">Grava em <code>item_controles.custom_payload</code> e atualiza vencimento pela data final.</p>
            </article>

            <article class="tl-card">
                <h3 class="tl-section-title">Marcos</h3>
                @forelse($milestones as $milestone)
                    <div class="tl-milestone"><span class="tl-diamond"></span><div><strong>#{{ $milestone['id'] }} · {{ $milestone['titulo'] }}</strong><div class="tl-meta">{{ $milestone['empresa'] }} · {{ $milestone['timeline_end'] }}</div></div></div>
                @empty
                    <div class="tl-empty tl-empty-actionable">
                        <strong>Nenhum marco definido</strong>
                        <span>Transforme tarefas importantes em marco para destacar entregas críticas na timeline.</span>
                    </div>
                @endforelse
            </article>
        </section>

        <section class="tl-card">
            <h3 class="tl-section-title">Tarefas sem agenda</h3>
            <div class="tl-unscheduled">
                @forelse($unscheduled as $task)
                    <div class="tl-note">
                        <strong>#{{ $task['id'] }} · {{ $task['titulo'] }}</strong>
                        <div class="tl-meta">{{ $task['empresa'] }} · {{ $task['responsavel'] }} · vencimento {{ $task['gantt_end'] }}</div>
                        <div class="tl-actions">
                            <button class="tl-btn small primary" wire:click="schedulePreset({{ $task['id'] }}, 'today')" wire:loading.attr="disabled" wire:target="schedulePreset({{ $task['id'] }}, 'today')">Agendar hoje 09:00</button>
                            <button class="tl-btn small" wire:click="schedulePreset({{ $task['id'] }}, 'tomorrow')" wire:loading.attr="disabled" wire:target="schedulePreset({{ $task['id'] }}, 'tomorrow')">Agendar amanhã</button>
                            <button class="tl-btn small" wire:click="schedulePreset({{ $task['id'] }}, 'next_week')" wire:loading.attr="disabled" wire:target="schedulePreset({{ $task['id'] }}, 'next_week')">Próxima semana</button>
                        </div>
                    </div>
                @empty
                    <div class="tl-empty tl-empty-positive">
                        <strong>Tudo agendado</strong>
                        <span>Todas as tarefas visíveis já possuem data na timeline.</span>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="tl-card">
            <h3 class="tl-section-title">Swimlanes por responsável</h3>
            <div class="tl-grid">
                @forelse($groups as $group)
                    <div class="tl-lane">
                        <div class="tl-lane-head">
                            <div><strong>{{ $group['responsavel'] }}</strong><div class="tl-meta">{{ $group['count'] }} tarefa(s) · {{ $group['open'] }} aberta(s) · {{ $group['late'] }} atrasada(s)</div></div>
                            <div><div class="tl-load"><i style="width:{{ min(100, $group['load_percent']) }}%"></i></div><div class="tl-meta">Carga estimada: {{ $group['load_percent'] }}%</div></div>
                            <span class="tl-pill {{ $group['overlaps'] > 0 ? 'orange' : 'green' }}">{{ $group['overlaps'] }} conflito(s)</span>
                        </div>
                        <div class="tl-lane-body">
                            @foreach($group['items'] as $item)
                                <div class="tl-task {{ $item['overlapping'] ? 'overlap' : '' }} {{ $item['is_late'] ? 'late' : '' }} {{ $item['done'] ? 'done' : '' }}">
                                    <div>
                                        <div class="tl-title">@if($item['is_milestone'])<span style="color:#a855f7">◆</span>@endif <span>#{{ $item['id'] }} · {{ $item['titulo'] }}</span></div>
                                        <div class="tl-meta">{{ $item['timeline_start'] }} → {{ $item['timeline_end'] }} · {{ $item['empresa'] }}</div>
                                        <div class="tl-tags">
                                            @if($item['overlapping'])<span class="tl-pill orange">Sobreposição</span>@endif
                                            @if($item['is_late'])<span class="tl-pill red">Atrasada</span>@endif
                                            @if($item['done'])<span class="tl-pill green">Concluída</span>@endif
                                            @if($item['is_milestone'])<span class="tl-pill purple">Marco</span>@endif
                                            <span class="tl-pill">{{ ucfirst(str_replace('_',' ', $item['status_normalized'])) }}</span>
                                            <span class="tl-pill">{{ $item['progress'] }}%</span>
                                        </div>
                                    </div>
                                    <div class="tl-track"><div class="tl-bar {{ $item['overlapping'] ? 'overlap' : '' }} {{ $item['done'] ? 'done' : '' }}" style="left:{{ $item['timeline_left_percent'] }}%;width:{{ $item['timeline_width_percent'] }}%"></div></div>
                                    <div class="tl-actions">
                                        <button class="tl-btn small" wire:click="quickMove({{ $item['id'] }}, -1)" wire:loading.attr="disabled" wire:target="quickMove({{ $item['id'] }}, -1)">Voltar 1d</button>
                                        <button class="tl-btn small" wire:click="quickMove({{ $item['id'] }}, 1)" wire:loading.attr="disabled" wire:target="quickMove({{ $item['id'] }}, 1)">Adiantar 1d</button>
                                        <button class="tl-btn small warn" wire:click="toggleMilestone({{ $item['id'] }})" wire:loading.attr="disabled" wire:target="toggleMilestone({{ $item['id'] }})">{{ $item['is_milestone'] ? 'Remover marco' : 'Virar marco' }}</button>
                                        @if(! $item['done'])<button class="tl-btn small primary" wire:click="updateStatus({{ $item['id'] }}, 'concluido')" wire:loading.attr="disabled" wire:target="updateStatus({{ $item['id'] }}, 'concluido')">Concluir</button>@endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="tl-empty tl-empty-actionable">
                        <strong>Nenhuma tarefa encontrada</strong>
                        <span>Revise os filtros acima ou crie uma nova tarefa para iniciar o planejamento operacional.</span>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-filament-panels::page>
