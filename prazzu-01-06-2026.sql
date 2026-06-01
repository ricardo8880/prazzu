-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 01-Jun-2026 às 13:54
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `prazzu`
--

DELIMITER $$
--
-- Procedimentos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddIndexSafe` (IN `tableName` VARCHAR(255), IN `indexName` VARCHAR(255), IN `indexColumns` VARCHAR(255))   BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.statistics 
        WHERE table_schema = DATABASE() 
        AND table_name = tableName 
        AND index_name = indexName
    ) THEN
        SET @sql = CONCAT('ALTER TABLE ', tableName, ' ADD INDEX ', indexName, ' (', indexColumns, ')');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddIndexTotalSafe` (IN `tableName` VARCHAR(255), IN `indexName` VARCHAR(255), IN `indexColumns` VARCHAR(255))   BEGIN
    -- 1. Verifica se a TABELA existe
    IF EXISTS (
        SELECT 1 
        FROM information_schema.tables 
        WHERE table_schema = DATABASE() 
        AND table_name = tableName
    ) THEN
        -- 2. Verifica se o ÍNDICE NÃO existe
        IF NOT EXISTS (
            SELECT 1 
            FROM information_schema.statistics 
            WHERE table_schema = DATABASE() 
            AND table_name = tableName 
            AND index_name = indexName
        ) THEN
            SET @sql = CONCAT('ALTER TABLE ', tableName, ' ADD INDEX ', indexName, ' (', indexColumns, ')');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END IF;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `activity_log`
--

INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `subject_id`, `event`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(1, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 33, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":33,\"empresa_id\":4,\"responsavel_id\":151,\"titulo\":\"Item de Controle - User Alpha 3\",\"descricao\":\"Registro de controle criado para User Alpha 3\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"media\",\"data_vencimento\":\"2026-05-24T00:00:00.000000Z\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Registro inicial criado via SQL.\",\"portal_ativo\":false,\"portal_token\":null,\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-24T14:57:10.000000Z\",\"updated_at\":\"2026-04-24T14:57:10.000000Z\",\"sla_horas\":null,\"sla_inicio_em\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":null,\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":0,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":0,\"notificacoes_internas_count\":0,\"checklists_concluidos_count\":0},\"attributes\":{\"id\":33,\"empresa_id\":4,\"responsavel_id\":151,\"titulo\":\"Item de Controle - User Alpha 3\",\"descricao\":\"Registro de controle criado para User Alpha 3\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"media\",\"data_vencimento\":\"2026-05-24\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Registro inicial criado via SQL.\",\"portal_ativo\":0,\"portal_token\":null,\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-04-28 11:53:15\",\"sla_horas\":24,\"sla_inicio_em\":\"2026-04-28 11:53:15\",\"sla_limite_em\":\"2026-04-29 11:53:15\",\"sla_concluido_em\":null,\"sla_status\":\"em_andamento\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":0,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":0,\"notificacoes_internas_count\":0,\"checklists_concluidos_count\":0}}', NULL, '2026-04-28 14:53:15', '2026-04-28 14:53:15'),
(2, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 33, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":33,\"empresa_id\":4,\"responsavel_id\":151,\"titulo\":\"Item de Controle - User Alpha 3\",\"descricao\":\"Registro de controle criado para User Alpha 3\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"media\",\"data_vencimento\":\"2026-05-24T00:00:00.000000Z\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Registro inicial criado via SQL.\",\"portal_ativo\":false,\"portal_token\":null,\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-24T14:57:10.000000Z\",\"updated_at\":\"2026-04-28T11:53:15.000000Z\",\"sla_horas\":24,\"sla_inicio_em\":\"2026-04-28T11:53:15.000000Z\",\"sla_limite_em\":\"2026-04-29T11:53:15.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"em_andamento\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":0,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":1,\"notificacoes_internas_count\":0,\"checklists_concluidos_count\":0},\"attributes\":{\"id\":33,\"empresa_id\":4,\"responsavel_id\":151,\"titulo\":\"Item de Controle - User Alpha 3\",\"descricao\":\"Registro de controle criado para User Alpha 3\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"media\",\"data_vencimento\":\"2026-05-24\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Registro inicial criado via SQL.\",\"portal_ativo\":0,\"portal_token\":null,\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-04-28 11:55:23\",\"sla_horas\":24,\"sla_inicio_em\":\"2026-04-28 11:53:15\",\"sla_limite_em\":\"2026-04-29 11:53:15\",\"sla_concluido_em\":\"2026-04-28 11:55:23\",\"sla_status\":\"concluido_no_prazo\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":0,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":1,\"notificacoes_internas_count\":0,\"checklists_concluidos_count\":0}}', NULL, '2026-04-28 14:55:23', '2026-04-28 14:55:23'),
(3, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 33, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":33,\"empresa_id\":4,\"responsavel_id\":151,\"titulo\":\"Item de Controle - User Alpha 3\",\"descricao\":\"Registro de controle criado para User Alpha 3\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"media\",\"data_vencimento\":\"2026-05-24T00:00:00.000000Z\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Registro inicial criado via SQL.\",\"portal_ativo\":false,\"portal_token\":null,\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-24T14:57:10.000000Z\",\"updated_at\":\"2026-04-28T11:55:23.000000Z\",\"sla_horas\":24,\"sla_inicio_em\":\"2026-04-28T11:53:15.000000Z\",\"sla_limite_em\":\"2026-04-29T11:53:15.000000Z\",\"sla_concluido_em\":\"2026-04-28T11:55:23.000000Z\",\"sla_status\":\"concluido_no_prazo\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":0,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":2,\"notificacoes_internas_count\":0,\"checklists_concluidos_count\":0},\"attributes\":{\"id\":33,\"empresa_id\":4,\"responsavel_id\":151,\"titulo\":\"Item de Controle - User Alpha 3\",\"descricao\":\"Registro de controle criado para User Alpha 3\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_aprovacao\",\"prioridade\":\"media\",\"data_vencimento\":\"2026-05-24\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Registro inicial criado via SQL.\",\"portal_ativo\":0,\"portal_token\":null,\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-04-28 11:57:20\",\"sla_horas\":24,\"sla_inicio_em\":\"2026-04-28 11:53:15\",\"sla_limite_em\":\"2026-04-29 11:53:15\",\"sla_concluido_em\":\"2026-04-28 11:55:23\",\"sla_status\":\"concluido_no_prazo\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":0,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":2,\"notificacoes_internas_count\":0,\"checklists_concluidos_count\":0}}', NULL, '2026-04-28 14:57:20', '2026-04-28 14:57:20'),
(4, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":false,\"portal_token\":null,\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-04-28T12:06:22.000000Z\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":0,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":0},\"attributes\":{\"id\":48,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":0,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-04-28 16:44:47\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":0,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":0}}', NULL, '2026-04-28 19:44:47', '2026-04-28 19:44:47'),
(5, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":false,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-04-28T16:44:47.000000Z\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":0,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":0},\"attributes\":{\"id\":48,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-04-28 16:44:47\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":0,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":0}}', NULL, '2026-04-28 19:44:47', '2026-04-28 19:44:47'),
(6, 'default', 'Registro criado', 'App\\Models\\ItemControle', 56, 'created', 'App\\Models\\User', 111, '{\"attributes\":{\"titulo\":\"Contrato RH Interno Portal do Cliente\",\"descricao\":\"teste\",\"categoria_id\":1,\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"media\",\"data_vencimento\":\"2026-04-30 00:00:00\",\"empresa_id\":4,\"responsavel_id\":147,\"arquivo\":null,\"observacao\":\"teste ssssssssssssssss\",\"portal_ativo\":true,\"portal_cliente_nome\":\"ricardo\",\"portal_cliente_email\":\"ricardo-s-a@hotmail.com\",\"portal_expira_em\":\"2026-04-30 00:00:00\",\"portal_token\":\"BD3W26Thqb2soy0b648UExabhDULsCY1cMHj0JsLXd0GbHf4GqZqPMDJQkfhLppx\",\"updated_at\":\"2026-04-30 17:30:56\",\"created_at\":\"2026-04-30 17:30:56\",\"id\":56}}', NULL, '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(7, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-04-28T16:44:47.000000Z\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":2,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":1},\"attributes\":{\"id\":48,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":false,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-04-30 19:43:06\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":2,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":1}}', NULL, '2026-04-30 22:43:06', '2026-04-30 22:43:06'),
(8, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":false,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-04-30T19:43:06.000000Z\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":3,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":1},\"attributes\":{\"id\":48,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-04-30 19:43:10\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":3,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":1}}', NULL, '2026-04-30 22:43:10', '2026-04-30 22:43:10'),
(9, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-04-30T19:43:10.000000Z\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":4,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":1},\"attributes\":{\"id\":48,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":false,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-04-30 19:43:18\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":4,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":1}}', NULL, '2026-04-30 22:43:18', '2026-04-30 22:43:18'),
(10, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 34, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":34,\"empresa_id\":5,\"responsavel_id\":156,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"media\",\"data_vencimento\":\"2026-05-24T00:00:00.000000Z\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Registro inicial criado via SQL.\",\"portal_ativo\":false,\"portal_token\":null,\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-24T14:57:10.000000Z\",\"updated_at\":\"2026-04-24T14:57:10.000000Z\",\"sla_horas\":null,\"sla_inicio_em\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":null,\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":0,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":0,\"notificacoes_internas_count\":0,\"checklists_concluidos_count\":0},\"attributes\":{\"id\":34,\"empresa_id\":5,\"responsavel_id\":156,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"media\",\"data_vencimento\":\"2026-05-24\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Registro inicial criado via SQL.\",\"portal_ativo\":0,\"portal_token\":\"8GvffdhZO2AjBuKjMafgmtA5ZAxZK1EVCpoZaIqIZ5kyJoBuNYYuTWHmcdX1E3xa\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-04-30 19:44:38\",\"sla_horas\":null,\"sla_inicio_em\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":null,\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":0,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":0,\"notificacoes_internas_count\":0,\"checklists_concluidos_count\":0}}', NULL, '2026-04-30 22:44:38', '2026-04-30 22:44:38'),
(11, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 34, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":34,\"empresa_id\":5,\"responsavel_id\":156,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"media\",\"data_vencimento\":\"2026-05-24T00:00:00.000000Z\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Registro inicial criado via SQL.\",\"portal_ativo\":false,\"portal_token\":\"8GvffdhZO2AjBuKjMafgmtA5ZAxZK1EVCpoZaIqIZ5kyJoBuNYYuTWHmcdX1E3xa\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-24T14:57:10.000000Z\",\"updated_at\":\"2026-04-30T19:44:38.000000Z\",\"sla_horas\":null,\"sla_inicio_em\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":null,\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":0,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":0,\"notificacoes_internas_count\":0,\"checklists_concluidos_count\":0},\"attributes\":{\"id\":34,\"empresa_id\":5,\"responsavel_id\":156,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"media\",\"data_vencimento\":\"2026-05-24\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Registro inicial criado via SQL.\",\"portal_ativo\":true,\"portal_token\":\"8GvffdhZO2AjBuKjMafgmtA5ZAxZK1EVCpoZaIqIZ5kyJoBuNYYuTWHmcdX1E3xa\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-04-30 19:44:38\",\"sla_horas\":null,\"sla_inicio_em\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":null,\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":0,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":0,\"notificacoes_internas_count\":0,\"checklists_concluidos_count\":0}}', NULL, '2026-04-30 22:44:38', '2026-04-30 22:44:38'),
(12, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 34, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":34,\"empresa_id\":5,\"responsavel_id\":156,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"media\",\"data_vencimento\":\"2026-05-24T00:00:00.000000Z\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Registro inicial criado via SQL.\",\"portal_ativo\":true,\"portal_token\":\"8GvffdhZO2AjBuKjMafgmtA5ZAxZK1EVCpoZaIqIZ5kyJoBuNYYuTWHmcdX1E3xa\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-24T14:57:10.000000Z\",\"updated_at\":\"2026-04-30T19:44:38.000000Z\",\"sla_horas\":null,\"sla_inicio_em\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":null,\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":0,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":1,\"notificacoes_internas_count\":0,\"checklists_concluidos_count\":0},\"attributes\":{\"id\":34,\"empresa_id\":5,\"responsavel_id\":156,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"media\",\"data_vencimento\":\"2026-05-24\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Registro inicial criado via SQL.\",\"portal_ativo\":false,\"portal_token\":\"8GvffdhZO2AjBuKjMafgmtA5ZAxZK1EVCpoZaIqIZ5kyJoBuNYYuTWHmcdX1E3xa\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-04-30 19:44:42\",\"sla_horas\":null,\"sla_inicio_em\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":null,\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":0,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":1,\"notificacoes_internas_count\":0,\"checklists_concluidos_count\":0}}', NULL, '2026-04-30 22:44:42', '2026-04-30 22:44:42'),
(13, 'default', 'Registro criado', 'App\\Models\\ItemControle', 57, 'created', 'App\\Models\\User', 111, '{\"attributes\":{\"titulo\":\"teste link\",\"descricao\":\"teste link\",\"categoria_id\":1,\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"media\",\"data_vencimento\":\"2026-05-01 00:00:00\",\"empresa_id\":4,\"responsavel_id\":147,\"arquivo\":null,\"observacao\":\"teste\",\"portal_ativo\":true,\"portal_cliente_nome\":\"ricardo\",\"portal_cliente_email\":\"ricardo-s-a@hotmail.com\",\"portal_expira_em\":\"2026-05-01 00:00:00\",\"portal_token\":\"9Bkkk5Xram9lvUqJ0pYY1EQxopjK2lm90icRMCpGS9pOl2pFuLIjLDVzMepsR5Ab\",\"updated_at\":\"2026-04-30 19:51:49\",\"created_at\":\"2026-04-30 19:51:49\",\"id\":57}}', NULL, '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(14, 'default', 'Registro criado', 'App\\Models\\ItemControle', 58, 'created', 'App\\Models\\User', 111, '{\"attributes\":{\"titulo\":\"teste link 2\",\"descricao\":\"teste link 2\",\"categoria_id\":1,\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"media\",\"data_vencimento\":\"2026-05-01 00:00:00\",\"empresa_id\":4,\"responsavel_id\":147,\"arquivo\":null,\"observacao\":\"teste link 2\",\"portal_ativo\":true,\"portal_cliente_nome\":\"ricardo\",\"portal_cliente_email\":\"ricardo-s-a@hotmail.com\",\"portal_expira_em\":\"2026-05-01 00:00:00\",\"portal_token\":\"L2K7aLdtNftcNESSQwqcGHlnrglfoT8kQjTY4oTrDH40Rk0Bfxr4xOm844tnZrja\",\"updated_at\":\"2026-04-30 20:30:59\",\"created_at\":\"2026-04-30 20:30:59\",\"id\":58}}', NULL, '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(15, 'default', 'Registro criado', 'App\\Models\\ItemControle', 59, 'created', 'App\\Models\\User', 111, '{\"attributes\":{\"titulo\":\"joyce\",\"descricao\":\"teste\",\"categoria_id\":1,\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"media\",\"data_vencimento\":\"2026-05-23 00:00:00\",\"empresa_id\":4,\"responsavel_id\":147,\"arquivo\":null,\"observacao\":\"teste\",\"portal_ativo\":true,\"portal_cliente_nome\":\"ricardo\",\"portal_cliente_email\":\"ricardo@ricardo.com\",\"portal_expira_em\":\"2026-05-22 00:00:00\",\"portal_token\":\"cV2XTn0PgmXcxkXuLnA1BeLrlDbBpZ3U19eZbtKp9xSqUdRTyfvf5g8BRzvuBi1E\",\"updated_at\":\"2026-05-01 18:18:37\",\"created_at\":\"2026-05-01 18:18:37\",\"id\":59}}', NULL, '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(16, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":false,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-04-30T19:43:18.000000Z\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":5,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":1},\"attributes\":{\"id\":48,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-05-01 18:49:54\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":5,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":1}}', NULL, '2026-05-01 21:49:54', '2026-05-01 21:49:54'),
(17, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-05-01T18:49:54.000000Z\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_count\":0},\"attributes\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":1,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-05-03 13:26:34\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_count\":0}}', NULL, '2026-05-03 16:26:34', '2026-05-03 16:26:34'),
(18, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-05-03T13:26:34.000000Z\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_count\":0},\"attributes\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":1,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-05-03 13:26:40\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_count\":0}}', NULL, '2026-05-03 16:26:40', '2026-05-03 16:26:40'),
(19, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 52, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":52,\"titulo\":\"SEED - Item conclu\\u00eddo\",\"descricao\":\"Item criado para testar indicadores de conclu\\u00eddos e hist\\u00f3rico.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"concluido\",\"prioridade\":\"media\",\"arquivo\":null,\"data_vencimento\":\"2026-05-13T00:00:00.000000Z\",\"data_conclusao\":\"2026-04-28T00:00:00.000000Z\",\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":0,\"observacao\":\"Item seed conclu\\u00eddo.\",\"portal_ativo\":true,\"portal_token\":\"portal-seed-16c955bd-4314-11f1-b74e-18a59cb167c9\",\"portal_cliente_nome\":\"Cliente Conclu\\u00eddo Seed\",\"portal_cliente_email\":\"cliente.concluido.seed@teste.com\",\"portal_expira_em\":\"2026-06-27T12:08:18.000000Z\",\"sla_horas\":12,\"sla_inicio_em\":\"2026-04-28T02:08:18.000000Z\",\"sla_limite_em\":\"2026-04-28T14:08:18.000000Z\",\"sla_concluido_em\":\"2026-04-28T11:08:18.000000Z\",\"sla_status\":\"concluido_no_prazo\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":null,\"ultimo_lembrete_enviado_em\":null,\"qtd_lembretes_enviados\":0,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28T12:08:18.000000Z\",\"updated_at\":\"2026-04-28T12:08:18.000000Z\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_count\":0},\"attributes\":{\"id\":52,\"titulo\":\"SEED - Item conclu\\u00eddo\",\"descricao\":\"Item criado para testar indicadores de conclu\\u00eddos e hist\\u00f3rico.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"prioridade\":\"media\",\"arquivo\":null,\"data_vencimento\":\"2026-05-13\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":0,\"observacao\":\"Item seed conclu\\u00eddo.\",\"portal_ativo\":1,\"portal_token\":\"portal-seed-16c955bd-4314-11f1-b74e-18a59cb167c9\",\"portal_cliente_nome\":\"Cliente Conclu\\u00eddo Seed\",\"portal_cliente_email\":\"cliente.concluido.seed@teste.com\",\"portal_expira_em\":\"2026-06-27 12:08:18\",\"sla_horas\":12,\"sla_inicio_em\":\"2026-04-28 02:08:18\",\"sla_limite_em\":\"2026-04-28 14:08:18\",\"sla_concluido_em\":null,\"sla_status\":\"concluido_no_prazo\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":null,\"ultimo_lembrete_enviado_em\":null,\"qtd_lembretes_enviados\":0,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28 12:08:18\",\"updated_at\":\"2026-05-03 13:26:49\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_count\":0}}', NULL, '2026-05-03 16:26:49', '2026-05-03 16:26:49'),
(20, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 52, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":52,\"titulo\":\"SEED - Item conclu\\u00eddo\",\"descricao\":\"Item criado para testar indicadores de conclu\\u00eddos e hist\\u00f3rico.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"prioridade\":\"media\",\"arquivo\":null,\"data_vencimento\":\"2026-05-13T00:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":0,\"observacao\":\"Item seed conclu\\u00eddo.\",\"portal_ativo\":true,\"portal_token\":\"portal-seed-16c955bd-4314-11f1-b74e-18a59cb167c9\",\"portal_cliente_nome\":\"Cliente Conclu\\u00eddo Seed\",\"portal_cliente_email\":\"cliente.concluido.seed@teste.com\",\"portal_expira_em\":\"2026-06-27T12:08:18.000000Z\",\"sla_horas\":12,\"sla_inicio_em\":\"2026-04-28T02:08:18.000000Z\",\"sla_limite_em\":\"2026-04-28T14:08:18.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"concluido_no_prazo\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":null,\"ultimo_lembrete_enviado_em\":null,\"qtd_lembretes_enviados\":0,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28T12:08:18.000000Z\",\"updated_at\":\"2026-05-03T13:26:49.000000Z\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_count\":0},\"attributes\":{\"id\":52,\"titulo\":\"SEED - Item conclu\\u00eddo\",\"descricao\":\"Item criado para testar indicadores de conclu\\u00eddos e hist\\u00f3rico.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"concluido\",\"prioridade\":\"media\",\"arquivo\":null,\"data_vencimento\":\"2026-05-13\",\"data_conclusao\":\"2026-05-03 00:00:00\",\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":0,\"observacao\":\"Item seed conclu\\u00eddo.\",\"portal_ativo\":1,\"portal_token\":\"portal-seed-16c955bd-4314-11f1-b74e-18a59cb167c9\",\"portal_cliente_nome\":\"Cliente Conclu\\u00eddo Seed\",\"portal_cliente_email\":\"cliente.concluido.seed@teste.com\",\"portal_expira_em\":\"2026-06-27 12:08:18\",\"sla_horas\":12,\"sla_inicio_em\":\"2026-04-28 02:08:18\",\"sla_limite_em\":\"2026-04-28 14:08:18\",\"sla_concluido_em\":\"2026-05-03 13:26:55\",\"sla_status\":\"concluido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":null,\"ultimo_lembrete_enviado_em\":null,\"qtd_lembretes_enviados\":0,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28 12:08:18\",\"updated_at\":\"2026-05-03 13:26:55\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_count\":0}}', NULL, '2026-05-03 16:26:55', '2026-05-03 16:26:55'),
(21, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-05-03T13:26:40.000000Z\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1},\"attributes\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":1,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-05-04 16:43:57\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1}}', NULL, '2026-05-04 19:43:57', '2026-05-04 19:43:57');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `subject_id`, `event`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(22, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-05-04T16:43:57.000000Z\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1},\"attributes\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":1,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-05-04 16:44:01\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1}}', NULL, '2026-05-04 19:44:01', '2026-05-04 19:44:01'),
(23, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-05-04T16:44:01.000000Z\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1},\"attributes\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":1,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-05-04 16:48:29\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1}}', NULL, '2026-05-04 19:48:29', '2026-05-04 19:48:29'),
(24, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-05-04T16:48:29.000000Z\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1},\"attributes\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":1,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-05-04 16:48:35\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1}}', NULL, '2026-05-04 19:48:35', '2026-05-04 19:48:35'),
(25, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-05-04T16:48:35.000000Z\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1},\"attributes\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":1,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-05-04 16:48:40\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1}}', NULL, '2026-05-04 19:48:40', '2026-05-04 19:48:40'),
(26, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-05-04T16:48:40.000000Z\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1},\"attributes\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":1,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-05-04 16:48:44\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1}}', NULL, '2026-05-04 19:48:44', '2026-05-04 19:48:44'),
(27, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-05-04T16:48:44.000000Z\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1},\"attributes\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":1,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-05-04 16:48:49\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1}}', NULL, '2026-05-04 19:48:49', '2026-05-04 19:48:49'),
(28, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-05-04T16:48:49.000000Z\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1},\"attributes\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":1,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-05-04 16:48:53\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1}}', NULL, '2026-05-04 19:48:53', '2026-05-04 19:48:53'),
(29, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-05-04T16:48:53.000000Z\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1},\"attributes\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":1,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-05-04 17:02:49\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1}}', NULL, '2026-05-04 20:02:50', '2026-05-04 20:02:50'),
(30, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-05-04T17:02:49.000000Z\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1},\"attributes\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":1,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-05-04 17:02:53\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1}}', NULL, '2026-05-04 20:02:53', '2026-05-04 20:02:53'),
(31, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-05-04T17:02:53.000000Z\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1},\"attributes\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":1,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-05-04 17:23:15\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1}}', NULL, '2026-05-04 20:23:15', '2026-05-04 20:23:15'),
(32, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 48, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:06:22.000000Z\",\"sla_limite_em\":\"2026-04-28T00:06:22.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28T12:06:22.000000Z\",\"updated_at\":\"2026-05-04T17:23:15.000000Z\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1},\"attributes\":{\"id\":48,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"arquivo\":null,\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":1,\"portal_token\":\"SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:06:22\",\"sla_limite_em\":\"2026-04-28 00:06:22\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":11,\"responsavel_id\":168,\"ultimo_alerta_enviado_em\":\"2026-04-27 12:06:22\",\"ultimo_lembrete_enviado_em\":\"2026-04-27 12:06:22\",\"qtd_lembretes_enviados\":2,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-28 12:06:22\",\"updated_at\":\"2026-05-04 17:23:21\",\"fluxo_operacional_id\":null,\"checklists_count\":1,\"comentarios_total\":1}}', NULL, '2026-05-04 20:23:21', '2026-05-04 20:23:21'),
(33, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 37, 'updated', 'App\\Models\\User', 381, '{\"old\":{\"id\":37,\"titulo\":\"Item de Controle - User Gamma 1\",\"descricao\":\"Registro de controle criado para User Gamma 1\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"view_type\":null,\"automation_status\":null,\"prioridade\":\"media\",\"risco_score\":null,\"bloqueado_por_dependencia\":false,\"arquivo\":null,\"data_vencimento\":\"2026-05-24T00:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":0,\"observacao\":\"Registro inicial criado via SQL.\",\"portal_ativo\":false,\"portal_token\":null,\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"portal_status\":null,\"ultima_interacao_cliente_em\":null,\"sla_horas\":null,\"sla_inicio_em\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":null,\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":6,\"responsavel_id\":163,\"ultimo_alerta_enviado_em\":null,\"ultimo_lembrete_enviado_em\":null,\"qtd_lembretes_enviados\":0,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-24T14:57:10.000000Z\",\"updated_at\":\"2026-04-24T14:57:10.000000Z\",\"fluxo_operacional_id\":null,\"kanban_order\":null,\"blocked_by_dependency\":false,\"estimated_minutes\":null,\"actual_minutes\":null,\"custom_payload\":null,\"template_id\":null,\"approval_required\":false,\"approval_status\":null,\"document_status\":null,\"risk_probability\":null,\"risk_impact\":null,\"risk_score\":null,\"checklists_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":37,\"titulo\":\"Item de Controle - User Gamma 1\",\"descricao\":\"Registro de controle criado para User Gamma 1\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"em_andamento\",\"view_type\":null,\"automation_status\":null,\"prioridade\":\"media\",\"risco_score\":null,\"bloqueado_por_dependencia\":0,\"arquivo\":null,\"data_vencimento\":\"2026-05-24\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":0,\"observacao\":\"Registro inicial criado via SQL.\",\"portal_ativo\":0,\"portal_token\":null,\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"portal_status\":null,\"ultima_interacao_cliente_em\":null,\"sla_horas\":null,\"sla_inicio_em\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":null,\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":6,\"responsavel_id\":163,\"ultimo_alerta_enviado_em\":null,\"ultimo_lembrete_enviado_em\":null,\"qtd_lembretes_enviados\":0,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-05-06 11:59:24\",\"fluxo_operacional_id\":null,\"kanban_order\":null,\"blocked_by_dependency\":0,\"estimated_minutes\":null,\"actual_minutes\":null,\"custom_payload\":null,\"template_id\":null,\"approval_required\":0,\"approval_status\":null,\"document_status\":null,\"risk_probability\":null,\"risk_impact\":null,\"risk_score\":null,\"checklists_count\":0,\"comentarios_total\":0}}', NULL, '2026-05-06 14:59:24', '2026-05-06 14:59:24'),
(34, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 51, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":51,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":false,\"portal_token\":null,\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28T12:08:18.000000Z\",\"updated_at\":\"2026-04-28T12:08:18.000000Z\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:08:18.000000Z\",\"sla_limite_em\":\"2026-04-28T00:08:18.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":0,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":0},\"attributes\":{\"id\":51,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":0,\"portal_token\":\"t5Q6RMAbVqk7WO5hoC3nqskz9d47wlmTut8W0R6nb7IJteQphRT5INOvMRAhvOeT\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28 12:08:18\",\"updated_at\":\"2026-05-08 11:30:31\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:08:18\",\"sla_limite_em\":\"2026-04-28 00:08:18\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":0,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":0}}', NULL, '2026-05-08 14:30:31', '2026-05-08 14:30:31'),
(35, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 51, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":51,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":false,\"portal_token\":\"t5Q6RMAbVqk7WO5hoC3nqskz9d47wlmTut8W0R6nb7IJteQphRT5INOvMRAhvOeT\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28T12:08:18.000000Z\",\"updated_at\":\"2026-05-08T11:30:31.000000Z\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:08:18.000000Z\",\"sla_limite_em\":\"2026-04-28T00:08:18.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":0,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":0},\"attributes\":{\"id\":51,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"t5Q6RMAbVqk7WO5hoC3nqskz9d47wlmTut8W0R6nb7IJteQphRT5INOvMRAhvOeT\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28 12:08:18\",\"updated_at\":\"2026-05-08 11:30:31\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:08:18\",\"sla_limite_em\":\"2026-04-28 00:08:18\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":0,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":0}}', NULL, '2026-05-08 14:30:31', '2026-05-08 14:30:31');

-- --------------------------------------------------------

--
-- Estrutura da tabela `ai_market_comments`
--

CREATE TABLE `ai_market_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `source_id` bigint(20) UNSIGNED DEFAULT NULL,
  `competitor_name` varchar(150) DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `language` varchar(20) NOT NULL DEFAULT 'pt-BR',
  `original_text` longtext NOT NULL,
  `detected_sentiment` varchar(30) DEFAULT NULL,
  `detected_category` varchar(150) DEFAULT NULL,
  `detected_problem` varchar(255) DEFAULT NULL,
  `detected_opportunity` text DEFAULT NULL,
  `detected_real_pain` text DEFAULT NULL,
  `detected_impact` int(11) DEFAULT NULL,
  `recommended_action` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `ai_market_comments`
--

INSERT INTO `ai_market_comments` (`id`, `source_id`, `competitor_name`, `rating`, `language`, `original_text`, `detected_sentiment`, `detected_category`, `detected_problem`, `detected_opportunity`, `detected_real_pain`, `detected_impact`, `recommended_action`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, 'pt-BR', 'ClickUp\'s biggest risk isn\'t competition — it\'s the first 10 minutes\nSigned up for ClickUp recently and noticed something.\n\nThe product can genuinely do everything. That\'s not the problem.\n\nThe problem is that when you open it for the first time, it asks you to make 10 decisions before you\'ve done anything useful.\n\nSpaces, Folders, Lists, Views, Docs — all before you\'ve completed a single task.\n\n\"All-in-one\" is a power-user dream and a new-user nightmare.\n\nAnyone else notice this? Curious if their onboarding has improved recently.', 'misto', 'Onboarding complexo / muitas decisões iniciais', 'Onboarding complexo / muitas decisões iniciais', 'Esconder a arquitetura e entregar valor em menos de 60 segundos com templates prontos por nicho.', 'Sobrecarga cognitiva antes do usuário enxergar valor no produto.', 10, 'Criar primeiro acesso guiado por nicho, com template automático e primeira tarefa pronta.', '{\"classification\":{\"sentiment\":\"misto\",\"insight_type\":\"problema\",\"category_key\":\"onboarding_complexo\",\"category\":\"Onboarding complexo \\/ muitas decis\\u00f5es iniciais\",\"problem\":\"Onboarding complexo \\/ muitas decis\\u00f5es iniciais\",\"real_pain\":\"Sobrecarga cognitiva antes do usu\\u00e1rio enxergar valor no produto.\",\"impact\":10,\"severity\":10,\"insight\":\"Usu\\u00e1rios querem produzir antes de aprender a arquitetura do sistema.\",\"market_learning\":\"O primeiro valor precisa aparecer antes da primeira configura\\u00e7\\u00e3o complexa.\",\"what_to_do\":\"Criar primeiro acesso guiado por nicho, com template autom\\u00e1tico e primeira tarefa pronta.\",\"what_not_to_do\":\"N\\u00e3o exigir que o usu\\u00e1rio entenda espa\\u00e7os, pastas, listas, tipos ou configura\\u00e7\\u00f5es antes de fazer algo \\u00fatil.\",\"opportunity\":\"Esconder a arquitetura e entregar valor em menos de 60 segundos com templates prontos por nicho.\",\"recommended_action\":\"Criar primeiro acesso guiado por nicho, com template autom\\u00e1tico e primeira tarefa pronta.\",\"complexity\":\"baixa\\/m\\u00e9dia\",\"seo_keywords\":[\"sistema pronto para contabilidade\",\"software simples para come\\u00e7ar r\\u00e1pido\",\"alternativa simples ao ClickUp\",\"onboarding r\\u00e1pido para equipes\"],\"matched_keywords\":[\"onboarding\",\"first 10 minutes\"],\"confidence\":\"m\\u00e9dia\",\"problems\":[{\"key\":\"onboarding_complexo\",\"category\":\"Onboarding complexo \\/ muitas decis\\u00f5es iniciais\",\"score\":30,\"impact\":10,\"severity\":10,\"real_pain\":\"Sobrecarga cognitiva antes do usu\\u00e1rio enxergar valor no produto.\",\"insight\":\"Usu\\u00e1rios querem produzir antes de aprender a arquitetura do sistema.\",\"market_learning\":\"O primeiro valor precisa aparecer antes da primeira configura\\u00e7\\u00e3o complexa.\",\"what_to_do\":\"Criar primeiro acesso guiado por nicho, com template autom\\u00e1tico e primeira tarefa pronta.\",\"what_not_to_do\":\"N\\u00e3o exigir que o usu\\u00e1rio entenda espa\\u00e7os, pastas, listas, tipos ou configura\\u00e7\\u00f5es antes de fazer algo \\u00fatil.\",\"opportunity\":\"Esconder a arquitetura e entregar valor em menos de 60 segundos com templates prontos por nicho.\",\"recommended_action\":\"Criar primeiro acesso guiado por nicho, com template autom\\u00e1tico e primeira tarefa pronta.\",\"complexity\":\"baixa\\/m\\u00e9dia\",\"seo_keywords\":[\"sistema pronto para contabilidade\",\"software simples para come\\u00e7ar r\\u00e1pido\",\"alternativa simples ao ClickUp\",\"onboarding r\\u00e1pido para equipes\"],\"matched_keywords\":[\"onboarding\",\"first 10 minutes\"],\"confidence\":\"m\\u00e9dia\"},{\"key\":\"excesso_funcionalidades\",\"category\":\"Excesso de funcionalidades \\/ produto inchado\",\"score\":16,\"impact\":8,\"severity\":8,\"real_pain\":\"Usu\\u00e1rio sente que a ferramenta faz tudo, mas exige esfor\\u00e7o demais para o b\\u00e1sico.\",\"insight\":\"Produto completo demais pode virar obst\\u00e1culo para quem s\\u00f3 quer executar o trabalho.\",\"market_learning\":\"Ser menor e mais claro pode vencer ser maior e mais confuso.\",\"what_to_do\":\"Manter recursos avan\\u00e7ados escondidos e priorizar caminhos simples para tarefas cr\\u00edticas.\",\"what_not_to_do\":\"N\\u00e3o copiar recursos avan\\u00e7ados dos concorrentes se eles aumentarem complexidade para novos usu\\u00e1rios.\",\"opportunity\":\"Vender simplicidade, foco no trabalho cr\\u00edtico e evitar copiar recursos que aumentam complexidade.\",\"recommended_action\":\"Manter recursos avan\\u00e7ados escondidos e priorizar caminhos simples para tarefas cr\\u00edticas.\",\"complexity\":\"baixa\",\"seo_keywords\":[\"alternativa simples ao ClickUp\",\"sistema sem complexidade\",\"software operacional simples\"],\"matched_keywords\":[\"all-in-one\"],\"confidence\":\"m\\u00e9dia\"}],\"strengths\":[{\"key\":\"centralizacao\",\"category\":\"Ponto forte: centraliza\\u00e7\\u00e3o \\/ tudo em um \\u00fatil\",\"score\":15,\"impact\":8,\"insight\":\"Usu\\u00e1rios valorizam centralizar trabalho quando isso n\\u00e3o aumenta complexidade.\",\"market_learning\":\"Centraliza\\u00e7\\u00e3o \\u00e9 vantagem para usu\\u00e1rios avan\\u00e7ados, mas precisa ser escondida dos iniciantes.\",\"what_to_do\":\"Oferecer vis\\u00e3o centralizada sem obrigar novos usu\\u00e1rios a configurar toda a estrutura antes de usar.\",\"what_not_to_do\":\"N\\u00e3o transformar centraliza\\u00e7\\u00e3o em excesso de menus, n\\u00edveis e decis\\u00f5es iniciais.\",\"seo_keywords\":[\"sistema centralizado para empresas\",\"gest\\u00e3o operacional em um lugar\"],\"matched_keywords\":[\"all-in-one\"],\"confidence\":\"m\\u00e9dia\"}]},\"imported_by_user_id\":111,\"imported_from_url\":null}', '2026-06-01 14:52:25', '2026-06-01 14:52:25');

-- --------------------------------------------------------

--
-- Estrutura da tabela `ai_market_sources`
--

CREATE TABLE `ai_market_sources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `competitor_name` varchar(150) DEFAULT NULL,
  `source_type` varchar(50) NOT NULL DEFAULT 'manual',
  `source_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `ai_market_sources`
--

INSERT INTO `ai_market_sources` (`id`, `name`, `competitor_name`, `source_type`, `source_url`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Importação manual', NULL, 'reddit', NULL, 1, '2026-06-01 14:52:25', '2026-06-01 14:52:25');

-- --------------------------------------------------------

--
-- Estrutura da tabela `alerta_enviados`
--

CREATE TABLE `alerta_enviados` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `tipo_alerta` varchar(255) NOT NULL,
  `destinatario` varchar(255) DEFAULT NULL,
  `canal` varchar(255) NOT NULL DEFAULT 'email',
  `enviado_em` timestamp NULL DEFAULT NULL,
  `status_envio` varchar(255) DEFAULT NULL,
  `mensagem` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `anexos`
--

CREATE TABLE `anexos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `caminho` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `anexo_items`
--

CREATE TABLE `anexo_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `nome_arquivo` varchar(255) NOT NULL,
  `caminho_arquivo` varchar(255) NOT NULL,
  `tipo_arquivo` varchar(255) DEFAULT NULL,
  `tamanho` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `assinaturas`
--

CREATE TABLE `assinaturas` (
  `id` bigint(20) NOT NULL,
  `empresa_id` bigint(20) NOT NULL,
  `gateway` varchar(50) DEFAULT 'asaas',
  `gateway_customer_id` varchar(255) DEFAULT NULL,
  `gateway_subscription_id` varchar(255) DEFAULT NULL,
  `plano` varchar(100) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `ciclo` varchar(20) DEFAULT 'MONTHLY',
  `status` varchar(50) DEFAULT 'PENDING',
  `proximo_vencimento` date DEFAULT NULL,
  `cancelado_em` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `assinaturas`
--

INSERT INTO `assinaturas` (`id`, `empresa_id`, `gateway`, `gateway_customer_id`, `gateway_subscription_id`, `plano`, `valor`, `ciclo`, `status`, `proximo_vencimento`, `cancelado_em`, `created_at`, `updated_at`) VALUES
(1, 13, 'asaas', 'cus_000007891665', 'sub_2gsdj8xz8mcrp0sm', 'profissional', 247.00, 'MONTHLY', 'ACTIVE', '2026-06-01', NULL, '2026-05-01 16:09:40', '2026-05-01 16:09:40'),
(2, 18, 'asaas', 'cus_000007891937', 'sub_e63ermcilkur78fp', 'business_plus', 697.00, 'MONTHLY', 'ACTIVE', '2026-06-01', NULL, '2026-05-01 17:35:42', '2026-05-01 17:35:42'),
(3, 21, 'asaas', 'cus_000007891970', 'sub_ybfxt28jcw2zjmiu', 'business', 397.00, 'MONTHLY', 'ACTIVE', '2026-06-01', NULL, '2026-05-01 17:53:11', '2026-05-01 17:53:11'),
(4, 22, 'asaas', 'cus_000007891989', 'sub_2sfju62w845xahpz', 'business', 397.00, 'MONTHLY', 'ACTIVE', '2026-06-01', NULL, '2026-05-01 17:59:12', '2026-05-01 17:59:12');

-- --------------------------------------------------------

--
-- Estrutura da tabela `atendimentos`
--

CREATE TABLE `atendimentos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `crm_cliente_id` bigint(20) UNSIGNED DEFAULT NULL,
  `portal_solicitacao_id` bigint(20) UNSIGNED DEFAULT NULL,
  `portal_mensagem_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_controle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `responsavel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `criado_por` bigint(20) UNSIGNED DEFAULT NULL,
  `titulo` varchar(180) NOT NULL,
  `descricao` longtext NOT NULL,
  `status` enum('aberto','em_andamento','aguardando_cliente','aguardando_suporte','resolvido','fechado','cancelado') NOT NULL DEFAULT 'aberto',
  `prioridade` enum('baixa','media','alta','urgente') NOT NULL DEFAULT 'media',
  `origem` enum('manual','portal','whatsapp','email','telefone') NOT NULL DEFAULT 'manual',
  `canal` enum('interno','portal','whatsapp','email','telefone') NOT NULL DEFAULT 'interno',
  `sla_horas` int(10) UNSIGNED DEFAULT NULL,
  `sla_limite_em` datetime DEFAULT NULL,
  `primeira_resposta_em` datetime DEFAULT NULL,
  `resolvido_em` datetime DEFAULT NULL,
  `fechado_em` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `atendimentos`
--

INSERT INTO `atendimentos` (`id`, `empresa_id`, `crm_cliente_id`, `portal_solicitacao_id`, `portal_mensagem_id`, `item_controle_id`, `responsavel_id`, `criado_por`, `titulo`, `descricao`, `status`, `prioridade`, `origem`, `canal`, `sla_horas`, `sla_limite_em`, `primeira_resposta_em`, `resolvido_em`, `fechado_em`, `created_at`, `updated_at`) VALUES
(1, 6, NULL, 10, NULL, NULL, 381, NULL, 'teste mensagem', 'teste', 'em_andamento', 'alta', 'portal', 'portal', 24, '2026-05-20 13:56:35', '2026-05-19 13:57:17', NULL, NULL, '2026-05-19 16:56:35', '2026-05-19 16:57:17'),
(2, 21, 1, NULL, 12, NULL, NULL, NULL, 'Mensagem do portal - ricardo', 'teste de hoije', 'aberto', 'media', 'portal', 'portal', 48, '2026-05-27 17:09:56', NULL, NULL, NULL, '2026-05-25 20:09:56', '2026-05-25 20:09:56'),
(3, 4, 11, NULL, 13, NULL, NULL, NULL, 'Mensagem do portal - Alpha', 'teste', 'aberto', 'media', 'portal', 'portal', 48, '2026-05-27 17:54:21', NULL, NULL, NULL, '2026-05-25 20:54:21', '2026-05-25 20:54:21'),
(4, 4, 11, NULL, 15, NULL, NULL, NULL, 'Mensagem do portal - Alpha', 'teste', 'aberto', 'media', 'portal', 'portal', 48, '2026-05-27 18:58:19', NULL, NULL, NULL, '2026-05-25 21:58:19', '2026-05-25 21:58:19'),
(5, 21, 1, NULL, 16, NULL, NULL, NULL, 'Mensagem do portal - ricardo empresa', 'sss', 'aberto', 'media', 'portal', 'portal', 48, '2026-05-27 19:08:15', NULL, NULL, NULL, '2026-05-25 22:08:15', '2026-05-25 22:08:15'),
(6, 21, 1, NULL, 17, NULL, NULL, NULL, 'Mensagem do portal - ricardo empresa', 'teste', 'aberto', 'media', 'portal', 'portal', 48, '2026-05-27 19:27:07', NULL, NULL, NULL, '2026-05-25 22:27:07', '2026-05-25 22:27:07'),
(7, 21, 1, NULL, 18, NULL, NULL, NULL, 'Mensagem do portal - ricardo empresa', 'teste', 'aberto', 'media', 'portal', 'portal', 48, '2026-05-27 20:05:06', NULL, NULL, NULL, '2026-05-25 23:05:06', '2026-05-25 23:05:06'),
(8, 21, 1, NULL, 19, NULL, NULL, NULL, 'Mensagem do portal - ricardo empresa', 'teste', 'aberto', 'media', 'portal', 'portal', 48, '2026-05-27 20:10:30', NULL, NULL, NULL, '2026-05-25 23:10:30', '2026-05-25 23:10:30'),
(9, 21, 1, NULL, 20, NULL, NULL, NULL, 'Mensagem do portal - ricardo empresa', 'teste', 'aberto', 'media', 'portal', 'portal', 48, '2026-05-27 20:10:37', NULL, NULL, NULL, '2026-05-25 23:10:37', '2026-05-25 23:10:37'),
(10, 21, 1, NULL, 21, NULL, NULL, NULL, 'Mensagem do portal - ricardo empresa', 'teste', 'aberto', 'media', 'portal', 'portal', 48, '2026-05-27 20:16:22', NULL, NULL, NULL, '2026-05-25 23:16:22', '2026-05-25 23:16:22'),
(11, 21, 1, NULL, 22, NULL, NULL, NULL, 'Mensagem do portal - ricardo empresa', 'teste,', 'aberto', 'media', 'portal', 'portal', 48, '2026-05-27 20:18:19', NULL, NULL, NULL, '2026-05-25 23:18:19', '2026-05-25 23:18:19'),
(12, 21, 1, NULL, 23, NULL, NULL, NULL, 'Mensagem do portal - ricardo empresa', 'teste 17:18', 'aberto', 'media', 'portal', 'portal', 48, '2026-05-27 20:18:35', NULL, NULL, NULL, '2026-05-25 23:18:35', '2026-05-25 23:18:35'),
(13, 21, 1, NULL, 24, NULL, NULL, NULL, 'Mensagem do portal - ricardo empresa', 'oi', 'aberto', 'media', 'portal', 'portal', 48, '2026-05-27 20:19:03', NULL, NULL, NULL, '2026-05-25 23:19:03', '2026-05-25 23:19:03'),
(14, 21, 1, NULL, 25, NULL, NULL, NULL, 'Mensagem do portal - ricardo empresa', 'ouasufbae', 'aberto', 'media', 'portal', 'portal', 48, '2026-05-27 20:19:47', NULL, NULL, NULL, '2026-05-25 23:19:47', '2026-05-25 23:19:47');

-- --------------------------------------------------------

--
-- Estrutura da tabela `atendimento_interacoes`
--

CREATE TABLE `atendimento_interacoes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `atendimento_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `origem` enum('interno','cliente','sistema','suporte') NOT NULL DEFAULT 'interno',
  `tipo` enum('abertura','comentario','alteracao','responsavel','resposta','resolucao','reabertura','sistema','anexo') NOT NULL DEFAULT 'comentario',
  `mensagem` longtext NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `atendimento_interacoes`
--

INSERT INTO `atendimento_interacoes` (`id`, `atendimento_id`, `user_id`, `origem`, `tipo`, `mensagem`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'cliente', 'abertura', 'Solicitação aberta pelo portal.\n\nPrioridade: Alta\n\nteste', '{\"portal_solicitacao_id\":10,\"portal_status\":\"aberto\"}', '2026-05-19 16:56:35', '2026-05-19 16:56:35'),
(2, 1, 381, 'interno', 'responsavel', 'Atendimento assumido por Admin Gamma.', NULL, '2026-05-19 16:57:17', '2026-05-19 16:57:17'),
(4, 2, NULL, 'cliente', 'abertura', 'Cliente: ricardo <ricardo-s-a@hotmail.com>\n\nteste de hoije', '{\"portal_mensagem_id\":12,\"nome\":\"ricardo\",\"email\":\"ricardo-s-a@hotmail.com\"}', '2026-05-25 20:09:56', '2026-05-25 20:09:56'),
(5, 3, NULL, 'cliente', 'abertura', 'Cliente: Alpha <alpha@email.com>\n\nteste', '{\"portal_mensagem_id\":13,\"nome\":\"Alpha\",\"email\":\"alpha@email.com\"}', '2026-05-25 20:54:21', '2026-05-25 20:54:21'),
(6, 4, NULL, 'cliente', 'abertura', 'Cliente: Alpha <alpha@email.com>\n\nteste', '{\"portal_mensagem_id\":15,\"nome\":\"Alpha\",\"email\":\"alpha@email.com\"}', '2026-05-25 21:58:19', '2026-05-25 21:58:19'),
(7, 5, NULL, 'cliente', 'abertura', 'Cliente: ricardo empresa <ricardo-s-a@hotmail.com>\n\nsss', '{\"portal_mensagem_id\":16,\"nome\":\"ricardo empresa\",\"email\":\"ricardo-s-a@hotmail.com\"}', '2026-05-25 22:08:15', '2026-05-25 22:08:15'),
(8, 6, NULL, 'cliente', 'abertura', 'Cliente: ricardo empresa <ricardo-s-a@hotmail.com>\n\nteste', '{\"portal_mensagem_id\":17,\"nome\":\"ricardo empresa\",\"email\":\"ricardo-s-a@hotmail.com\"}', '2026-05-25 22:27:07', '2026-05-25 22:27:07'),
(9, 7, NULL, 'cliente', 'abertura', 'Cliente: ricardo empresa <ricardo-s-a@hotmail.com>\n\nteste', '{\"portal_mensagem_id\":18,\"nome\":\"ricardo empresa\",\"email\":\"ricardo-s-a@hotmail.com\"}', '2026-05-25 23:05:06', '2026-05-25 23:05:06'),
(10, 8, NULL, 'cliente', 'abertura', 'Cliente: ricardo empresa <ricardo-s-a@hotmail.com>\n\nteste', '{\"portal_mensagem_id\":19,\"nome\":\"ricardo empresa\",\"email\":\"ricardo-s-a@hotmail.com\"}', '2026-05-25 23:10:30', '2026-05-25 23:10:30'),
(11, 9, NULL, 'cliente', 'abertura', 'Cliente: ricardo empresa <ricardo-s-a@hotmail.com>\n\nteste', '{\"portal_mensagem_id\":20,\"nome\":\"ricardo empresa\",\"email\":\"ricardo-s-a@hotmail.com\"}', '2026-05-25 23:10:37', '2026-05-25 23:10:37'),
(12, 10, NULL, 'cliente', 'abertura', 'Cliente: ricardo empresa <ricardo-s-a@hotmail.com>\n\nteste', '{\"portal_mensagem_id\":21,\"nome\":\"ricardo empresa\",\"email\":\"ricardo-s-a@hotmail.com\"}', '2026-05-25 23:16:22', '2026-05-25 23:16:22'),
(13, 11, NULL, 'cliente', 'abertura', 'Cliente: ricardo empresa <ricardo-s-a@hotmail.com>\n\nteste,', '{\"portal_mensagem_id\":22,\"nome\":\"ricardo empresa\",\"email\":\"ricardo-s-a@hotmail.com\"}', '2026-05-25 23:18:19', '2026-05-25 23:18:19'),
(14, 12, NULL, 'cliente', 'abertura', 'Cliente: ricardo empresa <ricardo-s-a@hotmail.com>\n\nteste 17:18', '{\"portal_mensagem_id\":23,\"nome\":\"ricardo empresa\",\"email\":\"ricardo-s-a@hotmail.com\"}', '2026-05-25 23:18:35', '2026-05-25 23:18:35'),
(15, 13, NULL, 'cliente', 'abertura', 'Cliente: ricardo empresa <ricardo-s-a@hotmail.com>\n\noi', '{\"portal_mensagem_id\":24,\"nome\":\"ricardo empresa\",\"email\":\"ricardo-s-a@hotmail.com\"}', '2026-05-25 23:19:03', '2026-05-25 23:19:03'),
(16, 14, NULL, 'cliente', 'abertura', 'Cliente: ricardo empresa <ricardo-s-a@hotmail.com>\n\nouasufbae', '{\"portal_mensagem_id\":25,\"nome\":\"ricardo empresa\",\"email\":\"ricardo-s-a@hotmail.com\"}', '2026-05-25 23:19:47', '2026-05-25 23:19:47');

-- --------------------------------------------------------

--
-- Estrutura da tabela `auditoria_detalhada`
--

CREATE TABLE `auditoria_detalhada` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `auditable_type` varchar(255) NOT NULL,
  `auditable_id` bigint(20) UNSIGNED NOT NULL,
  `evento` varchar(50) NOT NULL,
  `nivel` varchar(50) DEFAULT NULL,
  `campo` varchar(255) NOT NULL,
  `valor_anterior` longtext DEFAULT NULL,
  `valor_novo` longtext DEFAULT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `auditoria_detalhada`
--

INSERT INTO `auditoria_detalhada` (`id`, `empresa_id`, `user_id`, `auditable_type`, `auditable_id`, `evento`, `nivel`, `campo`, `valor_anterior`, `valor_novo`, `ip`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 19:44:47', '2026-04-28 19:44:47'),
(2, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'portal_ativo', '0', '0', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 19:44:47', '2026-04-28 19:44:47'),
(3, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'portal_token', NULL, 'SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 19:44:47', '2026-04-28 19:44:47'),
(4, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 19:44:47', '2026-04-28 19:44:47'),
(5, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'portal_ativo', '0', '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-28 19:44:47', '2026-04-28 19:44:47'),
(6, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'titulo', NULL, 'Contrato RH Interno Portal do Cliente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(7, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'descricao', NULL, 'teste', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(8, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'categoria_id', NULL, '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(9, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'tipo', NULL, 'documento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(10, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'status', NULL, 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(11, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'prioridade', NULL, 'media', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(12, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'data_vencimento', NULL, '2026-04-30 00:00:00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(13, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'empresa_id', NULL, '4', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(14, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'responsavel_id', NULL, '147', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(15, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'arquivo', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(16, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'observacao', NULL, 'teste ssssssssssssssss', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(17, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'portal_ativo', NULL, '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(18, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'portal_cliente_nome', NULL, 'ricardo', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(19, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'portal_cliente_email', NULL, 'ricardo-s-a@hotmail.com', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(20, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'portal_expira_em', NULL, '2026-04-30 00:00:00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(21, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'portal_token', NULL, 'BD3W26Thqb2soy0b648UExabhDULsCY1cMHj0JsLXd0GbHf4GqZqPMDJQkfhLppx', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(22, 4, 111, 'App\\Models\\ItemControle', 56, 'created', NULL, 'id', NULL, '56', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 20:30:56', '2026-04-30 20:30:56'),
(23, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:43:06', '2026-04-30 22:43:06'),
(24, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'portal_ativo', '1', '0', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:43:06', '2026-04-30 22:43:06'),
(25, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:43:10', '2026-04-30 22:43:10'),
(26, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'portal_ativo', '0', '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:43:10', '2026-04-30 22:43:10'),
(27, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:43:18', '2026-04-30 22:43:18'),
(28, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'portal_ativo', '1', '0', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:43:18', '2026-04-30 22:43:18'),
(29, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', NULL, 'data_vencimento', '\"2026-05-24T00:00:00.000000Z\"', '2026-05-24', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:44:38', '2026-04-30 22:44:38'),
(30, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', NULL, 'portal_ativo', '0', '0', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:44:38', '2026-04-30 22:44:38'),
(31, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', NULL, 'portal_token', NULL, '8GvffdhZO2AjBuKjMafgmtA5ZAxZK1EVCpoZaIqIZ5kyJoBuNYYuTWHmcdX1E3xa', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:44:38', '2026-04-30 22:44:38'),
(32, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', NULL, 'data_vencimento', '\"2026-05-24T00:00:00.000000Z\"', '2026-05-24', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:44:38', '2026-04-30 22:44:38'),
(33, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', NULL, 'portal_ativo', '0', '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:44:38', '2026-04-30 22:44:38'),
(34, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', NULL, 'data_vencimento', '\"2026-05-24T00:00:00.000000Z\"', '2026-05-24', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:44:42', '2026-04-30 22:44:42'),
(35, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', NULL, 'portal_ativo', '1', '0', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:44:42', '2026-04-30 22:44:42'),
(36, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'titulo', NULL, 'teste link', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(37, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'descricao', NULL, 'teste link', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(38, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'categoria_id', NULL, '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(39, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'tipo', NULL, 'documento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(40, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'status', NULL, 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(41, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'prioridade', NULL, 'media', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(42, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'data_vencimento', NULL, '2026-05-01 00:00:00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(43, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'empresa_id', NULL, '4', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(44, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'responsavel_id', NULL, '147', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(45, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'arquivo', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(46, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'observacao', NULL, 'teste', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(47, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'portal_ativo', NULL, '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(48, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'portal_cliente_nome', NULL, 'ricardo', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(49, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'portal_cliente_email', NULL, 'ricardo-s-a@hotmail.com', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(50, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'portal_expira_em', NULL, '2026-05-01 00:00:00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(51, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'portal_token', NULL, '9Bkkk5Xram9lvUqJ0pYY1EQxopjK2lm90icRMCpGS9pOl2pFuLIjLDVzMepsR5Ab', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(52, 4, 111, 'App\\Models\\ItemControle', 57, 'created', NULL, 'id', NULL, '57', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 22:51:49', '2026-04-30 22:51:49'),
(53, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'titulo', NULL, 'teste link 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(54, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'descricao', NULL, 'teste link 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(55, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'categoria_id', NULL, '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(56, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'tipo', NULL, 'documento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(57, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'status', NULL, 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(58, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'prioridade', NULL, 'media', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(59, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'data_vencimento', NULL, '2026-05-01 00:00:00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(60, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'empresa_id', NULL, '4', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(61, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'responsavel_id', NULL, '147', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(62, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'arquivo', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(63, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'observacao', NULL, 'teste link 2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(64, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'portal_ativo', NULL, '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(65, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'portal_cliente_nome', NULL, 'ricardo', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(66, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'portal_cliente_email', NULL, 'ricardo-s-a@hotmail.com', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(67, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'portal_expira_em', NULL, '2026-05-01 00:00:00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(68, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'portal_token', NULL, 'L2K7aLdtNftcNESSQwqcGHlnrglfoT8kQjTY4oTrDH40Rk0Bfxr4xOm844tnZrja', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(69, 4, 111, 'App\\Models\\ItemControle', 58, 'created', NULL, 'id', NULL, '58', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 23:30:59', '2026-04-30 23:30:59'),
(70, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'titulo', NULL, 'joyce', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(71, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'descricao', NULL, 'teste', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(72, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'categoria_id', NULL, '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(73, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'tipo', NULL, 'documento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(74, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'status', NULL, 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(75, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'prioridade', NULL, 'media', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(76, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'data_vencimento', NULL, '2026-05-23 00:00:00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(77, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'empresa_id', NULL, '4', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(78, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'responsavel_id', NULL, '147', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(79, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'arquivo', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(80, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'observacao', NULL, 'teste', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(81, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'portal_ativo', NULL, '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(82, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'portal_cliente_nome', NULL, 'ricardo', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(83, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'portal_cliente_email', NULL, 'ricardo@ricardo.com', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(84, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'portal_expira_em', NULL, '2026-05-22 00:00:00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(85, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'portal_token', NULL, 'cV2XTn0PgmXcxkXuLnA1BeLrlDbBpZ3U19eZbtKp9xSqUdRTyfvf5g8BRzvuBi1E', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(86, 4, 111, 'App\\Models\\ItemControle', 59, 'created', NULL, 'id', NULL, '59', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:18:37', '2026-05-01 21:18:37'),
(87, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:49:54', '2026-05-01 21:49:54'),
(88, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'portal_ativo', '0', '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-01 21:49:54', '2026-05-01 21:49:54'),
(89, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 16:26:34', '2026-05-03 16:26:34'),
(90, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 16:26:34', '2026-05-03 16:26:34'),
(91, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'status', 'em_andamento', 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 16:26:40', '2026-05-03 16:26:40'),
(92, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 16:26:40', '2026-05-03 16:26:40'),
(93, 11, 111, 'App\\Models\\ItemControle', 52, 'updated', NULL, 'status', 'concluido', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 16:26:49', '2026-05-03 16:26:49'),
(94, 11, 111, 'App\\Models\\ItemControle', 52, 'updated', NULL, 'data_vencimento', '\"2026-05-13T00:00:00.000000Z\"', '2026-05-13', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 16:26:49', '2026-05-03 16:26:49'),
(95, 11, 111, 'App\\Models\\ItemControle', 52, 'updated', NULL, 'data_conclusao', '\"2026-04-28T00:00:00.000000Z\"', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 16:26:49', '2026-05-03 16:26:49'),
(96, 11, 111, 'App\\Models\\ItemControle', 52, 'updated', NULL, 'sla_concluido_em', '\"2026-04-28T11:08:18.000000Z\"', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 16:26:49', '2026-05-03 16:26:49'),
(97, 11, 111, 'App\\Models\\ItemControle', 52, 'updated', NULL, 'status', 'em_andamento', 'concluido', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 16:26:55', '2026-05-03 16:26:55'),
(98, 11, 111, 'App\\Models\\ItemControle', 52, 'updated', NULL, 'data_vencimento', '\"2026-05-13T00:00:00.000000Z\"', '2026-05-13', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 16:26:55', '2026-05-03 16:26:55'),
(99, 11, 111, 'App\\Models\\ItemControle', 52, 'updated', NULL, 'data_conclusao', NULL, '2026-05-03 00:00:00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 16:26:55', '2026-05-03 16:26:55'),
(100, 11, 111, 'App\\Models\\ItemControle', 52, 'updated', NULL, 'sla_concluido_em', NULL, '2026-05-03 13:26:55', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 16:26:55', '2026-05-03 16:26:55'),
(101, 11, 111, 'App\\Models\\ItemControle', 52, 'updated', NULL, 'sla_status', 'concluido_no_prazo', 'concluido', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-03 16:26:55', '2026-05-03 16:26:55'),
(102, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 19:43:57', '2026-05-04 19:43:57'),
(103, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 19:43:57', '2026-05-04 19:43:57'),
(104, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'status', 'em_andamento', 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 19:44:01', '2026-05-04 19:44:01'),
(105, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 19:44:01', '2026-05-04 19:44:01'),
(106, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 19:48:29', '2026-05-04 19:48:29'),
(107, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 19:48:29', '2026-05-04 19:48:29'),
(108, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'status', 'em_andamento', 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 19:48:35', '2026-05-04 19:48:35'),
(109, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 19:48:35', '2026-05-04 19:48:35'),
(110, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 19:48:40', '2026-05-04 19:48:40'),
(111, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 19:48:40', '2026-05-04 19:48:40'),
(112, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'status', 'em_andamento', 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 19:48:44', '2026-05-04 19:48:44'),
(113, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 19:48:44', '2026-05-04 19:48:44'),
(114, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 19:48:49', '2026-05-04 19:48:49'),
(115, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 19:48:49', '2026-05-04 19:48:49'),
(116, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'status', 'em_andamento', 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 19:48:53', '2026-05-04 19:48:53'),
(117, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 19:48:53', '2026-05-04 19:48:53'),
(118, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 20:02:49', '2026-05-04 20:02:49'),
(119, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 20:02:49', '2026-05-04 20:02:49'),
(120, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'status', 'em_andamento', 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 20:02:53', '2026-05-04 20:02:53'),
(121, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 20:02:53', '2026-05-04 20:02:53'),
(122, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 20:23:15', '2026-05-04 20:23:15'),
(123, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 20:23:15', '2026-05-04 20:23:15'),
(124, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'status', 'em_andamento', 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 20:23:21', '2026-05-04 20:23:21'),
(125, 11, 111, 'App\\Models\\ItemControle', 48, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-04 20:23:21', '2026-05-04 20:23:21'),
(126, 6, 381, 'App\\Models\\ItemControle', 37, 'updated', NULL, 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 14:59:24', '2026-05-06 14:59:24'),
(127, 6, 381, 'App\\Models\\ItemControle', 37, 'updated', NULL, 'bloqueado_por_dependencia', '0', '0', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 14:59:24', '2026-05-06 14:59:24'),
(128, 6, 381, 'App\\Models\\ItemControle', 37, 'updated', NULL, 'data_vencimento', '\"2026-05-24T00:00:00.000000Z\"', '2026-05-24', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 14:59:24', '2026-05-06 14:59:24'),
(129, 6, 381, 'App\\Models\\ItemControle', 37, 'updated', NULL, 'portal_ativo', '0', '0', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 14:59:24', '2026-05-06 14:59:24'),
(130, 6, 381, 'App\\Models\\ItemControle', 37, 'updated', NULL, 'blocked_by_dependency', '0', '0', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 14:59:24', '2026-05-06 14:59:24'),
(131, 6, 381, 'App\\Models\\ItemControle', 37, 'updated', NULL, 'approval_required', '0', '0', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 14:59:24', '2026-05-06 14:59:24'),
(132, 11, 111, 'App\\Models\\ItemControle', 51, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 14:30:31', '2026-05-08 14:30:31'),
(133, 11, 111, 'App\\Models\\ItemControle', 51, 'updated', NULL, 'portal_ativo', '0', '0', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 14:30:31', '2026-05-08 14:30:31'),
(134, 11, 111, 'App\\Models\\ItemControle', 51, 'updated', NULL, 'portal_token', NULL, 't5Q6RMAbVqk7WO5hoC3nqskz9d47wlmTut8W0R6nb7IJteQphRT5INOvMRAhvOeT', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 14:30:31', '2026-05-08 14:30:31'),
(135, 11, 111, 'App\\Models\\ItemControle', 51, 'updated', NULL, 'data_vencimento', '\"2026-04-23T00:00:00.000000Z\"', '2026-04-23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 14:30:31', '2026-05-08 14:30:31'),
(136, 11, 111, 'App\\Models\\ItemControle', 51, 'updated', NULL, 'portal_ativo', '0', '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 14:30:31', '2026-05-08 14:30:31');

-- --------------------------------------------------------

--
-- Estrutura da tabela `audit_timeline`
--

CREATE TABLE `audit_timeline` (
  `id` bigint(20) NOT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` bigint(20) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `old_data` longtext DEFAULT NULL,
  `new_data` longtext DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `automation_rules`
--

CREATE TABLE `automation_rules` (
  `id` bigint(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `trigger_type` varchar(100) DEFAULT NULL,
  `condition_field` varchar(100) DEFAULT NULL,
  `condition_operator` varchar(20) DEFAULT NULL,
  `condition_value` varchar(255) DEFAULT NULL,
  `action_type` varchar(100) DEFAULT NULL,
  `action_value` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `categorias_item_controle`
--

CREATE TABLE `categorias_item_controle` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `cor` varchar(50) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `ordem` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `categorias_item_controle`
--

INSERT INTO `categorias_item_controle` (`id`, `empresa_id`, `nome`, `descricao`, `cor`, `ativo`, `ordem`, `created_at`, `updated_at`) VALUES
(1, 4, 'teste', NULL, 'gray', 1, 0, '2026-04-30 16:41:10', '2026-04-30 16:41:10');

-- --------------------------------------------------------

--
-- Estrutura da tabela `categoria_item_controle_checklist_templates`
--

CREATE TABLE `categoria_item_controle_checklist_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `categoria_item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ordem` int(11) NOT NULL DEFAULT 0,
  `obrigatorio` tinyint(1) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cliente_portal_password_reset_tokens`
--

CREATE TABLE `cliente_portal_password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cliente_portal_users`
--

CREATE TABLE `cliente_portal_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `cargo` varchar(120) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `ultimo_acesso_em` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `client_portal_messages`
--

CREATE TABLE `client_portal_messages` (
  `id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `message` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `comentarios`
--

CREATE TABLE `comentarios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `configuracoes`
--

CREATE TABLE `configuracoes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `dias_alerta` int(11) NOT NULL DEFAULT 3,
  `dias_lembrete` int(11) NOT NULL DEFAULT 2,
  `enviar_email` tinyint(1) NOT NULL DEFAULT 1,
  `enviar_sistema` tinyint(1) NOT NULL DEFAULT 1,
  `modulos_ativos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`modulos_ativos`)),
  `workflow_status` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`workflow_status`)),
  `campos_personalizados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`campos_personalizados`)),
  `notificacoes_granulares` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notificacoes_granulares`)),
  `tema` varchar(30) NOT NULL DEFAULT 'system',
  `cor_tema` varchar(30) NOT NULL DEFAULT 'blue',
  `tamanho_fonte` varchar(30) NOT NULL DEFAULT 'normal',
  `layout_sidebar` varchar(30) NOT NULL DEFAULT 'expanded',
  `automacoes_fluxo` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`automacoes_fluxo`)),
  `permissoes_acesso` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissoes_acesso`)),
  `integracoes_terceiros` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`integracoes_terceiros`)),
  `horas_semanais` int(11) NOT NULL DEFAULT 44,
  `feriados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`feriados`)),
  `limite_capacidade` int(11) NOT NULL DEFAULT 100,
  `templates_estrutura` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`templates_estrutura`)),
  `visualizacao_padrao` varchar(30) NOT NULL DEFAULT 'lista',
  `onboarding_progresso` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`onboarding_progresso`)),
  `onboarding_recursos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`onboarding_recursos`)),
  `onboarding_preferencias` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`onboarding_preferencias`)),
  `onboarding_finalizado_em` datetime DEFAULT NULL,
  `exigir_2fa` tinyint(1) NOT NULL DEFAULT 0,
  `sso_provider` varchar(120) DEFAULT NULL,
  `registrar_login` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `white_label` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `configuracoes`
--

INSERT INTO `configuracoes` (`id`, `empresa_id`, `dias_alerta`, `dias_lembrete`, `enviar_email`, `enviar_sistema`, `modulos_ativos`, `workflow_status`, `campos_personalizados`, `notificacoes_granulares`, `tema`, `cor_tema`, `tamanho_fonte`, `layout_sidebar`, `automacoes_fluxo`, `permissoes_acesso`, `integracoes_terceiros`, `horas_semanais`, `feriados`, `limite_capacidade`, `templates_estrutura`, `visualizacao_padrao`, `onboarding_progresso`, `onboarding_recursos`, `onboarding_preferencias`, `onboarding_finalizado_em`, `exigir_2fa`, `sso_provider`, `registrar_login`, `created_at`, `updated_at`, `white_label`) VALUES
(1, 1, 3, 2, 1, 1, '[\"controle_tempo\", \"metas\", \"prioridades\", \"mapas\", \"documentos\", \"financeiro\"]', '[\"A fazer\", \"Em andamento\", \"Em revisão\", \"Concluído\"]', '[\"Centro de custo: texto\", \"Valor previsto: moeda\", \"Prioridade executiva: lista\"]', '[\"mencoes_email\", \"mencoes_sistema\", \"vencimentos_email\", \"comentarios_sistema\"]', 'system', 'blue', 'normal', 'expanded', '[\"notificar_status_pronto\", \"marcar_risco_sla\", \"arquivar_concluidos\"]', '{\"listas\": \"admin_gestor\", \"pastas\": \"admin_gestor\", \"espacos\": \"admin\"}', '[]', 44, '[]', 100, '[\"Onboarding de cliente\", \"Rotina mensal de documentos\", \"Controle de vencimentos\"]', 'lista', '{}', '{}', '{\"modelo_aplicado\": null, \"responsavel_implantacao\": null, \"prazo_implantacao\": null, \"observacoes\": null}', NULL, 0, NULL, 1, '2026-04-23 17:21:35', '2026-05-25 16:09:13', '{\"ativo\":false,\"nome_sistema\":\"Gest\\u00e3o de Controle\",\"workspace_padrao\":\"Gest\\u00e3o de Controle\",\"cor_primaria\":\"#2e0af5\",\"cor_secundaria\":\"#ff0000\",\"cor_destaque\":\"#22c55e\",\"cor_sidebar\":\"#0f172a\",\"cor_botoes\":\"#2e0af5\",\"logo_light\":null,\"logo_dark\":null,\"favicon\":null,\"logo_login\":null,\"logo_email\":null,\"login_background\":\"#0f172a\",\"login_imagem_lateral\":null,\"login_personalizado\":true,\"login_titulo\":\"Gest\\u00e3o de Controle\",\"login_subtitulo\":\"Acesse sua conta\",\"emails_personalizados\":true,\"email_nome\":\"Gest\\u00e3o de Controle\",\"email_endereco\":\"sistema@gestao.com.br\",\"email_nome_remetente\":\"Gest\\u00e3o de Controle\",\"email_remetente\":\"sistema@gestao.com.br\",\"email_cor_template\":\"#2e0af5\",\"email_assinatura\":null,\"assistant_name\":\"Assistente Prazzu\",\"nome_assistente\":\"Assistente Prazzu\",\"documentos_personalizados\":true,\"documentos_marca_propria\":true,\"remover_referencias_internas\":false,\"ocultar_referencias_internas\":false,\"ocultar_nome_sistema\":false,\"ocultar_rodape\":false,\"ocultar_branding_documentos\":false,\"multiplos_workspaces\":false,\"multi_workspace\":false,\"workspaces\":[],\"dominio_personalizado\":null,\"url_convite\":null,\"sso_habilitado\":false,\"sso_ativo\":false,\"sso_provider\":null,\"sso_client_id\":null,\"sso_tenant_id\":null,\"sso_redirect_url\":null}'),
(2, 2, 3, 2, 1, 1, '[\"controle_tempo\", \"metas\", \"prioridades\", \"mapas\", \"documentos\", \"financeiro\"]', '[\"A fazer\", \"Em andamento\", \"Em revisão\", \"Concluído\"]', '[\"Centro de custo: texto\", \"Valor previsto: moeda\", \"Prioridade executiva: lista\"]', '[\"mencoes_email\", \"mencoes_sistema\", \"vencimentos_email\", \"comentarios_sistema\"]', 'system', 'blue', 'normal', 'expanded', '[\"notificar_status_pronto\", \"marcar_risco_sla\", \"arquivar_concluidos\"]', '{\"listas\": \"admin_gestor\", \"pastas\": \"admin_gestor\", \"espacos\": \"admin\"}', '[]', 44, '[]', 100, '[\"Onboarding de cliente\", \"Rotina mensal de documentos\", \"Controle de vencimentos\"]', 'lista', '{}', '{}', '{\"modelo_aplicado\": null, \"responsavel_implantacao\": null, \"prazo_implantacao\": null, \"observacoes\": null}', NULL, 0, NULL, 1, '2026-04-23 17:21:35', '2026-04-23 17:21:35', '{\r\n    \"ativo\": false,\r\n    \"workspace_padrao\": \"Prazzu\",\r\n    \"dominio_personalizado\": null,\r\n    \"url_convite\": null,\r\n    \"logo_light\": null,\r\n    \"logo_dark\": null,\r\n    \"favicon\": null,\r\n    \"logo_login\": null,\r\n    \"logo_email\": null,\r\n    \"cor_primaria\": \"#f59e0b\",\r\n    \"cor_secundaria\": \"#111827\",\r\n    \"cor_destaque\": \"#22c55e\",\r\n    \"cor_sidebar\": \"#0f172a\",\r\n    \"cor_botoes\": \"#f59e0b\",\r\n    \"ocultar_nome_sistema\": false,\r\n    \"ocultar_rodape\": false,\r\n    \"ocultar_referencias_internas\": false,\r\n    \"ocultar_branding_documentos\": false,\r\n    \"login_titulo\": \"Acesse sua área de trabalho\",\r\n    \"login_subtitulo\": \"Organize processos, documentos, tarefas e prazos em uma única plataforma.\",\r\n    \"login_background\": \"#0f172a\",\r\n    \"login_imagem_lateral\": null,\r\n    \"email_nome_remetente\": \"Prazzu\",\r\n    \"email_remetente\": null,\r\n    \"email_assinatura\": \"Equipe Prazzu\",\r\n    \"email_cor_template\": \"#f59e0b\",\r\n    \"sso_ativo\": false,\r\n    \"sso_provider\": null,\r\n    \"sso_client_id\": null,\r\n    \"sso_tenant_id\": null,\r\n    \"sso_redirect_url\": null,\r\n    \"documentos_marca_propria\": true,\r\n    \"multi_workspace\": false,\r\n    \"workspaces\": []\r\n}'),
(3, 3, 3, 2, 1, 1, '[\"controle_tempo\", \"metas\", \"prioridades\", \"mapas\", \"documentos\", \"financeiro\"]', '[\"A fazer\", \"Em andamento\", \"Em revisão\", \"Concluído\"]', '[\"Centro de custo: texto\", \"Valor previsto: moeda\", \"Prioridade executiva: lista\"]', '[\"mencoes_email\", \"mencoes_sistema\", \"vencimentos_email\", \"comentarios_sistema\"]', 'system', 'blue', 'normal', 'expanded', '[\"notificar_status_pronto\", \"marcar_risco_sla\", \"arquivar_concluidos\"]', '{\"listas\": \"admin_gestor\", \"pastas\": \"admin_gestor\", \"espacos\": \"admin\"}', '[]', 44, '[]', 100, '[\"Onboarding de cliente\", \"Rotina mensal de documentos\", \"Controle de vencimentos\"]', 'lista', '{}', '{}', '{\"modelo_aplicado\": null, \"responsavel_implantacao\": null, \"prazo_implantacao\": null, \"observacoes\": null}', NULL, 0, NULL, 1, '2026-04-23 17:21:35', '2026-04-23 17:21:35', '{\r\n    \"ativo\": false,\r\n    \"workspace_padrao\": \"Prazzu\",\r\n    \"dominio_personalizado\": null,\r\n    \"url_convite\": null,\r\n    \"logo_light\": null,\r\n    \"logo_dark\": null,\r\n    \"favicon\": null,\r\n    \"logo_login\": null,\r\n    \"logo_email\": null,\r\n    \"cor_primaria\": \"#f59e0b\",\r\n    \"cor_secundaria\": \"#111827\",\r\n    \"cor_destaque\": \"#22c55e\",\r\n    \"cor_sidebar\": \"#0f172a\",\r\n    \"cor_botoes\": \"#f59e0b\",\r\n    \"ocultar_nome_sistema\": false,\r\n    \"ocultar_rodape\": false,\r\n    \"ocultar_referencias_internas\": false,\r\n    \"ocultar_branding_documentos\": false,\r\n    \"login_titulo\": \"Acesse sua área de trabalho\",\r\n    \"login_subtitulo\": \"Organize processos, documentos, tarefas e prazos em uma única plataforma.\",\r\n    \"login_background\": \"#0f172a\",\r\n    \"login_imagem_lateral\": null,\r\n    \"email_nome_remetente\": \"Prazzu\",\r\n    \"email_remetente\": null,\r\n    \"email_assinatura\": \"Equipe Prazzu\",\r\n    \"email_cor_template\": \"#f59e0b\",\r\n    \"sso_ativo\": false,\r\n    \"sso_provider\": null,\r\n    \"sso_client_id\": null,\r\n    \"sso_tenant_id\": null,\r\n    \"sso_redirect_url\": null,\r\n    \"documentos_marca_propria\": true,\r\n    \"multi_workspace\": false,\r\n    \"workspaces\": []\r\n}'),
(5, 13, 3, 2, 1, 1, '[\"controle_tempo\", \"metas\", \"prioridades\", \"mapas\", \"documentos\", \"financeiro\"]', '[\"A fazer\", \"Em andamento\", \"Em revisão\", \"Concluído\"]', '[\"Centro de custo: texto\", \"Valor previsto: moeda\", \"Prioridade executiva: lista\"]', '[\"mencoes_email\", \"mencoes_sistema\", \"vencimentos_email\", \"comentarios_sistema\"]', 'system', 'blue', 'normal', 'expanded', '[\"notificar_status_pronto\", \"marcar_risco_sla\", \"arquivar_concluidos\"]', '{\"listas\": \"admin_gestor\", \"pastas\": \"admin_gestor\", \"espacos\": \"admin\"}', '[]', 44, '[]', 100, '[\"Onboarding de cliente\", \"Rotina mensal de documentos\", \"Controle de vencimentos\"]', 'lista', '{}', '{}', '{\"modelo_aplicado\": null, \"responsavel_implantacao\": null, \"prazo_implantacao\": null, \"observacoes\": null}', NULL, 0, NULL, 1, '2026-05-01 19:09:37', '2026-05-01 19:09:37', '{\r\n    \"ativo\": false,\r\n    \"workspace_padrao\": \"Prazzu\",\r\n    \"dominio_personalizado\": null,\r\n    \"url_convite\": null,\r\n    \"logo_light\": null,\r\n    \"logo_dark\": null,\r\n    \"favicon\": null,\r\n    \"logo_login\": null,\r\n    \"logo_email\": null,\r\n    \"cor_primaria\": \"#f59e0b\",\r\n    \"cor_secundaria\": \"#111827\",\r\n    \"cor_destaque\": \"#22c55e\",\r\n    \"cor_sidebar\": \"#0f172a\",\r\n    \"cor_botoes\": \"#f59e0b\",\r\n    \"ocultar_nome_sistema\": false,\r\n    \"ocultar_rodape\": false,\r\n    \"ocultar_referencias_internas\": false,\r\n    \"ocultar_branding_documentos\": false,\r\n    \"login_titulo\": \"Acesse sua área de trabalho\",\r\n    \"login_subtitulo\": \"Organize processos, documentos, tarefas e prazos em uma única plataforma.\",\r\n    \"login_background\": \"#0f172a\",\r\n    \"login_imagem_lateral\": null,\r\n    \"email_nome_remetente\": \"Prazzu\",\r\n    \"email_remetente\": null,\r\n    \"email_assinatura\": \"Equipe Prazzu\",\r\n    \"email_cor_template\": \"#f59e0b\",\r\n    \"sso_ativo\": false,\r\n    \"sso_provider\": null,\r\n    \"sso_client_id\": null,\r\n    \"sso_tenant_id\": null,\r\n    \"sso_redirect_url\": null,\r\n    \"documentos_marca_propria\": true,\r\n    \"multi_workspace\": false,\r\n    \"workspaces\": []\r\n}'),
(10, 18, 3, 2, 1, 1, '[\"controle_tempo\", \"metas\", \"prioridades\", \"mapas\", \"documentos\", \"financeiro\"]', '[\"A fazer\", \"Em andamento\", \"Em revisão\", \"Concluído\"]', '[\"Centro de custo: texto\", \"Valor previsto: moeda\", \"Prioridade executiva: lista\"]', '[\"mencoes_email\", \"mencoes_sistema\", \"vencimentos_email\", \"comentarios_sistema\"]', 'system', 'blue', 'normal', 'expanded', '[\"notificar_status_pronto\", \"marcar_risco_sla\", \"arquivar_concluidos\"]', '{\"listas\": \"admin_gestor\", \"pastas\": \"admin_gestor\", \"espacos\": \"admin\"}', '[]', 44, '[]', 100, '[\"Onboarding de cliente\", \"Rotina mensal de documentos\", \"Controle de vencimentos\"]', 'lista', '{}', '{}', '{\"modelo_aplicado\": null, \"responsavel_implantacao\": null, \"prazo_implantacao\": null, \"observacoes\": null}', NULL, 0, NULL, 1, '2026-05-01 20:35:39', '2026-05-01 20:35:39', '{\r\n    \"ativo\": false,\r\n    \"workspace_padrao\": \"Prazzu\",\r\n    \"dominio_personalizado\": null,\r\n    \"url_convite\": null,\r\n    \"logo_light\": null,\r\n    \"logo_dark\": null,\r\n    \"favicon\": null,\r\n    \"logo_login\": null,\r\n    \"logo_email\": null,\r\n    \"cor_primaria\": \"#f59e0b\",\r\n    \"cor_secundaria\": \"#111827\",\r\n    \"cor_destaque\": \"#22c55e\",\r\n    \"cor_sidebar\": \"#0f172a\",\r\n    \"cor_botoes\": \"#f59e0b\",\r\n    \"ocultar_nome_sistema\": false,\r\n    \"ocultar_rodape\": false,\r\n    \"ocultar_referencias_internas\": false,\r\n    \"ocultar_branding_documentos\": false,\r\n    \"login_titulo\": \"Acesse sua área de trabalho\",\r\n    \"login_subtitulo\": \"Organize processos, documentos, tarefas e prazos em uma única plataforma.\",\r\n    \"login_background\": \"#0f172a\",\r\n    \"login_imagem_lateral\": null,\r\n    \"email_nome_remetente\": \"Prazzu\",\r\n    \"email_remetente\": null,\r\n    \"email_assinatura\": \"Equipe Prazzu\",\r\n    \"email_cor_template\": \"#f59e0b\",\r\n    \"sso_ativo\": false,\r\n    \"sso_provider\": null,\r\n    \"sso_client_id\": null,\r\n    \"sso_tenant_id\": null,\r\n    \"sso_redirect_url\": null,\r\n    \"documentos_marca_propria\": true,\r\n    \"multi_workspace\": false,\r\n    \"workspaces\": []\r\n}'),
(13, 21, 3, 2, 1, 1, '[\"controle_tempo\", \"metas\", \"prioridades\", \"mapas\", \"documentos\", \"financeiro\"]', '[\"A fazer\", \"Em andamento\", \"Em revisão\", \"Concluído\"]', '[\"Centro de custo: texto\", \"Valor previsto: moeda\", \"Prioridade executiva: lista\"]', '[\"mencoes_email\", \"mencoes_sistema\", \"vencimentos_email\", \"comentarios_sistema\"]', 'system', 'blue', 'normal', 'expanded', '[\"notificar_status_pronto\", \"marcar_risco_sla\", \"arquivar_concluidos\"]', '{\"listas\": \"admin_gestor\", \"pastas\": \"admin_gestor\", \"espacos\": \"admin\"}', '[]', 44, '[]', 100, '[\"Onboarding de cliente\", \"Rotina mensal de documentos\", \"Controle de vencimentos\"]', 'lista', '{}', '{}', '{\"modelo_aplicado\": null, \"responsavel_implantacao\": null, \"prazo_implantacao\": null, \"observacoes\": null}', NULL, 0, NULL, 1, '2026-05-01 20:53:08', '2026-05-01 20:53:08', '{\r\n    \"ativo\": false,\r\n    \"workspace_padrao\": \"Prazzu\",\r\n    \"dominio_personalizado\": null,\r\n    \"url_convite\": null,\r\n    \"logo_light\": null,\r\n    \"logo_dark\": null,\r\n    \"favicon\": null,\r\n    \"logo_login\": null,\r\n    \"logo_email\": null,\r\n    \"cor_primaria\": \"#f59e0b\",\r\n    \"cor_secundaria\": \"#111827\",\r\n    \"cor_destaque\": \"#22c55e\",\r\n    \"cor_sidebar\": \"#0f172a\",\r\n    \"cor_botoes\": \"#f59e0b\",\r\n    \"ocultar_nome_sistema\": false,\r\n    \"ocultar_rodape\": false,\r\n    \"ocultar_referencias_internas\": false,\r\n    \"ocultar_branding_documentos\": false,\r\n    \"login_titulo\": \"Acesse sua área de trabalho\",\r\n    \"login_subtitulo\": \"Organize processos, documentos, tarefas e prazos em uma única plataforma.\",\r\n    \"login_background\": \"#0f172a\",\r\n    \"login_imagem_lateral\": null,\r\n    \"email_nome_remetente\": \"Prazzu\",\r\n    \"email_remetente\": null,\r\n    \"email_assinatura\": \"Equipe Prazzu\",\r\n    \"email_cor_template\": \"#f59e0b\",\r\n    \"sso_ativo\": false,\r\n    \"sso_provider\": null,\r\n    \"sso_client_id\": null,\r\n    \"sso_tenant_id\": null,\r\n    \"sso_redirect_url\": null,\r\n    \"documentos_marca_propria\": true,\r\n    \"multi_workspace\": false,\r\n    \"workspaces\": []\r\n}'),
(14, 22, 3, 2, 1, 1, '[\"controle_tempo\", \"metas\", \"prioridades\", \"mapas\", \"documentos\", \"financeiro\"]', '[\"A fazer\", \"Em andamento\", \"Em revisão\", \"Concluído\"]', '[\"Centro de custo: texto\", \"Valor previsto: moeda\", \"Prioridade executiva: lista\"]', '[\"mencoes_email\", \"mencoes_sistema\", \"vencimentos_email\", \"comentarios_sistema\"]', 'system', 'blue', 'normal', 'expanded', '[\"notificar_status_pronto\", \"marcar_risco_sla\", \"arquivar_concluidos\"]', '{\"listas\": \"admin_gestor\", \"pastas\": \"admin_gestor\", \"espacos\": \"admin\"}', '[]', 44, '[]', 100, '[\"Onboarding de cliente\", \"Rotina mensal de documentos\", \"Controle de vencimentos\"]', 'lista', '{}', '{}', '{\"modelo_aplicado\": null, \"responsavel_implantacao\": null, \"prazo_implantacao\": null, \"observacoes\": null}', NULL, 0, NULL, 1, '2026-05-01 20:59:09', '2026-05-01 20:59:09', '{\r\n    \"ativo\": false,\r\n    \"workspace_padrao\": \"Prazzu\",\r\n    \"dominio_personalizado\": null,\r\n    \"url_convite\": null,\r\n    \"logo_light\": null,\r\n    \"logo_dark\": null,\r\n    \"favicon\": null,\r\n    \"logo_login\": null,\r\n    \"logo_email\": null,\r\n    \"cor_primaria\": \"#f59e0b\",\r\n    \"cor_secundaria\": \"#111827\",\r\n    \"cor_destaque\": \"#22c55e\",\r\n    \"cor_sidebar\": \"#0f172a\",\r\n    \"cor_botoes\": \"#f59e0b\",\r\n    \"ocultar_nome_sistema\": false,\r\n    \"ocultar_rodape\": false,\r\n    \"ocultar_referencias_internas\": false,\r\n    \"ocultar_branding_documentos\": false,\r\n    \"login_titulo\": \"Acesse sua área de trabalho\",\r\n    \"login_subtitulo\": \"Organize processos, documentos, tarefas e prazos em uma única plataforma.\",\r\n    \"login_background\": \"#0f172a\",\r\n    \"login_imagem_lateral\": null,\r\n    \"email_nome_remetente\": \"Prazzu\",\r\n    \"email_remetente\": null,\r\n    \"email_assinatura\": \"Equipe Prazzu\",\r\n    \"email_cor_template\": \"#f59e0b\",\r\n    \"sso_ativo\": false,\r\n    \"sso_provider\": null,\r\n    \"sso_client_id\": null,\r\n    \"sso_tenant_id\": null,\r\n    \"sso_redirect_url\": null,\r\n    \"documentos_marca_propria\": true,\r\n    \"multi_workspace\": false,\r\n    \"workspaces\": []\r\n}'),
(15, 6, 3, 2, 1, 1, '[\"controle_tempo\",\"metas\",\"prioridades\",\"mapas\",\"documentos\",\"financeiro\"]', '[\"A fazer\",\"Em andamento\",\"Em revis\\u00e3o\",\"Conclu\\u00eddo\"]', '[\"Centro de custo: texto\",\"Valor previsto: moeda\",\"Prioridade executiva: lista\"]', '[\"mencoes_email\",\"mencoes_sistema\",\"vencimentos_email\",\"comentarios_sistema\"]', 'system', 'blue', 'normal', 'expanded', '[\"notificar_status_pronto\",\"marcar_risco_sla\",\"arquivar_concluidos\"]', '{\"listas\":\"admin_gestor\",\"pastas\":\"admin_gestor\",\"espacos\":\"admin\"}', '[]', 44, '[]', 100, '[\"Onboarding de cliente\",\"Rotina mensal de documentos\",\"Controle de vencimentos\"]', 'lista', '{}', '{\"multiplas_visualizacoes\":false}', '{\"modelo_aplicado\": null, \"responsavel_implantacao\": null, \"prazo_implantacao\": null, \"observacoes\": null}', NULL, 0, NULL, 1, '2026-05-05 17:55:11', '2026-05-14 15:26:14', '{\"ativo\":true,\"workspace_padrao\":\"casinha para cachorro\",\"dominio_personalizado\":\"portal.casinhacachorro.com\",\"url_convite\":\"http:\\/\\/gitlab.unisa.br\\/unisadev\\/unisadev\",\"logo_light\":\"white-label\\/logos\\/01KRK6PTY6V53A2AW1GH7X076P.png\",\"logo_dark\":null,\"favicon\":null,\"logo_login\":null,\"logo_email\":null,\"cor_primaria\":\"#f50ae8\",\"cor_secundaria\":\"#ff0000\",\"cor_destaque\":\"#22c55e\",\"cor_sidebar\":\"#004cff\",\"cor_botoes\":\"#07fc30\",\"ocultar_nome_sistema\":false,\"ocultar_rodape\":false,\"ocultar_referencias_internas\":false,\"ocultar_branding_documentos\":false,\"login_titulo\":\"Acesse sua \\u00e1rea de trabalho\",\"login_subtitulo\":\"Organize processos, documentos, tarefas e prazos em uma \\u00fanica plataforma.\",\"login_background\":\"#0f172a\",\"login_imagem_lateral\":null,\"email_nome_remetente\":\"Prazzu\",\"email_remetente\":null,\"email_assinatura\":\"Equipe Prazzu\",\"email_cor_template\":\"#f59e0b\",\"sso_ativo\":false,\"sso_provider\":null,\"sso_client_id\":null,\"sso_tenant_id\":null,\"sso_redirect_url\":null,\"documentos_marca_propria\":true,\"multi_workspace\":false,\"workspaces\":[]}'),
(16, 4, 3, 2, 1, 1, '[\"controle_tempo\",\"metas\",\"prioridades\",\"mapas\",\"documentos\",\"financeiro\",\"portal_cliente\",\"auditoria\",\"relatorios\"]', '[\"A fazer\",\"Em andamento\",\"Em revis\\u00e3o\",\"Conclu\\u00eddo\"]', '[\"Centro de custo: texto\",\"Valor previsto: moeda\",\"Prioridade executiva: lista\"]', '[\"mencoes_email\",\"mencoes_sistema\",\"vencimentos_email\",\"comentarios_sistema\"]', 'system', 'blue', 'normal', 'expanded', '[\"notificar_status_pronto\",\"marcar_risco_sla\",\"arquivar_concluidos\"]', '{\"listas\":\"admin_gestor\",\"pastas\":\"admin_gestor\",\"espacos\":\"admin\"}', '[]', 44, '[]', 100, '[\"Onboarding de cliente\",\"Rotina mensal de documentos\",\"Controle de vencimentos\"]', 'lista', '[]', '[]', '[]', NULL, 0, NULL, 1, '2026-05-12 20:57:54', '2026-05-14 20:36:46', '{\"ativo\":false,\"workspace_padrao\":\"Prazzu\",\"dominio_personalizado\":null,\"url_convite\":null,\"logo_light\":null,\"logo_dark\":null,\"favicon\":null,\"logo_login\":null,\"logo_email\":null,\"cor_primaria\":\"#f59e0b\",\"cor_secundaria\":\"#111827\",\"cor_destaque\":\"#22c55e\",\"cor_sidebar\":\"#0f172a\",\"cor_botoes\":\"#f59e0b\",\"ocultar_nome_sistema\":false,\"ocultar_rodape\":false,\"ocultar_referencias_internas\":false,\"ocultar_branding_documentos\":false,\"login_titulo\":\"Acesse sua \\u00e1rea de trabalho\",\"login_subtitulo\":\"Organize processos, documentos, tarefas e prazos em uma \\u00fanica plataforma.\",\"login_background\":\"#0f172a\",\"login_imagem_lateral\":null,\"email_nome_remetente\":\"Prazzu\",\"email_remetente\":null,\"email_assinatura\":\"Equipe Prazzu\",\"email_cor_template\":\"#f59e0b\",\"sso_ativo\":false,\"sso_provider\":null,\"sso_client_id\":null,\"sso_tenant_id\":null,\"sso_redirect_url\":null,\"documentos_marca_propria\":true,\"multi_workspace\":false,\"workspaces\":[]}');

-- --------------------------------------------------------

--
-- Estrutura da tabela `crm_clientes`
--

CREATE TABLE `crm_clientes` (
  `id` bigint(20) NOT NULL,
  `empresa_id` bigint(20) NOT NULL,
  `situacao` varchar(50) DEFAULT NULL,
  `proxima_acao` varchar(255) DEFAULT NULL,
  `ultimo_contato_em` datetime DEFAULT NULL,
  `proximo_followup_em` datetime DEFAULT NULL,
  `risco_churn` varchar(20) DEFAULT NULL,
  `responsavel_user_id` bigint(20) DEFAULT NULL,
  `valor_contrato` decimal(10,2) DEFAULT NULL,
  `valor_mensal` decimal(10,2) DEFAULT NULL,
  `proxima_entrega_em` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `crm_clientes`
--

INSERT INTO `crm_clientes` (`id`, `empresa_id`, `situacao`, `proxima_acao`, `ultimo_contato_em`, `proximo_followup_em`, `risco_churn`, `responsavel_user_id`, `valor_contrato`, `valor_mensal`, `proxima_entrega_em`, `created_at`, `updated_at`) VALUES
(1, 21, 'operando_bem', 'Enviar proposta de renovação', '2026-05-05 16:20:42', '2026-05-13 16:20:42', 'baixo', 404, 8500.00, 1200.00, '2026-05-15 16:20:42', '2026-05-08 19:20:42', '2026-05-08 19:20:42'),
(2, 21, 'aguardando_cliente', 'Cobrar aprovação do layout', '2026-05-01 16:20:42', '2026-05-10 16:20:42', 'medio', 404, 4200.00, 900.00, '2026-05-12 16:20:42', '2026-05-08 19:20:42', '2026-05-08 19:20:42'),
(3, 21, 'em_risco', 'Agendar reunião urgente', '2026-04-23 16:20:42', '2026-05-09 16:20:42', 'alto', 404, 15000.00, 2500.00, '2026-05-18 16:20:42', '2026-05-08 19:20:42', '2026-05-08 19:20:42'),
(7, 21, 'operando_bem', '[TESTE CRM] Enviar proposta de renovação', '2026-05-05 16:31:07', '2026-05-13 16:31:07', 'baixo', 404, 8500.00, 1200.00, '2026-05-15 16:31:07', '2026-05-08 19:31:07', '2026-05-08 19:31:07'),
(8, 21, 'aguardando_cliente', '[TESTE CRM] Cobrar aprovação do layout', '2026-04-30 16:31:07', '2026-05-09 16:31:07', 'medio', 404, 4200.00, 900.00, '2026-05-12 16:31:07', '2026-05-08 19:31:07', '2026-05-08 19:31:07'),
(9, 21, 'em_risco', '[TESTE CRM] Agendar reunião urgente de alinhamento', '2026-04-20 16:31:07', '2026-05-08 16:31:07', 'alto', 404, 15000.00, 2500.00, '2026-05-18 16:31:07', '2026-05-08 19:31:07', '2026-05-08 19:31:07'),
(10, 22, 'operando_bem', NULL, NULL, NULL, 'baixo', 111, NULL, NULL, NULL, '2026-05-11 14:27:59', '2026-05-11 14:27:59'),
(11, 4, 'em_implementacao', NULL, NULL, NULL, 'baixo', 111, NULL, NULL, NULL, '2026-05-11 14:28:49', '2026-05-11 14:45:00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `crm_historicos`
--

CREATE TABLE `crm_historicos` (
  `id` bigint(20) NOT NULL,
  `crm_cliente_id` bigint(20) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `crm_historicos`
--

INSERT INTO `crm_historicos` (`id`, `crm_cliente_id`, `tipo`, `descricao`, `created_at`) VALUES
(1, 1, 'reuniao', 'Reunião de alinhamento realizada', '2026-05-03 19:20:42'),
(2, 1, 'ligacao', 'Ligação para validar entregas', '2026-05-06 19:20:42'),
(9, 7, 'reuniao', 'Reunião de alinhamento realizada. Cliente demonstrou interesse em renovar.', '2026-05-03 19:31:07'),
(10, 7, 'decisao', 'Ficou decidido enviar nova proposta até o próximo follow-up.', '2026-05-04 19:31:07'),
(11, 8, 'contato', 'Mensagem enviada cobrando aprovação do layout.', '2026-05-06 19:31:07'),
(12, 8, 'pendencia', 'Cliente informou que ainda está validando internamente.', '2026-05-07 19:31:07'),
(13, 9, 'problema', 'Cliente relatou atraso e falta de retorno em entregas anteriores.', '2026-05-02 19:31:07'),
(14, 9, 'acao', 'Criado plano de ação para recuperação do relacionamento.', '2026-05-08 19:31:07'),
(15, 10, 'status', 'Status do contrato alterado para: Ativo', '2026-05-11 14:27:59'),
(16, 10, 'status', 'Status do contrato alterado para: Implementação', '2026-05-11 14:28:01'),
(17, 11, 'onboarding', 'Onboarding criado pela aba Clientes.', '2026-05-11 14:28:49'),
(18, 11, 'onboarding', 'Onboarding criado pela aba Clientes.', '2026-05-11 14:30:10'),
(19, 11, 'status', 'Status alterado para Renovação.', '2026-05-11 14:44:56'),
(20, 11, 'status', 'Status alterado para Em implementação.', '2026-05-11 14:45:00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `crm_pendencias`
--

CREATE TABLE `crm_pendencias` (
  `id` bigint(20) NOT NULL,
  `crm_cliente_id` bigint(20) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `crm_pendencias`
--

INSERT INTO `crm_pendencias` (`id`, `crm_cliente_id`, `titulo`, `status`, `created_at`) VALUES
(1, 1, 'Enviar documentos do contrato', 'pendente', '2026-05-08 19:20:42'),
(2, 1, 'Aprovar identidade visual', 'pendente', '2026-05-08 19:20:42'),
(9, 7, 'Enviar minuta de renovação contratual', 'pendente', '2026-05-08 19:31:07'),
(10, 7, 'Confirmar novo prazo de entrega com o cliente', 'pendente', '2026-05-08 19:31:07'),
(11, 8, 'Cliente precisa aprovar o layout enviado', 'pendente', '2026-05-08 19:31:07'),
(12, 8, 'Solicitar logotipo em alta resolução', 'pendente', '2026-05-08 19:31:07'),
(13, 9, 'Marcar reunião para entender insatisfação', 'pendente', '2026-05-08 19:31:07'),
(14, 9, 'Registrar plano de recuperação do cliente', 'pendente', '2026-05-08 19:31:07');

-- --------------------------------------------------------

--
-- Estrutura da tabela `dashboard_widget_configuracoes`
--

CREATE TABLE `dashboard_widget_configuracoes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `tipo` varchar(50) NOT NULL DEFAULT 'card',
  `fonte` varchar(100) NOT NULL DEFAULT 'itens_abertos',
  `largura` varchar(20) NOT NULL DEFAULT '1/3',
  `ordem` int(11) NOT NULL DEFAULT 1,
  `configuracao` longtext DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `dashboard_widget_configuracoes`
--

INSERT INTO `dashboard_widget_configuracoes` (`id`, `empresa_id`, `user_id`, `titulo`, `tipo`, `fonte`, `largura`, `ordem`, `configuracao`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 4, 111, 'teste', 'grafico', 'itens_abertos', '1/2', 1, NULL, 1, '2026-04-29 23:08:02', '2026-04-29 23:08:02'),
(2, 4, 111, 'Contrato RH Interno', 'tabela', 'itens_abertos', '1/1', 1, NULL, 1, '2026-04-30 14:44:49', '2026-04-30 23:01:41'),
(3, 4, 111, 'Contrato RH Interno', 'card', 'vencendo_hoje', '1/1', 1, NULL, 1, '2026-04-30 14:55:01', '2026-04-30 22:59:47'),
(4, 4, 371, 'Contrato RH Interno', 'grafico', 'itens_abertos', '1/2', 1, NULL, 1, '2026-04-30 15:02:39', '2026-04-30 23:01:19');

-- --------------------------------------------------------

--
-- Estrutura da tabela `document_versions`
--

CREATE TABLE `document_versions` (
  `id` bigint(20) NOT NULL,
  `document_id` bigint(20) NOT NULL,
  `version_number` int(11) DEFAULT 1,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_by` bigint(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `empresas`
--

CREATE TABLE `empresas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `razao_social` varchar(255) NOT NULL,
  `nome_fantasia` varchar(255) DEFAULT NULL,
  `cnpj` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefone` varchar(255) DEFAULT NULL,
  `responsavel_nome` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'ativo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `plano` varchar(50) NOT NULL DEFAULT 'starter',
  `limite_usuarios` int(11) DEFAULT 3,
  `limite_itens` int(11) DEFAULT 200,
  `limite_interacoes_ia` int(11) NOT NULL DEFAULT 150,
  `ativo` tinyint(1) DEFAULT 1,
  `crm_status_contrato` varchar(60) DEFAULT NULL,
  `crm_contato_nome` varchar(255) DEFAULT NULL,
  `crm_contato_email` varchar(255) DEFAULT NULL,
  `crm_contato_whatsapp` varchar(50) DEFAULT NULL,
  `crm_health_manual` varchar(60) DEFAULT NULL,
  `crm_observacoes` text DEFAULT NULL,
  `crm_ultima_reuniao_em` datetime DEFAULT NULL,
  `portal_token` varchar(255) DEFAULT NULL,
  `portal_ativo` tinyint(1) NOT NULL DEFAULT 1,
  `portal_expira_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `empresas`
--

INSERT INTO `empresas` (`id`, `razao_social`, `nome_fantasia`, `cnpj`, `email`, `telefone`, `responsavel_nome`, `status`, `created_at`, `updated_at`, `plano`, `limite_usuarios`, `limite_itens`, `limite_interacoes_ia`, `ativo`, `crm_status_contrato`, `crm_contato_nome`, `crm_contato_email`, `crm_contato_whatsapp`, `crm_health_manual`, `crm_observacoes`, `crm_ultima_reuniao_em`, `portal_token`, `portal_ativo`, `portal_expira_em`) VALUES
(4, 'Empresa Alpha LTDA', 'Alpha', '11111111000101', 'alpha@email.com', '11999990001', 'Admin Alpha', 'ativo', '2026-04-24 17:57:10', '2026-05-11 14:45:00', 'enterprise', 50, 10000, 5000, 1, 'em_implementacao', 'Admin Alpha', 'alpha@email.com', '11999990001', NULL, NULL, NULL, '539166a84b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(5, 'Empresa Beta LTDA', 'Beta', '22222222000102', 'beta@email.com', '11999990002', 'Admin Beta', 'ativo', '2026-04-24 17:57:10', '2026-05-08 19:01:59', 'starter', 3, 200, 150, 1, 'Ativo', 'Admin Beta', 'beta@email.com', '11999990002', NULL, NULL, NULL, '539170b44b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(6, 'Empresa Gamma LTDA', 'Gamma', '33333333000103', 'gamma@email.com', '11999990003', 'Admin Gamma', 'ativo', '2026-04-24 17:57:10', '2026-05-08 19:01:59', 'starter', 3, 200, 150, 1, 'Ativo', 'Admin Gamma', 'gamma@email.com', '11999990003', NULL, NULL, NULL, '539171ef4b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(7, 'Empresa Teste Completa LTDA', 'Teste Completo', '99999999000199', 'testecompleto@prazzu.com', '11999999999', 'Administrador Teste', 'ativo', '2026-04-28 14:58:04', '2026-05-08 19:01:59', 'enterprise', 999999, 999999, 15000, 1, 'Ativo', 'Administrador Teste', 'testecompleto@prazzu.com', '11999999999', NULL, NULL, NULL, '539172364b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(8, 'Empresa Teste Completa LTDA', 'Teste Completo', '99999999000199', 'testecompleto@prazzu.com', '11999999999', 'Administrador Teste', 'ativo', '2026-04-28 14:58:14', '2026-05-08 19:01:59', 'enterprise', 999999, 999999, 15000, 1, 'Ativo', 'Administrador Teste', 'testecompleto@prazzu.com', '11999999999', NULL, NULL, NULL, '539172e84b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(9, 'Empresa Teste Completa LTDA', 'Teste Completo', '99999999000195', 'testecompleto@prazzu.com', '11999999999', 'Administrador Teste', 'ativo', '2026-04-28 14:59:54', '2026-05-08 19:01:59', 'business_plus', 50, 10000, 5000, 1, 'Ativo', 'Administrador Teste', 'testecompleto@prazzu.com', '11999999999', NULL, NULL, NULL, '539173254b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(10, 'Empresa Teste Completa LTDA', 'Teste Completo', '99999999000193', 'testecompleto@prazzu.com', '11999999999', NULL, 'ativo', '2026-04-28 14:59:58', '2026-05-08 19:01:59', 'business', 25, 5000, 2000, 1, 'Ativo', NULL, 'testecompleto@prazzu.com', '11999999999', NULL, NULL, NULL, '5391736d4b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(11, 'Empresa Seed Teste LTDA', 'Seed Teste', '88999999000181', 'empresa.seed@prazzu.com', '11999998888', NULL, 'ativo', '2026-04-28 15:06:22', '2026-05-08 19:01:59', 'profissional', 10, 1000, 700, 1, 'Ativo', NULL, 'empresa.seed@prazzu.com', '11999998888', NULL, NULL, NULL, '539173b44b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(13, 'webconta', 'webconta', '11222333000181', 'webconta@webconta.com', '(11) 90000-0000', NULL, 'ativo', '2026-05-01 19:09:37', '2026-05-08 19:01:59', 'profissional', 10, 1000, 700, 1, 'Ativo', NULL, 'webconta@webconta.com', '(11) 90000-0000', NULL, NULL, NULL, '539173eb4b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(18, 'webconta2', 'webconta2', '12345678000195', 'roni@roni2.com', '(11) 90000-0000', NULL, 'ativo', '2026-05-01 20:35:39', '2026-05-08 19:01:59', 'business_plus', 50, 10000, 5000, 1, 'Ativo', NULL, 'roni@roni2.com', '(11) 90000-0000', NULL, NULL, NULL, '539174614b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(21, 'ricardo empresa', 'ricardo empresa', '32.724.443/0001-11', 'ricardo-s-a@hotmail.com', '(11) 90000-0000', NULL, 'ativo', '2026-05-01 20:53:08', '2026-05-08 19:01:59', 'business', 25, 5000, 2000, 1, 'Ativo', NULL, 'ricardo-s-a@hotmail.com', '(11) 90000-0000', NULL, NULL, NULL, '5391749c4b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(22, 'joyce empresa', 'joyce empresa', '43.061.009/0001-15', 'joyce@joyce.com', '(11) 90000-0000', NULL, 'ativo', '2026-05-01 20:59:09', '2026-05-11 14:28:01', 'business', 25, 5000, 2000, 1, 'Implementação', NULL, 'joyce@joyce.com', '(11) 90000-0000', NULL, NULL, NULL, '539174e24b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44');

-- --------------------------------------------------------

--
-- Estrutura da tabela `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `financeiro_assinaturas_cliente`
--

CREATE TABLE `financeiro_assinaturas_cliente` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `financeiro_cliente_id` bigint(20) UNSIGNED NOT NULL,
  `financeiro_gateway_integracao_id` bigint(20) UNSIGNED DEFAULT NULL,
  `gateway_subscription_id` varchar(255) DEFAULT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `valor` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ciclo` varchar(30) NOT NULL DEFAULT 'mensal',
  `forma_pagamento` varchar(40) NOT NULL DEFAULT 'manual',
  `status` varchar(30) NOT NULL DEFAULT 'ativa',
  `proxima_cobranca_em` date DEFAULT NULL,
  `ultima_cobranca_em` datetime DEFAULT NULL,
  `cancelada_em` datetime DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `financeiro_clientes`
--

CREATE TABLE `financeiro_clientes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(255) NOT NULL,
  `documento` varchar(40) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefone` varchar(40) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'ativo',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `financeiro_cobrancas`
--

CREATE TABLE `financeiro_cobrancas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `financeiro_cliente_id` bigint(20) UNSIGNED NOT NULL,
  `financeiro_assinatura_id` bigint(20) UNSIGNED DEFAULT NULL,
  `financeiro_gateway_integracao_id` bigint(20) UNSIGNED DEFAULT NULL,
  `gateway_payment_id` varchar(255) DEFAULT NULL,
  `descricao` varchar(255) NOT NULL,
  `referencia` varchar(120) DEFAULT NULL,
  `valor` decimal(12,2) NOT NULL DEFAULT 0.00,
  `vencimento` date NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'aberta',
  `forma_pagamento` varchar(40) NOT NULL DEFAULT 'manual',
  `link_pagamento` text DEFAULT NULL,
  `pix_qr_code` longtext DEFAULT NULL,
  `pago_em` datetime DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `payload_gateway` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `financeiro_gateway_integracoes`
--

CREATE TABLE `financeiro_gateway_integracoes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `gateway` varchar(50) NOT NULL DEFAULT 'manual',
  `nome` varchar(120) DEFAULT NULL,
  `ambiente` varchar(20) NOT NULL DEFAULT 'sandbox',
  `api_token_encrypted` text DEFAULT NULL,
  `webhook_secret_encrypted` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pendente',
  `ultima_sincronizacao_em` datetime DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `financeiro_recebimentos`
--

CREATE TABLE `financeiro_recebimentos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `financeiro_cobranca_id` bigint(20) UNSIGNED NOT NULL,
  `financeiro_cliente_id` bigint(20) UNSIGNED NOT NULL,
  `valor_recebido` decimal(12,2) NOT NULL DEFAULT 0.00,
  `forma_pagamento` varchar(40) NOT NULL DEFAULT 'manual',
  `recebido_em` datetime NOT NULL,
  `origem` varchar(40) NOT NULL DEFAULT 'manual',
  `gateway_event_id` varchar(255) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `payload_gateway` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `financeiro_webhook_logs`
--

CREATE TABLE `financeiro_webhook_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `financeiro_gateway_integracao_id` bigint(20) UNSIGNED DEFAULT NULL,
  `gateway` varchar(50) DEFAULT NULL,
  `evento` varchar(120) DEFAULT NULL,
  `gateway_event_id` varchar(255) DEFAULT NULL,
  `status_processamento` varchar(30) NOT NULL DEFAULT 'recebido',
  `payload` longtext DEFAULT NULL,
  `erro` text DEFAULT NULL,
  `processado_em` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `fluxos_operacionais`
--

CREATE TABLE `fluxos_operacionais` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` varchar(1000) DEFAULT NULL,
  `tipo_item` varchar(50) NOT NULL DEFAULT 'todos',
  `padrao` tinyint(1) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `fluxos_operacionais`
--

INSERT INTO `fluxos_operacionais` (`id`, `empresa_id`, `nome`, `descricao`, `tipo_item`, `padrao`, `ativo`, `created_at`, `updated_at`) VALUES
(3, 4, 'teste', NULL, 'todos', 1, 1, '2026-04-30 18:18:25', '2026-04-30 18:18:25');

-- --------------------------------------------------------

--
-- Estrutura da tabela `fluxos_operacionais_etapas`
--

CREATE TABLE `fluxos_operacionais_etapas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fluxo_operacional_id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` varchar(1000) DEFAULT NULL,
  `ordem` int(11) NOT NULL DEFAULT 1,
  `prazo_horas` int(11) DEFAULT NULL,
  `responsavel_padrao_id` bigint(20) UNSIGNED DEFAULT NULL,
  `exige_aprovacao` tinyint(1) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `fluxos_operacionais_etapas`
--

INSERT INTO `fluxos_operacionais_etapas` (`id`, `fluxo_operacional_id`, `nome`, `descricao`, `ordem`, `prazo_horas`, `responsavel_padrao_id`, `exige_aprovacao`, `ativo`, `created_at`, `updated_at`) VALUES
(5, 3, 'teste', 'teste', 1, 3, 147, 1, 1, '2026-04-30 18:18:25', '2026-04-30 18:18:25'),
(6, 3, 'teste2', 'teste2', 2, 4, 147, 1, 1, '2026-04-30 20:08:23', '2026-04-30 20:08:23');

-- --------------------------------------------------------

--
-- Estrutura da tabela `fluxos_operacionais_execucoes`
--

CREATE TABLE `fluxos_operacionais_execucoes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `fluxo_operacional_id` bigint(20) UNSIGNED NOT NULL,
  `fluxo_operacional_etapa_id` bigint(20) UNSIGNED NOT NULL,
  `responsavel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pendente',
  `iniciado_em` timestamp NULL DEFAULT NULL,
  `prazo_em` timestamp NULL DEFAULT NULL,
  `concluido_em` timestamp NULL DEFAULT NULL,
  `observacao` varchar(1000) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `historico_items`
--

CREATE TABLE `historico_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `acao` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `item_controles`
--

CREATE TABLE `item_controles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tipo` varchar(255) NOT NULL,
  `categoria_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pendente',
  `status_operacional_at` timestamp NULL DEFAULT NULL,
  `view_type` varchar(50) DEFAULT NULL,
  `automation_status` varchar(50) DEFAULT NULL,
  `prioridade` varchar(50) NOT NULL DEFAULT 'media',
  `urgencia` varchar(30) DEFAULT NULL,
  `risco_score` int(11) DEFAULT NULL,
  `bloqueado_por_dependencia` tinyint(1) NOT NULL DEFAULT 0,
  `bloqueado` tinyint(1) NOT NULL DEFAULT 0,
  `arquivo` varchar(255) DEFAULT NULL,
  `data_vencimento` date DEFAULT NULL,
  `data_conclusao` date DEFAULT NULL,
  `notificado_3_dias` tinyint(1) NOT NULL DEFAULT 0,
  `notificado_no_dia` tinyint(1) NOT NULL DEFAULT 0,
  `notificado_vencido` tinyint(1) NOT NULL DEFAULT 0,
  `observacao` text DEFAULT NULL,
  `portal_ativo` tinyint(1) NOT NULL DEFAULT 0,
  `portal_token` varchar(255) DEFAULT NULL,
  `portal_cliente_nome` varchar(255) DEFAULT NULL,
  `portal_cliente_email` varchar(255) DEFAULT NULL,
  `portal_expira_em` datetime DEFAULT NULL,
  `portal_status` varchar(50) DEFAULT NULL,
  `ultima_interacao_cliente_em` timestamp NULL DEFAULT NULL,
  `sla_horas` int(11) DEFAULT NULL,
  `sla_inicio_em` datetime DEFAULT NULL,
  `sla_limite_em` datetime DEFAULT NULL,
  `sla_prazo_alvo_em` timestamp NULL DEFAULT NULL,
  `sla_concluido_em` datetime DEFAULT NULL,
  `sla_status` varchar(50) DEFAULT NULL,
  `contrato_numero` varchar(255) DEFAULT NULL,
  `contrato_parte_nome` varchar(255) DEFAULT NULL,
  `contrato_parte_documento` varchar(255) DEFAULT NULL,
  `contrato_valor` decimal(15,2) DEFAULT NULL,
  `valor_tarefa` decimal(12,2) DEFAULT NULL,
  `faturado_em` timestamp NULL DEFAULT NULL,
  `pago_em` timestamp NULL DEFAULT NULL,
  `contrato_inicio_em` date DEFAULT NULL,
  `contrato_fim_em` date DEFAULT NULL,
  `contrato_status` varchar(50) DEFAULT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `responsavel_id` bigint(20) UNSIGNED NOT NULL,
  `ultimo_alerta_enviado_em` timestamp NULL DEFAULT NULL,
  `ultimo_lembrete_enviado_em` timestamp NULL DEFAULT NULL,
  `qtd_lembretes_enviados` int(11) NOT NULL DEFAULT 0,
  `ultima_falha_notificacao_em` timestamp NULL DEFAULT NULL,
  `ultima_falha_notificacao_msg` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `fluxo_operacional_id` bigint(20) UNSIGNED DEFAULT NULL,
  `kanban_order` int(11) DEFAULT NULL,
  `blocked_by_dependency` tinyint(1) NOT NULL DEFAULT 0,
  `estimated_minutes` int(11) DEFAULT NULL,
  `actual_minutes` int(11) DEFAULT NULL,
  `custom_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_payload`)),
  `template_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approval_required` tinyint(1) NOT NULL DEFAULT 0,
  `approval_status` varchar(50) DEFAULT NULL,
  `document_status` varchar(50) DEFAULT NULL,
  `signature_status` varchar(50) DEFAULT NULL,
  `risk_probability` tinyint(3) UNSIGNED DEFAULT NULL,
  `risk_impact` tinyint(3) UNSIGNED DEFAULT NULL,
  `risk_score` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `item_controles`
--

INSERT INTO `item_controles` (`id`, `titulo`, `descricao`, `tipo`, `categoria_id`, `status`, `status_operacional_at`, `view_type`, `automation_status`, `prioridade`, `urgencia`, `risco_score`, `bloqueado_por_dependencia`, `bloqueado`, `arquivo`, `data_vencimento`, `data_conclusao`, `notificado_3_dias`, `notificado_no_dia`, `notificado_vencido`, `observacao`, `portal_ativo`, `portal_token`, `portal_cliente_nome`, `portal_cliente_email`, `portal_expira_em`, `portal_status`, `ultima_interacao_cliente_em`, `sla_horas`, `sla_inicio_em`, `sla_limite_em`, `sla_prazo_alvo_em`, `sla_concluido_em`, `sla_status`, `contrato_numero`, `contrato_parte_nome`, `contrato_parte_documento`, `contrato_valor`, `valor_tarefa`, `faturado_em`, `pago_em`, `contrato_inicio_em`, `contrato_fim_em`, `contrato_status`, `empresa_id`, `responsavel_id`, `ultimo_alerta_enviado_em`, `ultimo_lembrete_enviado_em`, `qtd_lembretes_enviados`, `ultima_falha_notificacao_em`, `ultima_falha_notificacao_msg`, `created_at`, `updated_at`, `fluxo_operacional_id`, `kanban_order`, `blocked_by_dependency`, `estimated_minutes`, `actual_minutes`, `custom_payload`, `template_id`, `approval_required`, `approval_status`, `document_status`, `signature_status`, `risk_probability`, `risk_impact`, `risk_score`) VALUES
(31, 'Item de Controle - User Alpha 1', 'Registro de controle criado para User Alpha 1', 'documento', NULL, 'pendente', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-24', NULL, 0, 0, 0, 'Registro inicial criado via SQL.', 0, '1ef7a8d0880102be8409c4ceca8b4c5f', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 149, NULL, NULL, 0, NULL, NULL, '2026-04-24 17:57:10', '2026-04-24 17:57:10', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(32, 'Item de Controle - User Alpha 2', 'Registro de controle criado para User Alpha 2', 'documento', NULL, 'pendente', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-25', NULL, 0, 0, 0, 'Registro inicial criado via SQL.', 0, '8173ceaad16ef162f4e742df540d8c20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 150, NULL, NULL, 0, NULL, NULL, '2026-04-24 17:57:10', '2026-05-06 17:53:39', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\",\"is_milestone\":false,\"gantt_start\":\"2026-04-25\",\"timeline_start\":\"2026-04-25T00:00\",\"timeline_end\":\"2026-05-25T23:59\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(33, 'Item de Controle - User Alpha 3', 'Registro de controle criado para User Alpha 3', 'documento', NULL, 'em_aprovacao', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-24', NULL, 0, 0, 0, 'Registro inicial criado via SQL.', 0, 'f1d4f5dd0a3dbe52e3000b689f78a2b1', NULL, NULL, NULL, NULL, NULL, 24, '2026-04-28 11:53:15', '2026-04-29 11:53:15', NULL, '2026-04-28 11:55:23', 'concluido_no_prazo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 151, NULL, NULL, 0, NULL, NULL, '2026-04-24 17:57:10', '2026-04-28 14:57:20', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 'Item de Controle - User Beta 1', 'Registro de controle criado para User Beta 1', 'documento', NULL, 'pendente', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-24', NULL, 0, 0, 0, 'Registro inicial criado via SQL.', 0, '8GvffdhZO2AjBuKjMafgmtA5ZAxZK1EVCpoZaIqIZ5kyJoBuNYYuTWHmcdX1E3xa', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, 156, NULL, NULL, 0, NULL, NULL, '2026-04-24 17:57:10', '2026-04-30 22:44:42', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 'Item de Controle - User Beta 2', 'Registro de controle criado para User Beta 2', 'documento', NULL, 'pendente', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-25', NULL, 0, 0, 0, 'Registro inicial criado via SQL.', 0, 'f0820ec5ce595b69b629001d23247f12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, 157, NULL, NULL, 0, NULL, NULL, '2026-04-24 17:57:10', '2026-05-06 17:53:42', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\",\"gantt_start\":\"2026-04-25\",\"timeline_start\":\"2026-04-25T00:00\",\"timeline_end\":\"2026-05-25T23:59\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(36, 'Item de Controle - User Beta 3', 'Registro de controle criado para User Beta 3', 'documento', NULL, 'pendente', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-24', NULL, 0, 0, 0, 'Registro inicial criado via SQL.', 0, '4c2f9b6523086cb273aeb2cabba4d32a', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, 158, NULL, NULL, 0, NULL, NULL, '2026-04-24 17:57:10', '2026-04-24 17:57:10', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(37, 'Item de Controle - User Gamma 1', 'Registro de controle criado para User Gamma 1', 'documento', NULL, 'em_andamento', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-26', NULL, 0, 0, 0, 'Registro inicial criado via SQL.', 0, 'e8a9ace189b94c76fb109a4092fa9e93', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, 163, NULL, NULL, 0, NULL, NULL, '2026-04-24 17:57:10', '2026-05-06 16:11:35', NULL, NULL, 0, NULL, NULL, '{\"timeline_start\":\"2026-05-20T10:11\",\"timeline_end\":\"2026-05-26T10:11\",\"gantt_start\":\"2026-05-20\",\"baseline_start\":\"2026-05-20\",\"baseline_end\":\"2026-05-26\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(38, 'Item de Controle - User Gamma 2', 'Registro de controle criado para User Gamma 2', 'documento', NULL, 'pendente', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-24', NULL, 0, 0, 0, 'Registro inicial criado via SQL.', 0, 'e03fe0da604b41106160e88975d39496', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, 164, NULL, NULL, 0, NULL, NULL, '2026-04-24 17:57:10', '2026-04-24 17:57:10', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(39, 'Item de Controle - User Gamma 3', 'Registro de controle criado para User Gamma 3', 'documento', NULL, 'pendente', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-24', NULL, 0, 0, 0, 'Registro inicial criado via SQL.', 0, 'd6ec56f6bdfcc147050433c7d7a93671', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, 165, NULL, NULL, 0, NULL, NULL, '2026-04-24 17:57:10', '2026-04-24 17:57:10', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(47, 'SEED - Contrato vencendo hoje', 'Item criado para testar vencimento, portal, contrato, SLA, checklist, timeline e notificações.', 'contrato', NULL, 'pendente', NULL, NULL, NULL, 'alta', NULL, NULL, 0, 0, NULL, '2026-04-28', NULL, 0, 1, 0, 'Contrato seed para testar telas.', 1, 'portal-seed-d1df4b78-4313-11f1-b74e-18a59cb167c9', 'Cliente Portal Seed', 'cliente.portal.seed@teste.com', '2026-05-28 12:06:22', NULL, NULL, 24, '2026-04-28 10:06:22', '2026-04-29 10:06:22', NULL, NULL, 'em_andamento', 'CT-SEED-001', 'Cliente Seed LTDA', '12345678000100', 3500.00, NULL, NULL, NULL, '2026-04-28', '2026-05-28', 'ativo', 11, 168, NULL, NULL, 0, NULL, NULL, '2026-04-28 15:06:22', '2026-04-28 15:06:22', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-28\",\"baseline_end\":\"2026-04-28\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(48, 'SEED - Documento atrasado', 'Item criado para testar atrasados, SLA vencido, filtros e dashboard.', 'documento', NULL, 'pendente', NULL, NULL, NULL, 'alta', NULL, NULL, 0, 0, NULL, '2026-04-23', NULL, 0, 0, 1, 'Documento seed atrasado.', 1, 'SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP', NULL, NULL, NULL, NULL, NULL, 8, '2026-04-27 16:06:22', '2026-04-28 00:06:22', NULL, NULL, 'vencido', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11, 168, '2026-04-27 15:06:22', '2026-04-27 15:06:22', 2, NULL, NULL, '2026-04-28 15:06:22', '2026-05-04 20:23:21', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-28\",\"baseline_end\":\"2026-04-23\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(49, 'SEED - Item concluído', 'Item criado para testar indicadores de concluídos e histórico.', 'documento', NULL, 'concluido', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-13', '2026-04-28', 0, 0, 0, 'Item seed concluído.', 1, 'portal-seed-d1df50e6-4313-11f1-b74e-18a59cb167c9', 'Cliente Concluído Seed', 'cliente.concluido.seed@teste.com', '2026-06-27 12:06:22', NULL, NULL, 12, '2026-04-28 02:06:22', '2026-04-28 14:06:22', NULL, '2026-04-28 11:06:22', 'concluido_no_prazo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11, 168, NULL, NULL, 0, NULL, NULL, '2026-04-28 15:06:22', '2026-04-28 15:06:22', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-28\",\"baseline_end\":\"2026-05-13\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(50, 'SEED - Contrato vencendo hoje', 'Item criado para testar vencimento, portal, contrato, SLA, checklist, timeline e notificações.', 'contrato', NULL, 'pendente', NULL, NULL, NULL, 'alta', NULL, NULL, 0, 0, NULL, '2026-04-28', NULL, 0, 1, 0, 'Contrato seed para testar telas.', 1, 'portal-seed-16c901e2-4314-11f1-b74e-18a59cb167c9', 'Cliente Portal Seed', 'cliente.portal.seed@teste.com', '2026-05-28 12:08:18', NULL, NULL, 24, '2026-04-28 10:08:18', '2026-04-29 10:08:18', NULL, NULL, 'em_andamento', 'CT-SEED-001', 'Cliente Seed LTDA', '12345678000100', 3500.00, NULL, NULL, NULL, '2026-04-28', '2026-05-28', 'ativo', 11, 168, NULL, NULL, 0, NULL, NULL, '2026-04-28 15:08:18', '2026-04-28 15:08:18', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-28\",\"baseline_end\":\"2026-04-28\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(51, 'SEED - Documento atrasado', 'Item criado para testar atrasados, SLA vencido, filtros e dashboard.', 'documento', NULL, 'pendente', NULL, NULL, NULL, 'alta', NULL, NULL, 0, 0, NULL, '2026-04-23', NULL, 0, 0, 1, 'Documento seed atrasado.', 1, 't5Q6RMAbVqk7WO5hoC3nqskz9d47wlmTut8W0R6nb7IJteQphRT5INOvMRAhvOeT', NULL, NULL, NULL, NULL, NULL, 8, '2026-04-27 16:08:18', '2026-04-28 00:08:18', NULL, NULL, 'vencido', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11, 168, '2026-04-27 15:08:18', '2026-04-27 15:08:18', 2, NULL, NULL, '2026-04-28 15:08:18', '2026-05-08 14:30:31', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-28\",\"baseline_end\":\"2026-04-23\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(52, 'SEED - Item concluído', 'Item criado para testar indicadores de concluídos e histórico.', 'documento', NULL, 'concluido', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-13', '2026-05-03', 0, 0, 0, 'Item seed concluído.', 1, 'portal-seed-16c955bd-4314-11f1-b74e-18a59cb167c9', 'Cliente Concluído Seed', 'cliente.concluido.seed@teste.com', '2026-06-27 12:08:18', NULL, NULL, 12, '2026-04-28 02:08:18', '2026-04-28 14:08:18', NULL, '2026-05-03 13:26:55', 'concluido', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11, 168, NULL, NULL, 0, NULL, NULL, '2026-04-28 15:08:18', '2026-05-03 16:26:55', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-28\",\"baseline_end\":\"2026-05-13\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(56, 'Contrato RH Interno Portal do Cliente', 'teste', 'documento', 1, 'pendente', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-04-30', NULL, 0, 0, 0, 'teste ssssssssssssssss', 1, 'BD3W26Thqb2soy0b648UExabhDULsCY1cMHj0JsLXd0GbHf4GqZqPMDJQkfhLppx', 'ricardo', 'ricardo-s-a@hotmail.com', '2026-04-30 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 147, NULL, NULL, 0, NULL, NULL, '2026-04-30 20:30:56', '2026-04-30 20:30:56', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-30\",\"baseline_end\":\"2026-04-30\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(57, 'teste link', 'teste link', 'documento', 1, 'pendente', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-01', NULL, 0, 0, 0, 'teste', 1, '9Bkkk5Xram9lvUqJ0pYY1EQxopjK2lm90icRMCpGS9pOl2pFuLIjLDVzMepsR5Ab', 'ricardo', 'ricardo-s-a@hotmail.com', '2026-05-01 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 147, NULL, NULL, 0, NULL, NULL, '2026-04-30 22:51:49', '2026-04-30 22:51:49', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-30\",\"baseline_end\":\"2026-05-01\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(58, 'teste link 2', 'teste link 2', 'documento', 1, 'pendente', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-01', NULL, 0, 0, 0, 'teste link 2', 1, 'L2K7aLdtNftcNESSQwqcGHlnrglfoT8kQjTY4oTrDH40Rk0Bfxr4xOm844tnZrja', 'ricardo', 'ricardo-s-a@hotmail.com', '2026-05-01 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 147, NULL, NULL, 0, NULL, NULL, '2026-04-30 23:30:59', '2026-05-06 16:11:18', NULL, NULL, 0, NULL, NULL, '{\"gantt_start\":\"2026-04-30\",\"timeline_start\":\"2026-04-30T00:00\",\"timeline_end\":\"2026-05-01T23:59\",\"baseline_start\":\"2026-04-30\",\"baseline_end\":\"2026-05-01\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(59, 'joyce', 'teste', 'documento', 1, 'pendente', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-23', NULL, 0, 0, 0, 'teste', 1, 'cV2XTn0PgmXcxkXuLnA1BeLrlDbBpZ3U19eZbtKp9xSqUdRTyfvf5g8BRzvuBi1E', 'ricardo', 'ricardo@ricardo.com', '2026-05-22 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 147, NULL, NULL, 0, NULL, NULL, '2026-05-01 21:18:37', '2026-05-01 21:18:37', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-05-01\",\"baseline_end\":\"2026-05-23\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(60, 'Briefing aprovado pelo cliente', 'Documento inicial aprovado com objetivos, escopo e referências do projeto.', 'documento', NULL, 'concluido', NULL, NULL, NULL, 'alta', NULL, NULL, 0, 0, NULL, '2026-04-30', '2026-05-01', 0, 0, 0, 'Marco inicial concluído e disponível no histórico do cliente.', 1, '981813fb-4b07-11f1-933d-18a59cb167c9', 'Cliente Teste', 'cliente.teste@empresa.com', '2026-07-07 14:59:00', 'concluido', '2026-05-08 17:59:00', 24, '2026-04-28 14:59:00', '2026-04-30 14:59:00', NULL, '2026-05-01 14:59:00', 'concluido_no_prazo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 169, NULL, NULL, 0, NULL, NULL, '2026-04-26 17:59:00', '2026-05-08 17:59:00', NULL, 1, 0, 240, 210, '{\"portal_demo\": true, \"etapa\": \"briefing\"}', NULL, 0, 'aprovado', 'aprovado', NULL, 1, 1, 1),
(61, 'Layout da home pronto para revisão', 'Primeira versão visual da página inicial liberada para análise do cliente.', 'design', NULL, 'pronto_revisao', NULL, NULL, NULL, 'alta', NULL, NULL, 0, 0, NULL, '2026-05-10', NULL, 0, 0, 0, 'Cliente precisa validar cores, textos e disposição dos blocos.', 1, '981844d0-4b07-11f1-933d-18a59cb167c9', 'Cliente Teste', 'cliente.teste@empresa.com', '2026-07-07 14:59:00', 'aguardando_cliente', '2026-05-08 17:59:00', 24, '2026-05-08 14:59:00', '2026-05-09 14:59:00', NULL, NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 169, NULL, NULL, 0, NULL, NULL, '2026-05-04 17:59:00', '2026-05-08 17:59:00', NULL, 2, 0, 480, 360, '{\"portal_demo\": true, \"etapa\": \"revisao\"}', NULL, 1, 'pendente', 'em_revisao', NULL, 2, 2, 4),
(62, 'Integração do formulário de contato', 'Configurar envio dos leads para a fila comercial e validar notificação por e-mail.', 'desenvolvimento', NULL, 'em_andamento', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-13', NULL, 0, 0, 0, 'Entrega em produção parcial, aguardando homologação.', 1, '9818468c-4b07-11f1-933d-18a59cb167c9', 'Cliente Teste', 'cliente.teste@empresa.com', '2026-07-07 14:59:00', 'em_execucao', '2026-05-08 17:59:00', 48, '2026-05-08 14:59:00', '2026-05-10 14:59:00', NULL, NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 169, NULL, NULL, 0, NULL, NULL, '2026-05-05 17:59:00', '2026-05-08 17:59:00', NULL, 3, 0, 600, 280, '{\"portal_demo\": true, \"etapa\": \"execucao\"}', NULL, 0, NULL, 'em_andamento', NULL, 2, 3, 6),
(63, 'Publicação da área do cliente', 'Subir a área do cliente em ambiente final com os ajustes aprovados.', 'entrega', NULL, 'pendente', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-17', NULL, 0, 0, 0, 'Próxima entrega planejada após aprovação do layout.', 1, '9818478a-4b07-11f1-933d-18a59cb167c9', 'Cliente Teste', 'cliente.teste@empresa.com', '2026-07-07 14:59:00', 'planejado', NULL, 72, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 169, NULL, NULL, 0, NULL, NULL, '2026-05-07 17:59:00', '2026-05-08 17:59:00', NULL, 4, 0, 720, NULL, '{\"portal_demo\": true, \"etapa\": \"entrega\"}', NULL, 0, NULL, 'pendente', NULL, 1, 2, 2),
(64, 'Checklist final de qualidade', 'Revisão final de links, responsividade, formulários e conteúdo antes da entrega.', 'qualidade', NULL, 'em_aprovacao', NULL, NULL, NULL, 'urgente', NULL, NULL, 0, 0, NULL, '2026-05-20', NULL, 0, 0, 0, 'Item visível ao cliente para acompanhar validação final.', 1, '98184898-4b07-11f1-933d-18a59cb167c9', 'Cliente Teste', 'cliente.teste@empresa.com', '2026-07-07 14:59:00', 'em_aprovacao', '2026-05-08 17:59:00', 24, '2026-05-08 14:59:00', '2026-05-09 14:59:00', NULL, NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 169, NULL, NULL, 0, NULL, NULL, '2026-05-08 17:59:00', '2026-05-08 17:59:00', NULL, 5, 0, 300, NULL, '{\"portal_demo\": true, \"etapa\": \"aprovacao\"}', NULL, 1, 'pendente', 'em_aprovacao', NULL, 3, 2, 6),
(65, 'Onboarding - Diagnóstico inicial - Alpha', 'Etapa de onboarding criada pela aba Clientes para organizar a implantação do cliente.', 'tarefa', NULL, 'pendente', NULL, NULL, NULL, 'alta', NULL, NULL, 0, 0, NULL, '2026-05-14', NULL, 0, 0, 0, 'Criado automaticamente pela central de Clientes.', 1, '1a884d22ed2ba2715abcabd84c549e65', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 147, NULL, NULL, 0, NULL, NULL, '2026-05-11 14:28:48', '2026-05-11 14:28:48', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(66, 'Onboarding - Documentos e acessos - Alpha', 'Etapa de onboarding criada pela aba Clientes para organizar a implantação do cliente.', 'tarefa', NULL, 'pendente', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-17', NULL, 0, 0, 0, 'Criado automaticamente pela central de Clientes.', 1, '5757cc037fa795a56642c655f8b777c7', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 147, NULL, NULL, 0, NULL, NULL, '2026-05-11 14:28:48', '2026-05-11 14:28:48', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(67, 'Onboarding - Primeira entrega para aprovação - Alpha', 'Etapa de onboarding criada pela aba Clientes para organizar a implantação do cliente.', 'tarefa', NULL, 'pendente', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-05-20', NULL, 0, 0, 0, 'Criado automaticamente pela central de Clientes.', 1, '11981ea580fe4991bd18f92fb20f1cbd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 147, NULL, NULL, 0, NULL, NULL, '2026-05-11 14:28:48', '2026-05-11 14:28:48', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `item_controle_alertas`
--

CREATE TABLE `item_controle_alertas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tipo` varchar(50) DEFAULT 'manual',
  `data_alerta` datetime DEFAULT NULL,
  `recorrente` tinyint(1) DEFAULT 0,
  `status` varchar(50) DEFAULT 'pendente',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `item_controle_alertas`
--

INSERT INTO `item_controle_alertas` (`id`, `empresa_id`, `item_controle_id`, `titulo`, `descricao`, `tipo`, `data_alerta`, `recorrente`, `status`, `created_at`, `updated_at`) VALUES
(1, 11, 47, 'Alerta seed vencendo hoje', 'Alerta manual para testar tela de alertas.', 'manual', '2026-04-28 12:06:22', 0, 'pendente', '2026-04-28 15:06:22', '2026-04-28 15:06:22'),
(2, 11, 48, 'Alerta seed recorrente de atraso', 'Alerta recorrente para documento atrasado.', 'condicao', '2026-04-29 12:06:22', 1, 'pendente', '2026-04-28 15:06:22', '2026-04-28 15:06:22'),
(3, 11, 49, 'Alerta seed resolvido', 'Alerta concluído para testar status.', 'manual', '2026-04-27 12:06:22', 0, 'concluido', '2026-04-28 15:06:22', '2026-04-28 15:06:22'),
(4, 11, 50, 'Alerta seed vencendo hoje', 'Alerta manual para testar tela de alertas.', 'manual', '2026-04-28 12:08:18', 0, 'pendente', '2026-04-28 15:08:18', '2026-04-28 15:08:18'),
(5, 11, 51, 'Alerta seed recorrente de atraso', 'Alerta recorrente para documento atrasado.', 'condicao', '2026-04-29 12:08:18', 1, 'pendente', '2026-04-28 15:08:18', '2026-04-28 15:08:18'),
(6, 11, 52, 'Alerta seed resolvido', 'Alerta concluído para testar status.', 'manual', '2026-04-27 12:08:18', 0, 'concluido', '2026-04-28 15:08:18', '2026-04-28 15:08:18');

-- --------------------------------------------------------

--
-- Estrutura da tabela `item_controle_anexos`
--

CREATE TABLE `item_controle_anexos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `nome_original` varchar(255) DEFAULT NULL,
  `caminho` varchar(255) DEFAULT NULL,
  `mime_type` varchar(150) DEFAULT NULL,
  `tamanho_bytes` bigint(20) UNSIGNED DEFAULT NULL,
  `arquivo` varchar(255) NOT NULL,
  `observacao` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `item_controle_aprovacoes`
--

CREATE TABLE `item_controle_aprovacoes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `solicitante_id` bigint(20) UNSIGNED DEFAULT NULL,
  `aprovador_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pendente',
  `observacao_solicitacao` text DEFAULT NULL,
  `observacao_resposta` text DEFAULT NULL,
  `solicitado_em` datetime DEFAULT NULL,
  `respondido_em` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `motivo_reprovacao` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `item_controle_aprovacoes`
--

INSERT INTO `item_controle_aprovacoes` (`id`, `item_controle_id`, `empresa_id`, `solicitante_id`, `aprovador_id`, `status`, `observacao_solicitacao`, `observacao_resposta`, `solicitado_em`, `respondido_em`, `created_at`, `updated_at`, `motivo_reprovacao`) VALUES
(1, 33, 4, 111, NULL, 'pendente', 'aaaaa teste Solicitar aprovacao ', NULL, '2026-04-28 11:57:20', NULL, '2026-04-28 14:57:20', '2026-04-28 14:57:20', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `item_controle_assinaturas`
--

CREATE TABLE `item_controle_assinaturas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `documento` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `assinado_em` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `hash_assinatura` varchar(64) DEFAULT NULL,
  `aceite_texto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `item_controle_assinaturas`
--

INSERT INTO `item_controle_assinaturas` (`id`, `item_controle_id`, `empresa_id`, `user_id`, `nome`, `email`, `documento`, `ip`, `user_agent`, `observacao`, `assinado_em`, `created_at`, `updated_at`, `hash_assinatura`, `aceite_texto`) VALUES
(1, 57, NULL, NULL, 'ricardo', 'ricardo-s-a@hotmail.com', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, '2026-04-30 19:52:51', '2026-04-30 22:52:51', '2026-04-30 22:52:51', NULL, NULL),
(2, 58, 4, 111, 'ricardo', 'ricardo-s-a@hotmail.com', '47681192808', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, '2026-04-30 20:33:11', '2026-04-30 23:33:11', '2026-04-30 23:33:11', '7d861acc165dd391493e09b80bab3dd75ad1c5f57c8d80c7fb834fdc0272f06f', 'Declaro que li e concordo com as informações apresentadas neste item/documento, registrando minha assinatura eletrônica interna.'),
(3, 59, 4, 111, 'ricardo', 'ricardo@ricardo.com', '000000000000000', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, '2026-05-01 18:19:44', '2026-05-01 21:19:44', '2026-05-01 21:19:44', '244413a83977ba5ed65f20eed5a4b7859f9761528e3fd30048c04307b7b78eae', 'Declaro que li e concordo com as informações apresentadas neste item/documento, registrando minha assinatura eletrônica interna.'),
(4, 48, 11, 111, 'webconta', 'roni@roni2.com', '11111111111111111', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, '2026-05-01 18:50:30', '2026-05-01 21:50:30', '2026-05-01 21:50:30', 'dd1edf0a966830c0d6c963740fc2ca5fd128d6070f51ddcdd584ea086a88c7e8', 'Declaro que li e concordo com as informações apresentadas neste item/documento, registrando minha assinatura eletrônica interna.');

-- --------------------------------------------------------

--
-- Estrutura da tabela `item_controle_checklists`
--

CREATE TABLE `item_controle_checklists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `concluido` tinyint(1) NOT NULL DEFAULT 0,
  `ordem` int(11) NOT NULL DEFAULT 0,
  `concluido_em` datetime DEFAULT NULL,
  `concluido_por` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `item_controle_checklists`
--

INSERT INTO `item_controle_checklists` (`id`, `item_controle_id`, `titulo`, `descricao`, `concluido`, `ordem`, `concluido_em`, `concluido_por`, `created_at`, `updated_at`) VALUES
(1, 31, '1teste', NULL, 0, 3, NULL, NULL, '2026-04-28 15:12:58', '2026-04-28 15:12:58'),
(2, 47, 'Receber documento', 'Validar recebimento do documento.', 1, 1, '2026-04-27 12:06:22', 396, '2026-04-28 15:06:22', '2026-04-28 15:06:22'),
(3, 47, 'Conferir dados', 'Conferir dados do cliente.', 1, 2, '2026-04-28 00:06:22', 396, '2026-04-28 15:06:22', '2026-04-28 15:06:22'),
(4, 47, 'Aprovar internamente', 'Enviar para aprovação interna.', 0, 3, NULL, NULL, '2026-04-28 15:06:22', '2026-04-28 15:06:22'),
(5, 48, 'Cobrar responsável', 'Solicitar atualização do documento atrasado.', 1, 1, '2026-04-30 17:17:46', 111, '2026-04-28 15:06:22', '2026-04-30 20:17:46'),
(6, 49, 'Finalizar processo', 'Checklist final concluído.', 1, 1, '2026-04-28 11:06:22', 396, '2026-04-28 15:06:22', '2026-04-28 15:06:22'),
(7, 50, 'Receber documento', 'Validar recebimento do documento.', 1, 1, '2026-04-27 12:08:18', 396, '2026-04-28 15:08:18', '2026-04-28 15:08:18'),
(8, 50, 'Conferir dados', 'Conferir dados do cliente.', 1, 2, '2026-04-28 00:08:18', 396, '2026-04-28 15:08:18', '2026-04-28 15:08:18'),
(9, 50, 'Aprovar internamente', 'Enviar para aprovação interna.', 0, 3, NULL, NULL, '2026-04-28 15:08:18', '2026-04-28 15:08:18'),
(10, 51, 'Cobrar responsável', 'Solicitar atualização do documento atrasado.', 0, 1, NULL, NULL, '2026-04-28 15:08:18', '2026-04-28 15:08:18'),
(11, 52, 'Finalizar processo', 'Checklist final concluído.', 1, 1, '2026-04-28 11:08:18', 396, '2026-04-28 15:08:18', '2026-04-28 15:08:18');

-- --------------------------------------------------------

--
-- Estrutura da tabela `item_controle_comentarios`
--

CREATE TABLE `item_controle_comentarios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo` varchar(50) NOT NULL DEFAULT 'comentario',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `item_controle_comentarios`
--

INSERT INTO `item_controle_comentarios` (`id`, `item_controle_id`, `user_id`, `tipo`, `comentario`, `created_at`, `updated_at`) VALUES
(1, 47, 396, 'comentario', 'Comentário seed: contrato vencendo hoje precisa de atenção.', '2026-04-28 15:06:22', '2026-04-28 15:06:22'),
(2, 48, 396, 'comentario', 'Comentário seed: documento está atrasado e deve aparecer nos filtros.', '2026-04-28 15:06:22', '2026-04-28 15:06:22'),
(3, 49, 396, 'comentario', 'Comentário seed: item concluído com sucesso.', '2026-04-28 15:06:22', '2026-04-28 15:06:22'),
(4, 50, 396, 'comentario', 'Comentário seed: contrato vencendo hoje precisa de atenção.', '2026-04-28 15:08:18', '2026-04-28 15:08:18'),
(5, 51, 396, 'comentario', 'Comentário seed: documento está atrasado e deve aparecer nos filtros.', '2026-04-28 15:08:18', '2026-04-28 15:08:18'),
(6, 52, 396, 'comentario', 'Comentário seed: item concluído com sucesso.', '2026-04-28 15:08:18', '2026-04-28 15:08:18');

-- --------------------------------------------------------

--
-- Estrutura da tabela `item_controle_notificacao_logs`
--

CREATE TABLE `item_controle_notificacao_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `responsavel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo_notificacao` varchar(50) NOT NULL,
  `canal` varchar(50) NOT NULL DEFAULT 'database_mail',
  `status` varchar(50) NOT NULL DEFAULT 'enviado',
  `mensagem` text DEFAULT NULL,
  `enviado_em` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `item_controle_tags`
--

CREATE TABLE `item_controle_tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nome` varchar(255) NOT NULL,
  `cor` varchar(50) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `item_controle_tags`
--

INSERT INTO `item_controle_tags` (`id`, `empresa_id`, `nome`, `cor`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 4, 'teste', 'info', 1, '2026-04-30 16:40:20', '2026-04-30 16:40:20');

-- --------------------------------------------------------

--
-- Estrutura da tabela `item_controle_tag_relations`
--

CREATE TABLE `item_controle_tag_relations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_tag_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `item_controle_tag_relations`
--

INSERT INTO `item_controle_tag_relations` (`id`, `item_controle_id`, `item_controle_tag_id`, `created_at`, `updated_at`) VALUES
(1, 58, 1, '2026-04-30 23:30:59', '2026-04-30 23:30:59');

-- --------------------------------------------------------

--
-- Estrutura da tabela `item_controle_timeline`
--

CREATE TABLE `item_controle_timeline` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo` varchar(100) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `dados` longtext DEFAULT NULL CHECK (json_valid(`dados`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `item_controle_timeline`
--

INSERT INTO `item_controle_timeline` (`id`, `item_controle_id`, `empresa_id`, `user_id`, `tipo`, `titulo`, `descricao`, `dados`, `created_at`, `updated_at`) VALUES
(1, 33, 4, 111, 'sla', 'SLA iniciado', 'SLA iniciado com limite de 24 hora(s).', NULL, '2026-04-28 14:53:15', '2026-04-28 14:53:15'),
(2, 33, 4, 111, 'sla', 'SLA concluído', 'SLA finalizado com status: Concluído no prazo.', NULL, '2026-04-28 14:55:23', '2026-04-28 14:55:23'),
(3, 33, 4, 111, 'aprovacao_solicitada', 'Aprovação solicitada', 'aaaaa teste Solicitar aprovacao ', '{\"aprovacao_id\":1,\"solicitante_id\":111}', '2026-04-28 14:57:20', '2026-04-28 14:57:20'),
(4, 33, 4, 111, 'notificacao', 'Alerta interno criado', 'Aprovação solicitada', '{\"notificacao_id\":1,\"destinatario_id\":375,\"tipo\":\"aprovacao\"}', '2026-04-28 14:57:20', '2026-04-28 14:57:20'),
(5, 33, 4, 111, 'notificacao', 'Alerta interno criado', 'teste alerta', '{\"notificacao_id\":2,\"destinatario_id\":375,\"tipo\":\"manual\"}', '2026-04-28 14:57:53', '2026-04-28 14:57:53'),
(6, 31, 4, 111, 'checklist', 'Etapa adicionada ao checklist', '1teste', NULL, '2026-04-28 15:12:58', '2026-04-28 15:12:58'),
(11, 48, 11, 111, 'atualizacao', 'Portal do cliente ativado', 'O acesso externo do cliente foi ativado para este item.', NULL, '2026-04-28 19:44:47', '2026-04-28 19:44:47'),
(12, 48, 11, 111, 'checklist', 'Etapa concluida', 'Cobrar responsável', '{\"checklist_id\":5}', '2026-04-30 20:17:46', '2026-04-30 20:17:46'),
(13, 48, 11, 111, 'atualizacao', 'Portal do cliente desativado', 'O acesso externo do cliente foi desativado para este item.', NULL, '2026-04-30 22:43:06', '2026-04-30 22:43:06'),
(14, 48, 11, 111, 'atualizacao', 'Portal do cliente ativado', 'O acesso externo do cliente foi ativado para este item.', NULL, '2026-04-30 22:43:10', '2026-04-30 22:43:10'),
(15, 48, 11, 111, 'atualizacao', 'Portal do cliente desativado', 'O acesso externo do cliente foi desativado para este item.', NULL, '2026-04-30 22:43:18', '2026-04-30 22:43:18'),
(16, 34, 5, 376, 'atualizacao', 'Portal do cliente ativado', 'O acesso externo do cliente foi ativado para este item.', NULL, '2026-04-30 22:44:38', '2026-04-30 22:44:38'),
(17, 34, 5, 376, 'atualizacao', 'Portal do cliente desativado', 'O acesso externo do cliente foi desativado para este item.', NULL, '2026-04-30 22:44:42', '2026-04-30 22:44:42'),
(18, 48, 11, 111, 'atualizacao', 'Portal do cliente ativado', 'O acesso externo do cliente foi ativado para este item.', NULL, '2026-05-01 21:49:54', '2026-05-01 21:49:54'),
(19, 51, 11, 111, 'atualizacao', 'Portal do cliente ativado', 'O acesso externo do cliente foi ativado para este item.', NULL, '2026-05-08 14:30:31', '2026-05-08 14:30:31');

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `item_controle_timelines`
-- (Veja abaixo para a view atual)
--
CREATE TABLE `item_controle_timelines` (
`id` bigint(20) unsigned
,`item_controle_id` bigint(20) unsigned
,`empresa_id` bigint(20) unsigned
,`user_id` bigint(20) unsigned
,`tipo` varchar(100)
,`titulo` varchar(255)
,`descricao` text
,`dados` longtext
,`created_at` timestamp
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Estrutura da tabela `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `logs_sistema`
--

CREATE TABLE `logs_sistema` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `acao` varchar(100) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_04_14_144610_create_empresas_table', 1),
(5, '2026_04_14_144619_create_responsavels_table', 1),
(6, '2026_04_15_150455_add_arquivo_to_item_controles_table', 2);

-- --------------------------------------------------------

--
-- Estrutura da tabela `notificacoes_internas`
--

CREATE TABLE `notificacoes_internas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo` varchar(100) NOT NULL DEFAULT 'manual',
  `titulo` varchar(255) NOT NULL,
  `mensagem` text DEFAULT NULL,
  `lida` tinyint(1) NOT NULL DEFAULT 0,
  `lida_em` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `notificacoes_internas`
--

INSERT INTO `notificacoes_internas` (`id`, `item_controle_id`, `empresa_id`, `user_id`, `tipo`, `titulo`, `mensagem`, `lida`, `lida_em`, `created_at`, `updated_at`) VALUES
(1, 33, 4, 375, 'aprovacao', 'Aprovação solicitada', 'O item \"Item de Controle - User Alpha 3\" foi enviado para aprovação.', 0, NULL, '2026-04-28 14:57:20', '2026-04-28 14:57:20'),
(2, 33, 4, 375, 'manual', 'teste alerta', 'teste', 0, NULL, '2026-04-28 14:57:53', '2026-04-28 14:57:53'),
(3, 47, 11, 396, 'vencimento', 'Seed - item vencendo hoje', 'O contrato seed vence hoje.', 0, NULL, '2026-04-28 15:06:22', '2026-04-28 15:06:22'),
(4, 48, 11, 396, 'atraso', 'Seed - item atrasado', 'O documento seed está atrasado.', 0, NULL, '2026-04-28 15:06:22', '2026-04-28 15:06:22'),
(5, 49, 11, 396, 'concluido', 'Seed - item concluído', 'O item seed foi concluído.', 1, '2026-04-28 12:06:22', '2026-04-28 15:06:22', '2026-04-28 15:06:22'),
(6, 50, 11, 396, 'vencimento', 'Seed - item vencendo hoje', 'O contrato seed vence hoje.', 0, NULL, '2026-04-28 15:08:18', '2026-04-28 15:08:18'),
(7, 51, 11, 396, 'atraso', 'Seed - item atrasado', 'O documento seed está atrasado.', 1, '2026-04-30 17:12:22', '2026-04-28 15:08:18', '2026-04-30 20:12:22'),
(8, 52, 11, 396, 'concluido', 'Seed - item concluído', 'O item seed foi concluído.', 1, '2026-04-28 12:08:18', '2026-04-28 15:08:18', '2026-04-28 15:08:18');

-- --------------------------------------------------------

--
-- Estrutura da tabela `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `pagamentos`
--

CREATE TABLE `pagamentos` (
  `id` bigint(20) NOT NULL,
  `empresa_id` bigint(20) NOT NULL,
  `assinatura_id` bigint(20) NOT NULL,
  `gateway_payment_id` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `billing_type` varchar(50) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `vencimento` date DEFAULT NULL,
  `pago_em` datetime DEFAULT NULL,
  `invoice_url` text DEFAULT NULL,
  `pix_qr_code` longtext DEFAULT NULL,
  `payload_gateway` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `pagamentos`
--

INSERT INTO `pagamentos` (`id`, `empresa_id`, `assinatura_id`, `gateway_payment_id`, `status`, `billing_type`, `valor`, `vencimento`, `pago_em`, `invoice_url`, `pix_qr_code`, `payload_gateway`, `created_at`, `updated_at`) VALUES
(1, 13, 1, 'pay_ww30yvqwygo3a9nj', 'PENDING', 'UNDEFINED', 247.00, '2026-05-01', NULL, 'https://sandbox.asaas.com/i/ww30yvqwygo3a9nj', NULL, '{\"object\":\"payment\",\"id\":\"pay_ww30yvqwygo3a9nj\",\"dateCreated\":\"2026-05-01\",\"customer\":\"cus_000007891665\",\"subscription\":\"sub_2gsdj8xz8mcrp0sm\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":247,\"netValue\":241.6,\"originalValue\":null,\"interestValue\":null,\"description\":\"Assinatura Prazzu - Profissional\",\"billingType\":\"UNDEFINED\",\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-05-01\",\"originalDueDate\":\"2026-05-01\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https:\\/\\/sandbox.asaas.com\\/i\\/ww30yvqwygo3a9nj\",\"invoiceNumber\":\"14523785\",\"externalReference\":\"13\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":null,\"nossoNumero\":\"12341360\",\"bankSlipUrl\":\"https:\\/\\/sandbox.asaas.com\\/b\\/pdf\\/ww30yvqwygo3a9nj\",\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0,\"type\":\"FIXED\"},\"interest\":{\"value\":0,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}', '2026-05-01 16:09:41', '2026-05-01 16:09:41'),
(2, 18, 2, 'pay_vkjzzkndxi3abxlt', 'PENDING', 'UNDEFINED', 697.00, '2026-05-01', NULL, 'https://sandbox.asaas.com/i/vkjzzkndxi3abxlt', NULL, '{\"object\":\"payment\",\"id\":\"pay_vkjzzkndxi3abxlt\",\"dateCreated\":\"2026-05-01\",\"customer\":\"cus_000007891937\",\"subscription\":\"sub_e63ermcilkur78fp\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":697,\"netValue\":682.64,\"originalValue\":null,\"interestValue\":null,\"description\":\"Assinatura Prazzu - Business Plus\",\"billingType\":\"UNDEFINED\",\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-05-01\",\"originalDueDate\":\"2026-05-01\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https:\\/\\/sandbox.asaas.com\\/i\\/vkjzzkndxi3abxlt\",\"invoiceNumber\":\"14524349\",\"externalReference\":\"18\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":null,\"nossoNumero\":\"12341537\",\"bankSlipUrl\":\"https:\\/\\/sandbox.asaas.com\\/b\\/pdf\\/vkjzzkndxi3abxlt\",\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0,\"type\":\"FIXED\"},\"interest\":{\"value\":0,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}', '2026-05-01 17:35:43', '2026-05-01 17:35:43'),
(3, 21, 3, 'pay_mfb1fw28zxaon8b6', 'PENDING', 'UNDEFINED', 397.00, '2026-05-01', NULL, 'https://sandbox.asaas.com/i/mfb1fw28zxaon8b6', NULL, '{\"object\":\"payment\",\"id\":\"pay_mfb1fw28zxaon8b6\",\"dateCreated\":\"2026-05-01\",\"customer\":\"cus_000007891970\",\"subscription\":\"sub_ybfxt28jcw2zjmiu\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":397,\"netValue\":388.61,\"originalValue\":null,\"interestValue\":null,\"description\":\"Assinatura Prazzu - Business\",\"billingType\":\"UNDEFINED\",\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-05-01\",\"originalDueDate\":\"2026-05-01\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https:\\/\\/sandbox.asaas.com\\/i\\/mfb1fw28zxaon8b6\",\"invoiceNumber\":\"14524453\",\"externalReference\":\"21\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":null,\"nossoNumero\":\"12341571\",\"bankSlipUrl\":\"https:\\/\\/sandbox.asaas.com\\/b\\/pdf\\/mfb1fw28zxaon8b6\",\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0,\"type\":\"FIXED\"},\"interest\":{\"value\":0,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}', '2026-05-01 17:53:12', '2026-05-01 17:53:12'),
(4, 22, 4, 'pay_nz6z46hqdny41ow5', 'PENDING', 'UNDEFINED', 397.00, '2026-05-01', NULL, 'https://sandbox.asaas.com/i/nz6z46hqdny41ow5', NULL, '{\"object\":\"payment\",\"id\":\"pay_nz6z46hqdny41ow5\",\"dateCreated\":\"2026-05-01\",\"customer\":\"cus_000007891989\",\"subscription\":\"sub_2sfju62w845xahpz\",\"checkoutSession\":null,\"paymentLink\":null,\"value\":397,\"netValue\":388.61,\"originalValue\":null,\"interestValue\":null,\"description\":\"Assinatura Prazzu - Business\",\"billingType\":\"UNDEFINED\",\"pixTransaction\":null,\"status\":\"PENDING\",\"dueDate\":\"2026-05-01\",\"originalDueDate\":\"2026-05-01\",\"paymentDate\":null,\"clientPaymentDate\":null,\"installmentNumber\":null,\"invoiceUrl\":\"https:\\/\\/sandbox.asaas.com\\/i\\/nz6z46hqdny41ow5\",\"invoiceNumber\":\"14524487\",\"externalReference\":\"22\",\"deleted\":false,\"anticipated\":false,\"anticipable\":false,\"creditDate\":null,\"estimatedCreditDate\":null,\"transactionReceiptUrl\":null,\"nossoNumero\":\"12341583\",\"bankSlipUrl\":\"https:\\/\\/sandbox.asaas.com\\/b\\/pdf\\/nz6z46hqdny41ow5\",\"lastInvoiceViewedDate\":null,\"lastBankSlipViewedDate\":null,\"discount\":{\"value\":0,\"limitDate\":null,\"dueDateLimitDays\":0,\"type\":\"FIXED\"},\"fine\":{\"value\":0,\"type\":\"FIXED\"},\"interest\":{\"value\":0,\"type\":\"PERCENTAGE\"},\"postalService\":false,\"escrow\":null,\"refunds\":null}', '2026-05-01 17:59:13', '2026-05-01 17:59:13');

-- --------------------------------------------------------

--
-- Estrutura da tabela `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `portal_cliente_tokens`
--

CREATE TABLE `portal_cliente_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cliente_portal_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `tipo` varchar(30) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `portal_documentos`
--

CREATE TABLE `portal_documentos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `tipo` enum('wiki','ata','documento','link') NOT NULL DEFAULT 'documento',
  `conteudo` longtext DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `arquivo` varchar(500) DEFAULT NULL,
  `visivel_cliente` tinyint(1) NOT NULL DEFAULT 1,
  `criado_por` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `portal_documentos`
--

INSERT INTO `portal_documentos` (`id`, `empresa_id`, `item_controle_id`, `titulo`, `tipo`, `conteudo`, `url`, `arquivo`, `visivel_cliente`, `criado_por`, `created_at`, `updated_at`) VALUES
(1, 21, NULL, 'Wiki do Projeto', 'wiki', 'Regras gerais do projeto, canais oficiais, prazos combinados e responsáveis principais.', NULL, NULL, 1, NULL, '2026-05-08 17:19:14', '2026-05-08 17:19:14'),
(2, 21, NULL, 'Manual de Aprovação de Entregas', 'documento', 'Este documento explica como o cliente deve revisar, aprovar ou solicitar ajustes nas entregas.', NULL, NULL, 1, NULL, '2026-05-08 17:19:14', '2026-05-08 17:19:14'),
(3, 21, NULL, 'Link do Ambiente de Homologação', 'link', 'Acesso ao ambiente onde o cliente pode revisar as entregas antes da publicação.', 'https://homologacao.exemplo.com.br', NULL, 1, NULL, '2026-05-08 17:19:14', '2026-05-08 17:19:14'),
(4, 21, NULL, 'Ata da Reunião Inicial', 'ata', 'Foi definido o escopo inicial, prioridades da primeira fase e responsáveis pela validação.', NULL, NULL, 1, NULL, '2026-05-08 17:19:14', '2026-05-08 17:19:14'),
(5, 21, NULL, 'Ata de Alinhamento Semanal', 'ata', 'Foram revisadas as entregas em andamento e definido que a próxima validação será feita até sexta-feira.', NULL, NULL, 1, NULL, '2026-05-08 17:19:14', '2026-05-08 17:19:14'),
(6, 21, NULL, 'Wiki do projeto - Regras gerais', 'wiki', 'Escopo aprovado: criação da página inicial, formulário de contato, área do cliente e publicação assistida. Alterações fora do escopo devem ser registradas como solicitação no portal.', NULL, NULL, 1, NULL, '2026-05-08 17:59:01', '2026-05-08 17:59:01'),
(7, 21, NULL, 'Manual rápido para aprovações', 'documento', 'Para aprovar uma entrega, acesse o bloco Pronto para revisão / aprovação, leia a descrição do item e envie sua resposta pelo chat ou solicitação.', NULL, NULL, 1, NULL, '2026-05-08 17:59:01', '2026-05-08 17:59:01'),
(8, 21, NULL, 'Link do protótipo navegável', 'link', 'Protótipo visual para validação do cliente.', 'https://www.exemplo.com/prototipo-cliente', NULL, 1, NULL, '2026-05-08 17:59:01', '2026-05-08 17:59:01'),
(9, 21, 61, 'Ata da reunião - alinhamento de layout', 'ata', 'Decisões: manter destaque principal na home, reduzir quantidade de textos longos, priorizar botão de contato e exibir depoimentos na segunda dobra.', NULL, NULL, 1, NULL, '2026-05-06 17:59:01', '2026-05-08 17:59:01'),
(10, 21, 62, 'Ata da reunião - integração comercial', 'ata', 'Decisões: leads serão enviados para a equipe comercial, com cópia para o gestor responsável. O cliente validará um teste antes da publicação final.', NULL, NULL, 1, NULL, '2026-05-07 17:59:01', '2026-05-08 17:59:01');

-- --------------------------------------------------------

--
-- Estrutura da tabela `portal_mensagens`
--

CREATE TABLE `portal_mensagens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mensagem` longtext NOT NULL,
  `origem` enum('cliente','interno') NOT NULL DEFAULT 'cliente',
  `conversa_status` enum('aberta','finalizada') NOT NULL DEFAULT 'aberta',
  `visualizada_em` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `portal_mensagens`
--

INSERT INTO `portal_mensagens` (`id`, `empresa_id`, `item_controle_id`, `user_id`, `nome`, `email`, `mensagem`, `origem`, `conversa_status`, `visualizada_em`, `created_at`, `updated_at`) VALUES
(1, 21, NULL, NULL, 'Cliente', 'cliente@empresa.com.br', 'Olá, gostaria de confirmar se a próxima entrega continua prevista para esta semana.', 'cliente', 'aberta', NULL, '2026-05-08 17:19:14', '2026-05-08 17:19:14'),
(2, 21, NULL, NULL, 'Equipe Interna', 'equipe@sistema.com.br', 'Sim, a entrega segue prevista. Assim que finalizar, vamos mover para aprovação no portal.', 'interno', 'aberta', NULL, '2026-05-08 17:19:14', '2026-05-08 17:19:14'),
(3, 21, NULL, NULL, 'Cliente', 'cliente@empresa.com.br', 'Perfeito, obrigado pelo retorno.', 'cliente', 'aberta', NULL, '2026-05-08 17:19:14', '2026-05-08 17:19:14'),
(4, 22, NULL, 111, 'admin', 'admin@admin.com', 'oiii', 'cliente', 'aberta', NULL, '2026-05-08 20:40:43', '2026-05-08 20:40:43'),
(5, 21, 61, NULL, 'Cliente Teste', 'cliente.teste@empresa.com', 'Bom dia! Consegui ver o layout no portal. Vou revisar os textos hoje.', 'cliente', 'aberta', NULL, '2026-05-05 17:59:01', '2026-05-08 17:59:01'),
(6, 21, 61, NULL, 'Equipe Prazzu', 'equipe@prazzu.com', 'Perfeito. Deixamos o layout marcado como pronto para revisão. Qualquer ajuste pode ser enviado por aqui.', 'interno', 'aberta', NULL, '2026-05-05 17:59:01', '2026-05-08 17:59:01'),
(7, 21, 62, NULL, 'Cliente Teste', 'cliente.teste@empresa.com', 'Também quero validar se o formulário está enviando para o e-mail comercial.', 'cliente', 'aberta', NULL, '2026-05-06 17:59:01', '2026-05-08 17:59:01'),
(8, 21, 62, NULL, 'Equipe Prazzu', 'equipe@prazzu.com', 'Já deixamos essa validação em andamento. Assim que concluirmos, atualizamos o status no portal.', 'interno', 'aberta', NULL, '2026-05-07 17:59:01', '2026-05-08 17:59:01'),
(9, 21, NULL, NULL, 'Cliente Teste', 'cliente.teste@empresa.com', 'Obrigado, agora ficou bem mais fácil acompanhar tudo pelo portal.', 'cliente', 'aberta', NULL, '2026-05-08 17:59:01', '2026-05-08 17:59:01'),
(10, 13, NULL, NULL, 'ricardo', 'ricardo-s-a@hotmail.com', 'preciso de ajuda para resolver um assunto', 'cliente', 'finalizada', '2026-05-12 23:23:02', '2026-05-12 23:19:04', '2026-05-12 23:23:02'),
(11, 13, NULL, 111, 'admin', 'admin@admin.com', 'olá no que podemos ajudar', 'interno', 'finalizada', '2026-05-12 23:23:02', '2026-05-12 23:19:53', '2026-05-12 23:23:02'),
(12, 21, NULL, NULL, 'ricardo', 'ricardo-s-a@hotmail.com', 'teste de hoije', 'cliente', 'aberta', NULL, '2026-05-25 20:09:56', '2026-05-25 20:09:56'),
(13, 4, NULL, NULL, 'Alpha', 'alpha@email.com', 'teste', 'cliente', 'aberta', NULL, '2026-05-25 20:54:21', '2026-05-25 20:54:21'),
(15, 4, NULL, NULL, 'Alpha', 'alpha@email.com', 'teste', 'cliente', 'aberta', NULL, '2026-05-25 21:58:19', '2026-05-25 21:58:19'),
(16, 21, NULL, NULL, 'ricardo empresa', 'ricardo-s-a@hotmail.com', 'sss', 'cliente', 'aberta', NULL, '2026-05-25 22:08:15', '2026-05-25 22:08:15'),
(17, 21, NULL, NULL, 'ricardo empresa', 'ricardo-s-a@hotmail.com', 'teste', 'cliente', 'aberta', NULL, '2026-05-25 22:27:07', '2026-05-25 22:27:07'),
(18, 21, NULL, NULL, 'ricardo empresa', 'ricardo-s-a@hotmail.com', 'teste', 'cliente', 'aberta', NULL, '2026-05-25 23:05:06', '2026-05-25 23:05:06'),
(19, 21, NULL, NULL, 'ricardo empresa', 'ricardo-s-a@hotmail.com', 'teste', 'cliente', 'aberta', NULL, '2026-05-25 23:10:30', '2026-05-25 23:10:30'),
(20, 21, NULL, NULL, 'ricardo empresa', 'ricardo-s-a@hotmail.com', 'teste', 'cliente', 'aberta', NULL, '2026-05-25 23:10:37', '2026-05-25 23:10:37'),
(21, 21, NULL, NULL, 'ricardo empresa', 'ricardo-s-a@hotmail.com', 'teste', 'cliente', 'aberta', NULL, '2026-05-25 23:16:22', '2026-05-25 23:16:22'),
(22, 21, NULL, NULL, 'ricardo empresa', 'ricardo-s-a@hotmail.com', 'teste,', 'cliente', 'aberta', NULL, '2026-05-25 23:18:19', '2026-05-25 23:18:19'),
(23, 21, NULL, NULL, 'ricardo empresa', 'ricardo-s-a@hotmail.com', 'teste 17:18', 'cliente', 'aberta', NULL, '2026-05-25 23:18:35', '2026-05-25 23:18:35'),
(24, 21, NULL, NULL, 'ricardo empresa', 'ricardo-s-a@hotmail.com', 'oi', 'cliente', 'aberta', NULL, '2026-05-25 23:19:03', '2026-05-25 23:19:03'),
(25, 21, NULL, NULL, 'ricardo empresa', 'ricardo-s-a@hotmail.com', 'ouasufbae', 'cliente', 'aberta', NULL, '2026-05-25 23:19:47', '2026-05-25 23:19:47');

-- --------------------------------------------------------

--
-- Estrutura da tabela `portal_solicitacoes`
--

CREATE TABLE `portal_solicitacoes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` longtext NOT NULL,
  `prioridade` enum('baixa','media','alta','urgente') NOT NULL DEFAULT 'media',
  `status` enum('aberto','em_andamento','aguardando_cliente','aguardando_equipe','em_aprovacao','concluido','concluida','finalizado','finalizada','cancelado','cancelada') NOT NULL DEFAULT 'aberto',
  `origem` varchar(50) DEFAULT 'cliente',
  `resposta` longtext DEFAULT NULL,
  `respondido_por` bigint(20) UNSIGNED DEFAULT NULL,
  `respondido_em` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `portal_solicitacoes`
--

INSERT INTO `portal_solicitacoes` (`id`, `empresa_id`, `item_controle_id`, `user_id`, `titulo`, `descricao`, `prioridade`, `status`, `origem`, `resposta`, `respondido_por`, `respondido_em`, `created_at`, `updated_at`) VALUES
(1, 21, NULL, NULL, 'Ajustar texto da página inicial', 'Cliente solicitou alteração no texto principal da home para ficar mais comercial.', 'media', 'aberto', 'cliente', NULL, NULL, NULL, '2026-05-08 17:19:14', '2026-05-08 17:19:14'),
(2, 21, NULL, NULL, 'Revisar integração do formulário', 'Validar se os leads enviados pelo formulário estão chegando corretamente no sistema.', 'alta', 'em_andamento', 'cliente', NULL, NULL, NULL, '2026-05-08 17:19:14', '2026-05-08 17:19:14'),
(3, 21, NULL, NULL, 'Aprovar layout da área do cliente', 'Cliente precisa revisar a proposta visual antes da implementação final.', 'media', 'em_aprovacao', 'cliente', NULL, NULL, NULL, '2026-05-08 17:19:14', '2026-05-08 17:19:14'),
(4, 22, NULL, 111, 'edfg', 'kikkk', 'alta', 'aberto', 'cliente', NULL, NULL, NULL, '2026-05-08 20:41:03', '2026-05-08 20:41:03'),
(5, 21, 61, NULL, 'Alterar texto principal da home', 'Cliente pediu para trocar o título da primeira dobra por uma frase mais comercial e direta.', 'media', 'aberto', 'cliente', NULL, NULL, NULL, '2026-05-05 17:59:01', '2026-05-08 17:59:01'),
(6, 21, 62, NULL, 'Validar recebimento dos leads', 'Cliente quer confirmar se todos os testes feitos no formulário chegam corretamente para a equipe comercial.', 'alta', 'em_andamento', 'cliente', NULL, NULL, NULL, '2026-05-06 17:59:01', '2026-05-08 17:59:01'),
(7, 21, 61, NULL, 'Aprovar versão do layout', 'Cliente recebeu o layout e precisa aprovar ou solicitar ajustes antes da implementação final.', 'urgente', 'em_aprovacao', 'cliente', 'Aguardando retorno do cliente para seguir com a publicação.', NULL, NULL, '2026-05-07 17:59:01', '2026-05-08 17:59:01'),
(8, 21, NULL, NULL, 'Adicionar novo link útil na wiki', 'Cliente solicitou incluir um link para documentação interna no espaço de documentos do portal.', 'baixa', 'concluido', 'cliente', 'Link incluído na wiki do projeto.', NULL, '2026-05-08 17:59:01', '2026-05-03 17:59:01', '2026-05-08 17:59:01'),
(9, 13, NULL, NULL, 'teste mensagem', 'teste texto', 'urgente', 'aberto', 'cliente', NULL, NULL, NULL, '2026-05-12 23:17:38', '2026-05-12 23:17:38'),
(10, 6, NULL, NULL, 'teste mensagem', 'teste', 'alta', 'aberto', 'cliente', NULL, NULL, NULL, '2026-05-19 16:56:35', '2026-05-19 16:56:35');

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_automation_executions`
--

CREATE TABLE `prazzu_automation_executions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `automation_rule_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_controle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action_type` varchar(80) NOT NULL,
  `message` text DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `fingerprint` varchar(64) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_automation_rules`
--

CREATE TABLE `prazzu_automation_rules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `module` varchar(100) NOT NULL DEFAULT 'item_controles',
  `trigger_type` varchar(100) NOT NULL DEFAULT 'updated',
  `condition_field` varchar(100) NOT NULL,
  `condition_operator` varchar(20) NOT NULL DEFAULT '=',
  `condition_value` varchar(255) DEFAULT NULL,
  `action_type` varchar(100) NOT NULL,
  `action_value` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `prazzu_automation_rules`
--

INSERT INTO `prazzu_automation_rules` (`id`, `name`, `module`, `trigger_type`, `condition_field`, `condition_operator`, `condition_value`, `action_type`, `action_value`, `active`, `created_at`, `updated_at`) VALUES
(1, 'SLA vencido prioriza item', '', 'scheduled', 'sla_status', '=', 'vencido', 'prioridade', 'alta', 1, '2026-05-04 20:51:37', '2026-05-04 20:51:37'),
(2, 'Pagamento vencido entra na régua', 'cobrancas', 'scheduled', 'status', '=', 'OVERDUE', 'notificacao', 'Cliente entrou na régua interna de cobrança.', 1, '2026-05-04 20:51:37', '2026-05-04 20:51:37'),
(3, 'Aprovação concluída encerra etapa', 'portal-cliente', 'event', 'approval_status', '=', 'aprovado', 'status', 'concluido', 1, '2026-05-04 20:51:37', '2026-05-04 20:51:37'),
(4, 'Avisar documento vencendo em 30 dias', 'item_controles', 'documento_vencendo', 'data_vencimento', 'date_until', '30', 'notificacao', 'Documento vencendo em até 30 dias. Verifique responsável, anexos e aprovação antes do prazo.', 1, '2026-05-12 20:05:17', '2026-05-12 20:05:17'),
(5, 'Cobrar responsável por documento vencido', 'item_controles', 'documento_vencido', 'data_vencimento', 'date_overdue', '', 'cobrar_responsavel', 'Documento vencido. Regularize o item e atualize os anexos/status.', 1, '2026-05-12 20:05:17', '2026-05-12 20:05:17'),
(6, 'Avisar aprovação pendente', 'item_controles', 'aprovacao_pendente', 'approval_status', '=', 'pendente', 'notificacao', 'Aprovação pendente. Revise o documento/processo para não travar a operação.', 1, '2026-05-12 20:05:17', '2026-05-12 20:05:17'),
(7, 'Avisar assinatura pendente', 'item_controles', 'assinatura_pendente', 'status', '=', 'pendente', 'notificacao', 'Assinatura pendente. Confira o responsável e acione o cliente quando necessário.', 1, '2026-05-12 20:05:17', '2026-05-12 20:05:17');

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_billing_locks`
--

CREATE TABLE `prazzu_billing_locks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `pagamento_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `locked_at` datetime NOT NULL,
  `unlocked_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_billing_rules`
--

CREATE TABLE `prazzu_billing_rules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(180) NOT NULL,
  `days_after_due` int(11) NOT NULL DEFAULT 0,
  `action_type` varchar(80) NOT NULL DEFAULT 'notify',
  `message` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_client_messages`
--

CREATE TABLE `prazzu_client_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_controle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_name` varchar(180) DEFAULT NULL,
  `client_email` varchar(180) DEFAULT NULL,
  `message` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `direction` varchar(20) NOT NULL DEFAULT 'internal_to_client',
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_client_portal_messages`
--

CREATE TABLE `prazzu_client_portal_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_controle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `client_email` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `sender_type` varchar(20) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_custom_fields`
--

CREATE TABLE `prazzu_custom_fields` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(100) NOT NULL,
  `field_key` varchar(100) NOT NULL,
  `field_label` varchar(255) NOT NULL,
  `field_type` varchar(50) NOT NULL DEFAULT 'text',
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `required` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_custom_field_values`
--

CREATE TABLE `prazzu_custom_field_values` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `custom_field_id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(120) NOT NULL DEFAULT 'item_controle',
  `entity_id` bigint(20) UNSIGNED NOT NULL,
  `value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_dependencies`
--

CREATE TABLE `prazzu_dependencies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `depends_on_item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'finish_to_start',
  `notes` text DEFAULT NULL,
  `blocked_until_resolved` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_document_versions`
--

CREATE TABLE `prazzu_document_versions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` varchar(100) NOT NULL DEFAULT 'documento',
  `version_number` int(11) NOT NULL DEFAULT 1,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pendente',
  `notes` text DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_permissions`
--

CREATE TABLE `prazzu_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(80) NOT NULL,
  `action` varchar(80) NOT NULL,
  `scope` varchar(80) NOT NULL DEFAULT 'all',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `prazzu_permissions`
--

INSERT INTO `prazzu_permissions` (`id`, `role_id`, `module`, `action`, `scope`, `created_at`, `updated_at`, `name`) VALUES
(3, 6, 'segurança', 'visibility', 'private_default', '2026-05-06 11:37:52', '2026-05-06 11:37:52', 'Visibilidade padrão privada'),
(4, 11, 'workflow', 'manage_tags_status', 'admin_or_gestor', '2026-05-06 11:37:52', '2026-05-06 11:37:52', 'Gestão centralizada de tags e status');

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_permission_rules`
--

CREATE TABLE `prazzu_permission_rules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(50) NOT NULL,
  `module` varchar(100) NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 1,
  `can_create` tinyint(1) NOT NULL DEFAULT 0,
  `can_update` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  `scope` varchar(50) NOT NULL DEFAULT 'empresa',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `prazzu_permission_rules`
--

INSERT INTO `prazzu_permission_rules` (`id`, `role`, `module`, `can_view`, `can_create`, `can_update`, `can_delete`, `scope`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'clientes', 1, 1, 1, 1, 'global', '2026-05-04 20:35:07', '2026-05-04 20:35:07'),
(2, 'admin', 'clientes', 1, 1, 1, 1, 'empresa', '2026-05-04 20:35:07', '2026-05-04 20:35:07'),
(3, 'gestor', 'clientes', 1, 1, 1, 0, 'empresa', '2026-05-04 20:35:07', '2026-05-04 20:35:07'),
(4, 'user', 'clientes', 1, 0, 1, 0, 'proprio', '2026-05-04 20:35:07', '2026-05-04 20:35:07'),
(5, 'super_admin', 'financeiro', 1, 1, 1, 1, 'global', '2026-05-04 20:35:07', '2026-05-04 20:35:07'),
(6, 'admin', 'financeiro', 1, 1, 1, 0, 'empresa', '2026-05-04 20:35:07', '2026-05-04 20:35:07'),
(7, 'gestor', 'financeiro', 0, 0, 0, 0, 'empresa', '2026-05-04 20:35:07', '2026-05-04 20:35:07'),
(8, 'user', 'financeiro', 0, 0, 0, 0, 'proprio', '2026-05-04 20:35:07', '2026-05-04 20:35:07'),
(9, 'super_admin', 'configuracoes', 1, 1, 1, 1, 'global', '2026-05-04 20:35:07', '2026-05-04 20:35:07'),
(10, 'admin', 'configuracoes', 1, 1, 1, 0, 'empresa', '2026-05-04 20:35:07', '2026-05-04 20:35:07'),
(11, 'gestor', 'configuracoes', 0, 0, 0, 0, 'empresa', '2026-05-04 20:35:07', '2026-05-04 20:35:07'),
(12, 'user', 'configuracoes', 0, 0, 0, 0, 'proprio', '2026-05-04 20:35:07', '2026-05-04 20:35:07'),
(13, 'Admin', 'Todos os módulos', 1, 1, 1, 1, 'empresa', '2026-05-06 11:33:22', '2026-05-06 11:33:22'),
(14, 'Member', 'Operação', 1, 1, 1, 0, 'responsável/equipe', '2026-05-06 11:33:22', '2026-05-06 11:33:22'),
(15, 'Guest', 'Compartilhados', 1, 0, 0, 0, 'compartilhado', '2026-05-06 11:33:22', '2026-05-06 11:33:22'),
(16, 'Estagiário', 'Operação', 1, 1, 1, 0, 'responsável/equipe', '2026-05-06 11:33:22', '2026-05-06 11:33:22'),
(17, 'Visualizador Externo', 'Compartilhados', 1, 0, 0, 0, 'compartilhado', '2026-05-06 11:33:22', '2026-05-06 11:33:22'),
(18, 'Gestor', 'Operação + Gestão', 1, 1, 1, 0, 'equipe', '2026-05-06 11:37:52', '2026-05-06 11:37:52');

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_roles`
--

CREATE TABLE `prazzu_roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `prazzu_roles`
--

INSERT INTO `prazzu_roles` (`id`, `name`, `description`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'Acesso total à plataforma.', 1, '2026-05-04 20:51:37', '2026-05-04 20:51:37'),
(2, 'Operação', 'Acesso operacional a clientes, atendimentos, pendências e documentos.', 1, '2026-05-04 20:51:37', '2026-05-04 20:51:37'),
(3, 'Financeiro', 'Acesso ao módulo financeiro, cobranças e assinaturas.', 1, '2026-05-04 20:51:37', '2026-05-04 20:51:37'),
(4, 'Compliance', 'Acesso a auditoria, riscos, evidências e SLA.', 1, '2026-05-04 20:51:37', '2026-05-04 20:51:37'),
(5, 'Cliente Portal', 'Perfil externo limitado ao portal do cliente.', 1, '2026-05-04 20:51:37', '2026-05-04 20:51:37'),
(6, 'Admin', 'Administrador da empresa com acesso amplo aos módulos internos.', 1, '2026-05-06 11:33:22', '2026-05-06 11:33:22'),
(7, 'Member', 'Usuário interno com acesso operacional controlado.', 1, '2026-05-06 11:33:22', '2026-05-06 11:33:22'),
(8, 'Guest', 'Convidado externo com acesso limitado ao que foi compartilhado.', 1, '2026-05-06 11:33:22', '2026-05-06 11:33:22'),
(9, 'Estagiário', 'Perfil operacional restrito, sem exclusão e sem exportação.', 1, '2026-05-06 11:33:22', '2026-05-06 11:33:22'),
(10, 'Visualizador Externo', 'Perfil somente leitura para cliente, auditor externo ou freelancer.', 1, '2026-05-06 11:33:22', '2026-05-06 11:33:22'),
(11, 'Gestor', 'Gestor interno com acesso administrativo parcial.', 1, '2026-05-06 11:37:51', '2026-05-06 11:37:51'),
(12, 'Sistema - Segurança', 'Cargo interno usado para regras sensíveis globais do sistema.', 1, '2026-05-13 11:15:19', '2026-05-13 11:15:19');

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_sla_policies`
--

CREATE TABLE `prazzu_sla_policies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(80) DEFAULT NULL,
  `name` varchar(180) NOT NULL,
  `priority` varchar(50) DEFAULT NULL,
  `hours_limit` int(11) NOT NULL DEFAULT 24,
  `warning_hours` int(11) NOT NULL DEFAULT 8,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_sla_rules`
--

CREATE TABLE `prazzu_sla_rules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `module` varchar(100) NOT NULL DEFAULT 'item_controles',
  `priority` varchar(50) DEFAULT NULL,
  `hours_limit` int(11) NOT NULL DEFAULT 24,
  `warning_hours` int(11) NOT NULL DEFAULT 8,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `prazzu_sla_rules`
--

INSERT INTO `prazzu_sla_rules` (`id`, `name`, `module`, `priority`, `hours_limit`, `warning_hours`, `active`, `created_at`, `updated_at`) VALUES
(1, 'SLA crítico - 4h', 'item_controles', 'critica', 4, 1, 1, '2026-05-04 20:35:07', '2026-05-04 20:35:07'),
(2, 'SLA alta - 8h', 'item_controles', 'alta', 8, 2, 1, '2026-05-04 20:35:07', '2026-05-04 20:35:07'),
(3, 'SLA média - 24h', 'item_controles', 'media', 24, 8, 1, '2026-05-04 20:35:07', '2026-05-04 20:35:07');

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_subtasks`
--

CREATE TABLE `prazzu_subtasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pendente',
  `priority` varchar(50) NOT NULL DEFAULT 'media',
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_task_comments`
--

CREATE TABLE `prazzu_task_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `comment` text NOT NULL,
  `mentions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`mentions`)),
  `is_internal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_task_dependencies`
--

CREATE TABLE `prazzu_task_dependencies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `depends_on_item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `dependency_type` varchar(50) NOT NULL DEFAULT 'bloqueia',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_task_subtasks`
--

CREATE TABLE `prazzu_task_subtasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pendente',
  `priority` varchar(50) NOT NULL DEFAULT 'normal',
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_teams`
--

CREATE TABLE `prazzu_teams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `prazzu_teams`
--

INSERT INTO `prazzu_teams` (`id`, `name`, `description`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Administrativo', 'Equipe administrativa e backoffice.', 1, '2026-05-06 11:37:52', '2026-05-06 11:37:52'),
(2, 'Operacional', 'Equipe responsável pela operação e execução dos itens.', 1, '2026-05-06 11:37:52', '2026-05-06 11:37:52'),
(3, 'Financeiro', 'Equipe com acesso financeiro controlado.', 1, '2026-05-06 11:37:52', '2026-05-06 11:37:52');

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_team_user`
--

CREATE TABLE `prazzu_team_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_templates`
--

CREATE TABLE `prazzu_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(80) NOT NULL,
  `name` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_time_entries`
--

CREATE TABLE `prazzu_time_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `total_seconds` int(11) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_time_tracking`
--

CREATE TABLE `prazzu_time_tracking` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_controle_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `total_seconds` int(11) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_user_roles`
--

CREATE TABLE `prazzu_user_roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `relatorios_personalizados`
--

CREATE TABLE `relatorios_personalizados` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` varchar(1000) DEFAULT NULL,
  `fonte` varchar(100) NOT NULL DEFAULT 'item_controles',
  `formato_padrao` varchar(20) NOT NULL DEFAULT 'tela',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `publico` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `relatorios_personalizados`
--

INSERT INTO `relatorios_personalizados` (`id`, `empresa_id`, `user_id`, `nome`, `descricao`, `fonte`, `formato_padrao`, `ativo`, `publico`, `created_at`, `updated_at`) VALUES
(1, 4, 111, 'teste', 'teste', 'item_controles', 'tela', 1, 1, '2026-04-29 23:07:33', '2026-04-29 23:07:33');

-- --------------------------------------------------------

--
-- Estrutura da tabela `relatorios_personalizados_colunas`
--

CREATE TABLE `relatorios_personalizados_colunas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `relatorio_id` bigint(20) UNSIGNED NOT NULL,
  `campo` varchar(100) NOT NULL,
  `rotulo` varchar(255) DEFAULT NULL,
  `tipo` varchar(50) NOT NULL DEFAULT 'texto',
  `ordem` int(11) NOT NULL DEFAULT 1,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `relatorios_personalizados_filtros`
--

CREATE TABLE `relatorios_personalizados_filtros` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `relatorio_id` bigint(20) UNSIGNED NOT NULL,
  `campo` varchar(100) NOT NULL,
  `operador` varchar(50) NOT NULL DEFAULT 'igual',
  `valor_padrao` varchar(255) DEFAULT NULL,
  `rotulo` varchar(255) DEFAULT NULL,
  `ordem` int(11) NOT NULL DEFAULT 1,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `obrigatorio` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `responsaveis`
--

CREATE TABLE `responsaveis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefone` varchar(255) DEFAULT NULL,
  `cargo` varchar(255) DEFAULT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `gestor_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `responsaveis`
--

INSERT INTO `responsaveis` (`id`, `nome`, `email`, `telefone`, `cargo`, `empresa_id`, `user_id`, `gestor_user_id`, `created_at`, `updated_at`) VALUES
(147, 'Admin Alpha', 'admin.alpha@empresa.com', NULL, 'Administrador', 4, 371, NULL, '2026-04-24 17:57:10', '2026-04-24 17:57:10'),
(148, 'Gestor Alpha', 'gestor.alpha@empresa.com', NULL, 'Gestor', 4, 372, NULL, '2026-04-24 17:57:10', '2026-04-24 17:57:10'),
(149, 'User Alpha 1', 'user.alpha1@empresa.com', NULL, 'Funcionário', 4, 373, 372, '2026-04-24 17:57:10', '2026-04-24 17:57:10'),
(150, 'User Alpha 2', 'user.alpha2@empresa.com', NULL, 'Funcionário', 4, 374, 372, '2026-04-24 17:57:10', '2026-04-24 17:57:10'),
(151, 'User Alpha 3', 'user.alpha3@empresa.com', NULL, 'Funcionário', 4, 375, 372, '2026-04-24 17:57:10', '2026-04-24 17:57:10'),
(154, 'Admin Beta', 'admin.beta@empresa.com', NULL, 'Administrador', 5, 376, NULL, '2026-04-24 17:57:10', '2026-04-24 17:57:10'),
(155, 'Gestor Beta', 'gestor.beta@empresa.com', NULL, 'Gestor', 5, 377, NULL, '2026-04-24 17:57:10', '2026-04-24 17:57:10'),
(156, 'User Beta 1', 'user.beta1@empresa.com', NULL, 'Funcionário', 5, 378, 377, '2026-04-24 17:57:10', '2026-04-24 17:57:10'),
(157, 'User Beta 2', 'user.beta2@empresa.com', NULL, 'Funcionário', 5, 379, 377, '2026-04-24 17:57:10', '2026-04-24 17:57:10'),
(158, 'User Beta 3', 'user.beta3@empresa.com', NULL, 'Funcionário', 5, 380, 377, '2026-04-24 17:57:10', '2026-04-24 17:57:10'),
(161, 'Admin Gamma', 'admin.gamma@empresa.com', NULL, 'Administrador', 6, 381, NULL, '2026-04-24 17:57:10', '2026-04-24 17:57:10'),
(162, 'Gestor Gamma', 'gestor.gamma@empresa.com', NULL, 'Gestor', 6, 382, NULL, '2026-04-24 17:57:10', '2026-04-24 17:57:10'),
(163, 'User Gamma 1', 'user.gamma1@empresa.com', NULL, 'Funcionário', 6, 383, 382, '2026-04-24 17:57:10', '2026-04-24 17:57:10'),
(164, 'User Gamma 2', 'user.gamma2@empresa.com', NULL, 'Funcionário', 6, 384, 382, '2026-04-24 17:57:10', '2026-04-24 17:57:10'),
(165, 'User Gamma 3', 'user.gamma3@empresa.com', NULL, 'Funcionário', 6, 385, 382, '2026-04-24 17:57:10', '2026-04-24 17:57:10'),
(168, 'Responsável Seed Teste', 'responsavel.seed@prazzu.com', '11988887777', 'Responsável Operacional', 11, 396, NULL, '2026-04-28 15:06:22', '2026-04-28 15:06:22'),
(169, 'Responsável Portal Cliente Empresa 21', 'responsavel.portal.21@teste.local', NULL, 'Responsável Operacional', 21, NULL, NULL, '2026-05-08 17:59:00', '2026-05-08 17:59:00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `sla_rules`
--

CREATE TABLE `sla_rules` (
  `id` bigint(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `hours_limit` int(11) DEFAULT 24,
  `warning_hours` int(11) DEFAULT 4,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `sugestoes_melhorias`
--

CREATE TABLE `sugestoes_melhorias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo` varchar(50) NOT NULL DEFAULT 'melhoria',
  `prioridade` varchar(50) NOT NULL DEFAULT 'media',
  `status` varchar(50) NOT NULL DEFAULT 'aberta',
  `titulo` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `resposta_admin` text DEFAULT NULL,
  `analisado_por` bigint(20) UNSIGNED DEFAULT NULL,
  `analisado_em` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `sugestoes_melhorias`
--

INSERT INTO `sugestoes_melhorias` (`id`, `empresa_id`, `user_id`, `tipo`, `prioridade`, `status`, `titulo`, `descricao`, `resposta_admin`, `analisado_por`, `analisado_em`, `created_at`, `updated_at`) VALUES
(2, 6, 383, 'funcionalidade', 'media', 'aceita', 'xxxx xxxxx xxxxx xxxxxx xxxxxx xxxx', 'xxxxxx xxxxx xxxxx xxxxxx xxxxx xxx xxxx xxx xxxx xxx xxxx xxxx xxxx xxxxx xx xx', 'YYYYYY YYYYY YYYYY YYYYY YYYY YYYY\nZZZZZ ZZZZ ZZZZ ZZZZ ZZZZ ZZZ ZZZ ZZ Z', 111, '2026-04-26 00:09:54', '2026-04-25 23:31:43', '2026-04-26 00:09:54');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tags`
--

CREATE TABLE `tags` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `task_comments`
--

CREATE TABLE `task_comments` (
  `id` bigint(20) NOT NULL,
  `task_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `parent_id` bigint(20) DEFAULT NULL,
  `comment` text NOT NULL,
  `mentions` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `task_dependencies`
--

CREATE TABLE `task_dependencies` (
  `id` bigint(20) NOT NULL,
  `task_id` bigint(20) NOT NULL,
  `depends_on_task_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `task_subtasks`
--

CREATE TABLE `task_subtasks` (
  `id` bigint(20) NOT NULL,
  `task_id` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pendente',
  `priority` varchar(50) DEFAULT 'normal',
  `assigned_to` bigint(20) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `task_tags`
--

CREATE TABLE `task_tags` (
  `id` bigint(20) NOT NULL,
  `task_id` bigint(20) NOT NULL,
  `tag_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `last_access_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'user',
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `last_access_at`, `last_login_at`, `last_seen_at`, `created_at`, `updated_at`, `role`, `empresa_id`) VALUES
(111, 'admin', 'admin@admin.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-17 22:11:30', '2026-05-25 18:21:44', 'super_admin', NULL),
(371, 'Admin Alpha', 'admin.alpha@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'admin', 4),
(372, 'Gestor Alpha', 'gestor.alpha@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'gestor', 4),
(373, 'User Alpha 1', 'user.alpha1@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'user', 4),
(374, 'User Alpha 2', 'user.alpha2@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'user', 4),
(375, 'User Alpha 3', 'user.alpha3@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'user', 4),
(376, 'Admin Beta', 'admin.beta@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', 'TPNOFZ4qWE6wMAdNtdrZ4ygoBLNLO4jrXcBYAR1TjJbFz2C0QWteE4SBpm4l', NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'admin', 5),
(377, 'Gestor Beta', 'gestor.beta@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'gestor', 5),
(378, 'User Beta 1', 'user.beta1@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'user', 5),
(379, 'User Beta 2', 'user.beta2@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'user', 5),
(380, 'User Beta 3', 'user.beta3@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'user', 5),
(381, 'Admin Gamma', 'admin.gamma@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'admin', 6),
(382, 'Gestor Gamma', 'gestor.gamma@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'gestor', 6),
(383, 'User Gamma 1', 'user.gamma1@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'user', 6),
(384, 'User Gamma 2', 'user.gamma2@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'user', 6),
(385, 'User Gamma 3', 'user.gamma3@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'guest', 6),
(386, 'Admin Teste Completo', 'admin.teste.completo@prazzu.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-28 14:58:04', '2026-05-25 18:21:44', 'admin', 7),
(387, 'Gestor Teste Completo', 'gestor.teste.completo@prazzu.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-28 14:58:04', '2026-05-25 18:21:44', 'gestor', 7),
(388, 'Usuário Teste Completo', 'usuario.teste.completo@prazzu.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-28 14:58:04', '2026-05-25 18:21:44', 'user', 7),
(396, 'Admin Seed Teste', 'admin.seed@prazzu.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-28 15:06:22', '2026-05-25 18:21:44', 'admin', 11),
(398, 'Roni', 'webconta@webconta.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-05-01 19:09:38', '2026-05-25 18:21:44', 'admin', 13),
(401, 'Roni2', 'webconta@webconta2.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-05-01 20:35:39', '2026-05-25 18:21:44', 'admin', 18),
(404, 'ricardo empresa', 'ricardo-s-a@hotmail.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-05-01 20:53:08', '2026-05-25 18:21:44', 'admin', 21),
(405, 'joyce', 'joyce@joyce.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-05-01 20:59:10', '2026-05-25 18:21:44', 'admin', 22);

-- --------------------------------------------------------

--
-- Estrutura da tabela `white_label`
--

CREATE TABLE `white_label` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nome_sistema` varchar(255) DEFAULT NULL,
  `cor_primaria` varchar(20) DEFAULT NULL,
  `cor_secundaria` varchar(20) DEFAULT NULL,
  `cor_destaque` varchar(20) DEFAULT NULL,
  `cor_sidebar` varchar(20) DEFAULT NULL,
  `cor_botoes` varchar(20) DEFAULT NULL,
  `logo_light` varchar(255) DEFAULT NULL,
  `logo_dark` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `logo_login` varchar(255) DEFAULT NULL,
  `logo_email` varchar(255) DEFAULT NULL,
  `login_background` varchar(255) DEFAULT NULL,
  `login_titulo` varchar(255) DEFAULT NULL,
  `login_subtitulo` varchar(500) DEFAULT NULL,
  `email_nome` varchar(255) DEFAULT NULL,
  `email_endereco` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `white_label`
--

INSERT INTO `white_label` (`id`, `nome_sistema`, `cor_primaria`, `cor_secundaria`, `cor_destaque`, `cor_sidebar`, `cor_botoes`, `logo_light`, `logo_dark`, `favicon`, `logo_login`, `logo_email`, `login_background`, `login_titulo`, `login_subtitulo`, `email_nome`, `email_endereco`, `created_at`, `updated_at`) VALUES
(1, 'Gestão de Controle', '#2e0af5', '#ff0000', '#22c55e', '#0f172a', '#2e0af5', NULL, NULL, NULL, NULL, NULL, NULL, 'Gestão de Controle', 'Acesse sua conta para continuar', 'Gestão de Controle', 'no-reply@example.com', '2026-05-14 19:16:19', '2026-05-14 19:16:19');

-- --------------------------------------------------------

--
-- Estrutura para vista `item_controle_timelines`
--
DROP TABLE IF EXISTS `item_controle_timelines`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `item_controle_timelines`  AS SELECT `item_controle_timeline`.`id` AS `id`, `item_controle_timeline`.`item_controle_id` AS `item_controle_id`, `item_controle_timeline`.`empresa_id` AS `empresa_id`, `item_controle_timeline`.`user_id` AS `user_id`, `item_controle_timeline`.`tipo` AS `tipo`, `item_controle_timeline`.`titulo` AS `titulo`, `item_controle_timeline`.`descricao` AS `descricao`, `item_controle_timeline`.`dados` AS `dados`, `item_controle_timeline`.`created_at` AS `created_at`, `item_controle_timeline`.`updated_at` AS `updated_at` FROM `item_controle_timeline` ;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `log_name_index` (`log_name`),
  ADD KEY `idx_activity_subject` (`subject_type`,`subject_id`),
  ADD KEY `idx_activity_causer` (`causer_type`,`causer_id`),
  ADD KEY `idx_activity_log_subject` (`subject_type`,`subject_id`),
  ADD KEY `idx_activity_log_causer` (`causer_type`,`causer_id`),
  ADD KEY `idx_activity_log_created_at` (`created_at`);

--
-- Índices para tabela `ai_market_comments`
--
ALTER TABLE `ai_market_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_market_comments_source_id_index` (`source_id`),
  ADD KEY `ai_market_comments_competitor_name_index` (`competitor_name`),
  ADD KEY `ai_market_comments_rating_index` (`rating`),
  ADD KEY `ai_market_comments_language_index` (`language`),
  ADD KEY `ai_market_comments_detected_sentiment_index` (`detected_sentiment`),
  ADD KEY `ai_market_comments_detected_category_index` (`detected_category`),
  ADD KEY `ai_market_comments_detected_impact_index` (`detected_impact`),
  ADD KEY `ai_market_comments_created_at_index` (`created_at`);

--
-- Índices para tabela `ai_market_sources`
--
ALTER TABLE `ai_market_sources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_market_sources_competitor_name_index` (`competitor_name`),
  ADD KEY `ai_market_sources_source_type_index` (`source_type`),
  ADD KEY `ai_market_sources_is_active_index` (`is_active`),
  ADD KEY `ai_market_sources_name_competitor_type_index` (`name`,`competitor_name`,`source_type`);

--
-- Índices para tabela `alerta_enviados`
--
ALTER TABLE `alerta_enviados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alerta_enviados_item_controle_id_foreign` (`item_controle_id`);

--
-- Índices para tabela `anexos`
--
ALTER TABLE `anexos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `anexo_items`
--
ALTER TABLE `anexo_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `anexo_items_item_controle_id_foreign` (`item_controle_id`);

--
-- Índices para tabela `assinaturas`
--
ALTER TABLE `assinaturas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `atendimentos`
--
ALTER TABLE `atendimentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_atendimentos_portal_solicitacao` (`portal_solicitacao_id`),
  ADD UNIQUE KEY `ux_atendimentos_portal_mensagem` (`portal_mensagem_id`),
  ADD KEY `idx_atendimentos_empresa_status` (`empresa_id`,`status`),
  ADD KEY `idx_atendimentos_empresa_prioridade` (`empresa_id`,`prioridade`),
  ADD KEY `idx_atendimentos_responsavel_status` (`responsavel_id`,`status`),
  ADD KEY `idx_atendimentos_sla` (`sla_limite_em`),
  ADD KEY `idx_atendimentos_crm_cliente` (`crm_cliente_id`),
  ADD KEY `idx_atendimentos_portal_solicitacao` (`portal_solicitacao_id`),
  ADD KEY `idx_atendimentos_portal_mensagem` (`portal_mensagem_id`),
  ADD KEY `idx_atendimentos_item_controle` (`item_controle_id`),
  ADD KEY `idx_atendimentos_created_at` (`created_at`),
  ADD KEY `fk_atendimentos_criado_por` (`criado_por`);

--
-- Índices para tabela `atendimento_interacoes`
--
ALTER TABLE `atendimento_interacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_atendimento_interacoes_atendimento_created` (`atendimento_id`,`created_at`),
  ADD KEY `idx_atendimento_interacoes_user` (`user_id`);

--
-- Índices para tabela `auditoria_detalhada`
--
ALTER TABLE `auditoria_detalhada`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_auditoria_empresa` (`empresa_id`,`created_at`),
  ADD KEY `idx_auditoria_user` (`user_id`,`created_at`),
  ADD KEY `idx_auditoria_registro` (`auditable_type`,`auditable_id`),
  ADD KEY `idx_auditoria_evento` (`evento`,`created_at`);

--
-- Índices para tabela `audit_timeline`
--
ALTER TABLE `audit_timeline`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `automation_rules`
--
ALTER TABLE `automation_rules`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Índices para tabela `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Índices para tabela `categorias_item_controle`
--
ALTER TABLE `categorias_item_controle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_categorias_item_controle_empresa_id` (`empresa_id`),
  ADD KEY `idx_categorias_item_controle_ativo` (`ativo`);

--
-- Índices para tabela `categoria_item_controle_checklist_templates`
--
ALTER TABLE `categoria_item_controle_checklist_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_checklist_templates_categoria` (`categoria_item_controle_id`),
  ADD KEY `idx_checklist_templates_ativo` (`ativo`);

--
-- Índices para tabela `cliente_portal_password_reset_tokens`
--
ALTER TABLE `cliente_portal_password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Índices para tabela `cliente_portal_users`
--
ALTER TABLE `cliente_portal_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cliente_portal_users_email_unique` (`email`),
  ADD KEY `cliente_portal_users_empresa_id_idx` (`empresa_id`),
  ADD KEY `cliente_portal_users_ativo_idx` (`ativo`);

--
-- Índices para tabela `client_portal_messages`
--
ALTER TABLE `client_portal_messages`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comentarios_item_controle_created` (`item_controle_id`,`created_at`),
  ADD KEY `idx_comentarios_item_created` (`item_controle_id`,`created_at`);

--
-- Índices para tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_configuracoes_empresa_id` (`empresa_id`),
  ADD KEY `idx_config_empresa` (`empresa_id`);

--
-- Índices para tabela `crm_clientes`
--
ALTER TABLE `crm_clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_crm_clientes_empresa_updated` (`empresa_id`,`updated_at`),
  ADD KEY `idx_crm_clientes_responsavel` (`responsavel_user_id`);

--
-- Índices para tabela `crm_historicos`
--
ALTER TABLE `crm_historicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_crm_historicos_cliente_created` (`crm_cliente_id`,`created_at`);

--
-- Índices para tabela `crm_pendencias`
--
ALTER TABLE `crm_pendencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_crm_pendencias_cliente_status_created` (`crm_cliente_id`,`status`,`created_at`);

--
-- Índices para tabela `dashboard_widget_configuracoes`
--
ALTER TABLE `dashboard_widget_configuracoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dash_widget_empresa` (`empresa_id`,`ativo`,`ordem`),
  ADD KEY `idx_dash_widget_user` (`user_id`);

--
-- Índices para tabela `document_versions`
--
ALTER TABLE `document_versions`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_empresas_razao_social` (`razao_social`),
  ADD KEY `idx_empresas_nome_fantasia` (`nome_fantasia`),
  ADD KEY `idx_empresas_cnpj` (`cnpj`),
  ADD KEY `idx_empresas_plano` (`plano`),
  ADD KEY `idx_empresas_ativo_plano` (`ativo`,`plano`),
  ADD KEY `idx_empresas_portal_token` (`portal_token`),
  ADD KEY `idx_empresas_nome_razao` (`nome_fantasia`,`razao_social`);

--
-- Índices para tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Índices para tabela `financeiro_assinaturas_cliente`
--
ALTER TABLE `financeiro_assinaturas_cliente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `financeiro_assinaturas_empresa_status_idx` (`empresa_id`,`status`),
  ADD KEY `financeiro_assinaturas_cliente_idx` (`financeiro_cliente_id`),
  ADD KEY `financeiro_assinaturas_proxima_idx` (`proxima_cobranca_em`),
  ADD KEY `financeiro_assinaturas_empresa_status_proxima_idx` (`empresa_id`,`status`,`proxima_cobranca_em`);

--
-- Índices para tabela `financeiro_clientes`
--
ALTER TABLE `financeiro_clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `financeiro_clientes_empresa_status_idx` (`empresa_id`,`status`),
  ADD KEY `financeiro_clientes_nome_idx` (`nome`),
  ADD KEY `financeiro_clientes_empresa_nome_idx` (`empresa_id`,`nome`);

--
-- Índices para tabela `financeiro_cobrancas`
--
ALTER TABLE `financeiro_cobrancas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `financeiro_cobrancas_empresa_status_idx` (`empresa_id`,`status`),
  ADD KEY `financeiro_cobrancas_cliente_idx` (`financeiro_cliente_id`),
  ADD KEY `financeiro_cobrancas_vencimento_idx` (`vencimento`),
  ADD KEY `financeiro_cobrancas_gateway_idx` (`gateway_payment_id`),
  ADD KEY `financeiro_cobrancas_empresa_status_vencimento_idx` (`empresa_id`,`status`,`vencimento`),
  ADD KEY `financeiro_cobrancas_assinatura_referencia_idx` (`financeiro_assinatura_id`,`referencia`);

--
-- Índices para tabela `financeiro_gateway_integracoes`
--
ALTER TABLE `financeiro_gateway_integracoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `financeiro_gateway_empresa_gateway_unique` (`empresa_id`,`gateway`),
  ADD KEY `financeiro_gateway_status_idx` (`status`),
  ADD KEY `financeiro_gateway_empresa_gateway_idx` (`empresa_id`,`gateway`);

--
-- Índices para tabela `financeiro_recebimentos`
--
ALTER TABLE `financeiro_recebimentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `financeiro_recebimentos_empresa_data_idx` (`empresa_id`,`recebido_em`),
  ADD KEY `financeiro_recebimentos_cobranca_idx` (`financeiro_cobranca_id`),
  ADD KEY `financeiro_recebimentos_cliente_idx` (`financeiro_cliente_id`),
  ADD KEY `financeiro_recebimentos_cobranca_origem_idx` (`financeiro_cobranca_id`,`origem`);

--
-- Índices para tabela `financeiro_webhook_logs`
--
ALTER TABLE `financeiro_webhook_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `financeiro_webhook_empresa_gateway_idx` (`empresa_id`,`gateway`),
  ADD KEY `financeiro_webhook_evento_idx` (`gateway_event_id`);

--
-- Índices para tabela `fluxos_operacionais`
--
ALTER TABLE `fluxos_operacionais`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fluxos_empresa` (`empresa_id`,`ativo`);

--
-- Índices para tabela `fluxos_operacionais_etapas`
--
ALTER TABLE `fluxos_operacionais_etapas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fluxos_etapas_fluxo` (`fluxo_operacional_id`,`ordem`);

--
-- Índices para tabela `fluxos_operacionais_execucoes`
--
ALTER TABLE `fluxos_operacionais_execucoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fluxos_exec_item` (`item_controle_id`,`status`),
  ADD KEY `idx_fluxos_exec_empresa` (`empresa_id`,`status`),
  ADD KEY `idx_fluxos_exec_fluxo` (`fluxo_operacional_id`),
  ADD KEY `idx_fluxos_exec_etapa` (`fluxo_operacional_etapa_id`);

--
-- Índices para tabela `historico_items`
--
ALTER TABLE `historico_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `historico_items_item_controle_id_foreign` (`item_controle_id`);

--
-- Índices para tabela `item_controles`
--
ALTER TABLE `item_controles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_controles_empresa_id_foreign` (`empresa_id`),
  ADD KEY `idx_item_controles_responsavel_id` (`responsavel_id`),
  ADD KEY `idx_item_controles_status` (`status`),
  ADD KEY `idx_item_controles_data_vencimento` (`data_vencimento`),
  ADD KEY `idx_item_controles_status_data_vencimento` (`status`,`data_vencimento`),
  ADD KEY `idx_item_controles_responsavel_status_vencimento` (`responsavel_id`,`status`,`data_vencimento`),
  ADD KEY `idx_item_controles_empresa_status_vencimento` (`empresa_id`,`status`,`data_vencimento`),
  ADD KEY `idx_notificacao_3_dias` (`notificado_3_dias`,`data_vencimento`),
  ADD KEY `idx_notificacao_hoje` (`notificado_no_dia`,`data_vencimento`),
  ADD KEY `idx_notificacao_vencido` (`notificado_vencido`,`data_vencimento`),
  ADD KEY `idx_lembrete_recorrente` (`ultimo_lembrete_enviado_em`),
  ADD KEY `idx_item_status` (`status`),
  ADD KEY `idx_item_vencimento` (`data_vencimento`),
  ADD KEY `idx_item_master` (`empresa_id`,`status`,`data_vencimento`),
  ADD KEY `idx_item_controles_categoria_id` (`categoria_id`),
  ADD KEY `item_controles_empresa_status_vencimento_idx` (`empresa_id`,`status`,`data_vencimento`),
  ADD KEY `item_controles_responsavel_status_vencimento_idx` (`responsavel_id`,`status`,`data_vencimento`),
  ADD KEY `item_controles_empresa_updated_idx` (`empresa_id`,`updated_at`),
  ADD KEY `item_controles_status_updated_idx` (`status`,`updated_at`),
  ADD KEY `item_controles_sla_status_idx` (`sla_status`),
  ADD KEY `item_controles_approval_status_idx` (`approval_status`),
  ADD KEY `item_controles_document_status_idx` (`document_status`),
  ADD KEY `item_controles_portal_ativo_empresa_idx` (`portal_ativo`,`empresa_id`),
  ADD KEY `item_controles_arquivo_idx` (`arquivo`),
  ADD KEY `idx_item_relatorios_prazo_status` (`data_vencimento`,`status`),
  ADD KEY `idx_item_relatorios_empresa_status` (`empresa_id`,`status`,`data_vencimento`),
  ADD KEY `idx_item_relatorios_responsavel_status` (`responsavel_id`,`status`,`data_vencimento`),
  ADD KEY `idx_item_relatorios_prioridade` (`prioridade`,`data_vencimento`),
  ADD KEY `idx_item_relatorios_approval` (`approval_status`,`data_vencimento`),
  ADD KEY `idx_item_relatorios_document` (`document_status`,`data_vencimento`),
  ADD KEY `idx_item_controles_empresa_status` (`empresa_id`,`status`),
  ADD KEY `idx_item_controles_responsavel_status` (`responsavel_id`,`status`),
  ADD KEY `idx_item_controles_vencimento` (`data_vencimento`),
  ADD KEY `idx_item_controles_portal_token` (`portal_token`),
  ADD KEY `idx_item_controles_status_operacional_at` (`status_operacional_at`),
  ADD KEY `idx_item_controles_empresa_vencimento` (`empresa_id`,`data_vencimento`),
  ADD KEY `idx_item_controles_urgencia` (`urgencia`),
  ADD KEY `idx_item_controles_bloqueado` (`bloqueado`),
  ADD KEY `idx_item_controles_faturado_em` (`faturado_em`),
  ADD KEY `idx_item_controles_pago_em` (`pago_em`),
  ADD KEY `idx_item_controles_sla_prazo_alvo_em` (`sla_prazo_alvo_em`),
  ADD KEY `idx_item_controles_empresa_updated` (`empresa_id`,`updated_at`),
  ADD KEY `idx_item_controles_tipo_status` (`tipo`,`status`),
  ADD KEY `idx_item_controles_status_vencimento` (`status`,`data_vencimento`),
  ADD KEY `idx_item_controles_responsavel_vencimento` (`responsavel_id`,`data_vencimento`),
  ADD KEY `idx_item_controles_portal_ativo` (`portal_ativo`),
  ADD KEY `idx_item_controles_trabalho_status_vencimento` (`status`,`data_vencimento`),
  ADD KEY `idx_item_controles_trabalho_tipo_vencimento_updated` (`tipo`,`data_vencimento`,`updated_at`),
  ADD KEY `idx_item_controles_trabalho_empresa_responsavel` (`empresa_id`,`responsavel_id`),
  ADD KEY `idx_item_controles_trabalho_prioridade` (`prioridade`),
  ADD KEY `idx_trabalho_tipo_status_vencimento_updated` (`tipo`,`status`,`data_vencimento`,`updated_at`),
  ADD KEY `idx_trabalho_empresa_tipo_updated` (`empresa_id`,`tipo`,`updated_at`),
  ADD KEY `idx_trabalho_responsavel_tipo_updated` (`responsavel_id`,`tipo`,`updated_at`),
  ADD KEY `idx_trabalho_prioridade_status` (`prioridade`,`status`);

--
-- Índices para tabela `item_controle_alertas`
--
ALTER TABLE `item_controle_alertas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_alerta_empresa` (`empresa_id`),
  ADD KEY `idx_alerta_item` (`item_controle_id`),
  ADD KEY `idx_alerta_status` (`status`);

--
-- Índices para tabela `item_controle_anexos`
--
ALTER TABLE `item_controle_anexos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_controle_anexos_item` (`item_controle_id`),
  ADD KEY `idx_item_controle_anexos_user` (`user_id`),
  ADD KEY `idx_anexo_item` (`item_controle_id`),
  ADD KEY `idx_item_controle_anexos_item_controle_id` (`item_controle_id`),
  ADD KEY `idx_item_controle_anexos_user_id` (`user_id`),
  ADD KEY `idx_item_controle_anexos_item_user` (`item_controle_id`,`user_id`),
  ADD KEY `idx_anexos_item_created` (`item_controle_id`,`created_at`);

--
-- Índices para tabela `item_controle_aprovacoes`
--
ALTER TABLE `item_controle_aprovacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_controle_aprovacoes_item` (`item_controle_id`),
  ADD KEY `idx_item_controle_aprovacoes_empresa` (`empresa_id`),
  ADD KEY `idx_item_controle_aprovacoes_status` (`status`),
  ADD KEY `idx_item_controle_aprovacoes_solicitante` (`solicitante_id`),
  ADD KEY `idx_item_controle_aprovacoes_aprovador` (`aprovador_id`),
  ADD KEY `idx_aprovacoes_empresa_status` (`empresa_id`,`status`),
  ADD KEY `idx_aprovacoes_aprovador_status` (`aprovador_id`,`status`),
  ADD KEY `idx_aprovacoes_item_status` (`item_controle_id`,`status`),
  ADD KEY `idx_item_controle_aprovacoes_item_status` (`item_controle_id`,`status`);

--
-- Índices para tabela `item_controle_assinaturas`
--
ALTER TABLE `item_controle_assinaturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_controle_assinaturas_item` (`item_controle_id`),
  ADD KEY `idx_item_controle_assinaturas_empresa` (`empresa_id`),
  ADD KEY `idx_item_controle_assinaturas_user` (`user_id`),
  ADD KEY `idx_item_controle_assinaturas_assinado_em` (`assinado_em`),
  ADD KEY `idx_assinaturas_pendentes_item` (`assinado_em`,`item_controle_id`,`empresa_id`);

--
-- Índices para tabela `item_controle_checklists`
--
ALTER TABLE `item_controle_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_controle_checklists_item` (`item_controle_id`),
  ADD KEY `idx_item_controle_checklists_concluido` (`concluido`),
  ADD KEY `idx_item_controle_checklists_ordem` (`ordem`),
  ADD KEY `idx_item_controle_checklists_item_concluido` (`item_controle_id`,`concluido`),
  ADD KEY `idx_checklists_item_concluido_ordem` (`item_controle_id`,`concluido`,`ordem`);

--
-- Índices para tabela `item_controle_comentarios`
--
ALTER TABLE `item_controle_comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_controle_comentarios_item` (`item_controle_id`),
  ADD KEY `idx_item_controle_comentarios_user` (`user_id`),
  ADD KEY `idx_comentario_item` (`item_controle_id`),
  ADD KEY `idx_item_controle_comentarios_item_controle_id` (`item_controle_id`),
  ADD KEY `idx_item_controle_comentarios_user_id` (`user_id`),
  ADD KEY `idx_item_controle_comentarios_item_user` (`item_controle_id`,`user_id`),
  ADD KEY `idx_comentarios_item_created` (`item_controle_id`,`created_at`);

--
-- Índices para tabela `item_controle_notificacao_logs`
--
ALTER TABLE `item_controle_notificacao_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_controle_notificacao_logs_item` (`item_controle_id`),
  ADD KEY `idx_item_controle_notificacao_logs_user` (`user_id`),
  ADD KEY `idx_item_controle_notificacao_logs_responsavel` (`responsavel_id`),
  ADD KEY `idx_item_controle_notificacao_logs_tipo_status` (`tipo_notificacao`,`status`);

--
-- Índices para tabela `item_controle_tags`
--
ALTER TABLE `item_controle_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_controle_tags_empresa_id` (`empresa_id`),
  ADD KEY `idx_item_controle_tags_ativo` (`ativo`);

--
-- Índices para tabela `item_controle_tag_relations`
--
ALTER TABLE `item_controle_tag_relations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_item_controle_tag_relation` (`item_controle_id`,`item_controle_tag_id`),
  ADD KEY `idx_item_controle_tag_relations_item` (`item_controle_id`),
  ADD KEY `idx_item_controle_tag_relations_tag` (`item_controle_tag_id`),
  ADD KEY `idx_tag_relations_item_tag` (`item_controle_id`,`item_controle_tag_id`),
  ADD KEY `idx_tag_relations_tag_item` (`item_controle_tag_id`,`item_controle_id`);

--
-- Índices para tabela `item_controle_timeline`
--
ALTER TABLE `item_controle_timeline`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_controle_timeline_item` (`item_controle_id`),
  ADD KEY `idx_item_controle_timeline_empresa` (`empresa_id`),
  ADD KEY `idx_item_controle_timeline_user` (`user_id`),
  ADD KEY `idx_item_controle_timeline_tipo` (`tipo`),
  ADD KEY `idx_timeline_item_tipo_created` (`item_controle_id`,`tipo`,`created_at`);

--
-- Índices para tabela `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Índices para tabela `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `notificacoes_internas`
--
ALTER TABLE `notificacoes_internas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notificacoes_internas_item` (`item_controle_id`),
  ADD KEY `idx_notificacoes_internas_empresa` (`empresa_id`),
  ADD KEY `idx_notificacoes_internas_user` (`user_id`),
  ADD KEY `idx_notificacoes_internas_lida` (`lida`),
  ADD KEY `idx_notificacoes_internas_tipo` (`tipo`),
  ADD KEY `notificacoes_internas_user_lida_created_idx` (`user_id`,`lida`,`created_at`),
  ADD KEY `notificacoes_internas_item_tipo_created_idx` (`item_controle_id`,`tipo`,`created_at`),
  ADD KEY `idx_notificacoes_user_lida_created` (`user_id`,`lida`,`created_at`),
  ADD KEY `idx_notificacoes_empresa_lida_created` (`empresa_id`,`lida`,`created_at`),
  ADD KEY `idx_notificacoes_item_created` (`item_controle_id`,`created_at`);

--
-- Índices para tabela `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`),
  ADD KEY `idx_notifications_notifiable` (`notifiable_type`,`notifiable_id`);

--
-- Índices para tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pagamentos_empresa_status` (`empresa_id`,`status`);

--
-- Índices para tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Índices para tabela `portal_cliente_tokens`
--
ALTER TABLE `portal_cliente_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `portal_cliente_tokens_email_tipo_idx` (`email`,`tipo`),
  ADD KEY `portal_cliente_tokens_cliente_idx` (`cliente_portal_user_id`),
  ADD KEY `portal_cliente_tokens_expires_idx` (`expires_at`);

--
-- Índices para tabela `portal_documentos`
--
ALTER TABLE `portal_documentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `portal_documentos_empresa_id_index` (`empresa_id`),
  ADD KEY `portal_documentos_item_controle_id_index` (`item_controle_id`),
  ADD KEY `portal_documentos_tipo_index` (`tipo`),
  ADD KEY `portal_documentos_visivel_cliente_index` (`visivel_cliente`),
  ADD KEY `portal_documentos_empresa_visivel_tipo_created_idx` (`empresa_id`,`visivel_cliente`,`tipo`,`created_at`),
  ADD KEY `idx_portal_documentos_empresa_updated` (`empresa_id`,`updated_at`,`created_at`);

--
-- Índices para tabela `portal_mensagens`
--
ALTER TABLE `portal_mensagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `portal_mensagens_empresa_id_index` (`empresa_id`),
  ADD KEY `portal_mensagens_item_controle_id_index` (`item_controle_id`),
  ADD KEY `portal_mensagens_origem_index` (`origem`),
  ADD KEY `portal_mensagens_visualizada_em_index` (`visualizada_em`),
  ADD KEY `portal_mensagens_empresa_status_created_idx` (`empresa_id`,`conversa_status`,`created_at`),
  ADD KEY `idx_portal_msg_empresa_status_created` (`empresa_id`,`conversa_status`,`created_at`),
  ADD KEY `idx_portal_mensagens_empresa_created` (`empresa_id`,`created_at`);

--
-- Índices para tabela `portal_solicitacoes`
--
ALTER TABLE `portal_solicitacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `portal_solicitacoes_empresa_id_index` (`empresa_id`),
  ADD KEY `portal_solicitacoes_item_controle_id_index` (`item_controle_id`),
  ADD KEY `portal_solicitacoes_status_index` (`status`),
  ADD KEY `portal_solicitacoes_prioridade_index` (`prioridade`),
  ADD KEY `portal_solicitacoes_empresa_status_created_idx` (`empresa_id`,`status`,`created_at`),
  ADD KEY `idx_portal_sol_empresa_status_created` (`empresa_id`,`status`,`created_at`),
  ADD KEY `idx_portal_solicitacoes_empresa_status_created` (`empresa_id`,`status`,`created_at`);

--
-- Índices para tabela `prazzu_automation_executions`
--
ALTER TABLE `prazzu_automation_executions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prazzu_automation_executions_fingerprint_unique` (`fingerprint`),
  ADD KEY `prazzu_automation_executions_rule_idx` (`automation_rule_id`),
  ADD KEY `prazzu_automation_executions_item_idx` (`item_controle_id`),
  ADD KEY `prazzu_automation_executions_empresa_idx` (`empresa_id`),
  ADD KEY `prazzu_automation_executions_created_idx` (`created_at`);

--
-- Índices para tabela `prazzu_automation_rules`
--
ALTER TABLE `prazzu_automation_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_par_module_active` (`module`,`active`),
  ADD KEY `prazzu_automation_rules_active_module_idx` (`active`,`module`),
  ADD KEY `prazzu_automation_rules_trigger_idx` (`trigger_type`);

--
-- Índices para tabela `prazzu_billing_locks`
--
ALTER TABLE `prazzu_billing_locks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prazzu_billing_locks_empresa` (`empresa_id`);

--
-- Índices para tabela `prazzu_billing_rules`
--
ALTER TABLE `prazzu_billing_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prazzu_billing_rules_active` (`active`);

--
-- Índices para tabela `prazzu_client_messages`
--
ALTER TABLE `prazzu_client_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prazzu_client_messages_empresa` (`empresa_id`),
  ADD KEY `idx_prazzu_client_messages_item` (`item_controle_id`);

--
-- Índices para tabela `prazzu_client_portal_messages`
--
ALTER TABLE `prazzu_client_portal_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pcpm_empresa` (`empresa_id`),
  ADD KEY `idx_pcpm_item` (`item_controle_id`),
  ADD KEY `idx_pcpm_email` (`client_email`),
  ADD KEY `idx_pcpm_read` (`read_at`);

--
-- Índices para tabela `prazzu_custom_fields`
--
ALTER TABLE `prazzu_custom_fields`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_module_field` (`module`,`field_key`);

--
-- Índices para tabela `prazzu_custom_field_values`
--
ALTER TABLE `prazzu_custom_field_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prazzu_custom_values_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_prazzu_custom_values_field` (`custom_field_id`);

--
-- Índices para tabela `prazzu_dependencies`
--
ALTER TABLE `prazzu_dependencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prazzu_dependencies_item` (`item_controle_id`),
  ADD KEY `idx_prazzu_dependencies_depends` (`depends_on_item_controle_id`);

--
-- Índices para tabela `prazzu_document_versions`
--
ALTER TABLE `prazzu_document_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pdv_item` (`item_controle_id`),
  ADD KEY `idx_pdv_status` (`status`),
  ADD KEY `idx_pdv_approved` (`approved_at`),
  ADD KEY `idx_prazzu_document_versions_item` (`item_controle_id`);

--
-- Índices para tabela `prazzu_permissions`
--
ALTER TABLE `prazzu_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prazzu_permissions_role` (`role_id`),
  ADD KEY `idx_prazzu_permissions_module` (`module`),
  ADD KEY `idx_prazzu_permissions_role_id` (`role_id`);

--
-- Índices para tabela `prazzu_permission_rules`
--
ALTER TABLE `prazzu_permission_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_role_module` (`role`,`module`);

--
-- Índices para tabela `prazzu_roles`
--
ALTER TABLE `prazzu_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_prazzu_roles_name` (`name`);

--
-- Índices para tabela `prazzu_sla_policies`
--
ALTER TABLE `prazzu_sla_policies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prazzu_sla_policies_module` (`module`);

--
-- Índices para tabela `prazzu_sla_rules`
--
ALTER TABLE `prazzu_sla_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_psr_module_priority` (`module`,`priority`,`active`);

--
-- Índices para tabela `prazzu_subtasks`
--
ALTER TABLE `prazzu_subtasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prazzu_subtasks_item` (`item_controle_id`),
  ADD KEY `idx_prazzu_subtasks_status` (`status`),
  ADD KEY `idx_prazzu_subtasks_due_date` (`due_date`);

--
-- Índices para tabela `prazzu_task_comments`
--
ALTER TABLE `prazzu_task_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ptc_item` (`item_controle_id`),
  ADD KEY `idx_ptc_user` (`user_id`),
  ADD KEY `idx_ptc_created` (`created_at`);

--
-- Índices para tabela `prazzu_task_dependencies`
--
ALTER TABLE `prazzu_task_dependencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ptd_item` (`item_controle_id`),
  ADD KEY `idx_ptd_depends` (`depends_on_item_controle_id`);

--
-- Índices para tabela `prazzu_task_subtasks`
--
ALTER TABLE `prazzu_task_subtasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pts_item` (`item_controle_id`),
  ADD KEY `idx_pts_assigned` (`assigned_to`),
  ADD KEY `idx_pts_status_due` (`status`,`due_date`);

--
-- Índices para tabela `prazzu_teams`
--
ALTER TABLE `prazzu_teams`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `prazzu_team_user`
--
ALTER TABLE `prazzu_team_user`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `prazzu_templates`
--
ALTER TABLE `prazzu_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prazzu_templates_module` (`module`);

--
-- Índices para tabela `prazzu_time_entries`
--
ALTER TABLE `prazzu_time_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prazzu_time_entries_item` (`item_controle_id`),
  ADD KEY `idx_prazzu_time_entries_user` (`user_id`);

--
-- Índices para tabela `prazzu_time_tracking`
--
ALTER TABLE `prazzu_time_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ptt_item` (`item_controle_id`),
  ADD KEY `idx_ptt_user` (`user_id`),
  ADD KEY `idx_ptt_started` (`started_at`);

--
-- Índices para tabela `prazzu_user_roles`
--
ALTER TABLE `prazzu_user_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prazzu_user_roles_user` (`user_id`),
  ADD KEY `idx_prazzu_user_roles_role` (`role_id`);

--
-- Índices para tabela `relatorios_personalizados`
--
ALTER TABLE `relatorios_personalizados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rel_pers_empresa` (`empresa_id`),
  ADD KEY `idx_rel_pers_user` (`user_id`);

--
-- Índices para tabela `relatorios_personalizados_colunas`
--
ALTER TABLE `relatorios_personalizados_colunas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rel_pers_col_rel` (`relatorio_id`);

--
-- Índices para tabela `relatorios_personalizados_filtros`
--
ALTER TABLE `relatorios_personalizados_filtros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rel_pers_fil_rel` (`relatorio_id`);

--
-- Índices para tabela `responsaveis`
--
ALTER TABLE `responsaveis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_responsaveis_user_id` (`user_id`),
  ADD KEY `fk_responsaveis_user_id` (`user_id`),
  ADD KEY `idx_responsaveis_gestor_user_id` (`gestor_user_id`),
  ADD KEY `idx_resp_user` (`user_id`),
  ADD KEY `idx_resp_gestor` (`gestor_user_id`),
  ADD KEY `idx_responsaveis_user_id` (`user_id`),
  ADD KEY `idx_responsaveis_empresa_id` (`empresa_id`),
  ADD KEY `idx_responsaveis_nome_email` (`nome`,`email`);

--
-- Índices para tabela `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Índices para tabela `sla_rules`
--
ALTER TABLE `sla_rules`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `sugestoes_melhorias`
--
ALTER TABLE `sugestoes_melhorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sugestoes_melhorias_empresa_id_index` (`empresa_id`),
  ADD KEY `sugestoes_melhorias_user_id_index` (`user_id`),
  ADD KEY `sugestoes_melhorias_status_index` (`status`),
  ADD KEY `sugestoes_melhorias_tipo_index` (`tipo`),
  ADD KEY `sugestoes_melhorias_prioridade_index` (`prioridade`),
  ADD KEY `sugestoes_melhorias_analisado_por_foreign` (`analisado_por`);

--
-- Índices para tabela `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `task_comments`
--
ALTER TABLE `task_comments`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `task_dependencies`
--
ALTER TABLE `task_dependencies`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `task_subtasks`
--
ALTER TABLE `task_subtasks`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `task_tags`
--
ALTER TABLE `task_tags`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `idx_users_empresa_id` (`empresa_id`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_email` (`email`);

--
-- Índices para tabela `white_label`
--
ALTER TABLE `white_label`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de tabela `ai_market_comments`
--
ALTER TABLE `ai_market_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `ai_market_sources`
--
ALTER TABLE `ai_market_sources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `alerta_enviados`
--
ALTER TABLE `alerta_enviados`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `anexos`
--
ALTER TABLE `anexos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `anexo_items`
--
ALTER TABLE `anexo_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `assinaturas`
--
ALTER TABLE `assinaturas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `atendimentos`
--
ALTER TABLE `atendimentos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `atendimento_interacoes`
--
ALTER TABLE `atendimento_interacoes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `auditoria_detalhada`
--
ALTER TABLE `auditoria_detalhada`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=137;

--
-- AUTO_INCREMENT de tabela `audit_timeline`
--
ALTER TABLE `audit_timeline`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `automation_rules`
--
ALTER TABLE `automation_rules`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `categorias_item_controle`
--
ALTER TABLE `categorias_item_controle`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `categoria_item_controle_checklist_templates`
--
ALTER TABLE `categoria_item_controle_checklist_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cliente_portal_users`
--
ALTER TABLE `cliente_portal_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `client_portal_messages`
--
ALTER TABLE `client_portal_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `crm_clientes`
--
ALTER TABLE `crm_clientes`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `crm_historicos`
--
ALTER TABLE `crm_historicos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `crm_pendencias`
--
ALTER TABLE `crm_pendencias`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `dashboard_widget_configuracoes`
--
ALTER TABLE `dashboard_widget_configuracoes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `document_versions`
--
ALTER TABLE `document_versions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `financeiro_assinaturas_cliente`
--
ALTER TABLE `financeiro_assinaturas_cliente`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `financeiro_clientes`
--
ALTER TABLE `financeiro_clientes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `financeiro_cobrancas`
--
ALTER TABLE `financeiro_cobrancas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `financeiro_gateway_integracoes`
--
ALTER TABLE `financeiro_gateway_integracoes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `financeiro_recebimentos`
--
ALTER TABLE `financeiro_recebimentos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `financeiro_webhook_logs`
--
ALTER TABLE `financeiro_webhook_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `fluxos_operacionais`
--
ALTER TABLE `fluxos_operacionais`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `fluxos_operacionais_etapas`
--
ALTER TABLE `fluxos_operacionais_etapas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `fluxos_operacionais_execucoes`
--
ALTER TABLE `fluxos_operacionais_execucoes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico_items`
--
ALTER TABLE `historico_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `item_controles`
--
ALTER TABLE `item_controles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT de tabela `item_controle_alertas`
--
ALTER TABLE `item_controle_alertas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `item_controle_anexos`
--
ALTER TABLE `item_controle_anexos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `item_controle_aprovacoes`
--
ALTER TABLE `item_controle_aprovacoes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `item_controle_assinaturas`
--
ALTER TABLE `item_controle_assinaturas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `item_controle_checklists`
--
ALTER TABLE `item_controle_checklists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `item_controle_comentarios`
--
ALTER TABLE `item_controle_comentarios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `item_controle_notificacao_logs`
--
ALTER TABLE `item_controle_notificacao_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `item_controle_tags`
--
ALTER TABLE `item_controle_tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `item_controle_tag_relations`
--
ALTER TABLE `item_controle_tag_relations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `item_controle_timeline`
--
ALTER TABLE `item_controle_timeline`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `logs_sistema`
--
ALTER TABLE `logs_sistema`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `notificacoes_internas`
--
ALTER TABLE `notificacoes_internas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `portal_cliente_tokens`
--
ALTER TABLE `portal_cliente_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `portal_documentos`
--
ALTER TABLE `portal_documentos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `portal_mensagens`
--
ALTER TABLE `portal_mensagens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de tabela `portal_solicitacoes`
--
ALTER TABLE `portal_solicitacoes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `prazzu_automation_executions`
--
ALTER TABLE `prazzu_automation_executions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_automation_rules`
--
ALTER TABLE `prazzu_automation_rules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `prazzu_billing_locks`
--
ALTER TABLE `prazzu_billing_locks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_billing_rules`
--
ALTER TABLE `prazzu_billing_rules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_client_messages`
--
ALTER TABLE `prazzu_client_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_client_portal_messages`
--
ALTER TABLE `prazzu_client_portal_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_custom_fields`
--
ALTER TABLE `prazzu_custom_fields`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_custom_field_values`
--
ALTER TABLE `prazzu_custom_field_values`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_dependencies`
--
ALTER TABLE `prazzu_dependencies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_document_versions`
--
ALTER TABLE `prazzu_document_versions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_permissions`
--
ALTER TABLE `prazzu_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `prazzu_permission_rules`
--
ALTER TABLE `prazzu_permission_rules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `prazzu_roles`
--
ALTER TABLE `prazzu_roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `prazzu_sla_policies`
--
ALTER TABLE `prazzu_sla_policies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_sla_rules`
--
ALTER TABLE `prazzu_sla_rules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `prazzu_subtasks`
--
ALTER TABLE `prazzu_subtasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_task_comments`
--
ALTER TABLE `prazzu_task_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_task_dependencies`
--
ALTER TABLE `prazzu_task_dependencies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_task_subtasks`
--
ALTER TABLE `prazzu_task_subtasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_teams`
--
ALTER TABLE `prazzu_teams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `prazzu_team_user`
--
ALTER TABLE `prazzu_team_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_templates`
--
ALTER TABLE `prazzu_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_time_entries`
--
ALTER TABLE `prazzu_time_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_time_tracking`
--
ALTER TABLE `prazzu_time_tracking`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_user_roles`
--
ALTER TABLE `prazzu_user_roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `relatorios_personalizados`
--
ALTER TABLE `relatorios_personalizados`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `relatorios_personalizados_colunas`
--
ALTER TABLE `relatorios_personalizados_colunas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `relatorios_personalizados_filtros`
--
ALTER TABLE `relatorios_personalizados_filtros`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `responsaveis`
--
ALTER TABLE `responsaveis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;

--
-- AUTO_INCREMENT de tabela `sla_rules`
--
ALTER TABLE `sla_rules`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `sugestoes_melhorias`
--
ALTER TABLE `sugestoes_melhorias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `tags`
--
ALTER TABLE `tags`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `task_comments`
--
ALTER TABLE `task_comments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `task_dependencies`
--
ALTER TABLE `task_dependencies`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `task_subtasks`
--
ALTER TABLE `task_subtasks`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `task_tags`
--
ALTER TABLE `task_tags`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=406;

--
-- AUTO_INCREMENT de tabela `white_label`
--
ALTER TABLE `white_label`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `ai_market_comments`
--
ALTER TABLE `ai_market_comments`
  ADD CONSTRAINT `ai_market_comments_source_id_foreign` FOREIGN KEY (`source_id`) REFERENCES `ai_market_sources` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `alerta_enviados`
--
ALTER TABLE `alerta_enviados`
  ADD CONSTRAINT `alerta_enviados_item_controle_id_foreign` FOREIGN KEY (`item_controle_id`) REFERENCES `item_controles` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `anexo_items`
--
ALTER TABLE `anexo_items`
  ADD CONSTRAINT `anexo_items_item_controle_id_foreign` FOREIGN KEY (`item_controle_id`) REFERENCES `item_controles` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `atendimentos`
--
ALTER TABLE `atendimentos`
  ADD CONSTRAINT `fk_atendimentos_criado_por` FOREIGN KEY (`criado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_atendimentos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_atendimentos_responsavel` FOREIGN KEY (`responsavel_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `atendimento_interacoes`
--
ALTER TABLE `atendimento_interacoes`
  ADD CONSTRAINT `fk_atendimento_interacoes_atendimento` FOREIGN KEY (`atendimento_id`) REFERENCES `atendimentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_atendimento_interacoes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `categorias_item_controle`
--
ALTER TABLE `categorias_item_controle`
  ADD CONSTRAINT `fk_categoria_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `cliente_portal_users`
--
ALTER TABLE `cliente_portal_users`
  ADD CONSTRAINT `cliente_portal_users_empresa_id_fk` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `dashboard_widget_configuracoes`
--
ALTER TABLE `dashboard_widget_configuracoes`
  ADD CONSTRAINT `fk_dashboard_widget_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `fluxos_operacionais`
--
ALTER TABLE `fluxos_operacionais`
  ADD CONSTRAINT `fk_fluxos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `fluxos_operacionais_etapas`
--
ALTER TABLE `fluxos_operacionais_etapas`
  ADD CONSTRAINT `fk_fluxos_etapas_fluxo` FOREIGN KEY (`fluxo_operacional_id`) REFERENCES `fluxos_operacionais` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `fluxos_operacionais_execucoes`
--
ALTER TABLE `fluxos_operacionais_execucoes`
  ADD CONSTRAINT `fk_fluxos_execucoes_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fluxos_execucoes_etapa` FOREIGN KEY (`fluxo_operacional_etapa_id`) REFERENCES `fluxos_operacionais_etapas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fluxos_execucoes_fluxo` FOREIGN KEY (`fluxo_operacional_id`) REFERENCES `fluxos_operacionais` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fluxos_execucoes_item` FOREIGN KEY (`item_controle_id`) REFERENCES `item_controles` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `historico_items`
--
ALTER TABLE `historico_items`
  ADD CONSTRAINT `historico_items_item_controle_id_foreign` FOREIGN KEY (`item_controle_id`) REFERENCES `item_controles` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `item_controles`
--
ALTER TABLE `item_controles`
  ADD CONSTRAINT `fk_item_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_item_responsavel` FOREIGN KEY (`responsavel_id`) REFERENCES `responsaveis` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `item_controle_aprovacoes`
--
ALTER TABLE `item_controle_aprovacoes`
  ADD CONSTRAINT `fk_aprovacao_item` FOREIGN KEY (`item_controle_id`) REFERENCES `item_controles` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `item_controle_assinaturas`
--
ALTER TABLE `item_controle_assinaturas`
  ADD CONSTRAINT `fk_assinatura_item` FOREIGN KEY (`item_controle_id`) REFERENCES `item_controles` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `item_controle_checklists`
--
ALTER TABLE `item_controle_checklists`
  ADD CONSTRAINT `fk_checklist_item` FOREIGN KEY (`item_controle_id`) REFERENCES `item_controles` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `item_controle_tags`
--
ALTER TABLE `item_controle_tags`
  ADD CONSTRAINT `fk_tag_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `item_controle_tag_relations`
--
ALTER TABLE `item_controle_tag_relations`
  ADD CONSTRAINT `fk_relation_item` FOREIGN KEY (`item_controle_id`) REFERENCES `item_controles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_relation_tag` FOREIGN KEY (`item_controle_tag_id`) REFERENCES `item_controle_tags` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `item_controle_timeline`
--
ALTER TABLE `item_controle_timeline`
  ADD CONSTRAINT `fk_timeline_item` FOREIGN KEY (`item_controle_id`) REFERENCES `item_controles` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `notificacoes_internas`
--
ALTER TABLE `notificacoes_internas`
  ADD CONSTRAINT `fk_notificacao_item` FOREIGN KEY (`item_controle_id`) REFERENCES `item_controles` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `portal_cliente_tokens`
--
ALTER TABLE `portal_cliente_tokens`
  ADD CONSTRAINT `portal_cliente_tokens_cliente_fk` FOREIGN KEY (`cliente_portal_user_id`) REFERENCES `cliente_portal_users` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `relatorios_personalizados`
--
ALTER TABLE `relatorios_personalizados`
  ADD CONSTRAINT `fk_relatorios_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `relatorios_personalizados_colunas`
--
ALTER TABLE `relatorios_personalizados_colunas`
  ADD CONSTRAINT `fk_relatorios_colunas_relatorio` FOREIGN KEY (`relatorio_id`) REFERENCES `relatorios_personalizados` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `relatorios_personalizados_filtros`
--
ALTER TABLE `relatorios_personalizados_filtros`
  ADD CONSTRAINT `fk_relatorios_filtros_relatorio` FOREIGN KEY (`relatorio_id`) REFERENCES `relatorios_personalizados` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `sugestoes_melhorias`
--
ALTER TABLE `sugestoes_melhorias`
  ADD CONSTRAINT `sugestoes_melhorias_analisado_por_foreign` FOREIGN KEY (`analisado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sugestoes_melhorias_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sugestoes_melhorias_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
