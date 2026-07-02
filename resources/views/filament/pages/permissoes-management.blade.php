<x-filament-panels::page>
<div class="prazzu-perm-page">
        <section class="prazzu-hero">
            <h1>Perfis e Permissões</h1>
            <p>Configure perfis, matriz, vínculos de usuários, exceções, relatório efetivo e auditoria em etapas separadas para reduzir scroll e evitar erro operacional.</p>
        </section>

        @if ($activeTab === 'perfis')
            <section class="prazzu-readiness">
                @foreach ($readiness as $item)
                    <div class="{{ $item['ok'] ? 'ok' : 'warn' }}">
                        <strong>{{ $item['label'] }}</strong><br>
                        <span class="prazzu-muted">{{ $item['ok'] ? 'Pronto' : 'Pendente' }}</span>
                    </div>
                @endforeach
            </section>

            <section class="prazzu-kpi">
                <div><span class="prazzu-muted">Perfis cadastrados</span><br><strong>{{ $tabCounts['roles'] ?? 0 }}</strong></div>
                <div><span class="prazzu-muted">Perfis no usuário selecionado</span><br><strong>{{ $tabCounts['userRoles'] ?? 0 }}</strong></div>
                <div><span class="prazzu-muted">Exceções do usuário</span><br><strong>{{ $tabCounts['overrides'] ?? 0 }}</strong></div>
            </section>

            <section class="prazzu-grid two">
                <article class="prazzu-card">
                    <h2>Criar ou atualizar perfil de acesso</h2>
                    <p>Nomeie o perfil pelo cargo ou responsabilidade. Depois vá para Matriz para escolher as permissões.</p>
                    <form wire:submit.prevent="createRole" style="margin-top:14px">
                        <div class="prazzu-form-grid">
                            <label><span class="prazzu-muted">Nome do perfil</span><input class="prazzu-input" type="text" wire:model.defer="roleName" placeholder="Ex.: Supervisor Fiscal"></label>
                            <label><span class="prazzu-muted">Status</span><select class="prazzu-select" wire:model.defer="roleActive"><option value="1">Ativo</option><option value="0">Inativo</option></select></label>
                        </div>
                        <label style="display:block;margin-top:12px"><span class="prazzu-muted">Descrição</span><textarea class="prazzu-textarea" wire:model.defer="roleDescription" placeholder="Explique quando esse perfil deve ser usado."></textarea></label>
                        <button class="prazzu-button" type="submit" style="margin-top:14px">Salvar perfil</button>
                    </form>
                </article>

                <article class="prazzu-card">
                    <h2>Perfis cadastrados</h2>
                    <p>Selecione um perfil para editar a matriz.</p>
                    <div class="prazzu-toolbar">
                        <label style="min-width:260px;flex:1"><span class="prazzu-muted">Buscar perfil</span><input class="prazzu-input" type="search" wire:model.live.debounce.350ms="roleSearch" placeholder="Nome ou descrição"></label>
                    </div>
                    <div class="prazzu-role-list" style="margin-top:14px">
                        @forelse ($roles as $role)
                            @php($stats = $roleStats[$role->id] ?? ['active' => 0, 'total' => 0, 'percent' => 0])
                            <div class="prazzu-role-item {{ (int) $selectedRoleId === (int) $role->id ? 'active' : '' }}" wire:key="role-{{ $role->id }}-{{ (int) $role->active }}">
                                <label class="prazzu-role-body">
                                    <input type="radio" wire:model.live="selectedRoleId" value="{{ $role->id }}">
                                    <strong>{{ $role->name }}</strong>
                                    <span class="prazzu-badge {{ $role->active ? 'green' : 'red' }}">{{ $role->active ? 'Ativo' : 'Inativo' }}</span>
                                    <p>{{ $role->description ?: 'Sem descrição.' }}</p>
                                    <div class="prazzu-progress"><span style="width: {{ $stats['percent'] }}%"></span></div>
                                    <span class="prazzu-muted">{{ $stats['active'] }} de {{ $stats['total'] }} permissões marcadas</span>
                                </label>
                                <div class="prazzu-role-footer">
                                    <button class="prazzu-button secondary" type="button" wire:click="toggleRoleStatus({{ $role->id }})" wire:loading.attr="disabled" wire:target="toggleRoleStatus({{ $role->id }})">
                                        <span wire:loading.remove wire:target="toggleRoleStatus({{ $role->id }})">{{ $role->active ? 'Desativar' : 'Ativar' }}</span>
                                        <span wire:loading wire:target="toggleRoleStatus({{ $role->id }})">Atualizando...</span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="prazzu-empty">Nenhum perfil encontrado.</div>
                        @endforelse
                    </div>
                </article>
            </section>
        @endif

        @if ($activeTab === 'matriz')
            <section class="prazzu-card">
                <h2>Matriz de permissões por perfil</h2>
                <p>Perfil selecionado: <strong>{{ $selectedRole?->name ?? 'nenhum' }}</strong>. Use os atalhos para reduzir cliques e salve no final.</p>
                <div class="prazzu-toolbar">
                    <label style="min-width:240px"><span class="prazzu-muted">Filtrar módulo</span><select class="prazzu-select" wire:model.live="matrixModuleFilter"><option value="all">Todos os módulos</option>@foreach ($modules as $moduleKey => $moduleLabel)<option value="{{ $moduleKey }}">{{ $moduleLabel }}</option>@endforeach</select></label>
                    <div class="prazzu-actions">
                        <button class="prazzu-button light" type="button" wire:click="applyMatrixPreset('read')">Somente leitura</button>
                        <button class="prazzu-button light" type="button" wire:click="applyMatrixPreset('operational')">Operacional</button>
                        <button class="prazzu-button light" type="button" wire:click="applyMatrixPreset('manager')">Gestor</button>
                        <button class="prazzu-button secondary" type="button" wire:click="applyMatrixPreset('all')">Marcar tudo</button>
                        <button class="prazzu-button danger" type="button" wire:click="applyMatrixPreset('clear')">Limpar</button>
                    </div>
                </div>
                <div class="prazzu-table-wrap" style="margin-top:14px">
                    <table class="prazzu-matrix">
                        <thead><tr><th>Módulo</th>@foreach ($actions as $actionKey => $actionLabel)<th>{{ $actionLabel }}</th>@endforeach</tr></thead>
                        <tbody>
                            @foreach ($visibleMatrix as $moduleKey => $availableActions)
                                <tr wire:key="matrix-row-{{ $moduleKey }}">
                                    <td><strong>{{ $modules[$moduleKey] ?? $moduleKey }}</strong><br><span class="prazzu-muted">{{ $moduleKey }}</span><br><span class="prazzu-actions" style="margin-top:8px"><button class="prazzu-button light" type="button" wire:click="applyMatrixPreset('read','{{ $moduleKey }}')">Leitura</button><button class="prazzu-button light" type="button" wire:click="applyMatrixPreset('all','{{ $moduleKey }}')">Tudo</button><button class="prazzu-button light" type="button" wire:click="applyMatrixPreset('clear','{{ $moduleKey }}')">Limpar</button></span></td>
                                    @foreach ($actions as $actionKey => $actionLabel)
                                        <td class="prazzu-check-cell">@if (in_array($actionKey, $availableActions, true))<label title="{{ $modules[$moduleKey] ?? $moduleKey }}: {{ $actionLabel }}"><input type="checkbox" wire:model.defer="rolePermissions.{{ $moduleKey }}.{{ $actionKey }}"></label>@else<span class="prazzu-muted">—</span>@endif</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button class="prazzu-button" type="button" wire:click="saveRolePermissions" style="margin-top:14px">Salvar matriz do perfil</button>
            </section>
        @endif

        @if ($activeTab === 'usuarios')
            <section class="prazzu-card">
                <h2>Vincular perfil ao usuário</h2>
                <p>Pesquise o usuário, escolha o perfil e vincule. A lista já respeita a empresa do usuário logado.</p>
                <div class="prazzu-toolbar"><label style="min-width:280px;flex:1"><span class="prazzu-muted">Buscar usuário</span><input class="prazzu-input" type="search" wire:model.live.debounce.350ms="userSearch" placeholder="Nome ou e-mail"></label></div>
                <div class="prazzu-form-grid" style="margin-top:14px">
                    <label><span class="prazzu-muted">Usuário</span><select class="prazzu-select" wire:model.live="selectedUserId">@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }} — {{ $user->email }}</option>@endforeach</select></label>
                    <label><span class="prazzu-muted">Perfil</span><select class="prazzu-select" wire:model.defer="assignRoleId">@foreach ($roles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select></label>
                </div>
                <button class="prazzu-button" type="button" wire:click="assignRoleToUser" style="margin-top:14px">Vincular perfil</button>
                <div class="prazzu-table-wrap" style="margin-top:14px"><table class="prazzu-table"><thead><tr><th>Perfil vinculado</th><th>Status</th><th>Ação</th></tr></thead><tbody>@forelse ($userRoles as $userRole)<tr><td><strong>{{ $userRole->role?->name ?? 'Perfil removido' }}</strong></td><td><span class="prazzu-badge {{ $userRole->role?->active ? 'green' : 'red' }}">{{ $userRole->role?->active ? 'Ativo' : 'Inativo' }}</span></td><td><button class="prazzu-button danger" type="button" wire:click="removeUserRole({{ $userRole->id }})">Remover</button></td></tr>@empty<tr><td colspan="3" class="prazzu-empty">Usuário sem perfil avançado vinculado.</td></tr>@endforelse</tbody></table></div>
            </section>
        @endif

        @if ($activeTab === 'excecoes')
            <section class="prazzu-card">
                <h2>Exceção individual</h2>
                <p>Use somente para liberar ou bloquear uma ação específica para o usuário selecionado.</p>
                <div class="prazzu-form-grid" style="margin-top:14px">
                    <label><span class="prazzu-muted">Usuário</span><select class="prazzu-select" wire:model.live="selectedUserId">@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }} — {{ $user->email }}</option>@endforeach</select></label>
                    <label><span class="prazzu-muted">Módulo</span><select class="prazzu-select" wire:model.live="overrideModule">@foreach ($modules as $moduleKey => $moduleLabel)<option value="{{ $moduleKey }}">{{ $moduleLabel }}</option>@endforeach</select></label>
                    <label><span class="prazzu-muted">Ação</span><select class="prazzu-select" wire:model.defer="overrideAction">@foreach ($overrideActions as $actionKey)<option value="{{ $actionKey }}">{{ $actions[$actionKey] ?? $actionKey }}</option>@endforeach</select></label>
                    <label><span class="prazzu-muted">Escopo</span><select class="prazzu-select" wire:model.defer="overrideScope">@foreach ($scopeLabels as $scopeKey => $scopeLabel)<option value="{{ $scopeKey }}">{{ $scopeLabel }}</option>@endforeach</select></label>
                    <label><span class="prazzu-muted">Tipo</span><select class="prazzu-select" wire:model.defer="overrideAllowed"><option value="1">Permitir</option><option value="0">Bloquear</option></select></label>
                    <label><span class="prazzu-muted">Motivo</span><input class="prazzu-input" type="text" wire:model.defer="overrideReason" placeholder="Ex.: liberação temporária"></label>
                </div>
                <button class="prazzu-button" type="button" wire:click="saveUserOverride" style="margin-top:14px">Salvar exceção</button>
                <div class="prazzu-table-wrap" style="margin-top:14px"><table class="prazzu-table"><thead><tr><th>Permissão</th><th>Tipo</th><th>Motivo</th><th>Ação</th></tr></thead><tbody>@forelse ($userOverrides as $override)<tr><td><strong>{{ $modules[$override->module] ?? $override->module }}</strong><br><span class="prazzu-muted">{{ $override->module }}.{{ $override->action }} / {{ $override->scope }}</span></td><td><span class="prazzu-badge {{ $override->allowed ? 'green' : 'red' }}">{{ $override->allowed ? 'Permitir' : 'Bloquear' }}</span></td><td>{{ $override->reason ?? '-' }}</td><td><button class="prazzu-button danger" type="button" wire:click="removeUserOverride({{ $override->id }})">Remover</button></td></tr>@empty<tr><td colspan="4" class="prazzu-empty">Nenhuma exceção individual para o usuário selecionado.</td></tr>@endforelse</tbody></table></div>
            </section>
        @endif

        @if ($activeTab === 'relatorio')
            <section class="prazzu-card"><h2>Relatório efetivo do usuário</h2><p>Resultado final após somar perfil avançado, exceções individuais e fallback.</p><div class="prazzu-toolbar"><label style="min-width:280px;flex:1"><span class="prazzu-muted">Usuário</span><select class="prazzu-select" wire:model.live="selectedUserId">@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }} — {{ $user->email }}</option>@endforeach</select></label></div><div class="prazzu-table-wrap" style="margin-top:14px"><table class="prazzu-table"><thead><tr><th>Módulo</th><th>Ação</th><th>Escopo</th><th>Resultado</th><th>Origem</th><th>Perfis</th></tr></thead><tbody>@forelse ($effectivePermissions as $row)<tr><td><strong>{{ $modules[$row['module']] ?? $row['module'] }}</strong><br><span class="prazzu-muted">{{ $row['module'] }}</span></td><td>{{ $actions[$row['action']] ?? $row['action'] }}</td><td>{{ $scopeLabels[$row['scope']] ?? $row['scope'] }}</td><td><span class="prazzu-badge {{ $row['allowed'] ? 'green' : 'red' }}">{{ $row['allowed'] ? 'Permitido' : 'Bloqueado' }}</span></td><td>{{ $row['source'] }}</td><td>{{ $row['roles'] }}</td></tr>@empty<tr><td colspan="6" class="prazzu-empty">Selecione um usuário para ver o relatório efetivo.</td></tr>@endforelse</tbody></table></div></section>
        @endif

        @if ($activeTab === 'auditoria')
            <section class="prazzu-card"><h2>Auditoria de permissões</h2><p>Registra criação de perfil, alterações de matriz, vínculos e exceções.</p><div class="prazzu-toolbar"><label style="min-width:240px"><span class="prazzu-muted">Filtrar evento</span><select class="prazzu-select" wire:model.live="auditEventFilter"><option value="all">Todos</option><option value="role.">Perfis</option><option value="user.role.">Vínculos</option><option value="user.override.">Exceções</option></select></label></div><div class="prazzu-table-wrap" style="margin-top:14px"><table class="prazzu-table"><thead><tr><th>Data</th><th>Evento</th><th>Autor</th><th>Usuário alvo</th><th>Perfil</th><th>Permissão</th><th>Motivo</th></tr></thead><tbody>@forelse ($permissionAudits as $audit)<tr><td>{{ $audit->created_at?->format('d/m/Y H:i') }}</td><td><strong>{{ $audit->event_label }}</strong><br><span class="prazzu-muted">{{ $audit->event }}</span></td><td>{{ $audit->actor?->name ?? '-' }}</td><td>{{ $audit->targetUser?->name ?? '-' }}</td><td>{{ $audit->role?->name ?? '-' }}</td><td>{{ $audit->module ? $audit->module . '.' . $audit->action : '-' }}</td><td>{{ $audit->reason ?? '-' }}</td></tr>@empty<tr><td colspan="7" class="prazzu-empty">Nenhum evento de permissão registrado ainda.</td></tr>@endforelse</tbody></table></div></section>

            <section class="prazzu-card"><h2>Regras antigas sincronizadas</h2><p>Compatibilidade com <code>prazzu_permission_rules</code>. Fica na aba Auditoria para não poluir a configuração principal.</p><div class="prazzu-table-wrap" style="margin-top:14px"><table class="prazzu-table"><thead><tr><th>Perfil</th><th>Módulo</th><th>Ver</th><th>Criar</th><th>Editar</th><th>Excluir</th><th>Escopo</th></tr></thead><tbody>@forelse ($legacyRules as $rule)<tr><td><strong>{{ $rule->role }}</strong></td><td>{{ $rule->module }}</td><td>{{ $rule->can_view ? 'Sim' : 'Não' }}</td><td>{{ $rule->can_create ? 'Sim' : 'Não' }}</td><td>{{ $rule->can_update ? 'Sim' : 'Não' }}</td><td>{{ $rule->can_delete ? 'Sim' : 'Não' }}</td><td>{{ $rule->scope }}</td></tr>@empty<tr><td colspan="7" class="prazzu-empty">Nenhuma regra antiga encontrada.</td></tr>@endforelse</tbody></table></div></section>
        @endif
    </div>
</x-filament-panels::page>
