# Ajustes realizados

## Planos
- `PlanoService` atualizado para os limites/preços atuais da página `cadastro-empresa`.
- Página `planos.blade.php` sincronizada com os novos limites.
- Cadastro/edição de empresa agora parte do limite Starter atualizado: 200 itens.
- Banco ajustado em `prazzu_ajustado.sql`.

## Limites por plano
- Criação de usuários agora respeita o limite da empresa mesmo quando feita pelo super admin para uma empresa cliente.
- Criação de itens de controle agora valida empresa ativa e limite de itens do plano no próprio model, valendo para qualquer tela/fluxo.

## Dashboard Configurável
- Criada a página `Visualizar Dashboard`.
- A lista administrativa continua existindo para gerenciar widgets.
- O botão `Visualizar dashboard` abre uma visão em cards, agrupada por empresa para super admin.

## Banco
Use o arquivo `prazzu_ajustado.sql` incluído na raiz do projeto para importar o banco já com os limites atuais.
