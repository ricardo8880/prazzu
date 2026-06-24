<x-filament-panels::page>
    <style>
        .prazzu-admin-page{display:grid;gap:20px}.prazzu-hero{border-radius:24px;padding:24px;background:linear-gradient(135deg,#111827,#1f2937);color:#fff;display:flex;justify-content:space-between;gap:20px;align-items:flex-start}.prazzu-hero h1{font-size:28px;font-weight:900;margin:0}.prazzu-hero p{margin:8px 0 0;color:#d1d5db;max-width:860px}.prazzu-grid{display:grid;gap:16px}.prazzu-grid.two{grid-template-columns:repeat(2,minmax(0,1fr))}.prazzu-grid.four{grid-template-columns:repeat(4,minmax(0,1fr))}.prazzu-card{border:1px solid #e5e7eb;border-radius:20px;background:#fff;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.06)}.prazzu-card h2{font-size:18px;font-weight:900;margin:0;color:#111827}.prazzu-card p{color:#64748b;margin:6px 0 0}.prazzu-stat span{display:block;color:#64748b;font-size:13px}.prazzu-stat strong{display:block;font-size:24px;margin-top:6px;color:#111827}.prazzu-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:16px}.prazzu-field span{display:block;color:#64748b;font-size:12px;font-weight:800;margin-bottom:6px}.prazzu-input,.prazzu-select{width:100%;border:1px solid #d1d5db;border-radius:12px;padding:10px 12px;background:#fff}.prazzu-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}.prazzu-button,.prazzu-link{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:12px;padding:10px 14px;background:#111827;color:#fff;font-weight:800;text-decoration:none;cursor:pointer}.prazzu-link.light{background:#f3f4f6;color:#111827}.prazzu-muted{color:#64748b;font-size:12px}.prazzu-info{display:grid;gap:8px;margin-top:12px}.prazzu-info div{display:flex;justify-content:space-between;gap:12px;border-bottom:1px solid #f1f5f9;padding:8px 0}.prazzu-info strong{color:#111827}@media(max-width:960px){.prazzu-grid.two,.prazzu-grid.four,.prazzu-form{grid-template-columns:1fr}.prazzu-hero{display:block}}
    </style>

    <div class="prazzu-admin-page">
        <section class="prazzu-hero">
            <div>
                <h1>Empresa</h1>
                <p>Mantenha os dados do escritório atualizados para documentos, comunicações e identificação da conta.</p>
            </div>
        </section>

        <section class="prazzu-grid four">
            @forelse ($this->resumo() as $stat)
                <article class="prazzu-card prazzu-stat">
                    <span>{{ $stat['label'] }}</span>
                    <strong>{{ $stat['value'] }}</strong>
                    <small class="prazzu-muted">{{ $stat['hint'] }}</small>
                </article>
            @empty
                <article class="prazzu-card"><strong>Nenhuma empresa disponível.</strong></article>
            @endforelse
        </section>

        <section class="prazzu-grid two">
            <article class="prazzu-card">
                <h2>Dados principais</h2>
                <p>Edite os dados que o administrador mais procura no dia a dia.</p>

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
                    <label class="prazzu-field"><span>E-mail</span><input class="prazzu-input" type="email" wire:model.defer="email"></label>
                    <label class="prazzu-field"><span>Telefone / WhatsApp</span><input class="prazzu-input" wire:model.defer="telefone"></label>
                    <label class="prazzu-field"><span>Responsável</span><input class="prazzu-input" wire:model.defer="responsavel_nome"></label>
                    <label class="prazzu-field"><span>Status</span><input class="prazzu-input" wire:model.defer="status"></label>
                    <label class="prazzu-field"><span>Plano</span><input class="prazzu-input" wire:model.defer="plano"></label>
                </div>

                <div class="prazzu-actions">
                    @if ($this->podeEditar())
                        <button class="prazzu-button" type="button" wire:click="salvarEmpresa">Salvar empresa</button>
                    @endif
                    @if ($this->resourceUrl())
                        <a class="prazzu-link light" href="{{ $this->resourceUrl() }}">Abrir cadastro completo</a>
                    @endif
                </div>
            </article>

            <article class="prazzu-card">
                <h2>Informações da conta</h2>
                <p>Confira os principais dados usados na operação e na administração do escritório.</p>
                <div class="prazzu-info">
                    <div><span>Identidade</span><strong>Razão social, nome fantasia e CNPJ</strong></div>
                    <div><span>Contato</span><strong>E-mail, telefone e responsável</strong></div>
                    <div><span>Conta</span><strong>Status e plano</strong></div>
                    <div><span>Acesso</span><strong>Dados disponíveis para administradores</strong></div>
                </div>
            </article>
        </section>
    </div>
</x-filament-panels::page>
