<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/prazzu-enterprise-80.css') }}">

    @php
        $permissoesAvancadas = $advancedPermissions ?? [];
        $configuracao = $config ?? [];
    @endphp

    <div class="prazzu80-page">
        <section class="prazzu80-hero">
            <div>
                <span class="prazzu80-kicker">{{ $configuracao['group'] ?? 'CONFIGURAÇÕES' }}</span>
                <h1>{{ $configuracao['title'] ?? 'Permissões Avançadas' }}</h1>
                <p>{{ $configuracao['subtitle'] ?? 'Segurança avançada para cargos personalizados, exclusão, exportação, visibilidade, tags e status.' }}</p>
            </div>
            <div class="prazzu80-hero-actions">
                <span>Criar cargo personalizado</span>
                <span>Bloquear exclusão</span>
                <span>Bloquear exportação</span>
                <span>Definir visibilidade</span>
                <span>Controlar tags/status</span>
            </div>
        </section>

        <section class="prazzu80-search-card">
            <div>
                <strong>Segurança e governança dos dados</strong>
                <p>Defina quem pode visualizar, criar, editar, excluir, exportar e administrar padrões de workflow dentro da empresa.</p>
                <small>Filtros rápidos: Cargo · Módulo · Ação · Escopo · Exclusão · Exportação · Tags/Status</small>
            </div>
            <div class="prazzu80-search-fake">⌕ Buscar regra</div>
        </section>

        <section class="prazzu80-card">
            <header>
                <div>
                    <h2>Controles avançados</h2>
                    <p>Conteúdo focado em Permissões Avançadas: custom roles, exclusão, exportação, privacidade e workflow.</p>
                </div>
            </header>
            <div class="prazzu80-feature-grid">
                @foreach (($permissoesAvancadas['cards'] ?? []) as $card)
                    <div class="prazzu80-feature {{ ($card['status'] ?? '') === 'Configurado' ? 'ok' : 'todo' }}">
                        <strong>{{ $card['title'] }}</strong>
                        <span>{{ $card['description'] }}</span>
                        <span><b>Status:</b> {{ $card['status'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="prazzu80-grid two">
            <article class="prazzu80-card">
                <header>
                    <div>
                        <h2>Cargos personalizados (Custom Roles)</h2>
                        <p>Perfis reutilizáveis para aplicar travas específicas em usuários internos, convidados e visualizadores externos.</p>
                    </div>
                </header>
                <div class="prazzu80-list compact">
                    @forelse (($permissoesAvancadas['roles'] ?? []) as $role)
                        <div class="prazzu80-note">
                            <strong>{{ $role['name'] ?? 'Cargo' }}</strong>
                            <p>{{ $role['description'] ?? 'Sem descrição cadastrada.' }}</p>
                        </div>
                    @empty
                        <div class="prazzu80-empty">Nenhum cargo cadastrado. Execute o SQL enviado para criar prazzu_roles.</div>
                    @endforelse
                </div>
            </article>

            <article class="prazzu80-card">
                <header>
                    <div>
                        <h2>Ações sensíveis</h2>
                        <p>Regras explícitas para exclusão, exportação, visibilidade padrão e gestão de tags/status.</p>
                    </div>
                </header>
                <div class="prazzu80-list compact">
                    @forelse (($permissoesAvancadas['permissions'] ?? []) as $permission)
                        <div class="prazzu80-list-row">
                            <div>
                                <strong>{{ $permission['area'] ?? 'Permissão' }}</strong>
                                <span>{{ $permission['level'] ?? 'Sem escopo definido' }}</span>
                            </div>
                            <em>ACL</em>
                        </div>
                    @empty
                        <div class="prazzu80-empty">Nenhuma permissão explícita cadastrada.</div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="prazzu80-card">
            <header>
                <div>
                    <h2>Matriz de permissões por cargo</h2>
                    <p>Controle por módulo, ação e escopo para evitar exclusões indevidas, exportações indevidas e bagunça no workflow.</p>
                </div>
            </header>
            <div class="prazzu80-table-wrap">
                <table class="prazzu80-table">
                    <thead>
                        <tr>
                            <th>Cargo</th>
                            <th>Módulo</th>
                            <th>Visualizar</th>
                            <th>Criar</th>
                            <th>Editar</th>
                            <th>Excluir</th>
                            <th>Escopo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (($permissoesAvancadas['rules'] ?? []) as $rule)
                            <tr>
                                <td><strong>{{ $rule['role'] ?? '-' }}</strong></td>
                                <td>{{ $rule['module'] ?? '-' }}</td>
                                <td>{{ (bool) ($rule['can_view'] ?? false) ? 'Sim' : 'Não' }}</td>
                                <td>{{ (bool) ($rule['can_create'] ?? false) ? 'Sim' : 'Não' }}</td>
                                <td>{{ (bool) ($rule['can_update'] ?? false) ? 'Sim' : 'Não' }}</td>
                                <td class="{{ (bool) ($rule['can_delete'] ?? false) ? '' : 'danger' }}">{{ (bool) ($rule['can_delete'] ?? false) ? 'Sim' : 'Não' }}</td>
                                <td>{{ $rule['scope'] ?? 'empresa' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">Nenhuma regra encontrada. Execute o SQL enviado para criar prazzu_permission_rules.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
