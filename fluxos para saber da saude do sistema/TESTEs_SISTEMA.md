# Checklist final de validação — SistemRH / Prazzu

Este checklist corresponde ao Lote 15 em modo pesado. Ele deve ser executado depois de aplicar todos os lotes anteriores.

## 1. Preparação

1. Fazer backup do banco e dos arquivos de `storage/app/public`.
2. Aplicar os ZIPs anteriores na ordem recebida.
3. Limpar caches:

```bash
php artisan optimize:clear
```

4. Garantir que o link público de storage exista:

```bash
php artisan storage:link
```

## 2. Diagnóstico automático profundo

Executar:

```bash
php artisan sistemrh:diagnostico --limite=1000
```

Para gerar um JSON de evidência:

```bash
php artisan sistemrh:diagnostico --limite=1000 --arquivo=storage/logs/diagnostico-sistemrh.json
```

Se o ambiente tiver muitos anexos e você quiser validar primeiro sem checagem física de arquivos:

```bash
php artisan sistemrh:diagnostico --limite=1000 --sem-arquivos
```

Resultado esperado:

- `0 erros críticos`.
- Avisos devem ser analisados um por um antes de produção.
- Log gerado em `storage/logs/diagnostico-sistemrh.log`.

## 3. Portal público de item de controle

1. Abrir um item com `portal_ativo = 1` e `portal_token` válido.
2. Acessar a URL pública do item.
3. Confirmar que dados básicos aparecem corretamente.
4. Enviar uma mensagem pelo portal.
5. Confirmar que a mensagem aparece no administrativo.
6. Fazer upload de PDF, JPG ou PNG válido.
7. Confirmar que o arquivo aparece na tela e abre pelo link público.
8. Assinar eletronicamente.
9. Confirmar que a assinatura aparece no item.
10. Confirmar que o status visual do item não fica preso como aguardando assinatura.

## 4. Portal público de cliente

1. Abrir o portal público por token da empresa.
2. Criar uma solicitação com título, descrição e prioridade.
3. Confirmar que a solicitação aparece no administrativo.
4. Tentar abrir uma solicitação de outra empresa pelo ID.
5. Resultado esperado: acesso negado ou não encontrado, sem vazamento de dados.

## 5. Financeiro / Asaas

1. Cadastrar empresa pelo fluxo público.
2. Confirmar criação de cliente/assinatura no Asaas, quando credenciais estiverem configuradas.
3. Confirmar que a empresa não é liberada indevidamente antes de pagamento confirmado, se essa regra estiver ativa no ambiente.
4. Simular webhook válido de pagamento confirmado.
5. Confirmar atualização de `pagamentos`, `assinaturas` e `empresas`.
6. Simular webhook inválido ou sem token.
7. Resultado esperado: recusa segura e registro de log.
8. Testar cancelamento de assinatura, se a rota estiver exposta na UI.

## 6. Busca global

1. Pesquisar por empresa existente.
2. Pesquisar por item existente.
3. Pesquisar por termo inexistente.
4. Forçar cenário sem resultado.
5. Resultado esperado: distinguir ausência de resultado de falha técnica.
6. Conferir logs se alguma busca retornar erro.

## 7. Scheduler e comandos

Rodar manualmente:

```bash
php artisan itens-controle:atualizar-vencidos
php artisan item-controle:notificar-vencimentos
php artisan asaas:reconciliar-assinaturas --limit=100
```

Depois conferir:

1. Nenhum comando deve duplicar execução.
2. Logs devem indicar quantidade processada.
3. Não deve haver erro silencioso.

## 8. Segurança pública

1. Testar token inexistente.
2. Testar token expirado.
3. Testar upload com extensão inválida.
4. Testar upload acima do limite.
5. Testar assinatura com e-mail inválido.
6. Testar mensagem vazia.
7. Testar múltiplas requisições rápidas na mesma rota pública.

Resultado esperado: mensagens amigáveis, sem stack trace e com log técnico quando necessário.

## 9. Critérios de aceite final

O lote final só deve ser considerado validado quando:

- `php artisan sistemrh:diagnostico --limite=1000` finalizar sem erros críticos.
- Os avisos restantes forem compreendidos e aceitos conscientemente.
- Portal, assinatura, upload, mensagens, busca, financeiro e scheduler forem testados manualmente.
- `storage/logs/diagnostico-sistemrh.log` estiver sendo criado corretamente.
- Nenhuma tela pública exibir erro técnico ao usuário final.
