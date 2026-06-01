<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/compliance-module.css') }}?v={{ file_exists(public_path('css/compliance-module.css')) ? filemtime(public_path('css/compliance-module.css')) : time() }}">
    <div class="compliance-page">
        <section class="compliance-hero"><div><span>COMPLIANCE</span><h1>Riscos</h1><p>Mapa enxuto de riscos reais, com score, responsável, prazo e ação direta para não deixar nada perdido.</p></div></section>
        <section class="compliance-stats">@foreach (($data['stats'] ?? []) as $stat)<article class="compliance-stat"><span>{{ $stat['label'] }}</span><strong>{{ $stat['value'] }}</strong><small>{{ $stat['hint'] }}</small></article>@endforeach</section>

        <section class="compliance-grid">
            <article class="compliance-card">
                <header><div><h2>Visão de riscos</h2><p>Itens ordenados por risco e vencimento.</p></div></header>
                <div class="compliance-table-wrap"><table class="compliance-table"><thead><tr><th>Risco</th><th>Empresa</th><th>Responsável</th><th>Score</th><th>Prioridade</th><th>Prazo</th><th>Ação</th></tr></thead><tbody>
                    @forelse (($data['risks'] ?? []) as $risk)
                        <tr><td><strong>{{ $risk['titulo'] }}</strong><br><small>{{ \Illuminate\Support\Str::limit($risk['descricao'] ?: 'Sem descrição', 90) }}</small></td><td>{{ $risk['empresa'] }}</td><td>{{ $risk['responsavel'] }}</td><td><span class="compliance-badge {{ $risk['tone'] }}">{{ $risk['risk_level'] }} · {{ $risk['score'] }}</span></td><td>{{ ucfirst($risk['prioridade']) }}</td><td>{{ $risk['vencimento'] }} @if($risk['is_late'])<br><span class="compliance-badge danger">Vencido</span>@endif</td><td><a class="compliance-link" href="{{ $risk['url'] }}">Abrir</a></td></tr>
                    @empty
                        <tr><td colspan="7" class="compliance-empty">Nenhum risco encontrado. Crie o primeiro risco ao lado.</td></tr>
                    @endforelse
                </tbody></table></div>
            </article>

            <div class="compliance-list">
                <article class="compliance-card">
                    <header><div><h2>Matriz simples</h2><p>Quantidade por nível de risco.</p></div></header>
                    <div class="compliance-matrix">@foreach (($data['matrix'] ?? []) as $m)<article><span>{{ $m['label'] }}</span><strong>{{ $m['count'] }}</strong></article>@endforeach</div>
                </article>
                <article class="compliance-card">
                    <header><div><h2>Novo risco</h2><p>Crie um item real em item_controles.</p></div></header>
                    <form wire:submit.prevent="criarRisco" class="compliance-form">
                        @if(count($data['options']['empresas'] ?? []) > 1)<label class="wide"><span>Empresa</span><select wire:model="empresaId"><option value="">Selecione</option>@foreach($data['options']['empresas'] as $e)<option value="{{ $e['id'] }}">{{ $e['nome'] }}</option>@endforeach</select></label>@endif
                        <label class="wide"><span>Título</span><input type="text" wire:model.defer="titulo" placeholder="Ex: Documento obrigatório sem evidência"></label>
                        <label class="wide"><span>Descrição</span><textarea rows="3" wire:model.defer="descricao" placeholder="Explique o risco e a possível consequência"></textarea></label>
                        <label><span>Responsável</span><select wire:model="responsavelId"><option value="">Automático</option>@foreach($data['options']['responsaveis'] ?? [] as $r)<option value="{{ $r['id'] }}">{{ $r['nome'] }}</option>@endforeach</select></label>
                        <label><span>Prioridade</span><select wire:model="prioridade"><option value="baixa">Baixa</option><option value="media">Média</option><option value="alta">Alta</option><option value="urgente">Urgente</option></select></label>
                        <label><span>Probabilidade</span><select wire:model="probabilidade">@for($i=1;$i<=5;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor</select></label>
                        <label><span>Impacto</span><select wire:model="impacto">@for($i=1;$i<=5;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor</select></label>
                        <label class="wide"><span>Prazo de mitigação</span><input type="date" wire:model="dataVencimento"></label>
                        <div class="wide"><button type="submit">Criar risco</button></div>
                    </form>
                </article>
            </div>
        </section>
    </div>
</x-filament-panels::page>
