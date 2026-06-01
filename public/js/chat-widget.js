document.addEventListener('DOMContentLoaded', function () {
    const chatWidget = document.getElementById('chatWidget');
    const chatWidgetButton = document.getElementById('chatWidgetButton');
    const chatWidgetBox = document.getElementById('chatWidgetBox');
    const chatWidgetClose = document.getElementById('chatWidgetClose');
    const chatWidgetBody = document.getElementById('chatWidgetBody');
    const chatWidgetInput = document.getElementById('chatWidgetInput');
    const chatWidgetSend = document.getElementById('chatWidgetSend');

    if (!chatWidget || !chatWidgetButton || !chatWidgetBox || !chatWidgetClose || !chatWidgetBody || !chatWidgetInput || !chatWidgetSend) {
        return;
    }

    let conversaId = null;

    const apiUrl = chatWidget.dataset.apiUrl || 'http://localhost/chat-api/public/perguntar';
    const sistema = chatWidget.dataset.sistema || 'prazzu';
    const assunto = chatWidget.dataset.assunto || '';
    const clienteNome = chatWidget.dataset.clienteNome || 'Cliente';
    const clienteEmail = chatWidget.dataset.clienteEmail || '';

    function addMessage(message, sender) {
        const messageElement = document.createElement('div');

        messageElement.classList.add('chat-widget-message');
        messageElement.classList.add(sender === 'user' ? 'chat-widget-user' : 'chat-widget-bot');
        messageElement.innerText = message;

        chatWidgetBody.appendChild(messageElement);
        chatWidgetBody.scrollTop = chatWidgetBody.scrollHeight;
    }

    function setLoading(isLoading) {
        chatWidgetSend.disabled = isLoading;
        chatWidgetInput.disabled = isLoading;
        chatWidgetSend.innerText = isLoading ? '...' : 'Enviar';
    }

    async function sendMessage() {
        const message = chatWidgetInput.value.trim();

        if (!message) {
            return;
        }

        addMessage(message, 'user');
        chatWidgetInput.value = '';
        setLoading(true);

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    sistema: sistema,
                    sistema_slug: sistema,
                    assunto: assunto,
                    conversa_id: conversaId,
                    cliente_nome: clienteNome,
                    cliente_email: clienteEmail,
                    mensagem: message,
                }),
            });

            const data = await response.json();

            if (response.ok && data.success) {
                conversaId = data.conversa_id || conversaId;
                addMessage(data.resposta || 'Não encontrei uma resposta para essa pergunta.', 'bot');
            } else {
                addMessage(data.message || data.error || 'Erro ao processar sua pergunta.', 'bot');
                console.log('Erro API Chat:', data);
            }
        } catch (error) {
            addMessage('Erro ao conectar com o servidor do chat.', 'bot');
            console.error('Erro fetch Chat:', error);
        } finally {
            setLoading(false);
            chatWidgetInput.focus();
        }
    }

    chatWidgetButton.addEventListener('click', function () {
        chatWidgetBox.classList.toggle('active');

        if (chatWidgetBox.classList.contains('active')) {
            chatWidgetInput.focus();
        }
    });

    chatWidgetClose.addEventListener('click', function () {
        chatWidgetBox.classList.remove('active');
    });

    chatWidgetSend.addEventListener('click', sendMessage);

    chatWidgetInput.addEventListener('keypress', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            sendMessage();
        }
    });
});
