<?php
    $empresa = $detail['empresa'] ?? [];
    $storage = $detail['armazenamento'] ?? [];
    $tarefas = $detail['tarefas'] ?? [];
    $atendimentos = $detail['atendimentos'] ?? [];
    $portal = $detail['portal'] ?? [];
    $financeiro = $detail['financeiro'] ?? [];
    $contratos = $detail['contratos'] ?? [];
    $validade = $detail['validade'] ?? [];
    $governanca = $detail['governanca'] ?? [];
    $acoes = collect($detail['acoes'] ?? [])->filter(fn ($action) => filled($action['url'] ?? null));
    $recomendacoes = $detail['recomendacoes'] ?? [];
    $pesados = $storage['pesados'] ?? [];
?>

<style>
    .storage-client-modal { display: grid; gap: 1rem; }
    .storage-client-hero { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(260px, .8fr); gap: 1rem; align-items: stretch; }
    .storage-client-card, .storage-client-section, .storage-client-kpi { border: 1px solid rgba(148, 163, 184, .22); border-radius: 20px; padding: 1rem; background: rgba(248, 250, 252, .76); }
    .dark .storage-client-card, .dark .storage-client-section, .dark .storage-client-kpi { background: rgba(15, 23, 42, .58); border-color: rgba(148, 163, 184, .16); }
    .storage-client-card h3, .storage-client-section h4 { margin: 0; font-weight: 900; color: rgb(15, 23, 42); }
    .dark .storage-client-card h3, .dark .storage-client-section h4 { color: white; }
    .storage-client-card p { margin: .35rem 0 0; color: rgb(100, 116, 139); font-size: .86rem; line-height: 1.5; }
    .storage-client-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .75rem; }
    .storage-client-kpi span { display: block; font-size: .7rem; text-transform: uppercase; letter-spacing: .08em; font-weight: 850; color: rgb(100, 116, 139); }
    .storage-client-kpi strong { display: block; margin-top: .3rem; font-size: 1.25rem; font-weight: 950; color: rgb(15, 23, 42); }
    .dark .storage-client-kpi strong { color: white; }
    .storage-client-progress { height: .65rem; border-radius: 999px; background: rgba(148, 163, 184, .20); overflow: hidden; margin-top: .75rem; color: rgb(34, 197, 94); }
    .storage-client-progress.warning { color: rgb(245, 158, 11); }
    .storage-client-progress.danger { color: rgb(239, 68, 68); }
    .storage-client-progress span { display:block; height:100%; border-radius: inherit; max-width:100%; background: currentColor; }
    .storage-client-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem; }
    .storage-client-section h4 { margin-bottom: .65rem; }
    .storage-client-line { display: flex; justify-content: space-between; gap: .75rem; padding: .38rem 0; border-bottom: 1px solid rgba(148, 163, 184, .12); font-size: .84rem; color: rgb(100, 116, 139); }
    .storage-client-line:last-child { border-bottom: 0; }
    .storage-client-line strong { color: rgb(15, 23, 42); font-weight: 900; text-align: right; }
    .dark .storage-client-line strong { color: white; }
    .storage-client-recos { display: grid; gap: .5rem; }
    .storage-client-reco { border-radius: 14px; padding: .65rem .75rem; font-size: .84rem; font-weight: 700; background: rgba(148, 163, 184, .13); color: rgb(71, 85, 105); }
    .storage-client-reco.success { background: rgba(34, 197, 94, .12); color: rgb(21, 128, 61); }
    .storage-client-reco.warning { background: rgba(245, 158, 11, .14); color: rgb(180, 83, 9); }
    .storage-client-reco.danger { background: rgba(239, 68, 68, .13); color: rgb(185, 28, 28); }
    .storage-client-actions { display: flex; flex-wrap: wrap; gap: .5rem; }
    .storage-client-action { display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; padding: .5rem .8rem; font-size: .78rem; font-weight: 900; text-decoration: none; border: 1px solid rgba(124, 58, 237, .20); background: rgba(124, 58, 237, .10); color: rgb(109, 40, 217); }
    .storage-client-action.primary { background: rgb(124, 58, 237); color: white; }
    .storage-client-files { display: grid; gap: .45rem; }
    .storage-client-file { display: flex; justify-content: space-between; gap: .75rem; border-radius: 14px; padding: .6rem .7rem; background: rgba(248, 250, 252, .85); font-size: .82rem; color: rgb(100, 116, 139); }
    .dark .storage-client-file { background: rgba(15, 23, 42, .48); }
    .storage-client-file strong { color: rgb(15, 23, 42); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .dark .storage-client-file strong { color: white; }
    @media (max-width: 900px) { .storage-client-hero, .storage-client-grid { grid-template-columns: 1fr; } .storage-client-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>

<div class="storage-client-modal">
    <section class="storage-client-hero">
        <article class="storage-client-card">
            <h3><?php echo e($empresa['nome'] ?? 'Cliente'); ?></h3>
            <p><?php echo e($empresa['razao_social'] ?? 'Razão social não informada'); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($empresa['cnpj'])): ?> · CNPJ <?php echo e($empresa['cnpj']); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></p>
            <p>Status: <strong><?php echo e(ucfirst((string) ($empresa['status'] ?? 'não informado'))); ?></strong> · Plano: <strong><?php echo e($empresa['plano'] ?? 'sem plano'); ?></strong> · Desde: <strong><?php echo e($empresa['desde'] ?? 'Não informado'); ?></strong></p>
            <p>Responsável: <strong><?php echo e($empresa['responsavel'] ?: 'Não informado'); ?></strong> · Portal: <strong><?php echo e(($empresa['portal_ativo'] ?? false) ? 'Ativo' : 'Inativo'); ?></strong></p>
        </article>
        <article class="storage-client-card">
            <h3><?php echo e($storage['total_formatado'] ?? '0 B'); ?> usados</h3>
            <p>Limite: <?php echo e($storage['limite_formatado'] ?? '0 B'); ?> · <?php echo e($storage['percentual'] ?? 0); ?>% utilizado</p>
            <div class="storage-client-progress <?php echo e($storage['tom'] ?? 'success'); ?>"><span style="width: <?php echo e(min(100, (int) ($storage['percentual'] ?? 0))); ?>%"></span></div>
            <p>Espaço recuperável estimado: <strong><?php echo e($storage['recuperavel_formatado'] ?? '0 B'); ?></strong></p>
        </article>
    </section>

    <section class="storage-client-kpis">
        <article class="storage-client-kpi"><span>Arquivos</span><strong><?php echo e(number_format((int) ($storage['arquivos'] ?? 0), 0, ',', '.')); ?></strong></article>
        <article class="storage-client-kpi"><span>Expirados</span><strong><?php echo e(number_format((int) ($storage['expirados'] ?? 0), 0, ',', '.')); ?></strong></article>
        <article class="storage-client-kpi"><span>Tarefas abertas</span><strong><?php echo e(number_format((int) ($tarefas['abertas'] ?? 0), 0, ',', '.')); ?></strong></article>
        <article class="storage-client-kpi"><span>Atendimentos abertos</span><strong><?php echo e(number_format((int) ($atendimentos['abertos'] ?? 0), 0, ',', '.')); ?></strong></article>
    </section>

    <section class="storage-client-grid">
        <article class="storage-client-section"><h4>Documentos e armazenamento</h4><div class="storage-client-line"><span>Maior arquivo</span><strong><?php echo e($storage['maior_arquivo']['nome'] ?? 'Não identificado'); ?></strong></div><div class="storage-client-line"><span>Tamanho do maior</span><strong><?php echo e($storage['maior_arquivo']['tamanho_formatado'] ?? '0 B'); ?></strong></div><div class="storage-client-line"><span>Vencidos/antigos</span><strong><?php echo e(number_format((int) ($storage['expirados'] ?? 0), 0, ',', '.')); ?></strong></div><div class="storage-client-line"><span>Portal documentos</span><strong><?php echo e(number_format((int) ($portal['documentos'] ?? 0), 0, ',', '.')); ?></strong></div></article>
        <article class="storage-client-section"><h4>Tarefas, SLA e prazos</h4><div class="storage-client-line"><span>Abertas</span><strong><?php echo e(number_format((int) ($tarefas['abertas'] ?? 0), 0, ',', '.')); ?></strong></div><div class="storage-client-line"><span>Atrasadas</span><strong><?php echo e(number_format((int) ($tarefas['atrasadas'] ?? 0), 0, ',', '.')); ?></strong></div><div class="storage-client-line"><span>Críticas</span><strong><?php echo e(number_format((int) ($tarefas['criticas'] ?? 0), 0, ',', '.')); ?></strong></div><div class="storage-client-line"><span>SLA vencido</span><strong><?php echo e(number_format((int) ($tarefas['slaVencido'] ?? 0), 0, ',', '.')); ?></strong></div></article>
        <article class="storage-client-section"><h4>Atendimentos e portal</h4><div class="storage-client-line"><span>Atendimentos críticos</span><strong><?php echo e(number_format((int) ($atendimentos['criticos'] ?? 0), 0, ',', '.')); ?></strong></div><div class="storage-client-line"><span>Aguardando cliente</span><strong><?php echo e(number_format((int) ($atendimentos['aguardando_cliente'] ?? 0), 0, ',', '.')); ?></strong></div><div class="storage-client-line"><span>Solicitações portal</span><strong><?php echo e(number_format((int) ($portal['solicitacoes_abertas'] ?? 0), 0, ',', '.')); ?></strong></div><div class="storage-client-line"><span>Último contato</span><strong><?php echo e($atendimentos['ultimo_contato'] ?? $portal['ultima_mensagem'] ?? 'Não informado'); ?></strong></div></article>
        <article class="storage-client-section"><h4>Financeiro e cobranças</h4><div class="storage-client-line"><span>Cobranças abertas</span><strong><?php echo e(number_format((int) ($financeiro['abertas'] ?? 0), 0, ',', '.')); ?></strong></div><div class="storage-client-line"><span>Cobranças vencidas</span><strong><?php echo e(number_format((int) ($financeiro['vencidas'] ?? 0), 0, ',', '.')); ?></strong></div><div class="storage-client-line"><span>Valor vencido</span><strong><?php echo e($financeiro['vencido_formatado'] ?? 'R$ 0,00'); ?></strong></div><div class="storage-client-line"><span>Próximo vencimento</span><strong><?php echo e($financeiro['proximo_vencimento'] ?? 'Sem vencimento'); ?></strong></div></article>
        <article class="storage-client-section"><h4>Contratos, validades e governança</h4><div class="storage-client-line"><span>Contratos ativos</span><strong><?php echo e(number_format((int) ($contratos['ativos'] ?? 0), 0, ',', '.')); ?></strong></div><div class="storage-client-line"><span>Contratos vencendo</span><strong><?php echo e(number_format((int) ($contratos['vencendo'] ?? 0), 0, ',', '.')); ?></strong></div><div class="storage-client-line"><span>Validades vencidas</span><strong><?php echo e(number_format((int) ($validade['vencidos'] ?? 0), 0, ',', '.')); ?></strong></div><div class="storage-client-line"><span>Aprovações pendentes</span><strong><?php echo e(number_format((int) ($governanca['aprovacoes_pendentes'] ?? 0), 0, ',', '.')); ?></strong></div></article>
        <article class="storage-client-section"><h4>Recomendação operacional</h4><div class="storage-client-recos"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recomendacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recomendacao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><div class="storage-client-reco <?php echo e($recomendacao['tom'] ?? 'success'); ?>"><?php echo e($recomendacao['texto'] ?? ''); ?></div><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></div></article>
    </section>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($pesados) > 0): ?>
        <section class="storage-client-section"><h4>Arquivos que explicam o consumo</h4><div class="storage-client-files"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pesados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arquivo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><div class="storage-client-file"><strong title="<?php echo e($arquivo['nome'] ?? 'Arquivo'); ?>"><?php echo e($arquivo['nome'] ?? 'Arquivo'); ?></strong><span><?php echo e($arquivo['tamanho_formatado'] ?? '0 B'); ?> · <?php echo e($arquivo['origem'] ?? 'Origem'); ?></span></div><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></div></section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($acoes->isNotEmpty()): ?>
        <section class="storage-client-section"><h4>Continuar análise</h4><div class="storage-client-actions"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $acoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><a href="<?php echo e($acao['url']); ?>" class="storage-client-action <?php echo e($acao['style'] ?? 'secondary'); ?>"><?php echo e($acao['label'] ?? 'Abrir'); ?></a><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></div></section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/partials/armazenamento-cliente-modal.blade.php ENDPATH**/ ?>