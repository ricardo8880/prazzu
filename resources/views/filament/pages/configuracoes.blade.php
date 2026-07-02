<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/contabilidade-ux-lote6.css') }}?v={{ file_exists(public_path('css/contabilidade-ux-lote6.css')) ? filemtime(public_path('css/contabilidade-ux-lote6.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/configuracoes-prazzu.css') }}">

    <div class="configuracoes-prazzu configuracoes-lote4">
        <section class="configuracoes-hero">
            <div>
                <span><i class="bi bi-sliders2-vertical"></i> PARÂMETROS DO ESCRITÓRIO</span>
                <h1>Central de configuração contábil</h1>
                <p>
                    Esta tela concentra apenas regras, preferências e parâmetros internos. Conteúdos operacionais,
                    como resolver pendências, aprovar documentos ou acompanhar prazos, devem permanecer nas abas próprias.
                </p>
            </div>

            <div class="configuracoes-actions">
                <button type="button" wire:click="restaurarPadrao" class="secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar padrões
                </button>
                <button type="button" wire:click="salvar" class="primary">
                    <i class="bi bi-check2-circle"></i> Salvar configurações
                </button>
            </div>
        </section>

        <section class="configuracoes-diretriz">
            <article>
                <i class="bi bi-bullseye"></i>
                <div>
                    <strong>Propósito desta aba</strong>
                    <p>Definir parâmetros globais do escritório, notificações, módulos, integrações, permissões e padrões.</p>
                </div>
            </article>
            <article>
                <i class="bi bi-signpost-split"></i>
                <div>
                    <strong>O que não fica aqui</strong>
                    <p>Execução de pendências, documentos aguardando cliente, aprovações e análises devem ficar nas abas operacionais.</p>
                </div>
            </article>
            <article>
                <i class="bi bi-link-45deg"></i>
                <div>
                    <strong>Conexão entre áreas</strong>
                    <p>Quando uma configuração impactar a operação, use orientação e links; não duplique o fluxo operacional.</p>
                </div>
            </article>
        </section>

        <section class="configuracoes-metricas">
            @foreach ($this->resumoConfiguracoes as $label => $value)
                <article>
                    <span>{{ $label }}</span>
                    <strong>{{ $value }}</strong>
                </article>
            @endforeach
        </section>

        <form wire:submit.prevent="salvar" class="configuracoes-form">
            <div class="configuracoes-form-card">
                <div class="configuracoes-section-title">
                    <i class="bi bi-gear-wide-connected"></i>
                    <div>
                        <h2>Parâmetros editáveis</h2>
                        <p>Revise por bloco, salve com intenção e mantenha a operação concentrada nas telas corretas.</p>
                    </div>
                </div>

                {{ $this->form }}
            </div>

            <div class="configuracoes-footer-actions">
                <button type="button" wire:click="restaurarPadrao" class="secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar padrões
                </button>
                <button type="submit" class="primary">
                    <i class="bi bi-check2-circle"></i> Salvar configurações
                </button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
