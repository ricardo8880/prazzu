<x-filament-panels::page>
    <style>
        .prazzu-perm-page{display:grid;gap:20px}.prazzu-hero{border-radius:24px;padding:24px;background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff}.prazzu-hero h1{margin:0;font-size:28px;font-weight:900}.prazzu-hero p{margin:8px 0 0;color:#cbd5e1;max-width:980px}.prazzu-grid{display:grid;gap:16px}.prazzu-grid.two{grid-template-columns:1.1fr .9fr}.prazzu-grid.three{grid-template-columns:repeat(3,minmax(0,1fr))}.prazzu-card{border:1px solid #e5e7eb;border-radius:20px;background:#fff;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.06)}.prazzu-card h2{font-size:18px;font-weight:850;margin:0}.prazzu-card p{color:#64748b;margin:6px 0 0}.prazzu-input,.prazzu-select,.prazzu-textarea{width:100%;border:1px solid #d1d5db;border-radius:12px;padding:10px 12px;background:#fff}.prazzu-textarea{min-height:78px}.prazzu-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.prazzu-button{border:0;border-radius:12px;padding:10px 14px;background:#111827;color:#fff;font-weight:800;cursor:pointer}.prazzu-button.secondary{background:#334155}.prazzu-button.danger{background:#b91c1c}.prazzu-muted{color:#64748b;font-size:12px}.prazzu-badge{display:inline-flex;align-items:center;gap:4px;border-radius:999px;background:#eef2ff;color:#3730a3;padding:4px 10px;font-size:12px;font-weight:800}.prazzu-badge.green{background:#dcfce7;color:#166534}.prazzu-badge.red{background:#fee2e2;color:#991b1b}.prazzu-table-wrap{overflow:auto}.prazzu-table{width:100%;border-collapse:collapse}.prazzu-table th,.prazzu-table td{padding:12px;border-bottom:1px solid #e5e7eb;text-align:left;vertical-align:middle}.prazzu-table th{font-size:12px;text-transform:uppercase;color:#64748b}.prazzu-empty{padding:18px;text-align:center;color:#64748b}.prazzu-role-list{display:grid;gap:10px}.prazzu-role-item{display:grid;grid-template-columns:1fr auto;gap:10px;border:1px solid #e5e7eb;border-radius:16px;padding:12px;background:#f8fafc}.prazzu-role-item.active{border-color:#bfdbfe;background:#eff6ff}.prazzu-progress{height:8px;background:#e5e7eb;border-radius:999px;overflow:hidden;margin-top:8px}.prazzu-progress span{display:block;height:100%;background:#111827}.prazzu-matrix{width:100%;border-collapse:separate;border-spacing:0}.prazzu-matrix th,.prazzu-matrix td{border-bottom:1px solid #e5e7eb;padding:11px;text-align:center}.prazzu-matrix th:first-child,.prazzu-matrix td:first-child{text-align:left;position:sticky;left:0;background:#fff}.prazzu-check-cell label{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border:1px solid #d1d5db;border-radius:10px;background:#f8fafc}.prazzu-check-cell input{width:16px;height:16px}.prazzu-readiness{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px}.prazzu-readiness div{border:1px solid #e5e7eb;border-radius:14px;padding:12px;background:#f8fafc}.prazzu-readiness .ok{border-color:#bbf7d0;background:#f0fdf4}.prazzu-readiness .warn{border-color:#fde68a;background:#fffbeb}@media(max-width:1100px){.prazzu-grid.two,.prazzu-grid.three,.prazzu-form-grid,.prazzu-readiness{grid-template-columns:1fr}.prazzu-matrix th:first-child,.prazzu-matrix td:first-child{position:static}}
    </style>

    <div class="prazzu-perm-page">
        <section class="prazzu-hero">
            <h1>Perfis e Permissões</h1>
            <p>Controle avançado além do perfil simples: defina o que cada perfil pode ver, criar, editar, excluir, aprovar, cancelar, responder, encerrar, exportar e reatribuir. Este lote cria a interface de configuração; a aplicação prática nas telas vem no lote 3.</p>
        </section>

        <section class="prazzu-readiness">
            @foreach ($readiness as $item)
                <div class="{{ $item['ok'] ? 'ok' : 'warn' }}">
                    <strong>{{ $item['label'] }}</strong><br>
                    <span class="prazzu-muted">{{ $item['ok'] ? 'Pronto' : 'Pendente' }}</span>
                </div>
            @endforeach
        </section>

        <section class="prazzu-grid two">
            <article class="prazzu-card">
                <h2>Criar ou atualizar perfil de acesso</h2>
                <p>Use para perfis como Gerente, Supervisor, Analista, Assistente, Cliente, Auditor ou qualquer perfil interno do escritório.</p>
                <form wire:submit.prevent="createRole" style="margin-top:14px">
                    <div class="prazzu-form-grid">
                        <label>
                            <span class="prazzu-muted">Nome do perfil</span>
                            <input class="prazzu-input" type="text" wire:model.defer="roleName" placeholder="Ex.: Supervisor Fiscal">
                        </label>
                        <label>
                            <span class="prazzu-muted">Status</span>
                            <select class="prazzu-select" wire:model.defer="roleActive">
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                            </select>
                        </label>
                    </div>
                    <label style="display:block;margin-top:12px">
                        <span class="prazzu-muted">Descrição</span>
                        <textarea class="prazzu-textarea" wire:model.defer="roleDescription" placeholder="Explique quando esse perfil deve ser usado."></textarea>
                    </label>
                    <button class="prazzu-button" type="submit" style="margin-top:14px">Salvar perfil</button>
                </form>
            </article>

            <article class="prazzu-card">
                <h2>Perfis cadastrados</h2>
                <p>Selecione um perfil para editar a matriz de permissões.</p>
                <div class="prazzu-role-list" style="margin-top:14px">
                    @forelse ($roles as $role)
                        @php($stats = $roleStats[$role->id] ?? ['active' => 0, 'total' => 0, 'percent' => 0])
                        <div class="prazzu-role-item {{ (int) $selectedRoleId === (int) $role->id ? 'active' : '' }}" wire:key="role-{{ $role->id }}">
                            <label style="cursor:pointer">
                                <input type="radio" wire:model.live="selectedRoleId" value="{{ $role->id }}">
                                <strong>{{ $role->name }}</strong>
                                <span class="prazzu-badge {{ $role->active ? 'green' : 'red' }}">{{ $role->active ? 'Ativo' : 'Inativo' }}</span>
                                <p>{{ $role->description ?: 'Sem descrição.' }}</p>
                                <div class="prazzu-progress"><span style="width: {{ $stats['percent'] }}%"></span></div>
                                <span class="prazzu-muted">{{ $stats['active'] }} de {{ $stats['total'] }} permissões marcadas</span>
                            </label>
                            <button class="prazzu-button secondary" type="button" wire:click="toggleRoleStatus({{ $role->id }})">
                                {{ $role->active ? 'Desativar' : 'Ativar' }}
                            </button>
                        </div>
                    @empty
                        <div class="prazzu-empty">Nenhum perfil cadastrado.</div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="prazzu-card">
            <h2>Matriz de permissões por perfil</h2>
            <p>Perfil selecionado: <strong>{{ $selectedRole?->name ?? 'nenhum' }}</strong>. Marque somente o que esse perfil pode fazer.</p>
            <div class="prazzu-table-wrap" style="margin-top:14px">
                <table class="prazzu-matrix">
                    <thead>
                        <tr>
                            <th>Módulo</th>
                            @foreach ($actions as $actionKey => $actionLabel)
                                <th>{{ $actionLabel }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($matrix as $moduleKey => $availableActions)
                            <tr wire:key="matrix-row-{{ $moduleKey }}">
                                <td><strong>{{ $modules[$moduleKey] ?? $moduleKey }}</strong><br><span class="prazzu-muted">{{ $moduleKey }}</span></td>
                                @foreach ($actions as $actionKey => $actionLabel)
                                    <td class="prazzu-check-cell">
                                        @if (in_array($actionKey, $availableActions, true))
                                            <label title="{{ $modules[$moduleKey] ?? $moduleKey }}: {{ $actionLabel }}">
                                                <input type="checkbox" wire:model.defer="rolePermissions.{{ $moduleKey }}.{{ $actionKey }}">
                                            </label>
                                        @else
                                            <span class="prazzu-muted">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button class="prazzu-button" type="button" wire:click="saveRolePermissions" style="margin-top:14px">Salvar matriz do perfil</button>
        </section>

        <section class="prazzu-grid two">
            <article class="prazzu-card">
                <h2>Vincular perfil ao usuário</h2>
                <p>Permite dizer que um usuário usa um ou mais perfis avançados.</p>
                <div class="prazzu-form-grid" style="margin-top:14px">
                    <label>
                        <span class="prazzu-muted">Usuário</span>
                        <select class="prazzu-select" wire:model.live="selectedUserId">
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} — {{ $user->email }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span class="prazzu-muted">Perfil</span>
                        <select class="prazzu-select" wire:model.defer="assignRoleId">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <button class="prazzu-button" type="button" wire:click="assignRoleToUser" style="margin-top:14px">Vincular perfil</button>

                <div class="prazzu-table-wrap" style="margin-top:14px">
                    <table class="prazzu-table">
                        <thead><tr><th>Perfil vinculado</th><th>Status</th><th>Ação</th></tr></thead>
                        <tbody>
                            @forelse ($userRoles as $userRole)
                                <tr>
                                    <td><strong>{{ $userRole->role?->name ?? 'Perfil removido' }}</strong></td>
                                    <td><span class="prazzu-badge {{ $userRole->role?->active ? 'green' : 'red' }}">{{ $userRole->role?->active ? 'Ativo' : 'Inativo' }}</span></td>
                                    <td><button class="prazzu-button danger" type="button" wire:click="removeUserRole({{ $userRole->id }})">Remover</button></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="prazzu-empty">Usuário sem perfil avançado vinculado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="prazzu-card">
                <h2>Exceção individual</h2>
                <p>Use para liberar ou bloquear uma ação específica para um usuário, mesmo que o perfil diga outra coisa.</p>
                <div class="prazzu-form-grid" style="margin-top:14px">
                    <label>
                        <span class="prazzu-muted">Módulo</span>
                        <select class="prazzu-select" wire:model.live="overrideModule">
                            @foreach ($modules as $moduleKey => $moduleLabel)
                                <option value="{{ $moduleKey }}">{{ $moduleLabel }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span class="prazzu-muted">Ação</span>
                        <select class="prazzu-select" wire:model.defer="overrideAction">
                            @foreach ($actions as $actionKey => $actionLabel)
                                <option value="{{ $actionKey }}">{{ $actionLabel }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span class="prazzu-muted">Escopo</span>
                        <select class="prazzu-select" wire:model.defer="overrideScope">
                            <option value="empresa">Empresa</option>
                            <option value="all">Global</option>
                            <option value="proprio">Próprio usuário</option>
                            <option value="equipe">Equipe</option>
                        </select>
                    </label>
                    <label>
                        <span class="prazzu-muted">Tipo</span>
                        <select class="prazzu-select" wire:model.defer="overrideAllowed">
                            <option value="1">Permitir</option>
                            <option value="0">Bloquear</option>
                        </select>
                    </label>
                </div>
                <label style="display:block;margin-top:12px">
                    <span class="prazzu-muted">Motivo</span>
                    <input class="prazzu-input" type="text" wire:model.defer="overrideReason" placeholder="Ex.: liberação temporária para fechamento mensal">
                </label>
                <button class="prazzu-button" type="button" wire:click="saveUserOverride" style="margin-top:14px">Salvar exceção</button>

                <div class="prazzu-table-wrap" style="margin-top:14px">
                    <table class="prazzu-table">
                        <thead><tr><th>Permissão</th><th>Tipo</th><th>Motivo</th><th>Ação</th></tr></thead>
                        <tbody>
                            @forelse ($userOverrides as $override)
                                <tr>
                                    <td><strong>{{ $modules[$override->module] ?? $override->module }}</strong><br><span class="prazzu-muted">{{ $override->module }}.{{ $override->action }} / {{ $override->scope }}</span></td>
                                    <td><span class="prazzu-badge {{ $override->allowed ? 'green' : 'red' }}">{{ $override->allowed ? 'Permitir' : 'Bloquear' }}</span></td>
                                    <td>{{ $override->reason ?? '-' }}</td>
                                    <td><button class="prazzu-button danger" type="button" wire:click="removeUserOverride({{ $override->id }})">Remover</button></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="prazzu-empty">Nenhuma exceção individual para o usuário selecionado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>


        <section class="prazzu-card">
            <h2>Relatório efetivo do usuário</h2>
            <p>Mostra o resultado final após somar perfil avançado, exceções individuais e fallback do sistema.</p>
            <div class="prazzu-table-wrap" style="margin-top:14px">
                <table class="prazzu-table">
                    <thead><tr><th>Módulo</th><th>Ação</th><th>Resultado</th><th>Origem</th><th>Perfis</th></tr></thead>
                    <tbody>
                        @forelse ($effectivePermissions as $row)
                            <tr>
                                <td><strong>{{ $modules[$row['module']] ?? $row['module'] }}</strong><br><span class="prazzu-muted">{{ $row['module'] }}</span></td>
                                <td>{{ $actions[$row['action']] ?? $row['action'] }}</td>
                                <td><span class="prazzu-badge {{ $row['allowed'] ? 'green' : 'red' }}">{{ $row['allowed'] ? 'Permitido' : 'Bloqueado' }}</span></td>
                                <td>{{ $row['source'] }}</td>
                                <td>{{ $row['roles'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="prazzu-empty">Selecione um usuário para ver o relatório efetivo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="prazzu-card">
            <h2>Auditoria de permissões</h2>
            <p>Registra quem criou perfil, alterou matriz, vinculou perfil e criou/removeu exceções individuais.</p>
            <div class="prazzu-table-wrap" style="margin-top:14px">
                <table class="prazzu-table">
                    <thead><tr><th>Data</th><th>Evento</th><th>Autor</th><th>Usuário alvo</th><th>Perfil</th><th>Permissão</th><th>Motivo</th></tr></thead>
                    <tbody>
                        @forelse ($permissionAudits as $audit)
                            <tr>
                                <td>{{ $audit->created_at?->format('d/m/Y H:i') }}</td>
                                <td><strong>{{ $audit->event_label }}</strong><br><span class="prazzu-muted">{{ $audit->event }}</span></td>
                                <td>{{ $audit->actor?->name ?? '-' }}</td>
                                <td>{{ $audit->targetUser?->name ?? '-' }}</td>
                                <td>{{ $audit->role?->name ?? '-' }}</td>
                                <td>{{ $audit->module ? $audit->module . '.' . $audit->action : '-' }}</td>
                                <td>{{ $audit->reason ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="prazzu-empty">Nenhum evento de permissão registrado ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="prazzu-card">
            <h2>Regras antigas sincronizadas</h2>
            <p>Compatibilidade com <code>prazzu_permission_rules</code>. A matriz nova salva também atualiza Ver, Criar, Editar e Excluir nessa tabela quando existir.</p>
            <div class="prazzu-table-wrap" style="margin-top:14px">
                <table class="prazzu-table">
                    <thead><tr><th>Perfil</th><th>Módulo</th><th>Ver</th><th>Criar</th><th>Editar</th><th>Excluir</th><th>Escopo</th></tr></thead>
                    <tbody>
                        @forelse ($legacyRules as $rule)
                            <tr>
                                <td><strong>{{ $rule->role }}</strong></td>
                                <td>{{ $rule->module }}</td>
                                <td>{{ $rule->can_view ? 'Sim' : 'Não' }}</td>
                                <td>{{ $rule->can_create ? 'Sim' : 'Não' }}</td>
                                <td>{{ $rule->can_update ? 'Sim' : 'Não' }}</td>
                                <td>{{ $rule->can_delete ? 'Sim' : 'Não' }}</td>
                                <td>{{ $rule->scope }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="prazzu-empty">Nenhuma regra antiga encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
