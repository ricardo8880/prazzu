<x-filament-panels::page>
    @php
        $whiteLabel = \App\Support\WhiteLabelSettings::make();
        $brandName = $whiteLabel->displayName();
        $enterpriseLabel = $whiteLabel->enterpriseLabel();
    @endphp


    <div class="prazzu80-page" data-prazzu80-page>
        <section class="prazzu80-hero">
            <div>
                <span class="prazzu80-kicker">{{ $config['group'] ?? strtoupper($brandName) }}</span>
                <h1>{{ $config['title'] ?? $enterpriseLabel }}</h1>
                <p>{{ $config['subtitle'] ?? 'Operação, compliance, documentos, clientes e cobrança em uma única plataforma.' }}</p>
            </div>
            <div class="prazzu80-hero-actions">
                @foreach (($quickActions ?? []) as $action)
                    <span>{{ $action }}</span>
                @endforeach
            </div>
        </section>

        <section class="prazzu80-search-card">
            <div>
                <strong>Busca global inteligente</strong>
                <p>{{ $searchPlaceholder ?? 'Buscar...' }}</p>
                <small>Filtre os registros reais exibidos nesta página sem sair do módulo.</small>
            </div>
            <div class="prazzu80-search-controls">
                <input type="search" placeholder="Buscar por título, empresa, status, responsável..." data-prazzu80-search aria-label="Buscar nesta página">
                <select data-prazzu80-status aria-label="Filtrar por situação">
                    <option value="all">Todas as situações</option>
                    @foreach (($globalSearch['filters'] ?? []) as $filter)
                        <option value="{{ \Illuminate\Support\Str::lower($filter) }}">{{ $filter }}</option>
                    @endforeach
                </select>
                <button type="button" data-prazzu80-clear>Limpar</button>
            </div>
        </section>

        <section class="prazzu80-no-results" data-prazzu80-empty hidden>
            <strong>Nenhum resultado encontrado.</strong>
            <p>Altere a busca ou limpe os filtros para voltar a visualizar os registros desta página.</p>
        </section>

        <section class="prazzu80-stats">
            @foreach (($stats ?? []) as $stat)
                <article>
                    <span>{{ $stat['label'] }}</span>
                    <strong>{{ $stat['value'] }}</strong>
                    <small>{{ $stat['hint'] }}</small>
                </article>
            @endforeach
        </section>


        @if (($module ?? null) === 'clientes')
            <section class="prazzu80-grid three">
                <article class="prazzu80-card">
                    <header><div><h2>Status de contrato</h2><p>Carteira interna separada por Ativo, Implementação, risco ou churn.</p></div></header>
                    <div class="prazzu80-list compact">
                        @forelse (($clientCrm['statusSummary'] ?? []) as $row)
                            <div class="prazzu80-list-row"><div><strong>{{ $row['label'] }}</strong><span>Clientes nesse estágio</span></div><em>{{ $row['count'] }}</em></div>
                        @empty
                            <div class="prazzu80-empty">Nenhum cliente cadastrado.</div>
                        @endforelse
                    </div>
                </article>

                <article class="prazzu80-card">
                    <header><div><h2>Health Score</h2><p>Classificação calculada usando atrasos, pendências, aprovações e contato recente.</p></div></header>
                    <div class="prazzu80-list compact">
                        @forelse (($clientCrm['healthSummary'] ?? []) as $row)
                            <div class="prazzu80-list-row"><div><strong>{{ $row['label'] }}</strong><span>Saúde da carteira</span></div><em class="{{ ($row['tone'] ?? '') === 'danger' ? 'danger' : '' }}">{{ $row['count'] }}</em></div>
                        @empty
                            <div class="prazzu80-empty">Sem dados suficientes para calcular saúde.</div>
                        @endforelse
                    </div>
                </article>

                <article class="prazzu80-card">
                    <header><div><h2>Onboarding automático</h2><p>Clientes fechados ou ativos com tarefas pendentes para implantação.</p></div></header>
                    <div class="prazzu80-list compact">
                        @forelse (($clientCrm['onboarding'] ?? []) as $row)
                            <div class="prazzu80-note"><strong>{{ $row['client'] }}</strong><p>{{ $row['status'] }} · {{ $row['tasks'] }} tarefa(s) abertas · Saúde: {{ $row['health'] }}</p></div>
                        @empty
                            <div class="prazzu80-empty">Nenhum onboarding pendente.</div>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="prazzu80-card">
                <header><div><h2>Clientes — visão de tabela CRM</h2><p>Planilha operacional com campos personalizados, LTV, decisor, WhatsApp, última reunião e saúde.</p></div></header>
                <div class="prazzu80-table-wrap">
                    <table class="prazzu80-table">
                        <thead>
                            <tr>
                                <th>Cliente</th><th>Status de contrato</th><th>LTV</th><th>Decisor / contato</th><th>E-mail</th><th>WhatsApp</th><th>Última reunião</th><th>Health Score</th><th>Pendências</th><th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse (($clientCrm['clients'] ?? []) as $client)
                                <tr>
                                    <td><strong>{{ $client['name'] }}</strong><br><small>{{ $client['document'] }}</small></td>
                                    <td><span class="prazzu80-badge {{ ($client['contract_status'] ?? '') === 'Em risco' ? 'vencido' : 'ok' }}">{{ $client['contract_status'] }}</span></td>
                                    <td>R$ {{ number_format((float) ($client['ltv'] ?? 0), 2, ',', '.') }}</td>
                                    <td>{{ $client['contact_name'] ?? '-' }}</td>
                                    <td>{{ $client['contact_email'] ?? '-' }}</td>
                                    <td>{{ $client['contact_whatsapp'] ?? '-' }}</td>
                                    <td>{{ $client['last_meeting'] ?? 'Sem registro' }}</td>
                                    <td><span class="prazzu80-badge {{ $client['health_tone'] ?? '' }}">{{ $client['health_label'] }} · {{ $client['health_score'] }}%</span></td>
                                    <td>{{ $client['open_items'] }} abertas · {{ $client['late_items'] }} atrasadas</td>
                                    <td>
                                        <button type="button" class="prazzu80-mini-button" wire:click="criarOnboarding({{ (int) ($client['id'] ?? 0) }})">Criar onboarding</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="prazzu80-empty">Nenhum cliente encontrado no banco.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="prazzu80-card">
                <header><div><h2>Registrar reunião / ata do cliente</h2><p>Atualiza a data da última reunião usando a timeline real do cliente, sem dado fictício.</p></div></header>
                <form wire:submit.prevent="registrarReuniao" class="prazzu80-form">
                    <label>
                        <span>Cliente</span>
                        <select wire:model="meetingEmpresaId">
                            <option value="">Selecione</option>
                            @foreach (($clientFormOptions['empresas'] ?? []) as $empresa)
                                <option value="{{ $empresa['id'] }}">{{ $empresa['name'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>Título</span>
                        <input type="text" wire:model.defer="meetingTitulo" placeholder="Ex: Reunião de alinhamento">
                    </label>
                    <label class="wide">
                        <span>Ata / decisão</span>
                        <textarea wire:model.defer="meetingDescricao" rows="3" placeholder="Registre o que foi decidido na call"></textarea>
                    </label>
                    <button type="submit">Salvar reunião</button>
                </form>
            </section>

            <section class="prazzu80-grid two">
                <article class="prazzu80-card">
                    <header><div><h2>Integração de e-mail / histórico</h2><p>Mensagens relacionadas aos clientes ficam próximas da tarefa e do contrato.</p></div></header>
                    <div class="prazzu80-list compact">
                        @forelse (($clientCrm['emailHistory'] ?? []) as $mail)
                            <div class="prazzu80-note"><strong>{{ $mail['nome_fantasia'] ?? $mail['razao_social'] ?? $mail['titulo'] ?? 'Histórico' }}</strong><p>{{ \Illuminate\Support\Str::limit($mail['mensagem'] ?? '-', 130) }}</p></div>
                        @empty
                            <div class="prazzu80-empty">Nenhum histórico de e-mail encontrado nos comentários.</div>
                        @endforelse
                    </div>
                </article>

                <article class="prazzu80-card">
                    <header><div><h2>Campos personalizados usados</h2><p>CRM pronto para operação sem dados estáticos: tudo vem de empresas, pagamentos e itens de controle.</p></div></header>
                    <div class="prazzu80-feature-grid">
                        <div class="prazzu80-feature ok"><strong>Status de contrato</strong><span>Calculado por status, ativo, atrasos e tarefas.</span></div>
                        <div class="prazzu80-feature ok"><strong>LTV</strong><span>Soma dos pagamentos recebidos do cliente.</span></div>
                        <div class="prazzu80-feature ok"><strong>Ponto de contato</strong><span>Responsável, e-mail e telefone da empresa.</span></div>
                        <div class="prazzu80-feature ok"><strong>Última reunião</strong><span>Busca em timeline por reunião, ata ou call.</span></div>
                        <div class="prazzu80-feature ok"><strong>Health Score</strong><span>Calculado por risco, atraso, aprovação e relacionamento.</span></div>
                        <div class="prazzu80-feature ok"><strong>Onboarding</strong><span>Fila baseada nas tarefas abertas por cliente.</span></div>
                    </div>
                </article>
            </section>
        @elseif (($module ?? null) === 'portal-cliente')
            <section class="prazzu80-card">
                <header><div><h2>Progresso do projeto</h2><p>Battery chart para o cliente entender rapidamente o quanto já foi concluído.</p></div></header>
                <div class="prazzu80-battery-wrap">
                    <div class="prazzu80-battery"><i style="width: {{ $portalExperience['progress']['percent'] ?? 0 }}%"></i></div>
                    <strong>{{ $portalExperience['progress']['percent'] ?? 0 }}%</strong>
                    <span>{{ $portalExperience['progress']['done'] ?? 0 }} concluído(s), {{ $portalExperience['progress']['pending'] ?? 0 }} pendente(s), {{ $portalExperience['progress']['review'] ?? 0 }} em revisão.</span>
                </div>
            </section>

            <section class="prazzu80-grid two">
                <article class="prazzu80-card">
                    <header><div><h2>Pronto para revisão / aprovação</h2><p>Lista filtrada para o cliente ver só o que precisa da atenção dele.</p></div></header>
                    <div class="prazzu80-list compact">
                        @forelse (($portalExperience['visibleItems'] ?? []) as $item)
                            <div class="prazzu80-list-row">
                                <div><strong>{{ $item['titulo'] ?? 'Item' }}</strong><span>{{ $item['empresa'] ?? '-' }} · {{ ucfirst(str_replace('_', ' ', $item['status'] ?? '-')) }}</span></div>
                                <em>{{ $item['progress'] ?? 0 }}%</em>
                            </div>
                        @empty
                            <div class="prazzu80-empty">Nenhum item liberado para o portal.</div>
                        @endforelse
                    </div>
                </article>

                <article class="prazzu80-card">
                    <header><div><h2>Calendário de entregas</h2><p>Deadlines visíveis para reduzir perguntas sobre prazo.</p></div></header>
                    <div class="prazzu80-list compact">
                        @forelse (($portalExperience['calendar'] ?? []) as $item)
                            <div class="prazzu80-list-row">
                                <div><strong>{{ $item['titulo'] }}</strong><span>{{ $item['empresa'] ?? '-' }}</span></div>
                                <em class="{{ ($item['is_late'] ?? false) ? 'danger' : '' }}">{{ !empty($item['data_vencimento']) ? \Carbon\Carbon::parse($item['data_vencimento'])->format('d/m/Y') : '-' }}</em>
                            </div>
                        @empty
                            <div class="prazzu80-empty">Nenhuma entrega com vencimento.</div>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="prazzu80-grid three">
                <article class="prazzu80-card">
                    <header><div><h2>Wiki / documentos</h2><p>Documentos, manuais, contratos e links úteis do projeto.</p></div></header>
                    <div class="prazzu80-list compact">
                        @forelse (($portalExperience['documents'] ?? []) as $doc)
                            <div class="prazzu80-note"><strong>{{ $doc['nome_original'] ?? $doc['nome'] ?? $doc['titulo'] ?? 'Documento' }}</strong><p>{{ $doc['titulo'] ?? 'Documento do portal' }}</p></div>
                        @empty
                            <div class="prazzu80-empty">Nenhum documento disponível.</div>
                        @endforelse
                    </div>
                </article>

                <article class="prazzu80-card">
                    <header><div><h2>Atas de reunião</h2><p>Decisões de calls e alinhamentos compartilhados com o cliente.</p></div></header>
                    <div class="prazzu80-list compact">
                        @forelse (($portalExperience['meetingNotes'] ?? []) as $note)
                            <div class="prazzu80-note"><strong>{{ $note['titulo'] ?? 'Ata' }}</strong><p>{{ \Illuminate\Support\Str::limit($note['descricao'] ?? ($note['item_titulo'] ?? '-'), 120) }}</p></div>
                        @empty
                            <div class="prazzu80-empty">Nenhuma ata registrada.</div>
                        @endforelse
                    </div>
                </article>

                <article class="prazzu80-card">
                    <header><div><h2>Suporte / solicitações</h2><p>Pedidos do cliente caem na fila de trabalho sem se perder no WhatsApp.</p></div></header>
                    <form wire:submit.prevent="criarSolicitacaoSuporte" class="prazzu80-form single">
                        <label>
                            <span>Cliente</span>
                            <select wire:model="supportEmpresaId">
                                <option value="">Selecione</option>
                                @foreach (($portalFormOptions['empresas'] ?? []) as $empresa)
                                    <option value="{{ $empresa['id'] }}">{{ $empresa['name'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>Título da solicitação</span>
                            <input type="text" wire:model.defer="supportTitulo" placeholder="Ex: Solicitar ajuste no projeto">
                        </label>
                        <label>
                            <span>Prazo desejado</span>
                            <input type="date" wire:model.defer="supportDataVencimento">
                        </label>
                        <label>
                            <span>Descrição</span>
                            <textarea wire:model.defer="supportDescricao" rows="3" placeholder="Explique a solicitação do cliente"></textarea>
                        </label>
                        <button type="submit">Enviar solicitação</button>
                    </form>
                    <div class="prazzu80-list compact">
                        @forelse (($portalExperience['supportQueue'] ?? []) as $item)
                            <div class="prazzu80-note"><strong>{{ $item['titulo'] ?? 'Solicitação' }}</strong><p>{{ ucfirst($item['status'] ?? '-') }} · {{ $item['is_late'] ?? false ? 'Atrasado' : 'Dentro do fluxo' }}</p></div>
                        @empty
                            <div class="prazzu80-empty">Nenhuma solicitação de suporte.</div>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="prazzu80-grid two">
                <article class="prazzu80-card">
                    <header><div><h2>Chat do projeto</h2><p>Conversas recentes centralizadas no item, evitando mensagens soltas.</p></div></header>
                    <form wire:submit.prevent="enviarMensagemChat" class="prazzu80-form single">
                        <label>
                            <span>Item do portal</span>
                            <select wire:model="chatItemId">
                                <option value="">Selecione</option>
                                @foreach (($portalFormOptions['portalItems'] ?? []) as $itemOption)
                                    <option value="{{ $itemOption['id'] }}">{{ $itemOption['name'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>Mensagem</span>
                            <textarea wire:model.defer="chatMensagem" rows="3" placeholder="Digite uma mensagem para o histórico do projeto"></textarea>
                        </label>
                        <button type="submit">Enviar mensagem</button>
                    </form>
                    <div class="prazzu80-list compact">
                        @forelse (($portalExperience['chat'] ?? []) as $message)
                            <div class="prazzu80-note"><strong>{{ $message['titulo'] ?? 'Mensagem' }}</strong><p>{{ \Illuminate\Support\Str::limit($message['comentario'] ?? '-', 140) }}</p></div>
                        @empty
                            <div class="prazzu80-empty">Nenhuma conversa registrada.</div>
                        @endforelse
                    </div>
                </article>

                <article class="prazzu80-card">
                    <header><div><h2>O que o cliente enxerga</h2><p>Portal limpo, sem tarefas internas da equipe.</p></div></header>
                    <div class="prazzu80-feature-grid">
                        <div class="prazzu80-feature ok"><strong>Progresso</strong><span>Porcentagem geral do projeto.</span></div>
                        <div class="prazzu80-feature ok"><strong>Revisão</strong><span>Somente itens liberados ou em aprovação.</span></div>
                        <div class="prazzu80-feature ok"><strong>Documentos</strong><span>Anexos e wiki do projeto.</span></div>
                        <div class="prazzu80-feature ok"><strong>Atas</strong><span>Histórico de decisões.</span></div>
                        <div class="prazzu80-feature ok"><strong>Calendário</strong><span>Datas de entrega e vencimentos.</span></div>
                        <div class="prazzu80-feature ok"><strong>Suporte e chat</strong><span>Solicitações e mensagens centralizadas.</span></div>
                    </div>
                </article>
            </section>
        @else

        <section class="prazzu80-card">
            <header><div><h2>Primeiros passos / Empty state guiado</h2><p>O usuário sempre sabe o que fazer para deixar o módulo pronto.</p></div></header>
            <div class="prazzu80-onboarding">
                @foreach (($onboarding ?? []) as $step)
                    <div class="{{ ($step['done'] ?? false) ? 'done' : 'todo' }}">
                        <strong>{{ ($step['done'] ?? false) ? '✓' : '•' }} {{ $step['title'] }}</strong>
                        <span>{{ $step['hint'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="prazzu80-grid two">
            <article class="prazzu80-card">
                <header><div><h2>Funcionalidades enterprise</h2><p>Checklist do roadmap interno, sem depender de API externa.</p></div></header>
                <div class="prazzu80-feature-grid">
                    @foreach (($features ?? []) as $feature)
                        <div class="prazzu80-feature {{ ($feature['status'] ?? '') === 'ativo' ? 'ok' : 'todo' }}">
                            <strong>{{ $feature['name'] }}</strong>
                            <span>{{ ($feature['status'] ?? '') === 'ativo' ? 'Ativo' : 'Pendente de tabela/SQL' }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Permissões e governança</h2><p>Controle por perfil, cliente, módulo, ação e área sensível.</p></div></header>
                <div class="prazzu80-permissions">
                    @foreach (($permissions ?? []) as $permission)
                        <div><strong>{{ $permission['area'] }}</strong><span>{{ $permission['level'] }}</span></div>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="prazzu80-grid two">
            <article class="prazzu80-card">
                <header><div><h2>KPIs executivos</h2><p>Indicadores de operação, SLA, documentos, produtividade e cobrança.</p></div></header>
                <div class="prazzu80-kpi-grid">
                    @foreach (($kpis ?? []) as $kpi)
                        <div><span>{{ $kpi['label'] }}</span><strong>{{ $kpi['value'] }}</strong></div>
                    @endforeach
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Compliance engine</h2><p>Alertas internos de vencimento, SLA, contrato, responsável e risco.</p></div></header>
                <div class="prazzu80-list compact">
                    @foreach (($compliance ?? []) as $row)
                        <div class="prazzu80-list-row">
                            <div><strong>{{ $row['label'] }}</strong><span>{{ ucfirst($row['state'] ?? 'neutral') }}</span></div>
                            <em class="{{ ($row['state'] ?? '') === 'danger' ? 'danger' : '' }}">{{ $row['value'] }}</em>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="prazzu80-card">
            <header><div><h2>Kanban operacional</h2><p>Fluxo com status, prioridade, SLA, vencimento, bloqueio e responsável.</p></div></header>
            <div class="prazzu80-kanban">
                @foreach (($kanban ?? []) as $column)
                    <div class="prazzu80-kanban-column">
                        <div class="prazzu80-kanban-title"><strong>{{ $column['label'] }}</strong><span>{{ count($column['items'] ?? []) }}</span></div>
                        @forelse (($column['items'] ?? []) as $item)
                            <div class="prazzu80-task-card {{ ($item['is_blocked'] ?? false) ? 'blocked' : '' }}">
                                <strong>{{ $item['titulo'] ?? 'Sem título' }}</strong>
                                <p>{{ $item['empresa'] ?? '-' }}</p>
                                <div class="prazzu80-task-meta">
                                    <span class="priority">{{ ucfirst($item['prioridade'] ?? 'normal') }}</span>
                                    <span class="sla {{ $item['sla_state'] ?? 'sem_sla' }}">SLA {{ str_replace('_', ' ', $item['sla_state'] ?? 'sem SLA') }}</span>
                                    @if (($item['is_blocked'] ?? false))<span class="blocked">Bloqueado</span>@endif
                                </div>
                                <div class="prazzu80-progress"><i style="width: {{ $item['progress'] ?? 0 }}%"></i></div>
                            </div>
                        @empty
                            <div class="prazzu80-empty-small">Nenhum item neste status.</div>
                        @endforelse
                    </div>
                @endforeach
            </div>
        </section>

        <section class="prazzu80-grid two">
            <article class="prazzu80-card">
                <header><div><h2>Calendário e vencimentos</h2><p>Datas críticas, contratos, documentos, SLA e cobranças.</p></div></header>
                <div class="prazzu80-list">
                    @forelse (($calendar ?? []) as $item)
                        <div class="prazzu80-list-row">
                            <div><strong>{{ $item['titulo'] }}</strong><span>{{ $item['empresa'] ?? '-' }}</span></div>
                            <em class="{{ ($item['is_late'] ?? false) ? 'danger' : '' }}">{{ !empty($item['data_vencimento']) ? \Carbon\Carbon::parse($item['data_vencimento'])->format('d/m/Y') : '-' }}</em>
                        </div>
                    @empty
                        <div class="prazzu80-empty">Nenhum vencimento encontrado.</div>
                    @endforelse
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Gantt real / planejamento</h2><p>Barras por prazo, progresso, bloqueios e dependências.</p></div></header>
                <div class="prazzu80-gantt">
                    @forelse (($gantt ?? []) as $row)
                        <div class="prazzu80-gantt-row">
                            <div><strong>{{ $row['title'] }}</strong><span>{{ $row['start'] }} → {{ $row['end'] }} · {{ ucfirst(str_replace('_', ' ', $row['status'] ?? '')) }}</span></div>
                            <div><div class="prazzu80-gantt-bar"><i style="width: {{ $row['progress'] ?? 0 }}%"></i></div><small>{{ $row['empresa'] ?? '-' }} {{ ($row['is_blocked'] ?? false) ? '· bloqueado' : '' }}</small></div>
                        </div>
                    @empty
                        <div class="prazzu80-empty">Nenhum item para Gantt.</div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="prazzu80-grid two">
            <article class="prazzu80-card">
                <header><div><h2>Timeline operacional / auditoria</h2><p>Alterações, comentários, documentos, aprovações e evidências.</p></div></header>
                <div class="prazzu80-timeline">
                    @forelse (($timeline ?? []) as $event)
                        <div>
                            <b>{{ $event['titulo'] ?? 'Evento' }}</b>
                            <p>{{ \Illuminate\Support\Str::limit($event['descricao'] ?? ($event['item_titulo'] ?? '-'), 140) }}</p>
                            <small>{{ $event['tipo'] ?? 'timeline' }} · {{ !empty($event['created_at']) ? \Carbon\Carbon::parse($event['created_at'])->format('d/m/Y H:i') : '-' }}</small>
                        </div>
                    @empty
                        <div class="prazzu80-empty">Nenhum evento registrado.</div>
                    @endforelse
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Dependências visuais</h2><p>O usuário entende o que bloqueia cada entrega.</p></div></header>
                <div class="prazzu80-list compact">
                    @forelse (($dependencies ?? []) as $dep)
                        <div class="prazzu80-note"><strong>{{ $dep['atual'] ?? 'Item' }}</strong><p>Depende de: {{ $dep['depende'] ?? '-' }} · {{ $dep['type'] ?? 'finish_to_start' }}</p></div>
                    @empty
                        <div class="prazzu80-empty">Nenhuma dependência cadastrada.</div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="prazzu80-grid three">
            <article class="prazzu80-card">
                <header><div><h2>Central de aprovações</h2><p>Fila única de documentos, contratos e processos.</p></div></header>
                <div class="prazzu80-list compact">
                    @forelse (($approvals ?? []) as $approval)
                        <div class="prazzu80-note"><strong>{{ $approval['titulo'] ?? 'Aprovação' }}</strong><p>{{ ucfirst($approval['status'] ?? 'pendente') }} · {{ $approval['nome_fantasia'] ?? $approval['razao_social'] ?? '-' }}</p></div>
                    @empty
                        <div class="prazzu80-empty">Nenhuma aprovação pendente.</div>
                    @endforelse
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Gestão documental</h2><p>Arquivos, versões, aprovações e histórico.</p></div></header>
                <div class="prazzu80-list compact">
                    @forelse (($documents ?? []) as $doc)
                        <div class="prazzu80-note"><strong>{{ $doc['nome_original'] ?? $doc['nome'] ?? $doc['titulo'] ?? 'Documento' }}</strong><p>{{ $doc['titulo'] ?? 'Sem vínculo' }}</p></div>
                    @empty
                        <div class="prazzu80-empty">Nenhum documento.</div>
                    @endforelse
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Workflow documental</h2><p>Rascunho, aprovação, validade e vencimento.</p></div></header>
                <div class="prazzu80-list compact">
                    @foreach (($documentWorkflow ?? []) as $wf)
                        <div class="prazzu80-list-row"><div><strong>{{ $wf['label'] }}</strong><span>Documentos/processos</span></div><em>{{ $wf['count'] }}</em></div>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="prazzu80-grid three">
            <article class="prazzu80-card">
                <header><div><h2>Comentários e menções</h2><p>Colaboração operacional por item.</p></div></header>
                <div class="prazzu80-list compact">
                    @forelse (($comments ?? []) as $comment)
                        <div class="prazzu80-note"><strong>{{ $comment['titulo'] ?? 'Comentário' }}</strong><p>{{ \Illuminate\Support\Str::limit($comment['comentario'] ?? '-', 120) }}</p></div>
                    @empty
                        <div class="prazzu80-empty">Nenhum comentário.</div>
                    @endforelse
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Notificações internas</h2><p>Badge/polling visual sem websocket externo.</p></div></header>
                <div class="prazzu80-list compact">
                    @forelse (($notifications ?? []) as $notification)
                        <div class="prazzu80-note"><strong>{{ $notification['titulo'] ?? $notification['type'] ?? 'Notificação' }}</strong><p>{{ \Illuminate\Support\Str::limit($notification['mensagem'] ?? $notification['data'] ?? '-', 120) }}</p></div>
                    @empty
                        <div class="prazzu80-empty">Nenhuma notificação.</div>
                    @endforelse
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Time tracking</h2><p>Horas registradas e produtividade.</p></div></header>
                <div class="prazzu80-list compact">
                    @forelse (($timeTracking ?? []) as $time)
                        <div class="prazzu80-note"><strong>{{ $time['titulo'] ?? 'Registro de tempo' }}</strong><p>{{ number_format(($time['total_seconds'] ?? 0) / 60, 0, ',', '.') }} min · {{ $time['notes'] ?? '-' }}</p></div>
                    @empty
                        <div class="prazzu80-empty">Nenhum tempo registrado.</div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="prazzu80-grid two">
            <article class="prazzu80-card">
                <header><div><h2>Builder de automação visual</h2><p>Regras SE/ENTÃO usando somente dados internos.</p></div></header>
                <div class="prazzu80-list compact">
                    @foreach (($automationBuilder ?? []) as $rule)
                        <div class="prazzu80-note"><strong>SE {{ $rule['if'] }}</strong><p>ENTÃO {{ $rule['then'] }} · {{ ($rule['active'] ?? false) ? 'ativo' : 'inativo' }}</p></div>
                    @endforeach
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Cobrança inteligente interna</h2><p>Status financeiro, vencimento, régua, bloqueio e recuperação.</p></div></header>
                <div class="prazzu80-list compact">
                    @forelse (($billing ?? []) as $pay)
                        <div class="prazzu80-list-row"><div><strong>{{ $pay['nome_fantasia'] ?? $pay['razao_social'] ?? 'Cliente' }}</strong><span>{{ $pay['status'] ?? '-' }} · {{ !empty($pay['vencimento']) ? \Carbon\Carbon::parse($pay['vencimento'])->format('d/m/Y') : '-' }}</span></div><em>R$ {{ number_format((float)($pay['valor'] ?? 0), 2, ',', '.') }}</em></div>
                    @empty
                        <div class="prazzu80-empty">Nenhuma cobrança encontrada.</div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="prazzu80-grid two">
            <article class="prazzu80-card">
                <header><div><h2>Relatórios executivos</h2><p>Modelos gerenciais prontos para uso.</p></div></header>
                <div class="prazzu80-list compact">
                    @foreach (($reports ?? []) as $report)
                        <div class="prazzu80-note"><strong>{{ $report['title'] }}</strong><p>{{ $report['description'] }}</p></div>
                    @endforeach
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>White label</h2><p>Identidade e limites por empresa/tenant usando dados internos.</p></div></header>
                <div class="prazzu80-list compact">
                    @foreach (($whiteLabel ?? []) as $wl)
                        <div class="prazzu80-list-row"><div><strong>{{ $wl['label'] }}</strong><span>Configuração atual</span></div><em>{{ $wl['value'] }}</em></div>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="prazzu80-card">
            <header><div><h2>Tabela executiva</h2><p>Visão consolidada para gestão, auditoria e operação.</p></div></header>
            <div class="prazzu80-table-wrap">
                <table class="prazzu80-table">
                    <thead><tr><th>Item</th><th>Empresa</th><th>Tipo</th><th>Status</th><th>Prioridade</th><th>Responsável</th><th>Vencimento</th><th>SLA</th><th>Bloqueio</th></tr></thead>
                    <tbody>
                        @forelse (($items ?? []) as $item)
                            <tr>
                                <td><strong>{{ $item['titulo'] }}</strong></td>
                                <td>{{ $item['empresa'] ?? '-' }}</td>
                                <td>{{ ucfirst($item['tipo'] ?? '-') }}</td>
                                <td><span class="prazzu80-badge">{{ ucfirst(str_replace('_', ' ', $item['status'] ?? '-')) }}</span></td>
                                <td>{{ ucfirst($item['prioridade'] ?? '-') }}</td>
                                <td>{{ $item['responsavel_nome'] ?? '-' }}</td>
                                <td class="{{ ($item['is_late'] ?? false) ? 'danger' : '' }}">{{ !empty($item['data_vencimento']) ? \Carbon\Carbon::parse($item['data_vencimento'])->format('d/m/Y') : '-' }}</td>
                                <td><span class="prazzu80-badge {{ $item['sla_state'] ?? '' }}">{{ str_replace('_', ' ', $item['sla_state'] ?? 'sem SLA') }}</span></td>
                                <td>{{ ($item['is_blocked'] ?? false) ? 'Bloqueado' : 'Livre' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="prazzu80-empty">Nenhum registro encontrado. Use as ações rápidas acima para criar o primeiro item.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        @endif
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-prazzu80-page]');

            if (! root) {
                return;
            }

            const search = root.querySelector('[data-prazzu80-search]');
            const status = root.querySelector('[data-prazzu80-status]');
            const clear = root.querySelector('[data-prazzu80-clear]');
            const empty = root.querySelector('[data-prazzu80-empty]');
            const searchableSelectors = [
                '.prazzu80-card',
                '.prazzu80-stats article',
                '.prazzu80-kanban-column',
                '.prazzu80-task-card',
                '.prazzu80-list-row',
                '.prazzu80-note',
                '.prazzu80-feature',
                '.prazzu80-table tbody tr',
            ].join(',');

            const normalize = (value) => (value || '')
                .toString()
                .toLowerCase()
                .normalize('NFD')
                .replace(/[̀-ͯ]/g, '');

            const items = Array.from(root.querySelectorAll(searchableSelectors))
                .filter((item) => ! item.closest('[data-prazzu80-empty]'));

            const applyFilters = () => {
                const term = normalize(search?.value || '');
                const selected = normalize(status?.value || 'all');
                let visible = 0;

                items.forEach((item) => {
                    const text = normalize(item.textContent || '');
                    const matchesText = term === '' || text.includes(term);
                    const matchesStatus = selected === 'all' || text.includes(selected);
                    const show = matchesText && matchesStatus;

                    item.hidden = ! show;

                    if (show) {
                        visible++;
                    }
                });

                if (empty) {
                    empty.hidden = visible > 0 || items.length === 0;
                }
            };

            search?.addEventListener('input', applyFilters);
            status?.addEventListener('change', applyFilters);
            clear?.addEventListener('click', () => {
                if (search) {
                    search.value = '';
                }

                if (status) {
                    status.value = 'all';
                }

                applyFilters();
                search?.focus();
            });
        })();
    </script>

</x-filament-panels::page>
