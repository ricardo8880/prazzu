@php
    $empresa = $documento['nome_fantasia'] ?: ($documento['razao_social'] ?: '-');
    $prioridadeOperacional = $documento['prioridade_operacional'] ?? [
        'label' => 'Estável',
        'motivo' => 'Atualize o documento sem sair desta página.',
        'tom' => 'success',
        'prazo' => '-',
    ];
    $statusAtual = ucfirst(str_replace('_', ' ', (string) ($documento['status'] ?? '-')));
    $tipo = ucfirst(str_replace('_', ' ', (string) ($documento['tipo'] ?? '-')));
    $vencimento = ! empty($documento['data_vencimento']) ? \Carbon\Carbon::parse($documento['data_vencimento'])->format('d/m/Y') : '-';
@endphp

<div class="documentos-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="documentos-resolver-titulo">
    <div class="documentos-modal-card documentos-modal-card--wide">
        <form wire:submit.prevent="resolverDocumentoRapido({{ $documento['id'] }})" class="documentos-resolver-form documentos-resolver-form--dialog">
            <div class="documentos-modal-header">
                <div class="documentos-modal-title-block">
                    <span class="pz-ux-kicker">Centro de Documentos</span>
                    <h2 id="documentos-resolver-titulo">Regularizar documento</h2>
                    <p>{{ $documento['titulo'] }}</p>
                </div>
                <button type="button" class="documentos-modal-close" wire:click="fecharResolucaoDocumento" wire:loading.attr="disabled" aria-label="Fechar regularização">×</button>
            </div>

            <div class="documentos-focus-card {{ $prioridadeOperacional['tom'] ?? 'success' }}">
                <div class="documentos-focus-icon">!</div>
                <div>
                    <span>Atenção necessária</span>
                    <strong>{{ $prioridadeOperacional['motivo'] ?? 'Atualize o documento sem sair desta página.' }}</strong>
                    <p>Confira os dados abaixo, anexe o arquivo quando necessário e salve a regularização.</p>
                </div>
                <em>{{ $prioridadeOperacional['label'] ?? 'Estável' }}</em>
            </div>

            <section class="documentos-modal-section" aria-label="Resumo do documento">
                <div class="documentos-section-heading">
                    <span>1</span>
                    <div>
                        <h3>Resumo</h3>
                        <p>Dados principais para você conferir antes de alterar.</p>
                    </div>
                </div>

                <div class="documentos-summary-list">
                    <div><span>Empresa</span><strong>{{ $empresa }}</strong></div>
                    <div><span>Documento</span><strong>{{ $documento['titulo'] }}</strong></div>
                    <div><span>Tipo</span><strong>{{ $tipo }}</strong></div>
                    <div><span>Status atual</span><strong>{{ $statusAtual }}</strong></div>
                    <div><span>Vencimento</span><strong>{{ $vencimento }}</strong></div>
                    <div><span>Competência</span><strong>{{ $documento['competencia'] ?? '-' }}</strong></div>
                </div>
            </section>

            <section class="documentos-modal-section" aria-label="Regularização do documento">
                <div class="documentos-section-heading">
                    <span>2</span>
                    <div>
                        <h3>Regularização</h3>
                        <p>Preencha somente o que precisa mudar. O restante será mantido.</p>
                    </div>
                </div>

                <div class="documentos-form-stack">
                    <div class="documentos-form-row documentos-form-row--two">
                        <label class="documentos-resolver-field">
                            <span>Novo status</span>
                            <select wire:model.defer="resolverStatus.{{ $documento['id'] }}">
                                <option value="">Manter status atual</option>
                                @foreach (($documento['status_resolucao_options'] ?? $statusResolucaoOptions) as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('resolverStatus.' . $documento['id']) <small class="documentos-field-error">{{ $message }}</small> @enderror
                        </label>

                        <label class="documentos-resolver-field">
                            <span>Nova data de vencimento</span>
                            <input type="date" wire:model.defer="resolverDataVencimento.{{ $documento['id'] }}">
                            @error('resolverDataVencimento.' . $documento['id']) <small class="documentos-field-error">{{ $message }}</small> @enderror
                        </label>
                    </div>

                    <label class="documentos-resolver-field documentos-file-drop">
                        <span>Arquivo do documento</span>
                        <input type="file" wire:model="resolverArquivos.{{ $documento['id'] }}">
                        <small>{{ ! empty($documento['arquivo_url']) ? 'Escolha um novo arquivo apenas se quiser substituir o arquivo atual.' : 'Anexe o arquivo principal para deixar este documento completo.' }}</small>
                        @error('resolverArquivos.' . $documento['id']) <small class="documentos-field-error">{{ $message }}</small> @enderror
                    </label>

                    <label class="documentos-portal-option">
                        <input type="checkbox" wire:model.defer="resolverPortalAtivo.{{ $documento['id'] }}">
                        <span>
                            <strong>Liberar no portal do cliente</strong>
                            <small>Ative somente quando o cliente puder consultar este documento.</small>
                        </span>
                    </label>

                    <label class="documentos-resolver-field documentos-resolver-field--full">
                        <span>Observação do ajuste</span>
                        <textarea rows="5" wire:model.defer="resolverObservacao.{{ $documento['id'] }}" placeholder="Ex.: documento recebido do cliente, vencimento conferido, arquivo substituído ou pendência resolvida."></textarea>
                        @error('resolverObservacao.' . $documento['id']) <small class="documentos-field-error">{{ $message }}</small> @enderror
                    </label>
                </div>
            </section>

            <div class="documentos-resolver-footer documentos-resolver-footer--dialog">
                <div class="documentos-current-file">
                    @if (! empty($documento['arquivo_url']))
                        <span>Arquivo atual</span>
                        <a href="{{ $documento['arquivo_url'] }}" target="_blank" rel="noopener noreferrer">Abrir arquivo existente</a>
                    @else
                        <span>Arquivo atual</span>
                        <strong>Nenhum arquivo anexado</strong>
                    @endif
                </div>
                <div class="documentos-modal-actions">
                    <x-filament::button type="button" color="gray" wire:click="fecharResolucaoDocumento" wire:loading.attr="disabled">
                        Cancelar
                    </x-filament::button>
                    <x-filament::button type="submit" color="primary" icon="heroicon-m-check-circle" wire:loading.attr="disabled" wire:target="resolverDocumentoRapido({{ $documento['id'] }}),resolverArquivos.{{ $documento['id'] }}">
                        Salvar regularização
                    </x-filament::button>
                </div>
            </div>
        </form>
    </div>
</div>
