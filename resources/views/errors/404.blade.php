<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Link não encontrado</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#eef2f7;color:#0f172a;padding:20px}.card{width:min(560px,100%);background:white;border:1px solid #e2e8f0;border-radius:28px;padding:30px;box-shadow:0 24px 70px rgba(15,23,42,.12)}.eyebrow{display:inline-flex;border-radius:999px;background:#dbeafe;color:#1d4ed8;padding:7px 12px;font-weight:950;font-size:12px;text-transform:uppercase;letter-spacing:.06em}h1{margin:16px 0 8px;font-size:28px;letter-spacing:-.03em}p{color:#475569;line-height:1.65;margin:0 0 18px}.box{border-radius:18px;background:#f8fafc;border:1px solid #e2e8f0;padding:14px;color:#334155;line-height:1.55}.actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:20px}.btn{display:inline-flex;align-items:center;justify-content:center;border-radius:14px;background:#2563eb;color:white;text-decoration:none;font-weight:950;padding:12px 16px}.btn.secondary{background:#0f172a}@media(max-width:520px){.card{padding:22px;border-radius:22px}h1{font-size:24px}.btn{width:100%}}
    </style>
</head>
<body>
    <main class="card" role="main">
        <span class="eyebrow">Não encontrado</span>
        <h1>Não encontramos este acesso.</h1>
        <p>O link informado não corresponde a nenhum portal ativo ou foi digitado incompleto.</p>
        <div class="box">Use sempre o link mais recente enviado pela equipe. Por segurança, links inválidos não exibem detalhes do cliente ou do documento.</div>
        <div class="actions">
            <a class="btn" href="{{ url('/') }}">Voltar ao início</a>
        </div>
    </main>
</body>
</html>
