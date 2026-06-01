<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
<link rel="stylesheet" href="<?php echo e(asset('css/chat-widget.css')); ?>">

<?php
    $chatApiUrl = config('services.chat_api.url') ?: env('CHAT_API_URL', 'http://localhost/chat-api/public/perguntar');
    $chatSistema = config('services.chat_api.sistema') ?: env('CHAT_SISTEMA_SLUG', 'prazzu');
    $chatAssunto = config('services.chat_api.assunto') ?: env('CHAT_ASSUNTO_SLUG', '');
    $chatClienteNome = auth()->check() ? auth()->user()->name : 'Cliente';
    $chatClienteEmail = auth()->check() ? auth()->user()->email : '';
    $whiteLabel = \App\Support\WhiteLabelSettings::make();
    $assistantName = $whiteLabel->assistantName();
?>

<div
    id="chatWidget"
    class="chat-widget"
    data-api-url="<?php echo e($chatApiUrl); ?>"
    data-sistema="<?php echo e($chatSistema); ?>"
    data-assunto="<?php echo e($chatAssunto); ?>"
    data-cliente-nome="<?php echo e($chatClienteNome); ?>"
    data-cliente-email="<?php echo e($chatClienteEmail); ?>"
>
    <button type="button" id="chatWidgetButton" class="chat-widget-button">
        <i class="bi bi-chat-fill"></i>
    </button>

    <div id="chatWidgetBox" class="chat-widget-box">
        <div class="chat-widget-header">
            <div>
                <strong>Assistente Virtual</strong>
                <span>Online</span>
            </div>

            <button type="button" id="chatWidgetClose" class="chat-widget-close">
                ×
            </button>
        </div>

        <div class="chat-widget-body" id="chatWidgetBody">
            <div class="chat-widget-message chat-widget-bot">
                Olá, sou o <?php echo e($assistantName); ?>. Como posso ajudar você hoje?
            </div>
        </div>

        <div class="chat-widget-footer">
            <input
                type="text"
                id="chatWidgetInput"
                placeholder="Digite sua pergunta..."
                autocomplete="off"
            >

            <button type="button" id="chatWidgetSend">
                Enviar
            </button>
        </div>
    </div>
</div>

<script src="<?php echo e(asset('js/chat-widget.js')); ?>"></script>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\sistemrh\resources\views/components/chat-widget.blade.php ENDPATH**/ ?>