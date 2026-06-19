<x-filament-panels::page>
    <style>
        .prazzu-admin-page{display:grid;gap:20px}.prazzu-hero{border-radius:24px;padding:24px;background:linear-gradient(135deg,#111827,#1f2937);color:#fff;display:flex;justify-content:space-between;gap:20px;align-items:flex-start}.prazzu-hero h1{font-size:28px;font-weight:800;margin:0}.prazzu-hero p{margin:8px 0 0;color:#d1d5db;max-width:860px}.prazzu-grid{display:grid;gap:16px}.prazzu-grid.four{grid-template-columns:repeat(4,minmax(0,1fr))}.prazzu-card{border:1px solid #e5e7eb;border-radius:20px;background:#fff;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.06)}.prazzu-card h2{font-size:18px;font-weight:800;margin:0}.prazzu-card p{color:#64748b;margin:6px 0 0}.prazzu-stat span{display:block;color:#64748b;font-size:13px}.prazzu-stat strong{display:block;font-size:26px;margin-top:6px}.prazzu-filters{display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:12px;align-items:end}.prazzu-input,.prazzu-select{width:100%;border:1px solid #d1d5db;border-radius:12px;padding:10px 12px;background:#fff}.prazzu-button{border:0;border-radius:12px;padding:10px 14px;background:#111827;color:#fff;font-weight:700;cursor:pointer}.prazzu-button.light{background:#f3f4f6;color:#111827}.prazzu-table-wrap{overflow:auto}.prazzu-table{width:100%;border-collapse:collapse}.prazzu-table th,.prazzu-table td{padding:12px;border-bottom:1px solid #e5e7eb;text-align:left;vertical-align:top}.prazzu-table th{font-size:12px;text-transform:uppercase;color:#64748b}.prazzu-badge{display:inline-flex;border-radius:999px;background:#eef2ff;color:#3730a3;padding:4px 10px;font-size:12px;font-weight:700}.prazzu-muted{color:#64748b;font-size:12px}.prazzu-danger{color:#b91c1c;font-weight:700}.prazzu-empty{padding:18px;text-align:center;color:#64748b}@media(max-width:1100px){.prazzu-grid.four,.prazzu-filters{grid-template-columns:1fr}.prazzu-hero{display:block}}
    </style>

    <div class="prazzu-admin-page">
        <section class="prazzu-hero">
            <div>
                <h1>Usuários</h1>
                <p>Gestão real de pessoas, assentos, convidados, último acesso, cargos, perfis contábeis e grupos internos. Aqui você controla quem entra, qual nível de acesso possui e qual função exerce no escritório.</p>
            </div>
        </section>

        <section class="prazzu-grid four">
            @foreach ($stats as $stat)
                <article class="prazzu-card prazzu-stat">
                    <span>{{ $stat['label'] }}</span>
                    <strong>{{ $stat['value'] }}</strong>
                    <small class="prazzu-muted">{{ $stat['hint'] }}</small>
                </article>
            @endforeach
        </section>

        <section class="prazzu-card">
            <h2>Filtros de segurança e economia</h2>
            <p>Use o filtro de último acesso para encontrar usuários parados há meses, remover acesso e liberar assentos cobrados no plano.</p>
            <div class="prazzu-filters" style="margin-top:14px">
                <label>
                    <span class="prazzu-muted">Buscar usuário</span>
                    <input class="prazzu-input" type="search" wire:model.live.debounce.400ms="search" placeholder="Nome ou e-mail">
                </label>
                <label>
                    <span class="prazzu-muted">Cargo</span>
                    <select class="prazzu-select" wire:model.live="roleFilter">
                        <option value="todos">Todos</option>
                        @foreach ($roleOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="prazzu-muted">Perfil contábil</span>
                    <select class="prazzu-select" wire:model.live="perfilContabilFilter">
                        <option value="todos">Todos</option>
                        @foreach ($perfilContabilOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="prazzu-muted">Último acesso</span>
                    <select class="prazzu-select" wire:model.live="lastAccessFilter">
                        <option value="todos">Todos</option>
                        <option value="30">Sem acesso há 30+ dias</option>
                        <option value="60">Sem acesso há 60+ dias</option>
                        <option value="90">Sem acesso há 90+ dias</option>
                        <option value="nunca">Nunca acessou</option>
                    </select>
                </label>
                <button class="prazzu-button light" type="button" wire:click="clearFilters">Limpar filtros</button>
            </div>
        </section>

        <section class="prazzu-card">
            <h2>Gestão de usuários, cargos e grupos</h2>
            <p>Altere rapidamente Admin/Gestor/Usuário/Guest, defina o perfil contábil real da pessoa, visualize grupos internos e remova acessos inativos sem sair da aba.</p>
            <div class="prazzu-table-wrap" style="margin-top:14px">
                <table class="prazzu-table">
                    <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>Empresa</th>
                            <th>Cargo</th>
                            <th>Perfil contábil</th>
                            <th>Último acesso</th>
                            <th>Grupos / Teams</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @php $lastAccessValue = $lastAccessColumn ? $user->{$lastAccessColumn} : null; @endphp
                            <tr wire:key="user-row-{{ $user->id }}">
                                <td>
                                    <strong>{{ $user->name }}</strong><br>
                                    <span class="prazzu-muted">{{ $user->email }}</span>
                                </td>
                                <td>{{ $user->empresa?->nome_fantasia ?? $user->empresa?->razao_social ?? 'Todas / Super Admin' }}</td>
                                <td>
                                    <select class="prazzu-select" wire:change="updateUserRole({{ $user->id }}, $event.target.value)">
                                        @foreach ($roleOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="prazzu-select" wire:change="updateUserPerfilContabil({{ $user->id }}, $event.target.value)">
                                        <option value="">Não definido</option>
                                        @foreach ($perfilContabilOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($user->perfil_contabil === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <span class="{{ empty($lastAccessValue) ? 'prazzu-danger' : '' }}">{{ $this->formatLastAccess($lastAccessValue) }}</span>
                                    <br><span class="prazzu-muted">Base: {{ $lastAccessColumn ?? 'coluna indisponível' }}</span>
                                </td>
                                <td>
                                    <span class="prazzu-badge">{{ $teamsByUser[$user->id] ?? 'Sem grupo vinculado' }}</span>
                                </td>
                                <td>
                                    @if ($user->id !== auth()->id())
                                        <button class="prazzu-button" type="button" wire:click="removeUserAccess({{ $user->id }})" wire:confirm="Remover o acesso deste usuário e convertê-lo para Guest?">Remover acesso</button>
                                    @else
                                        <span class="prazzu-muted">Usuário atual</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="prazzu-empty">Nenhum usuário encontrado para os filtros atuais.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
