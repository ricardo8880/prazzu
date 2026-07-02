<x-filament-panels::page>

    <div class="prazzu-admin-page lote4-cadastro-page">
        <section class="prazzu-hero lote4-cadastro-hero">
            <div>
                <span><i class="bi bi-building-check"></i> DADOS DO ESCRITÓRIO</span>
                <h1>Cadastro institucional do escritório</h1>
                <p>Mantenha dados cadastrais, contato, responsável, plano e status em um lugar único. Fluxos operacionais continuam nas abas de Operação, Pendências, Documentos e Aprovações.</p>
            </div>
        </section>

        <section class="configuracoes-diretriz">
            <article>
                <i class="bi bi-person-vcard"></i>
                <div>
                    <strong>Propósito desta aba</strong>
                    <p>Guardar a identidade administrativa do escritório usada em documentos, conta, comunicação e permissões.</p>
                </div>
            </article>
            <article>
                <i class="bi bi-diagram-3"></i>
                <div>
                    <strong>Sem mistura operacional</strong>
                    <p>Demandas, pendências, aprovações e vencimentos não devem ser tratados aqui; esta é uma tela de cadastro.</p>
                </div>
            </article>
            <article>
                <i class="bi bi-shield-check"></i>
                <div>
                    <strong>Dados confiáveis</strong>
                    <p>Campos claros e revisáveis reduzem erro cadastral e melhoram a experiência do usuário administrador.</p>
                </div>
            </article>
        </section>

        <section class="prazzu-grid four">
            @forelse ($this->resumo() as $stat)
                <article class="prazzu-card prazzu-stat lote4-stat-card">
                    <span>{{ $stat['label'] }}</span>
                    <strong>{{ $stat['value'] }}</strong>
                    <small class="prazzu-muted">{{ $stat['hint'] }}</small>
                </article>
            @empty
                <article class="prazzu-card"><strong>Nenhuma empresa disponível.</strong></article>
            @endforelse
        </section>

        <section class="prazzu-grid two">
            <article class="prazzu-card lote4-form-card">
                <div class="configuracoes-section-title compact">
                    <i class="bi bi-pencil-square"></i>
                    <div>
                        <h2>Dados principais</h2>
                        <p>Edite apenas informações cadastrais e administrativas do escritório.</p>
                    </div>
                </div>

                @if (auth()->user()?->isSuperAdmin())
                    <label class="prazzu-field" style="display:block;margin-top:14px">
                        <span>Empresa administrada</span>
                        <select class="prazzu-select" wire:model.live="empresaId">
                            @foreach ($this->empresasDisponiveis() as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif

                <div class="prazzu-form">
                    <label class="prazzu-field"><span>Razão social</span><input class="prazzu-input" wire:model.defer="razao_social"></label>
                    <label class="prazzu-field"><span>Nome fantasia</span><input class="prazzu-input" wire:model.defer="nome_fantasia"></label>
                    <label class="prazzu-field"><span>CNPJ</span><input class="prazzu-input" wire:model.defer="cnpj"></label>
                    <label class="prazzu-field"><span>E-mail institucional</span><input class="prazzu-input" type="email" wire:model.defer="email"></label>
                    <label class="prazzu-field"><span>Telefone / WhatsApp</span><input class="prazzu-input" wire:model.defer="telefone"></label>
                    <label class="prazzu-field"><span>Responsável administrativo</span><input class="prazzu-input" wire:model.defer="responsavel_nome"></label>
                    <label class="prazzu-field"><span>Status da conta</span><input class="prazzu-input" wire:model.defer="status"></label>
                    <label class="prazzu-field"><span>Plano contratado</span><input class="prazzu-input" wire:model.defer="plano"></label>
                </div>

                <div class="prazzu-actions">
                    @if ($this->podeEditar())
                        <button class="prazzu-button" type="button" wire:click="salvarEmpresa"><i class="bi bi-check2-circle"></i> Salvar empresa</button>
                    @endif
                    @if ($this->resourceUrl())
                        <a class="prazzu-link light" href="{{ $this->resourceUrl() }}"><i class="bi bi-box-arrow-up-right"></i> Abrir cadastro completo</a>
                    @endif
                </div>
            </article>

            <article class="prazzu-card lote4-info-card">
                <div class="configuracoes-section-title compact">
                    <i class="bi bi-info-circle"></i>
                    <div>
                        <h2>Mapa de responsabilidade</h2>
                        <p>Use esta orientação para manter cada conteúdo na aba certa.</p>
                    </div>
                </div>
                <div class="prazzu-info">
                    <div><span>Identidade</span><strong>Razão social, fantasia e CNPJ ficam aqui</strong></div>
                    <div><span>Contato</span><strong>E-mail, telefone e responsável ficam aqui</strong></div>
                    <div><span>Conta</span><strong>Status, plano e dados administrativos ficam aqui</strong></div>
                    <div><span>Operação</span><strong>Pendências, documentos e aprovações ficam nas abas operacionais</strong></div>
                </div>
            </article>
        </section>
    </div>
</x-filament-panels::page>
