<x-filament-panels::page>
    <style>
        .prazzu-admin-page{display:grid;gap:20px}.prazzu-hero{border-radius:24px;padding:24px;background:linear-gradient(135deg,#111827,#1f2937);color:#fff}.prazzu-hero h1{font-size:28px;font-weight:900;margin:0}.prazzu-hero p{margin:8px 0 0;color:#d1d5db;max-width:900px}.prazzu-grid{display:grid;gap:16px}.prazzu-grid.two{grid-template-columns:repeat(2,minmax(0,1fr))}.prazzu-card{border:1px solid #e5e7eb;border-radius:20px;background:#fff;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.06)}.prazzu-card h2{font-size:18px;font-weight:900;margin:0;color:#111827}.prazzu-card p{color:#64748b;margin:6px 0 0}.prazzu-form{display:grid;gap:12px;margin-top:14px}.prazzu-field span{display:block;color:#64748b;font-size:12px;font-weight:800;margin-bottom:6px}.prazzu-input,.prazzu-select{width:100%;border:1px solid #d1d5db;border-radius:12px;padding:10px 12px;background:#fff}.prazzu-button{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:12px;padding:10px 14px;background:#111827;color:#fff;font-weight:800;cursor:pointer}.prazzu-button.light{background:#f3f4f6;color:#111827}.prazzu-table-wrap{overflow:auto}.prazzu-table{width:100%;border-collapse:collapse}.prazzu-table th,.prazzu-table td{padding:12px;border-bottom:1px solid #e5e7eb;text-align:left;vertical-align:top}.prazzu-table th{font-size:12px;text-transform:uppercase;color:#64748b}.prazzu-badge{display:inline-flex;border-radius:999px;background:#eef2ff;color:#3730a3;padding:4px 10px;font-size:12px;font-weight:800}.prazzu-badge.off{background:#f3f4f6;color:#64748b}.prazzu-muted{color:#64748b;font-size:12px}.prazzu-members{display:flex;gap:8px;flex-wrap:wrap}.prazzu-member{display:inline-flex;gap:8px;align-items:center;border:1px solid #e5e7eb;border-radius:999px;padding:6px 8px}.prazzu-remove{border:0;background:transparent;color:#b91c1c;font-weight:900;cursor:pointer}@media(max-width:960px){.prazzu-grid.two{grid-template-columns:1fr}.prazzu-table{min-width:760px}}
    </style>

    <div class="prazzu-admin-page">
        <section class="prazzu-hero">
            <h1>Equipes</h1>
            <p>Centraliza os grupos internos do escritório e o vínculo entre usuários e equipes. Isso prepara permissões por escopo de equipe e facilita separar operação, financeiro, atendimento e gestão.</p>
        </section>

        @if (! $this->tabelasDisponiveis())
            <section class="prazzu-card">
                <h2>Estrutura de equipes indisponível</h2>
                <p>As tabelas <strong>prazzu_teams</strong> e <strong>prazzu_team_user</strong> não foram encontradas neste banco.</p>
            </section>
        @else
            <section class="prazzu-grid two">
                <article class="prazzu-card">
                    <h2>Criar equipe</h2>
                    <p>Use para organizar pessoas por área ou responsabilidade.</p>
                    <div class="prazzu-form">
                        <label class="prazzu-field"><span>Nome</span><input class="prazzu-input" wire:model.defer="name" placeholder="Ex: Fiscal, Departamento Pessoal, Financeiro"></label>
                        <label class="prazzu-field"><span>Descrição</span><input class="prazzu-input" wire:model.defer="description" placeholder="Resumo da função da equipe"></label>
                        @if ($this->podeEditar())
                            <button class="prazzu-button" type="button" wire:click="criarEquipe">Criar equipe</button>
                        @endif
                    </div>
                </article>

                <article class="prazzu-card">
                    <h2>Vincular usuário</h2>
                    <p>Adicione uma pessoa em uma equipe sem sair da Central Administrativa.</p>
                    <div class="prazzu-form">
                        <label class="prazzu-field">
                            <span>Equipe</span>
                            <select class="prazzu-select" wire:model.defer="teamId">
                                <option value="">Selecione</option>
                                @foreach ($this->equipes() as $team)
                                    <option value="{{ $team['id'] }}">{{ $team['name'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="prazzu-field">
                            <span>Usuário</span>
                            <select class="prazzu-select" wire:model.defer="userId">
                                <option value="">Selecione</option>
                                @foreach ($this->usuariosDisponiveis() as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        @if ($this->podeEditar())
                            <button class="prazzu-button" type="button" wire:click="vincularUsuario">Vincular usuário</button>
                        @endif
                    </div>
                </article>
            </section>

            @php $membrosPorEquipe = $this->membrosPorEquipe(); @endphp
            <section class="prazzu-card">
                <h2>Equipes cadastradas</h2>
                <p>Visão única de equipes, status e membros vinculados.</p>
                <div class="prazzu-table-wrap" style="margin-top:14px">
                    <table class="prazzu-table">
                        <thead>
                            <tr>
                                <th>Equipe</th>
                                <th>Status</th>
                                <th>Membros</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->equipes() as $team)
                                <tr wire:key="team-row-{{ $team['id'] }}">
                                    <td><strong>{{ $team['name'] }}</strong><br><span class="prazzu-muted">{{ $team['description'] ?: 'Sem descrição' }}</span></td>
                                    <td><span class="prazzu-badge {{ $team['active'] ? '' : 'off' }}">{{ $team['active'] ? 'Ativa' : 'Inativa' }}</span><br><span class="prazzu-muted">{{ $team['users_count'] }} membro(s)</span></td>
                                    <td>
                                        <div class="prazzu-members">
                                            @forelse (($membrosPorEquipe[$team['id']] ?? []) as $member)
                                                <span class="prazzu-member">
                                                    {{ $member['name'] }}
                                                    @if ($this->podeEditar())
                                                        <button class="prazzu-remove" type="button" wire:click="removerVinculo({{ $team['id'] }}, {{ $member['id'] }})" title="Remover vínculo">×</button>
                                                    @endif
                                                </span>
                                            @empty
                                                <span class="prazzu-muted">Nenhum usuário vinculado</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td>
                                        @if ($this->podeEditar())
                                            <button class="prazzu-button light" type="button" wire:click="alternarEquipe({{ $team['id'] }})">{{ $team['active'] ? 'Inativar' : 'Ativar' }}</button>
                                        @else
                                            <span class="prazzu-muted">Somente leitura</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="prazzu-muted">Nenhuma equipe cadastrada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
</x-filament-panels::page>
