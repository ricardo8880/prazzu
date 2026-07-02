<x-filament-panels::page>
<div class="prazzu-timeline-op">
        <section class="prazzu-timeline-hero">
            <div>
                <span class="prazzu-timeline-kicker">{{ $config['group'] ?? 'TRABALHO' }}</span>
                <h1>{{ $config['title'] ?? 'Timeline Operacional' }}</h1>
                <p>{{ $config['subtitle'] ?? '' }}</p>
            </div>
            <div class="prazzu-timeline-actions">
                <span>Swimlanes por pessoa</span>
                <span>Tarefas não agendadas</span>
                <span>Marcos</span>
                <span>Zoom dia/semana</span>
            </div>
        </section>

        <section class="prazzu-timeline-stats">
            @foreach (($stats ?? []) as $stat)
                <article class="prazzu-timeline-stat">
                    <span>{{ $stat['label'] }}</span>
                    <strong>{{ $stat['value'] }}</strong>
                    <small>{{ $stat['hint'] }}</small>
                </article>
            @endforeach
        </section>

        <section class="prazzu-timeline-grid">
            <article class="prazzu-timeline-card">
                <header>
                    <div><h2>Agenda operacional por responsável</h2><p>Swimlanes para enxergar quem está sobrecarregado, ocioso ou com sobreposição.</p></div>
                    <div class="prazzu-timeline-controls">
                        @foreach (($zoom ?? []) as $item)<span class="{{ ($item['active'] ?? false) ? 'active' : '' }}">{{ $item['label'] }}</span>@endforeach
                    </div>
                </header>

                <div class="prazzu-timeline-board">
                    <div class="prazzu-timeline-scale"><span>Responsável</span><span>07h</span><span>09h</span><span>11h</span><span>13h</span><span>15h</span><span>17h</span><span>19h</span></div>
                    @forelse (($lanes ?? []) as $lane)
                        <div class="prazzu-timeline-lane">
                            <div class="prazzu-timeline-owner">
                                <strong>{{ $lane['owner'] }}</strong>
                                <span>{{ $lane['state'] }} · {{ count($lane['tasks'] ?? []) }} tarefas</span>
                                <div class="prazzu-timeline-load"><i style="width: {{ $lane['load'] }}%;"></i></div>
                            </div>
                            <div class="prazzu-timeline-track">
                                @foreach (($lane['tasks'] ?? []) as $task)
                                    <div class="prazzu-timeline-task {{ $task['is_late'] ? 'late' : '' }} {{ $task['is_blocked'] ? 'blocked' : '' }}" style="left: {{ $task['left'] }}%; width: {{ $task['width'] }}%;" title="{{ $task['start_label'] }} até {{ $task['end_label'] }}">
                                        <strong>{{ $task['title'] }}</strong>
                                        <span>{{ $task['project'] }} · {{ $task['start_label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="prazzu-timeline-empty">Nenhuma tarefa aberta para montar a timeline.</div>
                    @endforelse
                </div>
            </article>

            <aside class="prazzu-timeline-card">
                <header><div><h2>Filtros dinâmicos</h2><p>Foco no que está aberto, em revisão ou em risco.</p></div></header>
                <div class="prazzu-timeline-controls">
                    @foreach (($filters ?? []) as $filter)<span class="{{ ($filter['active'] ?? false) ? 'active' : '' }}">{{ $filter['label'] }}</span>@endforeach
                </div>
                <br>
                <div class="prazzu-timeline-list">
                    @forelse (($overlaps ?? []) as $overlap)
                        <div class="prazzu-timeline-note">
                            <strong>{{ $overlap['owner'] }}</strong>
                            <p>Sobreposição: {{ $overlap['first'] }} + {{ $overlap['second'] }}</p>
                        </div>
                    @empty
                        <div class="prazzu-timeline-empty">Nenhuma sobreposição crítica encontrada.</div>
                    @endforelse
                </div>
            </aside>
        </section>

        <section class="prazzu-timeline-columns">
            <article class="prazzu-timeline-card">
                <header><div><h2>Scheduling</h2><p>Tarefas não agendadas prontas para arrastar para alguém.</p></div></header>
                <div class="prazzu-timeline-list">
                    @forelse (($unscheduled ?? []) as $task)
                        <div class="prazzu-timeline-drop">↳ {{ $task['title'] }} · {{ ucfirst($task['priority']) }} · {{ $task['project'] }}</div>
                    @empty
                        <div class="prazzu-timeline-empty">Nenhuma tarefa sem agendamento.</div>
                    @endforelse
                </div>
            </article>

            <article class="prazzu-timeline-card">
                <header><div><h2>Marcos</h2><p>Datas críticas destacadas como diamantes.</p></div></header>
                <div class="prazzu-timeline-list">
                    @forelse (($milestones ?? []) as $milestone)
                        <div class="prazzu-timeline-note prazzu-milestone">
                            <span class="diamond"></span>
                            <div>
                                <strong>{{ $milestone['title'] }}</strong>
                                <p>{{ $milestone['date'] }} · {{ $milestone['project'] }} · {{ $milestone['owner'] }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="prazzu-timeline-empty">Nenhum marco encontrado.</div>
                    @endforelse
                </div>
            </article>

            <article class="prazzu-timeline-card">
                <header><div><h2>Execução do dia</h2><p>Timeline real de comentários, auditoria, documentos e SLA.</p></div></header>
                <div class="prazzu-timeline-feed">
                    @forelse (array_slice(($events ?? []), 0, 8) as $event)
                        <div class="prazzu-timeline-note">
                            <strong>{{ $event['title'] }}</strong>
                            <p>{{ $event['description'] }}</p>
                            <p>{{ $event['type'] }} · {{ $event['owner'] }} · {{ $event['created_label'] }}</p>
                        </div>
                    @empty
                        <div class="prazzu-timeline-empty">Nenhum evento registrado.</div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
</x-filament-panels::page>
