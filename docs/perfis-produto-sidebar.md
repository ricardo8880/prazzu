# Perfis de Produto - Sidebar

Este ajuste cria perfis de produto para reduzir a complexidade visual da navegação sem remover páginas, rotas ou permissões.

## Perfis disponíveis

- `completo`: mostra tudo.
- `escritorio_contabil`: foco em prazos, clientes, documentos, financeiro, relatórios e riscos.
- `juridico`: foco em contratos, documentos, prazos, aprovações e auditoria.
- `rh`: foco em pessoas, tarefas, contratos e documentos.
- `operacional`: foco em execução diária simples.

## Arquivo principal

A manutenção da lista de abas fica em:

`app/Support/ProductProfileNavigation.php`

Para mostrar uma aba em um perfil, adicione o nome em `visible_labels`.
Para forçar ocultação, adicione o nome em `hidden_labels`.
Para aceitar variações de nome, adicione em `aliases`.

## Importante

Este recurso apenas oculta itens da sidebar no navegador. Ele não bloqueia acesso direto por URL e não substitui permissões.

Isso foi proposital para ser seguro: o usuário pode testar perfis sem quebrar módulos existentes.

## Quando usar cada perfil

### Escritório Contábil
Use para clientes contábeis e equipes que precisam de menu enxuto.

### Completo
Use para super admin, implantação, manutenção e diagnóstico.

### Operacional
Use para usuários que só executam tarefas e resolvem pendências.

## Checklist de teste

- [ ] Entrar no painel admin.
- [ ] Selecionar Perfil Completo e confirmar que tudo aparece.
- [ ] Selecionar Escritório Contábil e confirmar que Kanban, Gantt, Projetos, White Label, Saúde do Sistema e Inteligência do Produto somem da sidebar.
- [ ] Recarregar a página e confirmar que o perfil escolhido permanece.
- [ ] Voltar para Perfil Completo e confirmar que a navegação completa retorna.
