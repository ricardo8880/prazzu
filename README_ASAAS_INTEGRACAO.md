# Integração Asaas

## Variáveis necessárias no `.env`

```env
ASAAS_BASE_URL=https://api-sandbox.asaas.com/v3
ASAAS_API_KEY=seu_token_asaas
ASAAS_WEBHOOK_TOKEN=um_token_secreto_para_validar_webhook
ASAAS_TIMEOUT=30
ASAAS_BILLING_TYPE=UNDEFINED
```

Para produção, altere:

```env
ASAAS_BASE_URL=https://api.asaas.com/v3
```

## Webhook no Asaas

Configure no painel do Asaas a URL:

```text
https://seu-dominio.com/webhooks/asaas
```

Use o mesmo token configurado em `ASAAS_WEBHOOK_TOKEN`.

## SQL

Se as tabelas ainda não existirem, o SQL está em:

```text
database/sql/asaas_pagamentos.sql
```

## Fluxo implementado

- Cadastro público cria empresa inativa com status `pendente_pagamento`.
- Sistema cria cliente e assinatura no Asaas.
- Sistema salva assinatura e primeira cobrança localmente.
- Usuário é redirecionado para a cobrança do Asaas.
- Webhook confirma pagamento e ativa empresa.
- Webhook de atraso/cancelamento bloqueia empresa.
- Super admin continua acessando normalmente.
