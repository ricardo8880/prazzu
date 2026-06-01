@php
    use App\Services\PlanoService;

    $codigoPlano = $codigoPlano ?? $codigo ?? null;
    $planoDados = $plano ?? [];
    $configuradoresConsumo = $planoDados['configuradores_consumo'] ?? PlanoService::configuradoresConsumo($codigoPlano);
    $valorBaseMensal = (float) ($planoDados['valor_mensal'] ?? PlanoService::valorMensal($codigoPlano));
    $nomeFormulario = $nomeFormulario ?? 'configuracao_consumo';
    $habilitarCamposFormulario = (bool) ($habilitarCamposFormulario ?? false);
    $compacto = (bool) ($compacto ?? false);
@endphp

@if(! empty($configuradoresConsumo))
    <div
        class="prazzu-plan-consumption-config {{ $compacto ? 'is-compact' : '' }}"
        data-plano-consumo-config
        data-base-price="{{ number_format($valorBaseMensal, 2, '.', '') }}"
    >
        <div class="prazzu-plan-consumption-config__header">
            <strong>Configure sua quantidade</strong>
            <small>Escolha quanto pretende usar por mês. O valor muda automaticamente.</small>
        </div>

        <div class="prazzu-plan-consumption-config__fields">
            @foreach($configuradoresConsumo as $chaveConsumo => $configuradorConsumo)
                @php
                    $opcoesConsumo = $configuradorConsumo['opcoes'] ?? [];
                @endphp

                @if(! empty($opcoesConsumo))
                    <label class="prazzu-plan-consumption-config__field">
                        <span>{{ $configuradorConsumo['label'] ?? ucfirst(str_replace('_', ' ', $chaveConsumo)) }}</span>
                        <select
                            data-plano-consumo-select
                            @if($habilitarCamposFormulario && $codigoPlano)
                                name="{{ $nomeFormulario }}[{{ $codigoPlano }}][{{ $chaveConsumo }}]"
                            @endif
                        >
                            @foreach($opcoesConsumo as $opcaoConsumo)
                                @php
                                    $opcaoSobConsulta = (bool) ($opcaoConsumo['sob_consulta'] ?? false);
                                    $adicionalOpcao = $opcaoSobConsulta ? 0 : (float) ($opcaoConsumo['adicional'] ?? 0);
                                    $labelOpcao = $opcaoConsumo['label'] ?? (($opcaoConsumo['quantidade'] ?? 0) . ' ' . ($configuradorConsumo['unidade'] ?? ''));
                                @endphp
                                <option
                                    value="{{ $opcaoConsumo['quantidade'] ?? '' }}"
                                    data-additional-price="{{ number_format($adicionalOpcao, 2, '.', '') }}"
                                    data-consultation="{{ $opcaoSobConsulta ? 'true' : 'false' }}"
                                >
                                    {{ $labelOpcao }}{{ (! $opcaoSobConsulta && $adicionalOpcao > 0) ? ' (+R$ ' . number_format($adicionalOpcao, 2, ',', '.') . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                @endif
            @endforeach
        </div>

        <div class="prazzu-plan-consumption-config__total">
            <span>Total estimado</span>
            <strong data-plano-consumo-total>R$ {{ number_format($valorBaseMensal, 2, ',', '.') }}/mês</strong>
        </div>

        @if($habilitarCamposFormulario && $codigoPlano)
            <input
                type="hidden"
                name="{{ $nomeFormulario }}[{{ $codigoPlano }}][valor_estimado]"
                value="{{ number_format($valorBaseMensal, 2, '.', '') }}"
                data-plano-consumo-total-input
            >
        @endif
    </div>

    @once
        <style>
            .prazzu-plan-consumption-config {
                margin-top: 16px;
                padding: 14px;
                border: 1px solid rgba(148, 163, 184, 0.35);
                border-radius: 16px;
                background: rgba(248, 250, 252, 0.94);
                display: grid;
                gap: 12px;
            }

            .prazzu-plan-consumption-config__header {
                display: grid;
                gap: 3px;
            }

            .prazzu-plan-consumption-config__header strong {
                color: #111827;
                font-size: 14px;
                font-weight: 800;
            }

            .prazzu-plan-consumption-config__header small {
                color: #64748b;
                font-size: 12px;
                line-height: 1.45;
            }

            .prazzu-plan-consumption-config__fields {
                display: grid;
                gap: 10px;
            }

            .prazzu-plan-consumption-config__field {
                display: grid;
                gap: 6px;
                margin: 0;
            }

            .prazzu-plan-consumption-config__field span {
                color: #334155;
                font-size: 12px;
                font-weight: 700;
            }

            .prazzu-plan-consumption-config__field select {
                width: 100%;
                border: 1px solid #cbd5e1;
                border-radius: 12px;
                background: #ffffff;
                color: #0f172a;
                font-size: 13px;
                font-weight: 600;
                padding: 10px 12px;
                outline: none;
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
            }

            .prazzu-plan-consumption-config__field select:focus {
                border-color: #6366f1;
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.14);
            }

            .prazzu-plan-consumption-config__total {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding-top: 10px;
                border-top: 1px solid rgba(148, 163, 184, 0.30);
                color: #475569;
                font-size: 13px;
                font-weight: 700;
            }

            .prazzu-plan-consumption-config__total strong {
                color: #4f46e5;
                font-size: 15px;
                font-weight: 900;
                white-space: nowrap;
            }

            .prazzu-plan-consumption-config.is-compact {
                padding: 12px;
                border-radius: 14px;
            }
        </style>

        <script>
            (function () {
                const inicializarConfiguradoresDeConsumo = function () {
                    const formatCurrency = function (value) {
                        return value.toLocaleString('pt-BR', {
                            style: 'currency',
                            currency: 'BRL',
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        }) + '/mês';
                    };

                    document.querySelectorAll('[data-plano-consumo-config]').forEach(function (configurador) {
                        if (configurador.dataset.initialized === 'true') {
                            return;
                        }

                        configurador.dataset.initialized = 'true';

                        const basePrice = parseFloat(configurador.dataset.basePrice || '0');
                        const totalElement = configurador.querySelector('[data-plano-consumo-total]');
                        const totalInput = configurador.querySelector('[data-plano-consumo-total-input]');
                        const selects = configurador.querySelectorAll('[data-plano-consumo-select]');

                        const atualizarTotal = function () {
                            let total = basePrice;

                            let exigeConsulta = false;

                            selects.forEach(function (select) {
                                const selectedOption = select.options[select.selectedIndex];

                                if (selectedOption?.dataset?.consultation === 'true') {
                                    exigeConsulta = true;
                                    return;
                                }

                                total += parseFloat(selectedOption?.dataset?.additionalPrice || '0');
                            });

                            if (totalElement) {
                                totalElement.textContent = exigeConsulta ? 'Sob consulta' : formatCurrency(total);
                            }

                            if (totalInput) {
                                totalInput.value = exigeConsulta ? 'sob_consulta' : total.toFixed(2);
                            }
                        };

                        selects.forEach(function (select) {
                            select.addEventListener('change', atualizarTotal);
                        });

                        atualizarTotal();
                    });
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', inicializarConfiguradoresDeConsumo);
                } else {
                    inicializarConfiguradoresDeConsumo();
                }

                document.addEventListener('livewire:navigated', inicializarConfiguradoresDeConsumo);
            })();
        </script>
    @endonce
@endif
