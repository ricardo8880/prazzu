-- Redistribuição de datas fictícias para testar a Home/Dashboard
-- Base analisada: dump `prazzu` gerado em 08/06/2026.
-- Execute depois de importar o dump.
-- Usa CURDATE() para funcionar em qualquer dia em que o script for executado.

START TRANSACTION;

-- =========================================================
-- 1) Normalização inicial dos itens de teste
-- =========================================================
UPDATE item_controles
SET
    data_conclusao = NULL,
    updated_at = NOW(),
    notificado_3_dias = 0,
    notificado_no_dia = 0,
    notificado_vencido = 0,
    ultimo_alerta_enviado_em = NULL,
    ultimo_lembrete_enviado_em = NULL,
    qtd_lembretes_enviados = 0,
    ultima_falha_notificacao_em = NULL,
    ultima_falha_notificacao_msg = NULL
WHERE id IN (31,32,33,34,35,36,37,38,39,47,48,49,50,51,52,56,57,58,59,60,61,62,63,64,65,66,67);

-- =========================================================
-- 2) Obrigações vencidas / atrasadas
-- =========================================================
UPDATE item_controles
SET
    data_vencimento = DATE_SUB(CURDATE(), INTERVAL 25 DAY),
    status = 'pendente',
    prioridade = 'alta',
    urgencia = 'critica',
    risco_score = 95,
    sla_status = 'vencido',
    sla_inicio_em = DATE_SUB(NOW(), INTERVAL 28 DAY),
    sla_limite_em = DATE_SUB(NOW(), INTERVAL 25 DAY),
    sla_prazo_alvo_em = DATE_SUB(NOW(), INTERVAL 25 DAY),
    sla_concluido_em = NULL,
    status_operacional_at = DATE_SUB(NOW(), INTERVAL 12 DAY),
    notificado_vencido = 1,
    ultimo_alerta_enviado_em = DATE_SUB(NOW(), INTERVAL 24 DAY),
    ultimo_lembrete_enviado_em = DATE_SUB(NOW(), INTERVAL 1 DAY),
    qtd_lembretes_enviados = 4,
    observacao = 'Seed redistribuído: obrigação muito atrasada para testar risco crítico na Home.'
WHERE id IN (31,48);

UPDATE item_controles
SET
    data_vencimento = DATE_SUB(CURDATE(), INTERVAL 12 DAY),
    status = 'em_andamento',
    prioridade = 'alta',
    urgencia = 'alta',
    risco_score = 88,
    sla_status = 'vencido',
    sla_inicio_em = DATE_SUB(NOW(), INTERVAL 15 DAY),
    sla_limite_em = DATE_SUB(NOW(), INTERVAL 12 DAY),
    sla_prazo_alvo_em = DATE_SUB(NOW(), INTERVAL 12 DAY),
    sla_concluido_em = NULL,
    status_operacional_at = DATE_SUB(NOW(), INTERVAL 8 DAY),
    notificado_vencido = 1,
    ultimo_alerta_enviado_em = DATE_SUB(NOW(), INTERVAL 11 DAY),
    ultimo_lembrete_enviado_em = DATE_SUB(NOW(), INTERVAL 2 DAY),
    qtd_lembretes_enviados = 3,
    observacao = 'Seed redistribuído: obrigação atrasada há mais de 10 dias.'
WHERE id IN (34,51);

UPDATE item_controles
SET
    data_vencimento = DATE_SUB(CURDATE(), INTERVAL 5 DAY),
    status = 'correcao_necessaria',
    prioridade = 'alta',
    urgencia = 'alta',
    risco_score = 82,
    sla_status = 'vencido',
    sla_inicio_em = DATE_SUB(NOW(), INTERVAL 7 DAY),
    sla_limite_em = DATE_SUB(NOW(), INTERVAL 5 DAY),
    sla_prazo_alvo_em = DATE_SUB(NOW(), INTERVAL 5 DAY),
    sla_concluido_em = NULL,
    status_operacional_at = DATE_SUB(NOW(), INTERVAL 6 DAY),
    notificado_vencido = 1,
    ultimo_alerta_enviado_em = DATE_SUB(NOW(), INTERVAL 4 DAY),
    ultimo_lembrete_enviado_em = DATE_SUB(NOW(), INTERVAL 1 DAY),
    qtd_lembretes_enviados = 2,
    observacao = 'Seed redistribuído: obrigação vencida recentemente e parada há mais de 5 dias.'
WHERE id IN (38,56);

UPDATE item_controles
SET
    data_vencimento = DATE_SUB(CURDATE(), INTERVAL 1 DAY),
    status = 'pendente',
    prioridade = 'alta',
    urgencia = 'alta',
    risco_score = 76,
    sla_status = 'vencido',
    sla_inicio_em = DATE_SUB(NOW(), INTERVAL 3 DAY),
    sla_limite_em = DATE_SUB(NOW(), INTERVAL 1 DAY),
    sla_prazo_alvo_em = DATE_SUB(NOW(), INTERVAL 1 DAY),
    sla_concluido_em = NULL,
    status_operacional_at = DATE_SUB(NOW(), INTERVAL 2 DAY),
    notificado_vencido = 1,
    ultimo_alerta_enviado_em = DATE_SUB(NOW(), INTERVAL 1 DAY),
    ultimo_lembrete_enviado_em = DATE_SUB(NOW(), INTERVAL 6 HOUR),
    qtd_lembretes_enviados = 1,
    observacao = 'Seed redistribuído: obrigação vencida ontem.'
WHERE id IN (57);

-- =========================================================
-- 3) Vencem hoje
-- =========================================================
UPDATE item_controles
SET
    data_vencimento = CURDATE(),
    status = 'pendente',
    prioridade = 'alta',
    urgencia = 'alta',
    risco_score = 72,
    sla_status = 'em_andamento',
    sla_inicio_em = DATE_SUB(NOW(), INTERVAL 2 DAY),
    sla_limite_em = DATE_ADD(CURDATE(), INTERVAL 23 HOUR),
    sla_prazo_alvo_em = DATE_ADD(CURDATE(), INTERVAL 23 HOUR),
    sla_concluido_em = NULL,
    status_operacional_at = DATE_SUB(NOW(), INTERVAL 1 DAY),
    notificado_no_dia = 1,
    ultimo_alerta_enviado_em = NOW(),
    ultimo_lembrete_enviado_em = NOW(),
    qtd_lembretes_enviados = 1,
    observacao = 'Seed redistribuído: vence hoje para testar o card Vencem hoje.'
WHERE id IN (32,47,58,61,65);

-- =========================================================
-- 4) Vencem amanhã
-- =========================================================
UPDATE item_controles
SET
    data_vencimento = DATE_ADD(CURDATE(), INTERVAL 1 DAY),
    status = 'em_andamento',
    prioridade = 'media',
    urgencia = 'media',
    risco_score = 58,
    sla_status = 'em_andamento',
    sla_inicio_em = DATE_SUB(NOW(), INTERVAL 1 DAY),
    sla_limite_em = DATE_ADD(NOW(), INTERVAL 1 DAY),
    sla_prazo_alvo_em = DATE_ADD(NOW(), INTERVAL 1 DAY),
    sla_concluido_em = NULL,
    status_operacional_at = DATE_SUB(NOW(), INTERVAL 1 DAY),
    notificado_3_dias = 1,
    ultimo_alerta_enviado_em = NOW(),
    ultimo_lembrete_enviado_em = NULL,
    qtd_lembretes_enviados = 0,
    observacao = 'Seed redistribuído: vence amanhã.'
WHERE id IN (33,59,62);

-- =========================================================
-- 5) Vencem em 3 dias
-- =========================================================
UPDATE item_controles
SET
    data_vencimento = DATE_ADD(CURDATE(), INTERVAL 3 DAY),
    status = 'pendente',
    prioridade = 'media',
    urgencia = 'media',
    risco_score = 45,
    sla_status = 'em_andamento',
    sla_inicio_em = NOW(),
    sla_limite_em = DATE_ADD(NOW(), INTERVAL 3 DAY),
    sla_prazo_alvo_em = DATE_ADD(NOW(), INTERVAL 3 DAY),
    sla_concluido_em = NULL,
    status_operacional_at = NOW(),
    notificado_3_dias = 1,
    ultimo_alerta_enviado_em = NOW(),
    qtd_lembretes_enviados = 0,
    observacao = 'Seed redistribuído: vence em 3 dias.'
WHERE id IN (35,50,63,66);

-- =========================================================
-- 6) Vencem em 7 dias
-- =========================================================
UPDATE item_controles
SET
    data_vencimento = DATE_ADD(CURDATE(), INTERVAL 7 DAY),
    status = 'pendente',
    prioridade = 'media',
    urgencia = 'baixa',
    risco_score = 35,
    sla_status = 'em_andamento',
    sla_inicio_em = NOW(),
    sla_limite_em = DATE_ADD(NOW(), INTERVAL 7 DAY),
    sla_prazo_alvo_em = DATE_ADD(NOW(), INTERVAL 7 DAY),
    sla_concluido_em = NULL,
    status_operacional_at = NOW(),
    observacao = 'Seed redistribuído: vence em 7 dias para testar previsões da Home.'
WHERE id IN (36,39,64,67);

-- =========================================================
-- 7) Futuro maior, sem urgência
-- =========================================================
UPDATE item_controles
SET
    data_vencimento = DATE_ADD(CURDATE(), INTERVAL 15 DAY),
    status = 'pendente',
    prioridade = 'baixa',
    urgencia = 'baixa',
    risco_score = 20,
    sla_status = 'em_andamento',
    sla_inicio_em = NOW(),
    sla_limite_em = DATE_ADD(NOW(), INTERVAL 15 DAY),
    sla_prazo_alvo_em = DATE_ADD(NOW(), INTERVAL 15 DAY),
    sla_concluido_em = NULL,
    status_operacional_at = NOW(),
    observacao = 'Seed redistribuído: vencimento futuro sem urgência.'
WHERE id IN (37);

-- =========================================================
-- 8) Concluídos preservados como histórico
-- =========================================================
UPDATE item_controles
SET
    data_vencimento = DATE_SUB(CURDATE(), INTERVAL 2 DAY),
    data_conclusao = DATE_SUB(CURDATE(), INTERVAL 3 DAY),
    status = 'concluido',
    prioridade = 'media',
    urgencia = 'baixa',
    risco_score = 5,
    sla_status = 'concluido_no_prazo',
    sla_inicio_em = DATE_SUB(NOW(), INTERVAL 8 DAY),
    sla_limite_em = DATE_SUB(NOW(), INTERVAL 2 DAY),
    sla_prazo_alvo_em = DATE_SUB(NOW(), INTERVAL 2 DAY),
    sla_concluido_em = DATE_SUB(NOW(), INTERVAL 3 DAY),
    status_operacional_at = NULL,
    notificado_3_dias = 0,
    notificado_no_dia = 0,
    notificado_vencido = 0,
    observacao = 'Seed redistribuído: concluído antes do prazo.'
WHERE id IN (49,52,60);

-- =========================================================
-- 9) Clientes sem enviar documentos / aguardando cliente
--    Mantém dados fictícios úteis para cards de risco e portal.
-- =========================================================
UPDATE item_controles
SET
    portal_ativo = 1,
    portal_status = 'aguardando_cliente',
    ultima_interacao_cliente_em = DATE_SUB(NOW(), INTERVAL 10 DAY),
    status = CASE
        WHEN status = 'concluido' THEN status
        ELSE 'pendente'
    END,
    observacao = CONCAT(COALESCE(observacao, ''), ' Cliente sem envio recente pelo portal.')
WHERE id IN (31,34,38,48,51,56,57,61,64);

UPDATE item_controles
SET
    portal_ativo = 1,
    portal_status = 'em_execucao',
    ultima_interacao_cliente_em = DATE_SUB(NOW(), INTERVAL 2 DAY)
WHERE id IN (47,58,59,62,63,65,66,67);

UPDATE item_controles
SET
    portal_ativo = 1,
    portal_status = 'concluido',
    ultima_interacao_cliente_em = DATE_SUB(NOW(), INTERVAL 1 DAY)
WHERE id IN (49,52,60);

-- =========================================================
-- 10) Conferência rápida após executar
-- =========================================================
SELECT
    SUM(CASE WHEN status <> 'concluido' AND data_vencimento < CURDATE() THEN 1 ELSE 0 END) AS vencidos_abertos,
    SUM(CASE WHEN status <> 'concluido' AND data_vencimento = CURDATE() THEN 1 ELSE 0 END) AS vencem_hoje,
    SUM(CASE WHEN status <> 'concluido' AND data_vencimento = DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS vencem_amanha,
    SUM(CASE WHEN status <> 'concluido' AND data_vencimento BETWEEN DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS vencem_ate_7_dias,
    SUM(CASE WHEN status <> 'concluido' AND status_operacional_at <= DATE_SUB(NOW(), INTERVAL 5 DAY) THEN 1 ELSE 0 END) AS parados_mais_5_dias,
    SUM(CASE WHEN status <> 'concluido' AND portal_status = 'aguardando_cliente' THEN 1 ELSE 0 END) AS aguardando_cliente
FROM item_controles
WHERE id IN (31,32,33,34,35,36,37,38,39,47,48,49,50,51,52,56,57,58,59,60,61,62,63,64,65,66,67);

COMMIT;
