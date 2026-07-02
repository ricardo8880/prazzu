<x-filament-panels::page>

    @php
        $usuarios = $userManagement ?? [];
        $configuracao = $config ?? [];
    @endphp

    <div class="prazzu80-page">
        <section class="prazzu80-hero">
            <div>
                <span class="prazzu80-kicker">{{ $configuracao['group'] ?? 'CONFIGURAÇÕES' }}</span>
                <h1>{{ $configuracao['title'] ?? 'Usuários' }}</h1>
                <p>{{ $configuracao['subtitle'] ?? 'Gestão de assentos, convidados, último acesso, cargos e grupos internos.' }}</p>
            </div>
            <div class="prazzu80-hero-actions">
                <span>Convidar usuário</span>
                <span>Filtrar último acesso</span>
                <span>Alterar cargo</span>
                <span>Gerenciar grupos</span>
            </div>
        </section>

        <section class="prazzu80-search-card">
            <div>
                <strong>Gestão de pessoas e custos</strong>
                <p>Acompanhe quem entra no sistema, quanto consome do plano e quais acessos devem ser mantidos, removidos ou convertidos em convidado.</p>
                <small>Filtros rápidos: Assentos · Guests · Último acesso · Cargo · Grupos internos</small>
            </div>
            <div class="prazzu80-search-fake">⌕ Buscar usuário</div>
        </section>

        <section class="prazzu80-stats">
            @foreach (($usuarios['seats'] ?? []) as $seat)
                <article>
                    <span>{{ $seat['label'] }}</span>
                    <strong>{{ $seat['value'] }}</strong>
                    <small>{{ $seat['hint'] }}</small>
                </article>
            @endforeach
        </section>

        <section class="prazzu80-card">
            <header>
                <div>
                    <h2>O que esta aba controla</h2>
                    <p>Conteúdo focado em Usuários: assentos do plano, convidados, último acesso, cargos e grupos.</p>
                </div>
            </header>
            <div class="prazzu80-feature-grid">
                @foreach (($usuarios['sections'] ?? []) as $section)
                    <div class="prazzu80-feature ok">
                        <strong>{{ $section['title'] }}</strong>
                        <span>{{ $section['description'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="prazzu80-grid two">
            <article class="prazzu80-card">
                <header>
                    <div>
                        <h2>Usuários e último acesso</h2>
                        <p>Use esta lista para encontrar usuários inativos, convidados e perfis que precisam de ajuste de cargo.</p>
                    </div>
                </header>
                <div class="prazzu80-list">
                    @forelse (($usuarios['users'] ?? []) as $user)
                        <div class="prazzu80-list-row">
                            <div>
                                <strong>{{ $user['name'] ?? $user['email'] ?? 'Usuário' }}</strong>
                                <span>{{ $user['email'] ?? '-' }} · {{ $user['access_type'] ?? 'Seat' }} · Último acesso: {{ $user['last_access_display'] ?? 'Sem registro' }}</span>
                            </div>
                            <em>{{ $user['display_role'] ?? 'Member' }}</em>
                        </div>
                    @empty
                        <div class="prazzu80-empty">Nenhum usuário encontrado. Execute o SQL enviado para habilitar último acesso, cargos e grupos.</div>
                    @endforelse
                </div>
            </article>

            <article class="prazzu80-card">
                <header>
                    <div>
                        <h2>Cargos rápidos (Roles)</h2>
                        <p>Base para mudar alguém de Member para Admin, Guest, Gestor, Estagiário ou perfis personalizados.</p>
                    </div>
                </header>
                <div class="prazzu80-list compact">
                    @forelse (($usuarios['roles'] ?? []) as $role)
                        <div class="prazzu80-note">
                            <strong>{{ $role['name'] ?? 'Cargo' }}</strong>
                            <p>{{ $role['description'] ?? 'Sem descrição cadastrada.' }}</p>
                        </div>
                    @empty
                        <div class="prazzu80-empty">Nenhum cargo cadastrado.</div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="prazzu80-card">
            <header>
                <div>
                    <h2>Grupos internos (Teams)</h2>
                    <p>Visualize em quais grupos internos os usuários podem ser inseridos para organizar acesso e responsabilidade.</p>
                </div>
            </header>
            <div class="prazzu80-feature-grid">
                @forelse (($usuarios['teams'] ?? []) as $team)
                    <div class="prazzu80-feature {{ (bool) ($team['active'] ?? true) ? 'ok' : 'todo' }}">
                        <strong>{{ $team['name'] ?? 'Grupo' }}</strong>
                        <span>{{ $team['description'] ?? 'Grupo interno para organização de usuários.' }}</span>
                    </div>
                @empty
                    <div class="prazzu80-empty">Nenhum grupo interno cadastrado.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-filament-panels::page>
