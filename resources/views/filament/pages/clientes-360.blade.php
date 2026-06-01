
<!-- Lote 3 - Cliente 360 UX Melhorado -->
<div class="cliente-360">
    <div class="header">
        <h2>{{ $cliente->nome ?? 'Cliente' }}</h2>
        <span class="status">{{ $cliente->status ?? 'Sem status' }}</span>
    </div>

    <div class="sections">
        <div class="section">
            <h3>Situação Atual</h3>
            <p>{{ $cliente->situacao ?? 'Sem dados' }}</p>
        </div>

        <div class="section">
            <h3>Pendências</h3>
            @foreach($pendencias ?? [] as $p)
                <div class="item">{{ $p->descricao }}</div>
            @endforeach
        </div>

        <div class="section">
            <h3>Histórico</h3>
            @foreach($historicos ?? [] as $h)
                <div class="item">{{ $h->descricao }}</div>
            @endforeach
        </div>

        <div class="section">
            <h3>Documentos</h3>
            @foreach($documentos ?? [] as $d)
                <div class="item">{{ $d->nome }}</div>
            @endforeach
        </div>
    </div>
</div>
