@php
    $empresa = $documento['nome_fantasia'] ?: ($documento['razao_social'] ?: '-');
    $prioridadeOperacional = $documento['prioridade_operacional'] ?? ['label' => 'Estável', 'motivo' => 'Atualize o documento sem sair desta página.', 'tom' => 'success', 'prazo' => '-'];
@endphp

<div class="documentos-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="documentos-resolver-titulo">
    <div class="documentos-modal-card documentos-modal-card--wide">
        <form wire:submit.prevent="resolverDocumentoRapido({{ $documento['id'] }})" class="documentos-resolver-form documentos-resolver-form--dialog">
            <div class="documentos-modal-header">
                <div>
                    <span class="pz-ux-kicker">Regularização documental</span>
                    <h2 id="documentos-resolver-titulo">{{ $documento['titulo'] }}</h2>
                    <p>Corrija o status, atualize o vencimento e anexe o arquivo necessário sem sair da página.</p>
                </div>
                <button type="button" class="documentos-modal-close" wire:click="fecharResolucaoDocumento" wire:loading.attr="disabled" aria-label="Fechar resolução">×</button>
            </div>

            <div class="documentos-resolver-header {{ $prioridadeOperacional['tom'] ?? 'success' }}">
                <div>
                    <span>Motivo da atenção</span>
                    <h3>{{ $prioridadeOperacional['motivo'] ?? 'Atualize o documento sem sair desta página.' }}</h3>
                    <p>Use esta tela para deixar o documento regularizado e registrado.</p>
                </div>
                <strong>{{ $prioridadeOperacional['label'] ?? 'Estável' }}</strong>
            </div>

            <div class="documentos-resolver-summary">
                <div><span>Empresa</span><strong>{{ $empresa }}</strong></div>
                <div><span>Tipo</span><strong>{{ ucfirst(str_replace('_', ' ', $documento['tipo'] ?? '-')) }}</strong></div>
                <div><span>Status atual</span><strong>{{ ucfirst(str_replace('_', ' ', $documento['status'] ?? '-')) }}</strong></div>
                <div><span>Vencimento</span><strong>{{ ! empty($documento['data_vencimento']) ? \Carbon\Carbon::parse($documento['data_vencimento'])->format('d/m/Y') : '-' }}</strong></div>
            </div>

            <div class="documentos-resolver-grid">
                <label class="documentos-resolver-field">
                    <span>Status</span>
                    <select wire:model.defer="resolverStatus.{{ $documento['id'] }}">
                        <option value="">Manter status atual</option>
                        @foreach (($documento['status_resolucao_options'] ?? $statusResolucaoOptions) as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('resolverStatus.' . $documento['id']) <small class="documentos-field-error">{{ $message }}</small> @enderror
                </label>

                <label class="documentos-resolver-field">
                    <span>Data de vencimento</span>
                    <input type="date" wire:model.defer="resolverDataVencimento.{{ $documento['id'] }}">
                    @error('resolverDataVencimento.' . $documento['id']) <small class="documentos-field-error">{{ $message }}</small> @enderror
                </label>

                <label class="documentos-resolver-field documentos-resolver-field--file">
                    <span>Arquivo principal</span>
                    <input type="file" wire:model="resolverArquivos.{{ $documento['id'] }}">
                    <small>{{ ! empty($documento['arquivo_url']) ? 'Enviar um novo arquivo substitui a referência principal.' : 'Anexe o arquivo para regularizar o item.' }}</small>
                    @error('resolverArquivos.' . $documento['id']) <small class="documentos-field-error">{{ $message }}</small> @enderror
                </label>

                <label class="documentos-resolver-toggle">
                    <input type="checkbox" wire:model.defer="resolverPortalAtivo.{{ $documento['id'] }}">
                    <span>Liberado no portal do cliente</span>
                </label>
            </div>

            <label class="documentos-resolver-field documentos-resolver-field--full">
                <span>Observação do ajuste</span>
                <textarea rows="4" wire:model.defer="resolverObservacao.{{ $documento['id'] }}" placeholder="Ex.: documento recebido do cliente, novo vencimento conferido, arquivo substituído."></textarea>
                @error('resolverObservacao.' . $documento['id']) <small class="documentos-field-error">{{ $message }}</small> @enderror
            </label>

            <div class="documentos-resolver-footer documentos-resolver-footer--dialog">
                <div>
                    @if (! empty($documento['arquivo_url']))
                        <a href="{{ $documento['arquivo_url'] }}" target="_blank" rel="noopener noreferrer">Abrir arquivo atual</a>
                    @endif
                    <a href="{{ $documento['enterprise_url'] ?? $documento['edit_url'] }}">Abrir edição completa</a>
                </div>
                <div class="documentos-modal-actions">
                    <x-filament::button type="button" color="gray" wire:click="fecharResolucaoDocumento" wire:loading.attr="disabled">
                        Fechar
                    </x-filament::button>
                    <x-filament::button type="submit" color="primary" icon="heroicon-m-check-circle" wire:loading.attr="disabled" wire:target="resolverDocumentoRapido({{ $documento['id'] }}),resolverArquivos.{{ $documento['id'] }}">
                        Salvar regularização
                    </x-filament::button>
                </div>
            </div>
        </form>
    </div>
</div>
