<x-filament-panels::page>

    <div class="onboarding-prazzu">
        <section class="onboarding-hero">
            <div>
                <span>CONFIGURAÇÃO GUIADA</span>
                <h1>Onboarding funcional da empresa</h1>
                <p>Prepare a estrutura inicial, ative recursos e aplique modelos reais que alimentam as configurações usadas pelos módulos.</p>
            </div>

            <div class="onboarding-score">
                <strong>{{ $this->resumo['progresso'] }}%</strong>
                <small>implantação concluída</small>
                @if (! empty($data['finalizado_em']))
                    <em>Finalizado em {{ \Carbon\Carbon::parse($data['finalizado_em'])->format('d/m/Y H:i') }}</em>
                @endif
            </div>
        </section>

        <section class="onboarding-metrics">
            <article><span>Recursos ativos</span><strong>{{ $this->resumo['recursos_ativos'] }}/{{ $this->resumo['total_recursos'] }}</strong></article>
            <article><span>Modelo aplicado</span><strong>{{ $this->resumo['modelo'] }}</strong></article>
            <article><span>Visualização padrão</span><strong>{{ $this->resumo['visualizacao'] }}</strong></article>
        </section>

        <section class="pz-ux-block soft">
            <div class="pz-ux-head">
                <div>
                    <span class="pz-ux-kicker">Primeiros passos</span>
                    <h2>Guia inicial para não se perder</h2>
                    <p>Ordem recomendada para implantar a operação com segurança, usando dados reais salvos na configuração da empresa.</p>
                </div>
                <div class="pz-ux-actions">
                    <a class="pz-ux-action primary" href="#checklist-onboarding">Abrir checklist</a>
                    <a class="pz-ux-action subtle" href="#templates-onboarding">Escolher modelo</a>
                </div>
            </div>

            <div class="pz-ux-grid four">
                @foreach ($this->guiaPrimeirosPassos as $passo)
                    <article class="pz-ux-guide-card">
                        <span class="pz-ux-guide-icon">{{ $passo['numero'] }}</span>
                        <div>
                            <strong>{{ $passo['titulo'] }}</strong>
                            <span>{{ $passo['descricao'] }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <div wire:loading.delay class="pz-ux-block">
            <div class="pz-ux-loading is-visible">
                <span class="pz-ux-spinner"></span>
                <span>Processando alteração do onboarding...</span>
            </div>
            <div class="pz-ux-skeleton" style="margin-top: 12px;">
                <i></i><i></i><i></i>
            </div>
        </div>

        <section class="onboarding-grid two">
            <article id="checklist-onboarding" class="onboarding-card">
                <header>
                    <div>
                        <h2>Checklist inicial</h2>
                        <p>Essas etapas ficam salvas no banco e indicam o progresso da implantação.</p>
                    </div>
                </header>

                <div class="onboarding-steps">
                    @foreach ($this->etapas as $etapa)
                        <button type="button" wire:loading.attr="disabled" wire:click="toggleEtapa('{{ $etapa['codigo'] }}')" class="step {{ $etapa['feito'] ? 'done' : '' }}">
                            <b>{{ $etapa['feito'] ? '✓' : '•' }}</b>
                            <span>
                                <strong>{{ $etapa['titulo'] }}</strong>
                                <small>{{ $etapa['descricao'] }}</small>
                            </span>
                        </button>
                    @endforeach
                </div>
            </article>

            <article class="onboarding-card">
                <header>
                    <div>
                        <h2>Preferências de implantação</h2>
                        <p>Dados operacionais do onboarding, também persistidos em configuração.</p>
                    </div>
                </header>

                <div class="onboarding-form">
                    <label>
                        <span>Responsável pela implantação</span>
                        <input type="text" wire:model.defer="data.onboarding_preferencias.responsavel_implantacao" placeholder="Nome do responsável">
                    </label>

                    <label>
                        <span>Prazo alvo</span>
                        <input type="date" wire:model.defer="data.onboarding_preferencias.prazo_implantacao">
                    </label>

                    <label class="full">
                        <span>Observações</span>
                        <textarea wire:model.defer="data.onboarding_preferencias.observacoes" rows="4" placeholder="Ex: cliente inicia por RH, depois financeiro e documentos..."></textarea>
                    </label>

                    <div class="actions full">
                        <button type="button" wire:loading.attr="disabled" wire:target="salvarPreferencias" wire:click="salvarPreferencias">Salvar preferências</button>
                        <button type="button" wire:loading.attr="disabled" wire:target="finalizarOnboarding" wire:click="finalizarOnboarding" class="secondary" onclick="return confirm('Deseja marcar o onboarding como finalizado?')">Finalizar onboarding</button>
                    </div>
                </div>
            </article>
        </section>

        <section class="onboarding-card">
            <header>
                <div>
                    <h2>Recursos ativáveis</h2>
                    <p>Ao ativar/desativar, o recurso é gravado em <strong>onboarding_recursos</strong> e também sincronizado com <strong>modulos_ativos</strong>.</p>
                </div>
                <button type="button" wire:loading.attr="disabled" wire:click="habilitarRecursosBase" onclick="return confirm('Deseja habilitar todos os recursos base?')">Habilitar todos</button>
            </header>

            <div class="feature-grid">
                @foreach ($this->recursos as $recurso)
                    <button type="button" wire:loading.attr="disabled" wire:click="toggleRecurso('{{ $recurso['codigo'] }}')" class="feature {{ $recurso['ativo'] ? 'active' : '' }}">
                        <strong>{{ $recurso['titulo'] }}</strong>
                        <span>{{ $recurso['descricao'] }}</span>
                        <em>{{ $recurso['ativo'] ? 'Ativo' : 'Inativo' }}</em>
                    </button>
                @endforeach
            </div>
        </section>

        <section id="templates-onboarding" class="onboarding-card">
            <header>
                <div>
                    <h2>Templates aplicáveis</h2>
                    <p>Aplicar um modelo grava workflow, campos personalizados, template e visualização padrão.</p>
                </div>
            </header>

            <div class="template-grid">
                @foreach ($this->modelos() as $modelo)
                    <article>
                        <h3>{{ $modelo['titulo'] }}</h3>
                        <p><strong>Workflow:</strong> {{ implode(' → ', $modelo['workflow']) }}</p>
                        <p><strong>Campos:</strong> {{ implode(', ', $modelo['campos']) }}</p>
                        <button type="button" wire:loading.attr="disabled" onclick="return confirm('Aplicar este modelo vai atualizar workflow, campos e visualização padrão. Continuar?')" wire:click="aplicarModelo('{{ $modelo['codigo'] }}')">Aplicar modelo</button>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="pz-ux-block">
            <div class="pz-ux-head">
                <div>
                    <span class="pz-ux-kicker">Dicas nas páginas principais</span>
                    <h2>O que fazer primeiro em cada área</h2>
                    <p>Orientações curtas para reduzir dúvida operacional e manter a navegação limpa.</p>
                </div>
            </div>
            <div class="pz-ux-grid two">
                @foreach ($this->dicasPrincipais as $pagina => $dica)
                    <div class="pz-ux-tip"><b>?</b><div><strong>{{ $pagina }}</strong><br>{{ $dica }}</div></div>
                @endforeach
            </div>
        </section>
    </div>
</x-filament-panels::page>
