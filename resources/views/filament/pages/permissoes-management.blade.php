<x-filament-panels::page>
    <style>
        .prazzu-admin-page{display:grid;gap:20px}.prazzu-hero{border-radius:24px;padding:24px;background:linear-gradient(135deg,#111827,#1f2937);color:#fff}.prazzu-hero h1{font-size:28px;font-weight:800;margin:0}.prazzu-hero p{margin:8px 0 0;color:#d1d5db;max-width:920px}.prazzu-grid{display:grid;gap:16px}.prazzu-grid.two{grid-template-columns:repeat(2,minmax(0,1fr))}.prazzu-grid.four{grid-template-columns:repeat(4,minmax(0,1fr))}.prazzu-card{border:1px solid #e5e7eb;border-radius:20px;background:#fff;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.06)}.prazzu-card h2{font-size:18px;font-weight:800;margin:0}.prazzu-card p{color:#64748b;margin:6px 0 0}.prazzu-input,.prazzu-select,.prazzu-textarea{width:100%;border:1px solid #d1d5db;border-radius:12px;padding:10px 12px;background:#fff}.prazzu-textarea{min-height:86px}.prazzu-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.prazzu-checks{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:12px}.prazzu-check{border:1px solid #e5e7eb;border-radius:14px;padding:12px}.prazzu-button{border:0;border-radius:12px;padding:10px 14px;background:#111827;color:#fff;font-weight:700;cursor:pointer}.prazzu-button.on{background:#16a34a}.prazzu-button.off{background:#b91c1c}.prazzu-rule{display:flex;justify-content:space-between;gap:12px;align-items:flex-start}.prazzu-muted{color:#64748b;font-size:12px}.prazzu-badge{display:inline-flex;border-radius:999px;background:#eef2ff;color:#3730a3;padding:4px 10px;font-size:12px;font-weight:700}.prazzu-table-wrap{overflow:auto}.prazzu-table{width:100%;border-collapse:collapse}.prazzu-table th,.prazzu-table td{padding:12px;border-bottom:1px solid #e5e7eb;text-align:left;vertical-align:top}.prazzu-table th{font-size:12px;text-transform:uppercase;color:#64748b}.prazzu-empty{padding:18px;text-align:center;color:#64748b}.prazzu-permission-checks{display:grid;gap:10px;margin-top:14px}.prazzu-permission-checks div{position:relative;border:1px solid #e5e7eb;border-radius:16px;padding:12px 88px 12px 12px;background:#f8fafc}.prazzu-permission-checks div.ok{border-color:#bbf7d0;background:#f0fdf4}.prazzu-permission-checks div.warn{border-color:#fde68a;background:#fffbeb}.prazzu-permission-checks strong{display:block;color:#0f172a}.prazzu-permission-checks span{display:block;margin-top:4px;color:#64748b;font-size:12px}.prazzu-permission-checks em{position:absolute;right:12px;top:12px;border-radius:999px;padding:4px 8px;background:#111827;color:#fff;font-size:11px;font-style:normal;font-weight:800}.prazzu-form-grid{grid-template-columns:repeat(3,minmax(0,1fr))}@media(max-width:1100px){.prazzu-grid.two,.prazzu-grid.four,.prazzu-form-grid,.prazzu-checks{grid-template-columns:1fr}.prazzu-rule{display:block}.prazzu-rule button{margin-top:12px}}
    </style>

    <div class="prazzu-admin-page">
        <section class="prazzu-hero">
            <h1>Permissões Avançadas</h1>
            <p>Controle funcional de cargos personalizados, exclusão, exportação, visibilidade Private/Public e gestão centralizada de tags/status. Esta aba grava regras reais nas tabelas prazzu_roles, prazzu_permission_rules e prazzu_permissions.</p>
        </section>

        <section class="prazzu-card">
            <h2>Criar cargo personalizado</h2>
            <p>Crie perfis como Estagiário, Visualizador Externo, Cliente, Auditor ou Freelancer com travas específicas.</p>

            <form wire:submit.prevent="createCustomRole" style="margin-top:14px">
                <div class="prazzu-form-grid">
                    <label>
                        <span class="prazzu-muted">Nome do cargo</span>
                        <input class="prazzu-input" type="text" wire:model.defer="roleName" placeholder="Ex.: Estagiário">
                    </label>
                    <label>
                        <span class="prazzu-muted">Módulo</span>
                        <select class="prazzu-select" wire:model.defer="roleModule">
                            <option value="Operação">Operação</option>
                            <option value="Documentos">Documentos</option>
                            <option value="CRM">CRM</option>
                            <option value="Auditoria">Auditoria</option>
                            <option value="Permissões">Permissões</option>
                            <option value="Anexos">Anexos</option>
                        </select>
                    </label>
                    <label>
                        <span class="prazzu-muted">Escopo</span>
                        <select class="prazzu-select" wire:model.defer="roleScope">
                            <option value="empresa">Empresa inteira</option>
                            <option value="equipe">Somente equipe</option>
                            <option value="responsável/equipe">Responsável/equipe</option>
                            <option value="compartilhado">Somente compartilhados</option>
                            <option value="somente_leitura">Somente leitura</option>
                        </select>
                    </label>
                </div>
                <label style="display:block;margin-top:12px">
                    <span class="prazzu-muted">Descrição</span>
                    <textarea class="prazzu-textarea" wire:model.defer="roleDescription" placeholder="Descreva quando este cargo deve ser usado."></textarea>
                </label>
                <div class="prazzu-checks">
                    <label class="prazzu-check"><input type="checkbox" wire:model.defer="canView"> Visualizar</label>
                    <label class="prazzu-check"><input type="checkbox" wire:model.defer="canCreate"> Criar</label>
                    <label class="prazzu-check"><input type="checkbox" wire:model.defer="canUpdate"> Editar</label>
                    <label class="prazzu-check"><input type="checkbox" wire:model.defer="canDelete"> Excluir</label>
                </div>
                <button class="prazzu-button" type="submit" style="margin-top:14px">Salvar cargo e permissões</button>
            </form>
        </section>

        <section class="prazzu-grid four">
            @foreach ($cards as $key => $card)
                <article class="prazzu-card prazzu-rule" wire:key="security-card-{{ $key }}">
                    <div>
                        <h2>{{ $card['title'] }}</h2>
                        <p>{{ $card['description'] }}</p>
                        <span class="prazzu-badge" style="margin-top:10px">{{ $securityRules[$key] ? $card['on'] : $card['off'] }}</span>
                    </div>
                    <button class="prazzu-button {{ $securityRules[$key] ? 'on' : 'off' }}" type="button" wire:click="toggleSecurityRule('{{ $key }}')">
                        {{ $securityRules[$key] ? 'Ativo' : 'Inativo' }}
                    </button>
                </article>
            @endforeach
        </section>

        <section class="prazzu-grid two">
            <article class="prazzu-card">
                <h2>Validação de segurança</h2>
                <p>Resumo prático para garantir menu, ações, rotas, upload e tenant cobertos por regras reais.</p>
                <div class="prazzu-permission-checks">
                    @foreach ($permissionChecklist as $check)
                        <div class="{{ $check['ok'] ? 'ok' : 'warn' }}">
                            <strong>{{ $check['label'] }}</strong>
                            <span>{{ $check['hint'] }}</span>
                            <em>{{ $check['ok'] ? 'Configurado' : 'Revisar' }}</em>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="prazzu-card">
                <h2>Permissões por módulo</h2>
                <p>Mapa operacional para enxergar onde cada módulo já possui regra de acesso.</p>
                <div class="prazzu-table-wrap" style="margin-top:14px">
                    <table class="prazzu-table">
                        <thead><tr><th>Módulo</th><th>Ver</th><th>Criar</th><th>Editar</th><th>Excluir</th></tr></thead>
                        <tbody>
                            @forelse ($moduleSummary as $module)
                                <tr>
                                    <td><strong>{{ $module->module }}</strong></td>
                                    <td>{{ (int) $module->view_total }}</td>
                                    <td>{{ (int) $module->create_total }}</td>
                                    <td>{{ (int) $module->update_total }}</td>
                                    <td>{{ (int) $module->delete_total }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="prazzu-empty">Nenhuma regra por módulo cadastrada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        <section class="prazzu-grid two">
            <article class="prazzu-card">
                <h2>Cargos personalizados cadastrados</h2>
                <p>Lista real de perfis disponíveis para governança de acesso.</p>
                <div class="prazzu-table-wrap" style="margin-top:14px">
                    <table class="prazzu-table">
                        <thead><tr><th>Cargo</th><th>Descrição</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($roles as $role)
                                <tr>
                                    <td><strong>{{ $role->name }}</strong></td>
                                    <td>{{ $role->description ?? '-' }}</td>
                                    <td><span class="prazzu-badge">{{ $role->active ? 'Ativo' : 'Inativo' }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="prazzu-empty">Nenhum cargo cadastrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="prazzu-card">
                <h2>Matriz de permissões</h2>
                <p>Regras de visualização, criação, edição e exclusão por perfil.</p>
                <div class="prazzu-table-wrap" style="margin-top:14px">
                    <table class="prazzu-table">
                        <thead><tr><th>Perfil</th><th>Módulo</th><th>Permissões</th><th>Escopo</th></tr></thead>
                        <tbody>
                            @forelse ($permissionRules as $rule)
                                <tr>
                                    <td><strong>{{ $rule->role }}</strong></td>
                                    <td>{{ $rule->module }}</td>
                                    <td>
                                        <span class="prazzu-badge">Ver: {{ $rule->can_view ? 'sim' : 'não' }}</span>
                                        <span class="prazzu-badge">Criar: {{ $rule->can_create ? 'sim' : 'não' }}</span>
                                        <span class="prazzu-badge">Editar: {{ $rule->can_update ? 'sim' : 'não' }}</span>
                                        <span class="prazzu-badge">Excluir: {{ $rule->can_delete ? 'sim' : 'não' }}</span>
                                    </td>
                                    <td>{{ $rule->scope ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="prazzu-empty">Nenhuma regra cadastrada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        <section class="prazzu-card">
            <h2>Permissões sensíveis gravadas</h2>
            <p>Estas regras são usadas para travar ações críticas: exclusão, exportação, visibilidade e gestão de workflow.</p>
            <div class="prazzu-table-wrap" style="margin-top:14px">
                <table class="prazzu-table">
                    <thead><tr><th>Módulo</th><th>Ação</th><th>Escopo</th><th>Descrição</th></tr></thead>
                    <tbody>
                        @forelse ($sensitivePermissions as $permission)
                            <tr>
                                <td>{{ $permission->module }}</td>
                                <td><span class="prazzu-badge">{{ $permission->action }}</span></td>
                                <td>{{ $permission->scope ?? '-' }}</td>
                                <td>{{ $permission->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="prazzu-empty">Nenhuma permissão sensível ativa.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
