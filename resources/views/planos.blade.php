<x-filament-panels::page>
    @php
        $planosComerciais = \App\Services\PlanoService::planos();
        $cardsServicos = [
            'Documentos e solicitações',
            'Itens de controle',
            'Checklist por item',
            'Comentários internos',
            'Timeline e histórico',
            'Anexos centralizados',
            'Kanban e calendário',
            'Portal do cliente',
            'Aprovações internas',
            'Controle de contratos',
            'SLA e alertas',
            'Relatórios e exportações',
            'Auditoria',
            'BI e produtividade',
            'Fluxos operacionais',
            'White label',
        ];
    @endphp

    <div style="max-width: 1440px; margin: 0 auto; padding: 24px; color: #111827;">
        <div style="text-align: center; margin-bottom: 48px;">
            <span style="display: inline-flex; align-items: center; padding: 8px 14px; border-radius: 999px; background: #ecfdf5; color: #047857; font-size: 13px; font-weight: 800; letter-spacing: .02em;">
                Planos para contabilidades
            </span>

            <h1 style="margin: 18px auto 0; max-width: 980px; font-size: clamp(32px, 5vw, 56px); line-height: 1.05; font-weight: 900;">
                Escolha um plano que acompanha o crescimento da sua operação contábil.
            </h1>

            <p style="margin: 18px auto 0; max-width: 860px; color: #6b7280; font-size: 18px; line-height: 1.7;">
                Centralize documentos, aprovações, anexos, prazos, relatórios e atendimento ao cliente em um único lugar.
                IA e Clicksign ficam fora do gratuito para evitar custo invisível, mas podem ser contratados nos planos pagos.
            </p>
        </div>

        <div style="margin-bottom: 42px; padding: 28px; border-radius: 28px; background: linear-gradient(135deg, #f8fafc, #ecfdf5); border: 1px solid #d1fae5;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; flex-wrap: wrap;">
                <div style="max-width: 760px;">
                    <h2 style="font-size: 28px; font-weight: 900; margin: 0;">Tudo que sua contabilidade consegue organizar com a Prazzu</h2>
                    <p style="margin: 10px 0 0; color: #4b5563; line-height: 1.7;">
                        Os cards abaixo não mostram só limites. Eles mostram o ecossistema do produto: operação, documentos, equipe, cliente e gestão.
                    </p>
                </div>

                <div style="padding: 14px 18px; border-radius: 18px; background: white; border: 1px solid #d1d5db; color: #374151; font-weight: 700;">
                    Sem fidelidade obrigatória
                </div>
            </div>

            <div style="margin-top: 26px; display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px;">
                @foreach($cardsServicos as $servico)
                    <div style="background: rgba(255,255,255,.86); border: 1px solid #e5e7eb; border-radius: 16px; padding: 14px 16px; font-weight: 700; color: #374151;">
                        ✓ {{ $servico }}
                    </div>
                @endforeach
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 22px; align-items: stretch;">
            @foreach($planosComerciais as $codigoPlano => $planoComercial)
                @php
                    $isDestaque = ! empty($planoComercial['destaque']);
                    $isEnterprise = $codigoPlano === \App\Services\PlanoService::ENTERPRISE;
                    $configuraveis = $planoComercial['configuraveis'] ?? [];
                    $servicos = $planoComercial['servicos'] ?? [];
                    $naoIncluso = $planoComercial['nao_incluso'] ?? [];
                @endphp

                <div style="position: relative; display: flex; flex-direction: column; background: {{ $isDestaque ? '#ffffff' : ($isEnterprise ? '#111827' : '#ffffff') }}; color: {{ $isEnterprise ? '#ffffff' : '#111827' }}; border: {{ $isDestaque ? '2px solid #10b981' : '1px solid #e5e7eb' }}; border-radius: 28px; padding: 28px; box-shadow: {{ $isDestaque ? '0 24px 60px rgba(16, 185, 129, .18)' : '0 14px 34px rgba(15, 23, 42, .06)' }};">
                    @if(! empty($planoComercial['tag']))
                        <div style="display: inline-flex; align-self: flex-start; padding: 7px 12px; border-radius: 999px; background: {{ $isDestaque ? '#10b981' : ($isEnterprise ? '#374151' : '#f3f4f6') }}; color: {{ $isDestaque || $isEnterprise ? '#ffffff' : '#374151' }}; font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: .04em;">
                            {{ $planoComercial['tag'] }}
                        </div>
                    @endif

                    <h2 style="margin: 18px 0 0; font-size: 30px; font-weight: 900;">{{ $planoComercial['nome'] }}</h2>

                    <div style="margin-top: 12px; display: flex; align-items: baseline; gap: 6px;">
                        <strong style="font-size: 42px; line-height: 1; font-weight: 950;">{{ $planoComercial['preco'] }}</strong>
                    </div>

                    <p style="margin: 16px 0 0; color: {{ $isEnterprise ? '#d1d5db' : '#6b7280' }}; line-height: 1.65; min-height: 78px;">
                        {{ $planoComercial['descricao'] }}
                    </p>

                    <div style="margin-top: 22px; padding-top: 22px; border-top: 1px solid {{ $isEnterprise ? '#374151' : '#e5e7eb' }};">
                        <h3 style="margin: 0 0 14px; font-size: 15px; font-weight: 900; text-transform: uppercase; letter-spacing: .05em; color: {{ $isEnterprise ? '#d1d5db' : '#374151' }};">
                            O que está incluso
                        </h3>

                        <div style="display: flex; flex-direction: column; gap: 11px;">
                            @foreach($planoComercial['itens'] ?? [] as $itemPlano)
                                <div style="display: flex; gap: 9px; align-items: flex-start; color: {{ $isEnterprise ? '#f9fafb' : '#374151' }}; line-height: 1.45;">
                                    <span style="color: #10b981; font-weight: 900;">✓</span>
                                    <span>{{ $itemPlano }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if(count($servicos))
                        <div style="margin-top: 22px; padding: 16px; border-radius: 18px; background: {{ $isEnterprise ? '#1f2937' : '#f9fafb' }}; border: 1px solid {{ $isEnterprise ? '#374151' : '#e5e7eb' }};">
                            <strong style="display: block; margin-bottom: 10px; font-size: 14px;">Serviços do plano</strong>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                @foreach($servicos as $servico)
                                    <span style="display: inline-flex; padding: 6px 9px; border-radius: 999px; background: {{ $isEnterprise ? '#111827' : '#ffffff' }}; border: 1px solid {{ $isEnterprise ? '#4b5563' : '#e5e7eb' }}; font-size: 12px; font-weight: 700; color: {{ $isEnterprise ? '#e5e7eb' : '#4b5563' }};">
                                        {{ $servico }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(count($configuraveis))
                        <div style="margin-top: 22px; display: flex; flex-direction: column; gap: 14px;">
                            <h3 style="margin: 0; font-size: 15px; font-weight: 900; text-transform: uppercase; letter-spacing: .05em; color: {{ $isEnterprise ? '#d1d5db' : '#374151' }};">
                                Ajuste conforme sua necessidade
                            </h3>

                            @foreach($configuraveis as $nomeConfiguravel => $opcoes)
                                <label style="display: block;">
                                    <span style="display: block; margin-bottom: 7px; font-size: 13px; font-weight: 800; text-transform: capitalize; color: {{ $isEnterprise ? '#e5e7eb' : '#4b5563' }};">
                                        {{ str_replace('_', ' ', $nomeConfiguravel) }}
                                    </span>
                                    <select style="width: 100%; padding: 12px 13px; border-radius: 14px; border: 1px solid {{ $isEnterprise ? '#4b5563' : '#d1d5db' }}; background: {{ $isEnterprise ? '#111827' : '#ffffff' }}; color: {{ $isEnterprise ? '#ffffff' : '#111827' }}; font-weight: 700;">
                                        @foreach($opcoes as $opcao)
                                            <option>
                                                {{ $opcao['label'] }}@if(isset($opcao['valor']) && $opcao['valor'] !== null && $opcao['valor'] > 0) — +R$ {{ number_format($opcao['valor'], 0, ',', '.') }}/mês @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    @if(count($naoIncluso))
                        <div style="margin-top: 22px; padding: 14px; border-radius: 16px; background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; font-size: 13px; line-height: 1.55;">
                            <strong>Não incluso no Free:</strong> {{ implode(', ', $naoIncluso) }}.
                        </div>
                    @endif

                    <a href="{{ $isEnterprise ? route('login') : route('empresa.cadastro.create', ['plano' => $codigoPlano]) }}"
                       style="margin-top: auto; display: block; text-align: center; padding: 15px 18px; border-radius: 16px; background: {{ $isDestaque ? '#10b981' : ($isEnterprise ? '#ffffff' : '#111827') }}; color: {{ $isEnterprise ? '#111827' : '#ffffff' }}; font-weight: 900; text-decoration: none;">
                        {{ $isEnterprise ? 'Falar com especialista' : 'Começar neste plano' }}
                    </a>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 34px; text-align: center; color: #6b7280; line-height: 1.7;">
            <p style="margin: 0;">IA e Clicksign não entram no plano gratuito porque geram custo operacional real. Nos planos pagos, eles aparecem como adicionais para o cliente contratar apenas quando precisar.</p>
        </div>
    </div>
</x-filament-panels::page>
