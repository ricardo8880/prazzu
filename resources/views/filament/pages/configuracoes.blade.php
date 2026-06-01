<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/configuracoes-prazzu.css') }}">

    <div class="configuracoes-prazzu">
        <section class="configuracoes-hero">
            <div>
                <span>CONFIGURAÇÕES REAIS</span>
                <h1>Central de parâmetros da empresa</h1>
                <p>
                    Ajuste módulos, workflows, notificações, aparência, automações, permissões, integrações,
                    carga de trabalho, templates e segurança. Tudo é salvo na tabela <strong>configuracoes</strong>.
                </p>
            </div>

            <div class="configuracoes-actions">
                <button type="button" wire:click="restaurarPadrao" class="secondary">
                    Restaurar padrões
                </button>
                <button type="button" wire:click="salvar" class="primary">
                    Salvar configurações
                </button>
            </div>
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
            {{ $this->form }}

            <div class="configuracoes-footer-actions">
                <button type="button" wire:click="restaurarPadrao" class="secondary">
                    Restaurar padrões
                </button>
                <button type="submit" class="primary">
                    Salvar configurações
                </button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
