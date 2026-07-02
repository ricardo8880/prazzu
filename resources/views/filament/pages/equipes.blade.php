<x-filament-panels::page>
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
