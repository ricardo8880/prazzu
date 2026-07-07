-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 07-Jul-2026 às 22:54
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_column_if_not_exists` (IN `table_name` VARCHAR(100), IN `column_name` VARCHAR(100), IN `column_definition` VARCHAR(255))   BEGIN
    DECLARE column_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO column_count
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = table_name
    AND COLUMN_NAME = column_name;
    
    IF column_count = 0 THEN
        SET @sql = CONCAT('ALTER TABLE `', table_name, '` ADD COLUMN ', column_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_index_if_not_exists` (IN `table_name` VARCHAR(100), IN `index_name` VARCHAR(100), IN `index_columns` VARCHAR(255))   BEGIN
    DECLARE index_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO index_count
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = table_name
    AND INDEX_NAME = index_name;
    
    IF index_count = 0 THEN
        SET @sql = CONCAT('CREATE INDEX `', index_name, '` ON `', table_name, '` (', index_columns, ')');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
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
(35, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 51, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":51,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23T00:00:00.000000Z\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":false,\"portal_token\":\"t5Q6RMAbVqk7WO5hoC3nqskz9d47wlmTut8W0R6nb7IJteQphRT5INOvMRAhvOeT\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28T12:08:18.000000Z\",\"updated_at\":\"2026-05-08T11:30:31.000000Z\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27T16:08:18.000000Z\",\"sla_limite_em\":\"2026-04-28T00:08:18.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":0,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":0},\"attributes\":{\"id\":51,\"empresa_id\":11,\"responsavel_id\":168,\"titulo\":\"SEED - Documento atrasado\",\"descricao\":\"Item criado para testar atrasados, SLA vencido, filtros e dashboard.\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"prioridade\":\"alta\",\"data_vencimento\":\"2026-04-23\",\"data_conclusao\":null,\"arquivo\":null,\"observacao\":\"Documento seed atrasado.\",\"portal_ativo\":true,\"portal_token\":\"t5Q6RMAbVqk7WO5hoC3nqskz9d47wlmTut8W0R6nb7IJteQphRT5INOvMRAhvOeT\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"created_at\":\"2026-04-28 12:08:18\",\"updated_at\":\"2026-05-08 11:30:31\",\"sla_horas\":8,\"sla_inicio_em\":\"2026-04-27 16:08:18\",\"sla_limite_em\":\"2026-04-28 00:08:18\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"comentarios_count\":0,\"anexos_count\":0,\"checklists_count\":1,\"assinaturas_count\":0,\"aprovacoes_count\":0,\"timelines_count\":0,\"notificacoes_internas_count\":1,\"checklists_concluidos_count\":0}}', NULL, '2026-05-08 14:30:31', '2026-05-08 14:30:31'),
(37, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 31, 'updated', 'App\\Models\\User', 111, '{\"old\":{\"id\":31,\"titulo\":\"Item de Controle - User Alpha 1\",\"descricao\":\"Registro de controle criado para User Alpha 1\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"status_operacional_at\":\"2026-06-07T14:04:16.000000Z\",\"view_type\":null,\"automation_status\":null,\"prioridade\":\"alta\",\"urgencia\":\"critica\",\"risco_score\":95,\"bloqueado_por_dependencia\":false,\"bloqueado\":false,\"arquivo\":null,\"data_vencimento\":\"2026-05-25T03:00:00.000000Z\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Seed redistribu\\u00eddo: obriga\\u00e7\\u00e3o muito atrasada para testar risco cr\\u00edtico na Home. Cliente sem envio recente pelo portal.\",\"portal_ativo\":true,\"portal_token\":\"1ef7a8d0880102be8409c4ceca8b4c5f\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"portal_status\":\"aguardando_cliente\",\"ultima_interacao_cliente_em\":\"2026-06-09 11:04:16\",\"sla_horas\":null,\"sla_inicio_em\":\"2026-05-22T14:04:16.000000Z\",\"sla_limite_em\":\"2026-05-25T14:04:16.000000Z\",\"sla_prazo_alvo_em\":\"2026-05-25T14:04:16.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"valor_tarefa\":null,\"faturado_em\":null,\"pago_em\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":4,\"atendimento_id\":null,\"responsavel_id\":149,\"ultimo_alerta_enviado_em\":\"2026-05-26 11:04:16\",\"ultimo_lembrete_enviado_em\":\"2026-06-18 11:04:16\",\"qtd_lembretes_enviados\":4,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-24T17:57:10.000000Z\",\"updated_at\":\"2026-06-19T14:04:16.000000Z\",\"fluxo_operacional_id\":null,\"kanban_order\":null,\"blocked_by_dependency\":false,\"estimated_minutes\":null,\"actual_minutes\":null,\"custom_payload\":{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"},\"template_id\":null,\"approval_required\":false,\"approval_status\":null,\"document_status\":null,\"signature_status\":null,\"risk_probability\":null,\"risk_impact\":null,\"risk_score\":null},\"attributes\":{\"id\":31,\"titulo\":\"Item de Controle - User Alpha 1\",\"descricao\":\"Registro de controle criado para User Alpha 1\",\"tipo\":\"documento\",\"categoria_id\":null,\"status\":\"pendente\",\"status_operacional_at\":\"2026-06-07 11:04:16\",\"view_type\":null,\"automation_status\":null,\"prioridade\":\"alta\",\"urgencia\":\"critica\",\"risco_score\":95,\"bloqueado_por_dependencia\":0,\"bloqueado\":0,\"arquivo\":null,\"data_vencimento\":\"2026-07-25 00:00:00\",\"data_conclusao\":null,\"notificado_3_dias\":0,\"notificado_no_dia\":0,\"notificado_vencido\":1,\"observacao\":\"Seed redistribu\\u00eddo: obriga\\u00e7\\u00e3o muito atrasada para testar risco cr\\u00edtico na Home. Cliente sem envio recente pelo portal.\",\"portal_ativo\":true,\"portal_token\":\"1ef7a8d0880102be8409c4ceca8b4c5f\",\"portal_cliente_nome\":null,\"portal_cliente_email\":null,\"portal_expira_em\":null,\"portal_status\":\"aguardando_cliente\",\"ultima_interacao_cliente_em\":\"2026-06-09 11:04:16\",\"sla_horas\":null,\"sla_inicio_em\":\"2026-05-22 11:04:16\",\"sla_limite_em\":\"2026-05-25 11:04:16\",\"sla_prazo_alvo_em\":\"2026-05-25 11:04:16\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"contrato_numero\":null,\"contrato_parte_nome\":null,\"contrato_parte_documento\":null,\"contrato_valor\":null,\"valor_tarefa\":null,\"faturado_em\":null,\"pago_em\":null,\"contrato_inicio_em\":null,\"contrato_fim_em\":null,\"contrato_status\":null,\"empresa_id\":4,\"atendimento_id\":null,\"responsavel_id\":149,\"ultimo_alerta_enviado_em\":\"2026-05-26 11:04:16\",\"ultimo_lembrete_enviado_em\":\"2026-06-18 11:04:16\",\"qtd_lembretes_enviados\":4,\"ultima_falha_notificacao_em\":null,\"ultima_falha_notificacao_msg\":null,\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-06-19 11:29:18\",\"fluxo_operacional_id\":null,\"kanban_order\":null,\"blocked_by_dependency\":0,\"estimated_minutes\":null,\"actual_minutes\":null,\"custom_payload\":\"{\\\"baseline_start\\\":\\\"2026-04-24\\\",\\\"baseline_end\\\":\\\"2026-05-24\\\",\\\"baseline_saved_at\\\":\\\"2026-05-06 13:12:12\\\"}\",\"template_id\":null,\"approval_required\":0,\"approval_status\":null,\"document_status\":null,\"signature_status\":null,\"risk_probability\":null,\"risk_impact\":null,\"risk_score\":null}}', NULL, '2026-06-19 14:29:18', '2026-06-19 14:29:18'),
(38, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 80, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":80,\"titulo\":\"DEMO ARMAZENAMENTO - Tempor\\u00e1rios Beta\",\"descricao\":\"Arquivos tempor\\u00e1rios para espa\\u00e7o recuper\\u00e1vel.\",\"observacao\":null,\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"baixa\",\"empresa_id\":5,\"responsavel_id\":154,\"data_vencimento\":\"2026-06-17T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":null,\"created_at\":\"2026-05-15T15:54:23.000000Z\",\"updated_at\":\"2026-06-19T15:54:23.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":80,\"titulo\":\"DEMO ARMAZENAMENTO - Tempor\\u00e1rios Beta\",\"descricao\":\"Arquivos tempor\\u00e1rios para espa\\u00e7o recuper\\u00e1vel.\",\"observacao\":null,\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"baixa\",\"empresa_id\":5,\"responsavel_id\":154,\"data_vencimento\":\"2026-06-17\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-05-15 12:54:23\",\"updated_at\":\"2026-07-07 17:02:15\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:02:15\",\"urgencia\":\"baixa\"}}', NULL, '2026-07-07 20:02:15', '2026-07-07 20:02:15'),
(39, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 34, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":34,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"observacao\":\"Seed redistribu\\u00eddo: obriga\\u00e7\\u00e3o atrasada h\\u00e1 mais de 10 dias. Cliente sem envio recente pelo portal.\",\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"alta\",\"empresa_id\":5,\"responsavel_id\":156,\"data_vencimento\":\"2026-06-20T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-06-20T19:36:17.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24T17:57:10.000000Z\",\"updated_at\":\"2026-07-02T19:36:17.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":34,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"observacao\":\"Seed redistribu\\u00eddo: obriga\\u00e7\\u00e3o atrasada h\\u00e1 mais de 10 dias. Cliente sem envio recente pelo portal.\",\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"alta\",\"empresa_id\":5,\"responsavel_id\":156,\"data_vencimento\":\"2026-06-20\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-06-20 16:36:17\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-07-07 17:02:16\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:02:16\",\"urgencia\":\"alta\"}}', NULL, '2026-07-07 20:02:16', '2026-07-07 20:02:16');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `subject_id`, `event`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(40, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 34, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":34,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"observacao\":\"Seed redistribu\\u00eddo: obriga\\u00e7\\u00e3o atrasada h\\u00e1 mais de 10 dias. Cliente sem envio recente pelo portal.\",\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"alta\",\"empresa_id\":5,\"responsavel_id\":156,\"data_vencimento\":\"2026-06-20T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-06-20T19:36:17.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24T17:57:10.000000Z\",\"updated_at\":\"2026-07-07T20:02:16.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":34,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"observacao\":\"Seed redistribu\\u00eddo: obriga\\u00e7\\u00e3o atrasada h\\u00e1 mais de 10 dias. Cliente sem envio recente pelo portal.\",\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"empresa_id\":5,\"responsavel_id\":156,\"data_vencimento\":\"2026-06-20\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-06-20 16:36:17\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-07-07 17:10:16\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:10:16\",\"urgencia\":\"alta\"}}', NULL, '2026-07-07 20:10:16', '2026-07-07 20:10:16'),
(41, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 36, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":36,\"titulo\":\"Item de Controle - User Beta 3\",\"descricao\":\"Registro de controle criado para User Beta 3\",\"observacao\":\"Seed redistribu\\u00eddo: vence em 7 dias para testar previs\\u00f5es da Home.\",\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"media\",\"empresa_id\":5,\"responsavel_id\":158,\"data_vencimento\":\"2026-07-09T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-07-09T19:36:17.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"em_andamento\",\"created_at\":\"2026-04-24T17:57:10.000000Z\",\"updated_at\":\"2026-07-02T19:36:17.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":36,\"titulo\":\"Item de Controle - User Beta 3\",\"descricao\":\"Registro de controle criado para User Beta 3\",\"observacao\":\"Seed redistribu\\u00eddo: vence em 7 dias para testar previs\\u00f5es da Home.\",\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"media\",\"empresa_id\":5,\"responsavel_id\":158,\"data_vencimento\":\"2026-07-09\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-07-09 16:36:17\",\"sla_concluido_em\":null,\"sla_status\":\"em_andamento\",\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-07-07 17:10:20\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:10:20\",\"urgencia\":\"media\"}}', NULL, '2026-07-07 20:10:20', '2026-07-07 20:10:20'),
(42, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 80, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":80,\"titulo\":\"DEMO ARMAZENAMENTO - Tempor\\u00e1rios Beta\",\"descricao\":\"Arquivos tempor\\u00e1rios para espa\\u00e7o recuper\\u00e1vel.\",\"observacao\":null,\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"baixa\",\"empresa_id\":5,\"responsavel_id\":154,\"data_vencimento\":\"2026-06-17T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-05-15T15:54:23.000000Z\",\"updated_at\":\"2026-07-07T20:02:15.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":80,\"titulo\":\"DEMO ARMAZENAMENTO - Tempor\\u00e1rios Beta\",\"descricao\":\"Arquivos tempor\\u00e1rios para espa\\u00e7o recuper\\u00e1vel.\",\"observacao\":null,\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"baixa\",\"empresa_id\":5,\"responsavel_id\":154,\"data_vencimento\":\"2026-06-17\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-05-15 12:54:23\",\"updated_at\":\"2026-07-07 17:10:26\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:10:26\",\"urgencia\":\"baixa\"}}', NULL, '2026-07-07 20:10:26', '2026-07-07 20:10:26'),
(43, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 36, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":36,\"titulo\":\"Item de Controle - User Beta 3\",\"descricao\":\"Registro de controle criado para User Beta 3\",\"observacao\":\"Seed redistribu\\u00eddo: vence em 7 dias para testar previs\\u00f5es da Home.\",\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"media\",\"empresa_id\":5,\"responsavel_id\":158,\"data_vencimento\":\"2026-07-09T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-07-09T19:36:17.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"em_andamento\",\"created_at\":\"2026-04-24T17:57:10.000000Z\",\"updated_at\":\"2026-07-07T20:10:20.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":36,\"titulo\":\"Item de Controle - User Beta 3\",\"descricao\":\"Registro de controle criado para User Beta 3\",\"observacao\":\"Seed redistribu\\u00eddo: vence em 7 dias para testar previs\\u00f5es da Home.\",\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"media\",\"empresa_id\":5,\"responsavel_id\":158,\"data_vencimento\":\"2026-07-09\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-07-09 16:36:17\",\"sla_concluido_em\":null,\"sla_status\":\"em_andamento\",\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-07-07 17:10:29\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:10:29\",\"urgencia\":\"media\"}}', NULL, '2026-07-07 20:10:29', '2026-07-07 20:10:29'),
(44, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 34, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":34,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"observacao\":\"Seed redistribu\\u00eddo: obriga\\u00e7\\u00e3o atrasada h\\u00e1 mais de 10 dias. Cliente sem envio recente pelo portal.\",\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"empresa_id\":5,\"responsavel_id\":156,\"data_vencimento\":\"2026-06-20T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-06-20T19:36:17.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24T17:57:10.000000Z\",\"updated_at\":\"2026-07-07T20:10:16.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":34,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"observacao\":\"Seed redistribu\\u00eddo: obriga\\u00e7\\u00e3o atrasada h\\u00e1 mais de 10 dias. Cliente sem envio recente pelo portal.\",\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"alta\",\"empresa_id\":5,\"responsavel_id\":156,\"data_vencimento\":\"2026-06-20\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-06-20 16:36:17\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-07-07 17:10:31\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:10:31\",\"urgencia\":\"alta\"}}', NULL, '2026-07-07 20:10:31', '2026-07-07 20:10:31'),
(45, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 80, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":80,\"titulo\":\"DEMO ARMAZENAMENTO - Tempor\\u00e1rios Beta\",\"descricao\":\"Arquivos tempor\\u00e1rios para espa\\u00e7o recuper\\u00e1vel.\",\"observacao\":null,\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"baixa\",\"empresa_id\":5,\"responsavel_id\":154,\"data_vencimento\":\"2026-06-17T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-05-15T15:54:23.000000Z\",\"updated_at\":\"2026-07-07T20:10:26.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":80,\"titulo\":\"DEMO ARMAZENAMENTO - Tempor\\u00e1rios Beta\",\"descricao\":\"Arquivos tempor\\u00e1rios para espa\\u00e7o recuper\\u00e1vel.\",\"observacao\":null,\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"baixa\",\"empresa_id\":5,\"responsavel_id\":154,\"data_vencimento\":\"2026-06-17\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-05-15 12:54:23\",\"updated_at\":\"2026-07-07 17:10:33\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:10:33\",\"urgencia\":\"baixa\"}}', NULL, '2026-07-07 20:10:33', '2026-07-07 20:10:33'),
(46, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 36, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":36,\"titulo\":\"Item de Controle - User Beta 3\",\"descricao\":\"Registro de controle criado para User Beta 3\",\"observacao\":\"Seed redistribu\\u00eddo: vence em 7 dias para testar previs\\u00f5es da Home.\",\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"media\",\"empresa_id\":5,\"responsavel_id\":158,\"data_vencimento\":\"2026-07-09T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-07-09T19:36:17.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"em_andamento\",\"created_at\":\"2026-04-24T17:57:10.000000Z\",\"updated_at\":\"2026-07-07T20:10:29.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":36,\"titulo\":\"Item de Controle - User Beta 3\",\"descricao\":\"Registro de controle criado para User Beta 3\",\"observacao\":\"Seed redistribu\\u00eddo: vence em 7 dias para testar previs\\u00f5es da Home.\",\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"media\",\"empresa_id\":5,\"responsavel_id\":158,\"data_vencimento\":\"2026-07-09\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-07-09 16:36:17\",\"sla_concluido_em\":null,\"sla_status\":\"em_andamento\",\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-07-07 17:10:40\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:10:40\",\"urgencia\":\"media\"}}', NULL, '2026-07-07 20:10:40', '2026-07-07 20:10:40'),
(47, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 34, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":34,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"observacao\":\"Seed redistribu\\u00eddo: obriga\\u00e7\\u00e3o atrasada h\\u00e1 mais de 10 dias. Cliente sem envio recente pelo portal.\",\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"alta\",\"empresa_id\":5,\"responsavel_id\":156,\"data_vencimento\":\"2026-06-20T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-06-20T19:36:17.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24T17:57:10.000000Z\",\"updated_at\":\"2026-07-07T20:10:31.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":34,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"observacao\":\"Seed redistribu\\u00eddo: obriga\\u00e7\\u00e3o atrasada h\\u00e1 mais de 10 dias. Cliente sem envio recente pelo portal.\",\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"empresa_id\":5,\"responsavel_id\":156,\"data_vencimento\":\"2026-06-20\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-06-20 16:36:17\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-07-07 17:10:43\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:10:43\",\"urgencia\":\"alta\"}}', NULL, '2026-07-07 20:10:43', '2026-07-07 20:10:43'),
(48, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 35, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":35,\"titulo\":\"Item de Controle - User Beta 2\",\"descricao\":\"Registro de controle criado para User Beta 2\",\"observacao\":\"Seed redistribu\\u00eddo: vence em 3 dias.\",\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"media\",\"empresa_id\":5,\"responsavel_id\":157,\"data_vencimento\":\"2026-07-05T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-07-05T19:36:17.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"em_andamento\",\"created_at\":\"2026-04-24T17:57:10.000000Z\",\"updated_at\":\"2026-07-02T19:36:17.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":35,\"titulo\":\"Item de Controle - User Beta 2\",\"descricao\":\"Registro de controle criado para User Beta 2\",\"observacao\":\"Seed redistribu\\u00eddo: vence em 3 dias.\",\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"media\",\"empresa_id\":5,\"responsavel_id\":157,\"data_vencimento\":\"2026-07-05\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-07-05 16:36:17\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-07-07 17:10:44\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:10:44\",\"urgencia\":\"media\"}}', NULL, '2026-07-07 20:10:44', '2026-07-07 20:10:44'),
(49, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 80, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":80,\"titulo\":\"DEMO ARMAZENAMENTO - Tempor\\u00e1rios Beta\",\"descricao\":\"Arquivos tempor\\u00e1rios para espa\\u00e7o recuper\\u00e1vel.\",\"observacao\":null,\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"baixa\",\"empresa_id\":5,\"responsavel_id\":154,\"data_vencimento\":\"2026-06-17T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-05-15T15:54:23.000000Z\",\"updated_at\":\"2026-07-07T20:10:33.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":80,\"titulo\":\"DEMO ARMAZENAMENTO - Tempor\\u00e1rios Beta\",\"descricao\":\"Arquivos tempor\\u00e1rios para espa\\u00e7o recuper\\u00e1vel.\",\"observacao\":null,\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"baixa\",\"empresa_id\":5,\"responsavel_id\":154,\"data_vencimento\":\"2026-06-17\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-05-15 12:54:23\",\"updated_at\":\"2026-07-07 17:10:51\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:10:51\",\"urgencia\":\"baixa\"}}', NULL, '2026-07-07 20:10:51', '2026-07-07 20:10:51'),
(50, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 34, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":34,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"observacao\":\"Seed redistribu\\u00eddo: obriga\\u00e7\\u00e3o atrasada h\\u00e1 mais de 10 dias. Cliente sem envio recente pelo portal.\",\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"empresa_id\":5,\"responsavel_id\":156,\"data_vencimento\":\"2026-06-20T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-06-20T19:36:17.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24T17:57:10.000000Z\",\"updated_at\":\"2026-07-07T20:10:43.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":34,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"observacao\":\"Seed redistribu\\u00eddo: obriga\\u00e7\\u00e3o atrasada h\\u00e1 mais de 10 dias. Cliente sem envio recente pelo portal.\",\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"alta\",\"empresa_id\":5,\"responsavel_id\":156,\"data_vencimento\":\"2026-06-20\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-06-20 16:36:17\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-07-07 17:10:52\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:10:52\",\"urgencia\":\"alta\"}}', NULL, '2026-07-07 20:10:52', '2026-07-07 20:10:52'),
(51, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 35, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":35,\"titulo\":\"Item de Controle - User Beta 2\",\"descricao\":\"Registro de controle criado para User Beta 2\",\"observacao\":\"Seed redistribu\\u00eddo: vence em 3 dias.\",\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"media\",\"empresa_id\":5,\"responsavel_id\":157,\"data_vencimento\":\"2026-07-05T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-07-05T19:36:17.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24T17:57:10.000000Z\",\"updated_at\":\"2026-07-07T20:10:44.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":35,\"titulo\":\"Item de Controle - User Beta 2\",\"descricao\":\"Registro de controle criado para User Beta 2\",\"observacao\":\"Seed redistribu\\u00eddo: vence em 3 dias.\",\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"media\",\"empresa_id\":5,\"responsavel_id\":157,\"data_vencimento\":\"2026-07-05\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-07-05 16:36:17\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-07-07 17:10:54\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:10:54\",\"urgencia\":\"media\"}}', NULL, '2026-07-07 20:10:54', '2026-07-07 20:10:54'),
(52, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 36, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":36,\"titulo\":\"Item de Controle - User Beta 3\",\"descricao\":\"Registro de controle criado para User Beta 3\",\"observacao\":\"Seed redistribu\\u00eddo: vence em 7 dias para testar previs\\u00f5es da Home.\",\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"media\",\"empresa_id\":5,\"responsavel_id\":158,\"data_vencimento\":\"2026-07-09T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-07-09T19:36:17.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"em_andamento\",\"created_at\":\"2026-04-24T17:57:10.000000Z\",\"updated_at\":\"2026-07-07T20:10:40.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":36,\"titulo\":\"Item de Controle - User Beta 3\",\"descricao\":\"Registro de controle criado para User Beta 3\",\"observacao\":\"Seed redistribu\\u00eddo: vence em 7 dias para testar previs\\u00f5es da Home.\",\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"media\",\"empresa_id\":5,\"responsavel_id\":158,\"data_vencimento\":\"2026-07-09\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-07-09 16:36:17\",\"sla_concluido_em\":null,\"sla_status\":\"em_andamento\",\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-07-07 17:10:58\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:10:58\",\"urgencia\":\"media\"}}', NULL, '2026-07-07 20:10:58', '2026-07-07 20:10:58'),
(53, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 80, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":80,\"titulo\":\"DEMO ARMAZENAMENTO - Tempor\\u00e1rios Beta\",\"descricao\":\"Arquivos tempor\\u00e1rios para espa\\u00e7o recuper\\u00e1vel.\",\"observacao\":null,\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"baixa\",\"empresa_id\":5,\"responsavel_id\":154,\"data_vencimento\":\"2026-06-17T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-05-15T15:54:23.000000Z\",\"updated_at\":\"2026-07-07T20:10:51.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":80,\"titulo\":\"DEMO ARMAZENAMENTO - Tempor\\u00e1rios Beta\",\"descricao\":\"Arquivos tempor\\u00e1rios para espa\\u00e7o recuper\\u00e1vel.\",\"observacao\":null,\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"baixa\",\"empresa_id\":5,\"responsavel_id\":154,\"data_vencimento\":\"2026-06-17\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-05-15 12:54:23\",\"updated_at\":\"2026-07-07 17:11:02\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:11:02\",\"urgencia\":\"baixa\"}}', NULL, '2026-07-07 20:11:02', '2026-07-07 20:11:02'),
(54, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 34, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":34,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"observacao\":\"Seed redistribu\\u00eddo: obriga\\u00e7\\u00e3o atrasada h\\u00e1 mais de 10 dias. Cliente sem envio recente pelo portal.\",\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"alta\",\"empresa_id\":5,\"responsavel_id\":156,\"data_vencimento\":\"2026-06-20T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-06-20T19:36:17.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24T17:57:10.000000Z\",\"updated_at\":\"2026-07-07T20:10:52.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":34,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"observacao\":\"Seed redistribu\\u00eddo: obriga\\u00e7\\u00e3o atrasada h\\u00e1 mais de 10 dias. Cliente sem envio recente pelo portal.\",\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"empresa_id\":5,\"responsavel_id\":156,\"data_vencimento\":\"2026-06-20\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-06-20 16:36:17\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-07-07 17:11:03\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:11:03\",\"urgencia\":\"alta\"}}', NULL, '2026-07-07 20:11:03', '2026-07-07 20:11:03'),
(55, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 35, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":35,\"titulo\":\"Item de Controle - User Beta 2\",\"descricao\":\"Registro de controle criado para User Beta 2\",\"observacao\":\"Seed redistribu\\u00eddo: vence em 3 dias.\",\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"media\",\"empresa_id\":5,\"responsavel_id\":157,\"data_vencimento\":\"2026-07-05T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-07-05T19:36:17.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24T17:57:10.000000Z\",\"updated_at\":\"2026-07-07T20:10:54.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":35,\"titulo\":\"Item de Controle - User Beta 2\",\"descricao\":\"Registro de controle criado para User Beta 2\",\"observacao\":\"Seed redistribu\\u00eddo: vence em 3 dias.\",\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"media\",\"empresa_id\":5,\"responsavel_id\":157,\"data_vencimento\":\"2026-07-05\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-07-05 16:36:17\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-07-07 17:11:05\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:11:05\",\"urgencia\":\"media\"}}', NULL, '2026-07-07 20:11:05', '2026-07-07 20:11:05'),
(56, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 36, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":36,\"titulo\":\"Item de Controle - User Beta 3\",\"descricao\":\"Registro de controle criado para User Beta 3\",\"observacao\":\"Seed redistribu\\u00eddo: vence em 7 dias para testar previs\\u00f5es da Home.\",\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"media\",\"empresa_id\":5,\"responsavel_id\":158,\"data_vencimento\":\"2026-07-09T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-07-09T19:36:17.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"em_andamento\",\"created_at\":\"2026-04-24T17:57:10.000000Z\",\"updated_at\":\"2026-07-07T20:10:58.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":36,\"titulo\":\"Item de Controle - User Beta 3\",\"descricao\":\"Registro de controle criado para User Beta 3\",\"observacao\":\"Seed redistribu\\u00eddo: vence em 7 dias para testar previs\\u00f5es da Home.\",\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"media\",\"empresa_id\":5,\"responsavel_id\":158,\"data_vencimento\":\"2026-07-09\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-07-09 16:36:17\",\"sla_concluido_em\":null,\"sla_status\":\"em_andamento\",\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-07-07 17:11:07\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:11:07\",\"urgencia\":\"media\"}}', NULL, '2026-07-07 20:11:07', '2026-07-07 20:11:07'),
(57, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 80, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":80,\"titulo\":\"DEMO ARMAZENAMENTO - Tempor\\u00e1rios Beta\",\"descricao\":\"Arquivos tempor\\u00e1rios para espa\\u00e7o recuper\\u00e1vel.\",\"observacao\":null,\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"baixa\",\"empresa_id\":5,\"responsavel_id\":154,\"data_vencimento\":\"2026-06-17T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-05-15T15:54:23.000000Z\",\"updated_at\":\"2026-07-07T20:11:02.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":80,\"titulo\":\"DEMO ARMAZENAMENTO - Tempor\\u00e1rios Beta\",\"descricao\":\"Arquivos tempor\\u00e1rios para espa\\u00e7o recuper\\u00e1vel.\",\"observacao\":null,\"tipo\":\"documento\",\"status\":\"concluido\",\"prioridade\":\"baixa\",\"empresa_id\":5,\"responsavel_id\":154,\"data_vencimento\":\"2026-06-17\",\"data_conclusao\":\"2026-07-07 17:11:10\",\"sla_horas\":null,\"sla_limite_em\":null,\"sla_concluido_em\":\"2026-07-07 17:11:10\",\"sla_status\":\"concluido_atrasado\",\"created_at\":\"2026-05-15 12:54:23\",\"updated_at\":\"2026-07-07 17:11:10\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:11:10\",\"urgencia\":\"baixa\"}}', NULL, '2026-07-07 20:11:10', '2026-07-07 20:11:10'),
(58, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 80, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":80,\"titulo\":\"DEMO ARMAZENAMENTO - Tempor\\u00e1rios Beta\",\"descricao\":\"Arquivos tempor\\u00e1rios para espa\\u00e7o recuper\\u00e1vel.\",\"observacao\":null,\"tipo\":\"documento\",\"status\":\"concluido\",\"prioridade\":\"baixa\",\"empresa_id\":5,\"responsavel_id\":154,\"data_vencimento\":\"2026-06-17T03:00:00.000000Z\",\"data_conclusao\":\"2026-07-07T03:00:00.000000Z\",\"sla_horas\":null,\"sla_limite_em\":null,\"sla_concluido_em\":\"2026-07-07T20:11:10.000000Z\",\"sla_status\":\"concluido_atrasado\",\"created_at\":\"2026-05-15T15:54:23.000000Z\",\"updated_at\":\"2026-07-07T20:11:10.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":80,\"titulo\":\"DEMO ARMAZENAMENTO - Tempor\\u00e1rios Beta\",\"descricao\":\"Arquivos tempor\\u00e1rios para espa\\u00e7o recuper\\u00e1vel.\",\"observacao\":null,\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"baixa\",\"empresa_id\":5,\"responsavel_id\":154,\"data_vencimento\":\"2026-06-17\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":\"concluido_atrasado\",\"created_at\":\"2026-05-15 12:54:23\",\"updated_at\":\"2026-07-07 17:11:14\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:11:14\",\"urgencia\":\"baixa\"}}', NULL, '2026-07-07 20:11:14', '2026-07-07 20:11:14'),
(59, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 80, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":80,\"titulo\":\"DEMO ARMAZENAMENTO - Tempor\\u00e1rios Beta\",\"descricao\":\"Arquivos tempor\\u00e1rios para espa\\u00e7o recuper\\u00e1vel.\",\"observacao\":null,\"tipo\":\"documento\",\"status\":\"pendente\",\"prioridade\":\"baixa\",\"empresa_id\":5,\"responsavel_id\":154,\"data_vencimento\":\"2026-06-17T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":null,\"sla_concluido_em\":null,\"sla_status\":\"concluido_atrasado\",\"created_at\":\"2026-05-15T15:54:23.000000Z\",\"updated_at\":\"2026-07-07T20:11:14.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":80,\"titulo\":\"DEMO ARMAZENAMENTO - Tempor\\u00e1rios Beta\",\"descricao\":\"Arquivos tempor\\u00e1rios para espa\\u00e7o recuper\\u00e1vel.\",\"observacao\":null,\"tipo\":\"documento\",\"status\":\"concluido\",\"prioridade\":\"baixa\",\"empresa_id\":5,\"responsavel_id\":154,\"data_vencimento\":\"2026-06-17\",\"data_conclusao\":\"2026-07-07 17:11:20\",\"sla_horas\":null,\"sla_limite_em\":null,\"sla_concluido_em\":\"2026-07-07 17:11:20\",\"sla_status\":\"concluido_atrasado\",\"created_at\":\"2026-05-15 12:54:23\",\"updated_at\":\"2026-07-07 17:11:20\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:11:20\",\"urgencia\":\"baixa\"}}', NULL, '2026-07-07 20:11:20', '2026-07-07 20:11:20'),
(60, 'default', 'Registro atualizado', 'App\\Models\\ItemControle', 34, 'updated', 'App\\Models\\User', 376, '{\"old\":{\"id\":34,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"observacao\":\"Seed redistribu\\u00eddo: obriga\\u00e7\\u00e3o atrasada h\\u00e1 mais de 10 dias. Cliente sem envio recente pelo portal.\",\"tipo\":\"documento\",\"status\":\"em_andamento\",\"prioridade\":\"alta\",\"empresa_id\":5,\"responsavel_id\":156,\"data_vencimento\":\"2026-06-20T03:00:00.000000Z\",\"data_conclusao\":null,\"sla_horas\":null,\"sla_limite_em\":\"2026-06-20T19:36:17.000000Z\",\"sla_concluido_em\":null,\"sla_status\":\"vencido\",\"created_at\":\"2026-04-24T17:57:10.000000Z\",\"updated_at\":\"2026-07-07T20:11:03.000000Z\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0},\"attributes\":{\"id\":34,\"titulo\":\"Item de Controle - User Beta 1\",\"descricao\":\"Registro de controle criado para User Beta 1\",\"observacao\":\"Seed redistribu\\u00eddo: obriga\\u00e7\\u00e3o atrasada h\\u00e1 mais de 10 dias. Cliente sem envio recente pelo portal.\",\"tipo\":\"documento\",\"status\":\"concluido\",\"prioridade\":\"alta\",\"empresa_id\":5,\"responsavel_id\":156,\"data_vencimento\":\"2026-06-20\",\"data_conclusao\":\"2026-07-07 17:11:25\",\"sla_horas\":null,\"sla_limite_em\":\"2026-06-20 16:36:17\",\"sla_concluido_em\":\"2026-07-07 17:11:25\",\"sla_status\":\"concluido_atrasado\",\"created_at\":\"2026-04-24 14:57:10\",\"updated_at\":\"2026-07-07 17:11:25\",\"checklists_count\":0,\"checklists_concluidos_count\":0,\"comentarios_total\":0,\"status_operacional_at\":\"2026-07-07 17:11:25\",\"urgencia\":\"alta\"}}', NULL, '2026-07-07 20:11:25', '2026-07-07 20:11:25');

-- --------------------------------------------------------

--
-- Estrutura da tabela `ai_market_comments`
--

CREATE TABLE `ai_market_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `source_id` bigint(20) UNSIGNED DEFAULT NULL,
  `competitor_name` varchar(255) DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `language` varchar(20) DEFAULT NULL,
  `original_text` longtext NOT NULL,
  `detected_sentiment` varchar(50) DEFAULT NULL,
  `detected_category` varchar(150) DEFAULT NULL,
  `detected_problem` varchar(255) DEFAULT NULL,
  `detected_opportunity` text DEFAULT NULL,
  `detected_real_pain` text DEFAULT NULL,
  `detected_impact` tinyint(3) UNSIGNED DEFAULT NULL,
  `recommended_action` text DEFAULT NULL,
  `metadata` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ai_market_sources`
--

CREATE TABLE `ai_market_sources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `competitor_name` varchar(255) DEFAULT NULL,
  `source_type` varchar(100) DEFAULT NULL,
  `source_url` varchar(1000) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ai_product_improvement_resolutions`
--

CREATE TABLE `ai_product_improvement_resolutions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_key` varchar(255) NOT NULL,
  `item_type` varchar(100) NOT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `resolved_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Estrutura da tabela `asaas_webhook_events`
--

CREATE TABLE `asaas_webhook_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event` varchar(120) DEFAULT NULL,
  `gateway_payment_id` varchar(255) DEFAULT NULL,
  `gateway_subscription_id` varchar(255) DEFAULT NULL,
  `payload_hash` char(64) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'received',
  `attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ip` varchar(45) DEFAULT NULL,
  `payload` longtext DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `failed_at` datetime DEFAULT NULL,
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
(4, 22, 'asaas', 'cus_000007891989', 'sub_2sfju62w845xahpz', 'starter', 0.00, 'MONTHLY', 'ACTIVE', '2026-06-01', NULL, '2026-05-01 17:59:12', '2026-06-22 15:10:28');

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
(55, 4, 11, 18, NULL, NULL, 111, NULL, 'teste1', 'teste1', 'aguardando_cliente', 'media', 'portal', 'portal', 48, '2026-06-20 17:26:43', '2026-06-18 17:28:08', NULL, NULL, '2026-06-18 20:26:43', '2026-06-18 20:28:08');

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
(90, 55, NULL, 'cliente', 'abertura', 'Solicitação aberta pelo portal.\n\nPrioridade: Média\n\nteste1', '{\"portal_solicitacao_id\":18,\"portal_status\":\"aberto\"}', '2026-06-18 20:26:43', '2026-06-18 20:26:43'),
(91, 55, 111, 'interno', 'resposta', 'teeste', '{\"acao\":\"responder_cliente\",\"origem\":\"painel_interno_suporte\",\"status_anterior\":\"aguardando_cliente\",\"status_novo\":\"aguardando_cliente\",\"responsavel_anterior_id\":111,\"responsavel_novo_id\":111,\"primeira_resposta_registrada\":false,\"visivel_cliente\":true,\"suporte_nome\":\"admin\",\"suporte_email\":\"admin@admin.com\",\"portal_mensagem_id\":118,\"anexos\":[],\"auditoria\":{\"registrado_em\":\"2026-06-18 17:28:08\",\"usuario_id\":111,\"usuario_nome\":\"admin\",\"usuario_email\":\"admin@admin.com\",\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"origem_interface\":\"filament_atendimentos_popup_abrir\"}}', '2026-06-18 20:28:08', '2026-06-18 20:28:08'),
(92, 55, 111, 'interno', 'resposta', '8770', '{\"acao\":\"responder_cliente\",\"origem\":\"painel_interno_suporte\",\"status_anterior\":\"aguardando_cliente\",\"status_novo\":\"aguardando_cliente\",\"responsavel_anterior_id\":111,\"responsavel_novo_id\":111,\"primeira_resposta_registrada\":false,\"visivel_cliente\":true,\"suporte_nome\":\"admin\",\"suporte_email\":\"admin@admin.com\",\"portal_mensagem_id\":119,\"anexos\":[],\"auditoria\":{\"registrado_em\":\"2026-06-18 17:52:42\",\"usuario_id\":111,\"usuario_nome\":\"admin\",\"usuario_email\":\"admin@admin.com\",\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"origem_interface\":\"filament_atendimentos_popup_abrir\"}}', '2026-06-18 20:52:42', '2026-06-18 20:52:42');

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
(136, 11, 111, 'App\\Models\\ItemControle', 51, 'updated', NULL, 'portal_ativo', '0', '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-08 14:30:31', '2026-05-08 14:30:31'),
(149, 4, 111, 'App\\Models\\ItemControle', 31, 'updated', NULL, 'bloqueado_por_dependencia', '0', '0', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-19 14:29:18', '2026-06-19 14:29:18'),
(150, 4, 111, 'App\\Models\\ItemControle', 31, 'updated', NULL, 'bloqueado', '0', '0', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-19 14:29:18', '2026-06-19 14:29:18'),
(151, 4, 111, 'App\\Models\\ItemControle', 31, 'updated', NULL, 'data_vencimento', '\"2026-05-25T03:00:00.000000Z\"', '2026-07-25 00:00:00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-19 14:29:18', '2026-06-19 14:29:18'),
(152, 4, 111, 'App\\Models\\ItemControle', 31, 'updated', NULL, 'blocked_by_dependency', '0', '0', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-19 14:29:18', '2026-06-19 14:29:18'),
(153, NULL, 111, 'App\\Models\\User', 111, 'login.success', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"remember\":false,\"email\":\"admin@admin.com\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-26 11:23:12', '2026-06-26 11:23:12'),
(154, NULL, 111, 'App\\Models\\User', 111, 'login.success', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"remember\":false,\"email\":\"admin@admin.com\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-01 19:44:12', '2026-07-01 19:44:12'),
(155, NULL, 111, 'App\\Models\\User', 111, 'login.success', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"remember\":false,\"email\":\"admin@admin.com\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 11:18:44', '2026-07-02 11:18:44'),
(156, NULL, 111, 'App\\Models\\User', 111, 'logout', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"email\":\"admin@admin.com\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 12:01:12', '2026-07-02 12:01:12'),
(157, 5, 376, 'App\\Models\\User', 376, 'login.success', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"remember\":false,\"email\":\"admin.beta@empresa.com\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 12:02:17', '2026-07-02 12:02:17'),
(158, 5, 376, 'App\\Models\\UserSidebarFavorite', 1, 'created', 'info', 'user_id', NULL, '376', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 12:21:06', '2026-07-02 12:21:06'),
(159, 5, 376, 'App\\Models\\UserSidebarFavorite', 1, 'created', 'info', 'item_key', NULL, 'Documentos|Armazenamento|http://localhost:8000/admin/armazenamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 12:21:06', '2026-07-02 12:21:06'),
(160, 5, 376, 'App\\Models\\UserSidebarFavorite', 1, 'created', 'info', 'position', NULL, '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 12:21:06', '2026-07-02 12:21:06'),
(161, 5, 376, 'App\\Models\\UserSidebarFavorite', 1, 'created', 'info', 'id', NULL, '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 12:21:06', '2026-07-02 12:21:06'),
(162, 5, 376, 'App\\Models\\UserSidebarFavorite', 2, 'created', 'info', 'user_id', NULL, '376', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 12:21:06', '2026-07-02 12:21:06'),
(163, 5, 376, 'App\\Models\\UserSidebarFavorite', 2, 'created', 'info', 'item_key', NULL, 'Administração|Administração|http://localhost:8000/admin/central-administrativa', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 12:21:06', '2026-07-02 12:21:06'),
(164, 5, 376, 'App\\Models\\UserSidebarFavorite', 2, 'created', 'info', 'position', NULL, '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 12:21:06', '2026-07-02 12:21:06'),
(165, 5, 376, 'App\\Models\\UserSidebarFavorite', 2, 'created', 'info', 'id', NULL, '2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 12:21:06', '2026-07-02 12:21:06'),
(166, 5, 376, 'App\\Models\\UserSidebarFavorite', 3, 'created', 'info', 'user_id', NULL, '376', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 12:21:06', '2026-07-02 12:21:06'),
(167, 5, 376, 'App\\Models\\UserSidebarFavorite', 3, 'created', 'info', 'item_key', NULL, 'Auditoria e Riscos|Auditoria|http://localhost:8000/admin/auditoria', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 12:21:06', '2026-07-02 12:21:06'),
(168, 5, 376, 'App\\Models\\UserSidebarFavorite', 3, 'created', 'info', 'position', NULL, '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 12:21:06', '2026-07-02 12:21:06'),
(169, 5, 376, 'App\\Models\\UserSidebarFavorite', 3, 'created', 'info', 'id', NULL, '3', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 12:21:06', '2026-07-02 12:21:06'),
(170, 5, 376, 'App\\Models\\UserSidebarFavorite', 4, 'created', 'info', 'user_id', NULL, '376', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 13:02:01', '2026-07-02 13:02:01'),
(171, 5, 376, 'App\\Models\\UserSidebarFavorite', 4, 'created', 'info', 'item_key', NULL, 'Documentos|Armazenamento|http://localhost:8000/admin/armazenamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 13:02:01', '2026-07-02 13:02:01'),
(172, 5, 376, 'App\\Models\\UserSidebarFavorite', 4, 'created', 'info', 'position', NULL, '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 13:02:01', '2026-07-02 13:02:01'),
(173, 5, 376, 'App\\Models\\UserSidebarFavorite', 4, 'created', 'info', 'id', NULL, '4', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 13:02:01', '2026-07-02 13:02:01'),
(174, 5, 376, 'App\\Models\\UserSidebarFavorite', 5, 'created', 'info', 'user_id', NULL, '376', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 13:10:45', '2026-07-02 13:10:45'),
(175, 5, 376, 'App\\Models\\UserSidebarFavorite', 5, 'created', 'info', 'item_key', NULL, 'Administração|Empresa|http://localhost:8000/admin/empresa-administrativa', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 13:10:45', '2026-07-02 13:10:45'),
(176, 5, 376, 'App\\Models\\UserSidebarFavorite', 5, 'created', 'info', 'position', NULL, '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 13:10:45', '2026-07-02 13:10:45'),
(177, 5, 376, 'App\\Models\\UserSidebarFavorite', 5, 'created', 'info', 'id', NULL, '5', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 13:10:45', '2026-07-02 13:10:45'),
(178, 5, 376, 'App\\Models\\UserSidebarFavorite', 6, 'created', 'info', 'user_id', NULL, '376', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 13:10:45', '2026-07-02 13:10:45'),
(179, 5, 376, 'App\\Models\\UserSidebarFavorite', 6, 'created', 'info', 'item_key', NULL, 'Administração|Administração|http://localhost:8000/admin/central-administrativa', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 13:10:45', '2026-07-02 13:10:45'),
(180, 5, 376, 'App\\Models\\UserSidebarFavorite', 6, 'created', 'info', 'position', NULL, '2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 13:10:45', '2026-07-02 13:10:45'),
(181, 5, 376, 'App\\Models\\UserSidebarFavorite', 6, 'created', 'info', 'id', NULL, '6', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 13:10:45', '2026-07-02 13:10:45'),
(182, NULL, 111, 'App\\Models\\User', 111, 'login.success', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"remember\":false,\"email\":\"admin@admin.com\",\"_auditoria\":{\"evento\":\"login.success\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/login\",\"request_id\":\"83b467ea-7cc1-4c20-b2d9-56c0a52092d3\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 14:56:30', '2026-07-07 14:56:30'),
(183, NULL, 111, 'App\\Models\\User', 111, 'login.success', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"remember\":false,\"email\":\"admin@admin.com\",\"_auditoria\":{\"evento\":\"login.success\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/login\",\"request_id\":\"d3c30554-4baf-4433-b289-d869baab2a5f\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 17:24:56', '2026-07-07 17:24:56'),
(184, NULL, 111, 'App\\Models\\UserSidebarFavorite', 7, 'created', 'info', 'user_id', NULL, '111', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 17:30:33', '2026-07-07 17:30:33'),
(185, NULL, 111, 'App\\Models\\UserSidebarFavorite', 7, 'created', 'info', 'item_key', NULL, 'Clientes e Atendimentos|Clientes e Atendimentos|http://localhost:8000/admin/atendimentos', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 17:30:33', '2026-07-07 17:30:33');
INSERT INTO `auditoria_detalhada` (`id`, `empresa_id`, `user_id`, `auditable_type`, `auditable_id`, `evento`, `nivel`, `campo`, `valor_anterior`, `valor_novo`, `ip`, `user_agent`, `created_at`, `updated_at`) VALUES
(186, NULL, 111, 'App\\Models\\UserSidebarFavorite', 7, 'created', 'info', 'position', NULL, '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 17:30:33', '2026-07-07 17:30:33'),
(187, NULL, 111, 'App\\Models\\UserSidebarFavorite', 7, 'created', 'info', 'id', NULL, '7', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 17:30:33', '2026-07-07 17:30:33'),
(188, NULL, 111, 'App\\Models\\User', 111, 'logout', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"email\":\"admin@admin.com\",\"_auditoria\":{\"evento\":\"logout\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"filament.admin.auth.logout\",\"path\":\"admin/logout\",\"referer\":\"http://localhost:8000/admin/item-controles/anexos-comentarios\",\"request_id\":\"bdbc5871-7055-405f-827e-443e2a16b1e1\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 18:37:27', '2026-07-07 18:37:27'),
(189, 5, 376, 'App\\Models\\User', 376, 'login.success', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"remember\":false,\"email\":\"admin.beta@empresa.com\",\"_auditoria\":{\"evento\":\"login.success\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/login\",\"request_id\":\"b9ddbd03-2f2a-4b07-9258-23e1f1f78d74\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 18:37:53', '2026-07-07 18:37:53'),
(190, 5, 376, 'App\\Models\\User', 376, 'logout', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"email\":\"admin.beta@empresa.com\",\"_auditoria\":{\"evento\":\"logout\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"filament.admin.auth.logout\",\"path\":\"admin/logout\",\"referer\":\"http://localhost:8000/admin/atendimentos\",\"request_id\":\"60fe63a8-c9d8-440d-9100-76664fa88386\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 19:08:12', '2026-07-07 19:08:12'),
(191, 5, 378, 'App\\Models\\User', 378, 'login.success', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"remember\":false,\"email\":\"user.beta1@empresa.com\",\"_auditoria\":{\"evento\":\"login.success\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/login\",\"request_id\":\"74e98011-3b67-4597-81fb-92247dfd2969\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 19:08:21', '2026-07-07 19:08:21'),
(192, 5, 378, 'App\\Models\\User', 378, 'logout', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"email\":\"user.beta1@empresa.com\",\"_auditoria\":{\"evento\":\"logout\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"filament.admin.auth.logout\",\"path\":\"admin/logout\",\"referer\":\"http://localhost:8000/admin/home\",\"request_id\":\"23ce3567-1508-4091-b6a2-c681060f6e4b\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 19:09:18', '2026-07-07 19:09:18'),
(193, 5, 376, 'App\\Models\\User', 376, 'login.success', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"remember\":false,\"email\":\"admin.beta@empresa.com\",\"_auditoria\":{\"evento\":\"login.success\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/login\",\"request_id\":\"2ab19a34-b0b1-4278-bad5-853f14ba4b79\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 19:09:24', '2026-07-07 19:09:24'),
(194, 5, 376, 'App\\Models\\User', 376, 'logout', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"email\":\"admin.beta@empresa.com\",\"_auditoria\":{\"evento\":\"logout\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"filament.admin.auth.logout\",\"path\":\"admin/logout\",\"referer\":\"http://localhost:8000/admin/clientes\",\"request_id\":\"f1f95456-5731-47f8-a424-f8a785499150\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 19:42:44', '2026-07-07 19:42:44'),
(195, NULL, 111, 'App\\Models\\User', 111, 'login.success', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"remember\":false,\"email\":\"admin@admin.com\",\"_auditoria\":{\"evento\":\"login.success\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/login\",\"request_id\":\"0ae5c888-50a4-422e-8c3c-a26816683e03\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 19:42:53', '2026-07-07 19:42:53'),
(196, NULL, 111, 'App\\Models\\User', 111, 'logout', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"email\":\"admin@admin.com\",\"_auditoria\":{\"evento\":\"logout\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"filament.admin.auth.logout\",\"path\":\"admin/logout\",\"referer\":\"http://localhost:8000/admin/clientes\",\"request_id\":\"3eb647ba-6b82-4b0c-a542-ace411418f5d\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:01:17', '2026-07-07 20:01:17'),
(197, 5, 376, 'App\\Models\\User', 376, 'login.success', 'info', 'evento_manual', NULL, '{\"guard\":\"web\",\"remember\":false,\"email\":\"admin.beta@empresa.com\",\"_auditoria\":{\"evento\":\"login.success\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/login\",\"request_id\":\"37f7ff79-9c89-49fb-8ab8-d0417afb7915\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:02:00', '2026-07-07 20:02:00'),
(198, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:02:15', '2026-07-07 20:02:15'),
(199, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'data_vencimento', '\"2026-06-17T03:00:00.000000Z\"', '2026-06-17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:02:15', '2026-07-07 20:02:15'),
(200, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'sla_status', NULL, 'vencido', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:02:15', '2026-07-07 20:02:15'),
(201, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:02:15', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:02:15', '2026-07-07 20:02:15'),
(202, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'urgencia', NULL, 'baixa', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:02:15', '2026-07-07 20:02:15'),
(203, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"status\",\"valor_anterior\":\"pendente\",\"valor_novo\":\"em_andamento\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"d0b7fdbe-ca81-44eb-aac1-2f439f9351e1\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:02:15', '2026-07-07 20:02:15'),
(204, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-06-17T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-06-17\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"15216163-136f-423b-9b2d-7c9483ea86f4\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:02:15', '2026-07-07 20:02:15'),
(205, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"sla_status\",\"valor_anterior\":null,\"valor_novo\":\"vencido\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"1f99fa02-bff9-4b80-a734-ed87fc4e0334\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:02:15', '2026-07-07 20:02:15'),
(206, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'data_vencimento', '\"2026-06-20T03:00:00.000000Z\"', '2026-06-20', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:02:16', '2026-07-07 20:02:16'),
(207, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'sla_limite_em', '\"2026-06-20T19:36:17.000000Z\"', '2026-06-20 16:36:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:02:16', '2026-07-07 20:02:16'),
(208, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:02:16', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:02:16', '2026-07-07 20:02:16'),
(209, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'urgencia', NULL, 'alta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:02:16', '2026-07-07 20:02:16'),
(210, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-06-20T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-06-20\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"1576ad5d-97ff-40ce-8e91-53bade3539f6\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:02:16', '2026-07-07 20:02:16'),
(211, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"sla_limite_em\",\"valor_anterior\":\"\\\"2026-06-20T19:36:17.000000Z\\\"\",\"valor_novo\":\"2026-06-20 16:36:17\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"cba52e1e-8193-41f8-b19f-8aaf6358093f\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:02:16', '2026-07-07 20:02:16'),
(212, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:16', '2026-07-07 20:10:16'),
(213, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'data_vencimento', '\"2026-06-20T03:00:00.000000Z\"', '2026-06-20', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:16', '2026-07-07 20:10:16'),
(214, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'sla_limite_em', '\"2026-06-20T19:36:17.000000Z\"', '2026-06-20 16:36:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:16', '2026-07-07 20:10:16'),
(215, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:10:16', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:16', '2026-07-07 20:10:16'),
(216, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'urgencia', NULL, 'alta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:16', '2026-07-07 20:10:16'),
(217, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"status\",\"valor_anterior\":\"pendente\",\"valor_novo\":\"em_andamento\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"b8bac59d-05d7-45d5-8724-e27fca0f253d\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:16', '2026-07-07 20:10:16'),
(218, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-06-20T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-06-20\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"6ca92720-51e1-4446-8c19-e36d5b1dc851\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:16', '2026-07-07 20:10:16'),
(219, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"sla_limite_em\",\"valor_anterior\":\"\\\"2026-06-20T19:36:17.000000Z\\\"\",\"valor_novo\":\"2026-06-20 16:36:17\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"422cb646-15c1-4789-ad57-686cb6ce8299\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:16', '2026-07-07 20:10:16'),
(220, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:20', '2026-07-07 20:10:20'),
(221, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'data_vencimento', '\"2026-07-09T03:00:00.000000Z\"', '2026-07-09', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:20', '2026-07-07 20:10:20'),
(222, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'sla_limite_em', '\"2026-07-09T19:36:17.000000Z\"', '2026-07-09 16:36:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:20', '2026-07-07 20:10:20'),
(223, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:10:20', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:20', '2026-07-07 20:10:20'),
(224, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'urgencia', NULL, 'media', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:20', '2026-07-07 20:10:20'),
(225, 5, 376, 'App\\Models\\ItemControle', 36, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":36,\"campo\":\"status\",\"valor_anterior\":\"pendente\",\"valor_novo\":\"em_andamento\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"09bdd0ef-0aca-4743-bbff-7f3d2bd74112\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:20', '2026-07-07 20:10:20'),
(226, 5, 376, 'App\\Models\\ItemControle', 36, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":36,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-07-09T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-07-09\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"14cf19f8-dcf9-4876-8a4f-97b275edd6f8\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:20', '2026-07-07 20:10:20'),
(227, 5, 376, 'App\\Models\\ItemControle', 36, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":36,\"campo\":\"sla_limite_em\",\"valor_anterior\":\"\\\"2026-07-09T19:36:17.000000Z\\\"\",\"valor_novo\":\"2026-07-09 16:36:17\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"28835d8f-ebdb-4479-bf3c-93f1c6afc891\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:20', '2026-07-07 20:10:20'),
(228, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'status', 'em_andamento', 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:26', '2026-07-07 20:10:26'),
(229, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'data_vencimento', '\"2026-06-17T03:00:00.000000Z\"', '2026-06-17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:26', '2026-07-07 20:10:26'),
(230, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:10:26', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:26', '2026-07-07 20:10:26'),
(231, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'urgencia', NULL, 'baixa', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:26', '2026-07-07 20:10:26'),
(232, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"status\",\"valor_anterior\":\"em_andamento\",\"valor_novo\":\"pendente\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"5d068929-14a6-4ecf-9e77-02a71b9c4bb0\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:26', '2026-07-07 20:10:26'),
(233, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-06-17T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-06-17\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"671768fe-b7f0-4a99-9503-8971daee782c\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:26', '2026-07-07 20:10:26'),
(234, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'status', 'em_andamento', 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:29', '2026-07-07 20:10:29'),
(235, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'data_vencimento', '\"2026-07-09T03:00:00.000000Z\"', '2026-07-09', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:29', '2026-07-07 20:10:29'),
(236, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'sla_limite_em', '\"2026-07-09T19:36:17.000000Z\"', '2026-07-09 16:36:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:29', '2026-07-07 20:10:29'),
(237, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:10:29', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:29', '2026-07-07 20:10:29'),
(238, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'urgencia', NULL, 'media', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:29', '2026-07-07 20:10:29'),
(239, 5, 376, 'App\\Models\\ItemControle', 36, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":36,\"campo\":\"status\",\"valor_anterior\":\"em_andamento\",\"valor_novo\":\"pendente\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"59ed0614-3c96-48e0-8d9a-7af57f40ecaf\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:29', '2026-07-07 20:10:29'),
(240, 5, 376, 'App\\Models\\ItemControle', 36, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":36,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-07-09T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-07-09\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"50171f59-ce2b-447e-ba5b-92f3611ddc31\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:29', '2026-07-07 20:10:29'),
(241, 5, 376, 'App\\Models\\ItemControle', 36, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":36,\"campo\":\"sla_limite_em\",\"valor_anterior\":\"\\\"2026-07-09T19:36:17.000000Z\\\"\",\"valor_novo\":\"2026-07-09 16:36:17\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"c4689eab-5156-4139-969f-38c64f0fb0f7\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:29', '2026-07-07 20:10:29'),
(242, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'status', 'em_andamento', 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:31', '2026-07-07 20:10:31'),
(243, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'data_vencimento', '\"2026-06-20T03:00:00.000000Z\"', '2026-06-20', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:31', '2026-07-07 20:10:31'),
(244, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'sla_limite_em', '\"2026-06-20T19:36:17.000000Z\"', '2026-06-20 16:36:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:31', '2026-07-07 20:10:31'),
(245, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:10:31', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:31', '2026-07-07 20:10:31'),
(246, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'urgencia', NULL, 'alta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:31', '2026-07-07 20:10:31'),
(247, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"status\",\"valor_anterior\":\"em_andamento\",\"valor_novo\":\"pendente\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"7d50dc5c-3b0f-46a4-aff8-0b418d626eb9\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:31', '2026-07-07 20:10:31'),
(248, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-06-20T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-06-20\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"a1faa886-8ee3-41c6-bad3-f843209e07ca\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:31', '2026-07-07 20:10:31'),
(249, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"sla_limite_em\",\"valor_anterior\":\"\\\"2026-06-20T19:36:17.000000Z\\\"\",\"valor_novo\":\"2026-06-20 16:36:17\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"8a344c4b-b124-4807-9e20-b1eb1d445a51\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:31', '2026-07-07 20:10:31'),
(250, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:33', '2026-07-07 20:10:33'),
(251, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'data_vencimento', '\"2026-06-17T03:00:00.000000Z\"', '2026-06-17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:33', '2026-07-07 20:10:33'),
(252, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:10:33', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:33', '2026-07-07 20:10:33'),
(253, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'urgencia', NULL, 'baixa', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:33', '2026-07-07 20:10:33'),
(254, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"status\",\"valor_anterior\":\"pendente\",\"valor_novo\":\"em_andamento\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"d87c18b6-9eae-4564-8eed-2c355cb538c0\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:33', '2026-07-07 20:10:33'),
(255, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-06-17T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-06-17\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"2e4a8def-d580-4c29-a1d5-54afcfd0608d\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:33', '2026-07-07 20:10:33'),
(256, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:40', '2026-07-07 20:10:40'),
(257, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'data_vencimento', '\"2026-07-09T03:00:00.000000Z\"', '2026-07-09', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:40', '2026-07-07 20:10:40'),
(258, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'sla_limite_em', '\"2026-07-09T19:36:17.000000Z\"', '2026-07-09 16:36:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:40', '2026-07-07 20:10:40'),
(259, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:10:40', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:40', '2026-07-07 20:10:40'),
(260, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'urgencia', NULL, 'media', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:40', '2026-07-07 20:10:40'),
(261, 5, 376, 'App\\Models\\ItemControle', 36, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":36,\"campo\":\"status\",\"valor_anterior\":\"pendente\",\"valor_novo\":\"em_andamento\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"30b96ffa-5006-435f-a576-e6d5077b8124\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:40', '2026-07-07 20:10:40'),
(262, 5, 376, 'App\\Models\\ItemControle', 36, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":36,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-07-09T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-07-09\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"f276af78-c4a2-41b8-87cc-162a6877eeab\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:40', '2026-07-07 20:10:40'),
(263, 5, 376, 'App\\Models\\ItemControle', 36, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":36,\"campo\":\"sla_limite_em\",\"valor_anterior\":\"\\\"2026-07-09T19:36:17.000000Z\\\"\",\"valor_novo\":\"2026-07-09 16:36:17\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"98a916ef-24ad-444d-8ec7-30a7612cd235\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:40', '2026-07-07 20:10:40'),
(264, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:43', '2026-07-07 20:10:43'),
(265, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'data_vencimento', '\"2026-06-20T03:00:00.000000Z\"', '2026-06-20', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:43', '2026-07-07 20:10:43'),
(266, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'sla_limite_em', '\"2026-06-20T19:36:17.000000Z\"', '2026-06-20 16:36:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:43', '2026-07-07 20:10:43'),
(267, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:10:43', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:43', '2026-07-07 20:10:43'),
(268, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'urgencia', NULL, 'alta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:43', '2026-07-07 20:10:43'),
(269, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"status\",\"valor_anterior\":\"pendente\",\"valor_novo\":\"em_andamento\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"e4b65524-8465-4963-a51e-9da97aba694c\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:43', '2026-07-07 20:10:43'),
(270, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-06-20T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-06-20\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"b93b6f59-1250-461b-b04d-60209efe71a3\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:43', '2026-07-07 20:10:43'),
(271, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"sla_limite_em\",\"valor_anterior\":\"\\\"2026-06-20T19:36:17.000000Z\\\"\",\"valor_novo\":\"2026-06-20 16:36:17\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"b055f6dc-2670-49f5-b466-984bfa188b14\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:43', '2026-07-07 20:10:43'),
(272, 5, 376, 'App\\Models\\ItemControle', 35, 'updated', 'info', 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:44', '2026-07-07 20:10:44'),
(273, 5, 376, 'App\\Models\\ItemControle', 35, 'updated', 'info', 'data_vencimento', '\"2026-07-05T03:00:00.000000Z\"', '2026-07-05', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:44', '2026-07-07 20:10:44'),
(274, 5, 376, 'App\\Models\\ItemControle', 35, 'updated', 'info', 'sla_limite_em', '\"2026-07-05T19:36:17.000000Z\"', '2026-07-05 16:36:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:44', '2026-07-07 20:10:44'),
(275, 5, 376, 'App\\Models\\ItemControle', 35, 'updated', 'info', 'sla_status', 'em_andamento', 'vencido', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:44', '2026-07-07 20:10:44'),
(276, 5, 376, 'App\\Models\\ItemControle', 35, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:10:44', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:44', '2026-07-07 20:10:44'),
(277, 5, 376, 'App\\Models\\ItemControle', 35, 'updated', 'info', 'urgencia', NULL, 'media', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:44', '2026-07-07 20:10:44'),
(278, 5, 376, 'App\\Models\\ItemControle', 35, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":35,\"campo\":\"status\",\"valor_anterior\":\"pendente\",\"valor_novo\":\"em_andamento\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"1532e68a-77e5-42ca-b14a-a8bb288785d1\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:44', '2026-07-07 20:10:44'),
(279, 5, 376, 'App\\Models\\ItemControle', 35, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":35,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-07-05T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-07-05\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"12409aa8-7161-41ad-a21d-2d159c8cdae9\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:44', '2026-07-07 20:10:44'),
(280, 5, 376, 'App\\Models\\ItemControle', 35, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":35,\"campo\":\"sla_limite_em\",\"valor_anterior\":\"\\\"2026-07-05T19:36:17.000000Z\\\"\",\"valor_novo\":\"2026-07-05 16:36:17\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"b3569d7e-9c74-42a3-9a8c-fa52449def50\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:44', '2026-07-07 20:10:44'),
(281, 5, 376, 'App\\Models\\ItemControle', 35, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":35,\"campo\":\"sla_status\",\"valor_anterior\":\"em_andamento\",\"valor_novo\":\"vencido\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"7a662bd0-fd7b-423c-a865-3a0aff0a8dbc\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:44', '2026-07-07 20:10:44'),
(282, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'status', 'em_andamento', 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:51', '2026-07-07 20:10:51'),
(283, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'data_vencimento', '\"2026-06-17T03:00:00.000000Z\"', '2026-06-17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:51', '2026-07-07 20:10:51'),
(284, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:10:51', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:51', '2026-07-07 20:10:51'),
(285, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'urgencia', NULL, 'baixa', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:51', '2026-07-07 20:10:51'),
(286, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"status\",\"valor_anterior\":\"em_andamento\",\"valor_novo\":\"pendente\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"02abcd90-0773-4c15-a0c4-3e36d5b79748\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:51', '2026-07-07 20:10:51');
INSERT INTO `auditoria_detalhada` (`id`, `empresa_id`, `user_id`, `auditable_type`, `auditable_id`, `evento`, `nivel`, `campo`, `valor_anterior`, `valor_novo`, `ip`, `user_agent`, `created_at`, `updated_at`) VALUES
(287, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-06-17T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-06-17\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"9714ac81-86fc-4cc4-8cec-4d084d1fe328\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:51', '2026-07-07 20:10:51'),
(288, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'status', 'em_andamento', 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:52', '2026-07-07 20:10:52'),
(289, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'data_vencimento', '\"2026-06-20T03:00:00.000000Z\"', '2026-06-20', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:52', '2026-07-07 20:10:52'),
(290, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'sla_limite_em', '\"2026-06-20T19:36:17.000000Z\"', '2026-06-20 16:36:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:52', '2026-07-07 20:10:52'),
(291, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:10:52', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:52', '2026-07-07 20:10:52'),
(292, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'urgencia', NULL, 'alta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:52', '2026-07-07 20:10:52'),
(293, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"status\",\"valor_anterior\":\"em_andamento\",\"valor_novo\":\"pendente\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"04fe8235-3023-4052-881c-2f0fdf51641d\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:52', '2026-07-07 20:10:52'),
(294, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-06-20T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-06-20\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"c26009fc-ae10-403f-a62e-bf4ac7c2ebd5\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:52', '2026-07-07 20:10:52'),
(295, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"sla_limite_em\",\"valor_anterior\":\"\\\"2026-06-20T19:36:17.000000Z\\\"\",\"valor_novo\":\"2026-06-20 16:36:17\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"0a9035ec-fecc-4850-a1b9-7af643f4daee\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:52', '2026-07-07 20:10:52'),
(296, 5, 376, 'App\\Models\\ItemControle', 35, 'updated', 'info', 'status', 'em_andamento', 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:54', '2026-07-07 20:10:54'),
(297, 5, 376, 'App\\Models\\ItemControle', 35, 'updated', 'info', 'data_vencimento', '\"2026-07-05T03:00:00.000000Z\"', '2026-07-05', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:54', '2026-07-07 20:10:54'),
(298, 5, 376, 'App\\Models\\ItemControle', 35, 'updated', 'info', 'sla_limite_em', '\"2026-07-05T19:36:17.000000Z\"', '2026-07-05 16:36:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:54', '2026-07-07 20:10:54'),
(299, 5, 376, 'App\\Models\\ItemControle', 35, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:10:54', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:54', '2026-07-07 20:10:54'),
(300, 5, 376, 'App\\Models\\ItemControle', 35, 'updated', 'info', 'urgencia', NULL, 'media', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:54', '2026-07-07 20:10:54'),
(301, 5, 376, 'App\\Models\\ItemControle', 35, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":35,\"campo\":\"status\",\"valor_anterior\":\"em_andamento\",\"valor_novo\":\"pendente\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"bf08241e-7414-40dc-a074-18010f12ac09\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:54', '2026-07-07 20:10:54'),
(302, 5, 376, 'App\\Models\\ItemControle', 35, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":35,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-07-05T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-07-05\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"34a0ee96-8a64-465d-9bbb-c9f6997c67b3\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:54', '2026-07-07 20:10:54'),
(303, 5, 376, 'App\\Models\\ItemControle', 35, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":35,\"campo\":\"sla_limite_em\",\"valor_anterior\":\"\\\"2026-07-05T19:36:17.000000Z\\\"\",\"valor_novo\":\"2026-07-05 16:36:17\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"8eefe2b0-01a1-48b6-ac47-c7204e407043\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:54', '2026-07-07 20:10:54'),
(304, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'status', 'em_andamento', 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:58', '2026-07-07 20:10:58'),
(305, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'data_vencimento', '\"2026-07-09T03:00:00.000000Z\"', '2026-07-09', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:58', '2026-07-07 20:10:58'),
(306, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'sla_limite_em', '\"2026-07-09T19:36:17.000000Z\"', '2026-07-09 16:36:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:58', '2026-07-07 20:10:58'),
(307, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:10:58', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:58', '2026-07-07 20:10:58'),
(308, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'urgencia', NULL, 'media', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:58', '2026-07-07 20:10:58'),
(309, 5, 376, 'App\\Models\\ItemControle', 36, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":36,\"campo\":\"status\",\"valor_anterior\":\"em_andamento\",\"valor_novo\":\"pendente\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"d1d4d44e-4811-4e1a-91d2-a71f1ed6ed9f\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:58', '2026-07-07 20:10:58'),
(310, 5, 376, 'App\\Models\\ItemControle', 36, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":36,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-07-09T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-07-09\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"cfe5a34a-7c4e-4606-a7ec-fbad6ff9f996\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:58', '2026-07-07 20:10:58'),
(311, 5, 376, 'App\\Models\\ItemControle', 36, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":36,\"campo\":\"sla_limite_em\",\"valor_anterior\":\"\\\"2026-07-09T19:36:17.000000Z\\\"\",\"valor_novo\":\"2026-07-09 16:36:17\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"398f3501-3e77-4b07-a365-301fcf0a5cde\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:10:58', '2026-07-07 20:10:58'),
(312, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:02', '2026-07-07 20:11:02'),
(313, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'data_vencimento', '\"2026-06-17T03:00:00.000000Z\"', '2026-06-17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:02', '2026-07-07 20:11:02'),
(314, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:11:02', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:02', '2026-07-07 20:11:02'),
(315, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'urgencia', NULL, 'baixa', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:02', '2026-07-07 20:11:02'),
(316, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"status\",\"valor_anterior\":\"pendente\",\"valor_novo\":\"em_andamento\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"ae03e99d-a5e4-4bb2-b903-c98eee465426\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:02', '2026-07-07 20:11:02'),
(317, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-06-17T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-06-17\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"f7fb05e1-a8a1-4a4e-a66d-ae3e3bada956\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:02', '2026-07-07 20:11:02'),
(318, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:03', '2026-07-07 20:11:03'),
(319, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'data_vencimento', '\"2026-06-20T03:00:00.000000Z\"', '2026-06-20', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:03', '2026-07-07 20:11:03'),
(320, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'sla_limite_em', '\"2026-06-20T19:36:17.000000Z\"', '2026-06-20 16:36:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:03', '2026-07-07 20:11:03'),
(321, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:11:03', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:03', '2026-07-07 20:11:03'),
(322, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'urgencia', NULL, 'alta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:03', '2026-07-07 20:11:03'),
(323, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"status\",\"valor_anterior\":\"pendente\",\"valor_novo\":\"em_andamento\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"4c013f25-eac6-4c4a-a49a-89869c984555\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:03', '2026-07-07 20:11:03'),
(324, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-06-20T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-06-20\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"43a0d738-5bc3-4c57-88b9-3828e9abf426\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:03', '2026-07-07 20:11:03'),
(325, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"sla_limite_em\",\"valor_anterior\":\"\\\"2026-06-20T19:36:17.000000Z\\\"\",\"valor_novo\":\"2026-06-20 16:36:17\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"cadacf0d-b786-41e1-aa3c-c541fa519470\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:03', '2026-07-07 20:11:03'),
(326, 5, 376, 'App\\Models\\ItemControle', 35, 'updated', 'info', 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:05', '2026-07-07 20:11:05'),
(327, 5, 376, 'App\\Models\\ItemControle', 35, 'updated', 'info', 'data_vencimento', '\"2026-07-05T03:00:00.000000Z\"', '2026-07-05', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:05', '2026-07-07 20:11:05'),
(328, 5, 376, 'App\\Models\\ItemControle', 35, 'updated', 'info', 'sla_limite_em', '\"2026-07-05T19:36:17.000000Z\"', '2026-07-05 16:36:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:05', '2026-07-07 20:11:05'),
(329, 5, 376, 'App\\Models\\ItemControle', 35, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:11:05', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:05', '2026-07-07 20:11:05'),
(330, 5, 376, 'App\\Models\\ItemControle', 35, 'updated', 'info', 'urgencia', NULL, 'media', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:05', '2026-07-07 20:11:05'),
(331, 5, 376, 'App\\Models\\ItemControle', 35, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":35,\"campo\":\"status\",\"valor_anterior\":\"pendente\",\"valor_novo\":\"em_andamento\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"6e98ad5a-a86f-438f-a857-616ee11d87ad\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:05', '2026-07-07 20:11:05'),
(332, 5, 376, 'App\\Models\\ItemControle', 35, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":35,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-07-05T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-07-05\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"43d13727-7737-4ef2-8891-a4ec56b50e01\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:05', '2026-07-07 20:11:05'),
(333, 5, 376, 'App\\Models\\ItemControle', 35, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":35,\"campo\":\"sla_limite_em\",\"valor_anterior\":\"\\\"2026-07-05T19:36:17.000000Z\\\"\",\"valor_novo\":\"2026-07-05 16:36:17\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"cff6fe68-5e3c-458e-bb0a-c72baea47c70\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:05', '2026-07-07 20:11:05'),
(334, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'status', 'pendente', 'em_andamento', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:07', '2026-07-07 20:11:07'),
(335, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'data_vencimento', '\"2026-07-09T03:00:00.000000Z\"', '2026-07-09', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:07', '2026-07-07 20:11:07'),
(336, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'sla_limite_em', '\"2026-07-09T19:36:17.000000Z\"', '2026-07-09 16:36:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:07', '2026-07-07 20:11:07'),
(337, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:11:07', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:07', '2026-07-07 20:11:07'),
(338, 5, 376, 'App\\Models\\ItemControle', 36, 'updated', 'info', 'urgencia', NULL, 'media', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:07', '2026-07-07 20:11:07'),
(339, 5, 376, 'App\\Models\\ItemControle', 36, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":36,\"campo\":\"status\",\"valor_anterior\":\"pendente\",\"valor_novo\":\"em_andamento\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"9b80fc77-bb01-48de-96f5-0fb40eb44c6f\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:07', '2026-07-07 20:11:07'),
(340, 5, 376, 'App\\Models\\ItemControle', 36, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":36,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-07-09T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-07-09\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"9b05e534-8e07-4739-9fd6-cce224c509d1\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:07', '2026-07-07 20:11:07'),
(341, 5, 376, 'App\\Models\\ItemControle', 36, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":36,\"campo\":\"sla_limite_em\",\"valor_anterior\":\"\\\"2026-07-09T19:36:17.000000Z\\\"\",\"valor_novo\":\"2026-07-09 16:36:17\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"4358dcbb-c7dd-420b-bed0-496334c233d1\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:07', '2026-07-07 20:11:07'),
(342, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'status', 'em_andamento', 'concluido', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:10', '2026-07-07 20:11:10'),
(343, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'data_vencimento', '\"2026-06-17T03:00:00.000000Z\"', '2026-06-17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:10', '2026-07-07 20:11:10'),
(344, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'data_conclusao', NULL, '2026-07-07 17:11:10', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:10', '2026-07-07 20:11:10'),
(345, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'sla_concluido_em', NULL, '2026-07-07 17:11:10', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:10', '2026-07-07 20:11:10'),
(346, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'sla_status', 'vencido', 'concluido_atrasado', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:10', '2026-07-07 20:11:10'),
(347, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:11:10', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:10', '2026-07-07 20:11:10'),
(348, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'urgencia', NULL, 'baixa', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:10', '2026-07-07 20:11:10'),
(349, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"status\",\"valor_anterior\":\"em_andamento\",\"valor_novo\":\"concluido\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"2cccd35e-b153-4d1b-91ff-01e49d0afdd6\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:10', '2026-07-07 20:11:10'),
(350, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-06-17T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-06-17\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"2f8a80e9-26dd-4a83-8c8a-be5254d282de\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:10', '2026-07-07 20:11:10'),
(351, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"sla_concluido_em\",\"valor_anterior\":null,\"valor_novo\":\"2026-07-07 17:11:10\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"d95fa557-e69d-4a50-a6a1-d049d9d2c6b7\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:10', '2026-07-07 20:11:10'),
(352, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"sla_status\",\"valor_anterior\":\"vencido\",\"valor_novo\":\"concluido_atrasado\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"773a9cd3-419b-401d-9bb8-5470c109f5cd\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:10', '2026-07-07 20:11:10'),
(353, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'status', 'concluido', 'pendente', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:14', '2026-07-07 20:11:14'),
(354, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'data_vencimento', '\"2026-06-17T03:00:00.000000Z\"', '2026-06-17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:14', '2026-07-07 20:11:14'),
(355, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'data_conclusao', '\"2026-07-07T03:00:00.000000Z\"', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:14', '2026-07-07 20:11:14'),
(356, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'sla_concluido_em', '\"2026-07-07T20:11:10.000000Z\"', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:14', '2026-07-07 20:11:14'),
(357, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:11:14', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:14', '2026-07-07 20:11:14'),
(358, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'urgencia', NULL, 'baixa', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:14', '2026-07-07 20:11:14'),
(359, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"status\",\"valor_anterior\":\"concluido\",\"valor_novo\":\"pendente\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"b964c85c-cd38-443a-a4c2-b0df3b0f2202\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:14', '2026-07-07 20:11:14'),
(360, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-06-17T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-06-17\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"6df68f6c-2e39-4fe7-bb75-8b64bafabb58\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:14', '2026-07-07 20:11:14'),
(361, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"sla_concluido_em\",\"valor_anterior\":\"\\\"2026-07-07T20:11:10.000000Z\\\"\",\"valor_novo\":null},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"77e18d91-df28-464f-8e43-92d309f51569\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:14', '2026-07-07 20:11:14'),
(362, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'status', 'pendente', 'concluido', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:20', '2026-07-07 20:11:20'),
(363, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'data_vencimento', '\"2026-06-17T03:00:00.000000Z\"', '2026-06-17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:20', '2026-07-07 20:11:20'),
(364, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'data_conclusao', NULL, '2026-07-07 17:11:20', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:20', '2026-07-07 20:11:20'),
(365, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'sla_concluido_em', NULL, '2026-07-07 17:11:20', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:20', '2026-07-07 20:11:20'),
(366, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:11:20', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:20', '2026-07-07 20:11:20'),
(367, 5, 376, 'App\\Models\\ItemControle', 80, 'updated', 'info', 'urgencia', NULL, 'baixa', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:20', '2026-07-07 20:11:20'),
(368, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"status\",\"valor_anterior\":\"pendente\",\"valor_novo\":\"concluido\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"f6417d8b-7247-4bdc-a126-65fd7c759d60\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:20', '2026-07-07 20:11:20'),
(369, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-06-17T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-06-17\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"74bf1ca2-a6fa-45e1-9fe5-5001b1cc9264\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:20', '2026-07-07 20:11:20'),
(370, 5, 376, 'App\\Models\\ItemControle', 80, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":80,\"campo\":\"sla_concluido_em\",\"valor_anterior\":null,\"valor_novo\":\"2026-07-07 17:11:20\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"86eaf124-ca6e-46c3-8628-f4f93afb48d6\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:20', '2026-07-07 20:11:20'),
(371, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'status', 'em_andamento', 'concluido', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:25', '2026-07-07 20:11:25'),
(372, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'data_vencimento', '\"2026-06-20T03:00:00.000000Z\"', '2026-06-20', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:25', '2026-07-07 20:11:25'),
(373, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'data_conclusao', NULL, '2026-07-07 17:11:25', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:25', '2026-07-07 20:11:25'),
(374, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'sla_limite_em', '\"2026-06-20T19:36:17.000000Z\"', '2026-06-20 16:36:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:25', '2026-07-07 20:11:25'),
(375, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'sla_concluido_em', NULL, '2026-07-07 17:11:25', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:25', '2026-07-07 20:11:25'),
(376, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'sla_status', 'vencido', 'concluido_atrasado', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:25', '2026-07-07 20:11:25'),
(377, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'status_operacional_at', NULL, '2026-07-07 17:11:25', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:25', '2026-07-07 20:11:25'),
(378, 5, 376, 'App\\Models\\ItemControle', 34, 'updated', 'info', 'urgencia', NULL, 'alta', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:25', '2026-07-07 20:11:25'),
(379, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.status.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"status\",\"valor_anterior\":\"em_andamento\",\"valor_novo\":\"concluido\"},\"_auditoria\":{\"evento\":\"item_controle.status.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"d7d82766-91e7-4a67-a8b7-a4e2a99eb9e7\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:25', '2026-07-07 20:11:25'),
(380, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.vencimento.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"data_vencimento\",\"valor_anterior\":\"\\\"2026-06-20T03:00:00.000000Z\\\"\",\"valor_novo\":\"2026-06-20\"},\"_auditoria\":{\"evento\":\"item_controle.vencimento.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"5a730890-e311-4a9b-b9ff-16b199f9d987\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:25', '2026-07-07 20:11:25'),
(381, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"sla_limite_em\",\"valor_anterior\":\"\\\"2026-06-20T19:36:17.000000Z\\\"\",\"valor_novo\":\"2026-06-20 16:36:17\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"97001396-6233-409d-8444-2a005a5f3bba\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:25', '2026-07-07 20:11:25'),
(382, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"sla_concluido_em\",\"valor_anterior\":null,\"valor_novo\":\"2026-07-07 17:11:25\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"e1b90afd-aa6c-496a-97f1-043db985389f\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:25', '2026-07-07 20:11:25'),
(383, 5, 376, 'App\\Models\\ItemControle', 34, 'item_controle.sla.changed', 'warning', 'evento_manual', NULL, '{\"dominio\":\"acao_critica\",\"origem\":\"auditoria_global_observer\",\"dados\":{\"acao\":\"updated\",\"tabela\":\"item_controles\",\"registro_id\":34,\"campo\":\"sla_status\",\"valor_anterior\":\"vencido\",\"valor_novo\":\"concluido_atrasado\"},\"_auditoria\":{\"evento\":\"item_controle.sla.changed\",\"canal\":\"http\",\"metodo\":\"POST\",\"rota\":\"default-livewire.update\",\"path\":\"livewire-50fe612b/update\",\"referer\":\"http://localhost:8000/admin/kanban\",\"request_id\":\"5d3634a8-4c1f-4da9-9d11-97af2b1dd401\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:11:25', '2026-07-07 20:11:25'),
(384, 5, 376, 'App\\Models\\ItemControleComentario', 10, 'created', 'info', 'item_controle_id', NULL, '35', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:28:30', '2026-07-07 20:28:30'),
(385, 5, 376, 'App\\Models\\ItemControleComentario', 10, 'created', 'info', 'user_id', NULL, '376', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:28:30', '2026-07-07 20:28:30'),
(386, 5, 376, 'App\\Models\\ItemControleComentario', 10, 'created', 'info', 'comentario', NULL, 'teste', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:28:30', '2026-07-07 20:28:30'),
(387, 5, 376, 'App\\Models\\ItemControleComentario', 10, 'created', 'info', 'id', NULL, '10', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 20:28:30', '2026-07-07 20:28:30');

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
-- Estrutura da tabela `backup_client_portal_messages`
--

CREATE TABLE `backup_client_portal_messages` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `client_id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `backup_prazzu_client_messages`
--

CREATE TABLE `backup_prazzu_client_messages` (
  `id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_controle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_email` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachment_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direction` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal_to_client',
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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

--
-- Extraindo dados da tabela `cliente_portal_users`
--

INSERT INTO `cliente_portal_users` (`id`, `empresa_id`, `nome`, `email`, `password`, `telefone`, `cargo`, `ativo`, `email_verified_at`, `ultimo_acesso_em`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 4, 'ricardo', 'ricardo-s-a@hotmail.com', '$2y$12$Y70oncoXwB6GfawD07lOzeJNfQ2GXvz.txpTRENaAT4YECMrD0AZS', '11111111111111', NULL, 1, NULL, '2026-06-18 19:39:32', NULL, '2026-06-18 18:59:18', '2026-06-18 19:39:32'),
(2, 4, 'joyce', 'joyce@joyce.com', '$2y$12$//gxoQft.35c83oeJf6VZO9IxPXPzUQNKvOciGZl.Zd3qQ2tay5Ou', NULL, NULL, 1, NULL, '2026-06-18 20:32:16', NULL, '2026-06-18 19:40:13', '2026-06-18 20:32:16');

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
  `limite_armazenamento_mb` int(10) UNSIGNED DEFAULT NULL,
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

INSERT INTO `empresas` (`id`, `razao_social`, `nome_fantasia`, `cnpj`, `email`, `telefone`, `responsavel_nome`, `status`, `created_at`, `updated_at`, `plano`, `limite_usuarios`, `limite_itens`, `limite_armazenamento_mb`, `limite_interacoes_ia`, `ativo`, `crm_status_contrato`, `crm_contato_nome`, `crm_contato_email`, `crm_contato_whatsapp`, `crm_health_manual`, `crm_observacoes`, `crm_ultima_reuniao_em`, `portal_token`, `portal_ativo`, `portal_expira_em`) VALUES
(4, 'Empresa Alpha LTDA', 'Alpha', '11111111000101', 'alpha@email.com', '11999990001', 'Admin Alpha', 'ativo', '2026-04-24 17:57:10', '2026-06-18 19:37:55', 'enterprise', 999999, 999999, 40960, 15000, 1, 'em_implementacao', 'Admin Alpha', 'alpha@email.com', '11999990001', NULL, NULL, NULL, '6Gn3Oz4nDDaQSBXXKuvexIErlylEeq8PwfXaeLLegS4pBhVANIcsEwn44fXlqFGZ', 1, '2027-05-08 15:25:44'),
(5, 'Empresa Beta LTDA', 'Beta', '22222222000102', 'beta@email.com', '11999990002', 'Admin Beta', 'ativo', '2026-04-24 17:57:10', '2026-06-22 18:10:10', 'starter', 3, 200, 6144, 150, 1, 'Ativo', 'Admin Beta', 'beta@email.com', '11999990002', NULL, NULL, NULL, '539170b44b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(6, 'Empresa Gamma LTDA', 'Gamma', '33333333000103', 'gamma@email.com', '11999990003', 'Admin Gamma', 'ativo', '2026-04-24 17:57:10', '2026-05-08 19:01:59', 'starter', 3, 200, 6144, 150, 1, 'Ativo', 'Admin Gamma', 'gamma@email.com', '11999990003', NULL, NULL, NULL, '539171ef4b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(7, 'Empresa Teste Completa LTDA', 'Teste Completo', '99999999000199', 'testecompleto@prazzu.com', '11999999999', 'Administrador Teste', 'ativo', '2026-04-28 14:58:04', '2026-05-08 19:01:59', 'enterprise', 999999, 999999, 40960, 15000, 1, 'Ativo', 'Administrador Teste', 'testecompleto@prazzu.com', '11999999999', NULL, NULL, NULL, '539172364b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(8, 'Empresa Teste Completa LTDA', 'Teste Completo', '99999999000199', 'testecompleto@prazzu.com', '11999999999', 'Administrador Teste', 'ativo', '2026-04-28 14:58:14', '2026-05-08 19:01:59', 'enterprise', 999999, 999999, 40960, 15000, 1, 'Ativo', 'Administrador Teste', 'testecompleto@prazzu.com', '11999999999', NULL, NULL, NULL, '539172e84b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(9, 'Empresa Teste Completa LTDA', 'Teste Completo', '99999999000195', 'testecompleto@prazzu.com', '11999999999', 'Administrador Teste', 'ativo', '2026-04-28 14:59:54', '2026-05-08 19:01:59', 'business_plus', 50, 10000, 20480, 5000, 1, 'Ativo', 'Administrador Teste', 'testecompleto@prazzu.com', '11999999999', NULL, NULL, NULL, '539173254b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(10, 'Empresa Teste Completa LTDA', 'Teste Completo', '99999999000193', 'testecompleto@prazzu.com', '11999999999', NULL, 'ativo', '2026-04-28 14:59:58', '2026-05-08 19:01:59', 'business', 25, 5000, 10240, 2000, 1, 'Ativo', NULL, 'testecompleto@prazzu.com', '11999999999', NULL, NULL, NULL, '5391736d4b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(11, 'Empresa Seed Teste LTDA', 'Seed Teste', '88999999000181', 'empresa.seed@prazzu.com', '11999998888', NULL, 'ativo', '2026-04-28 15:06:22', '2026-06-18 18:51:59', 'profissional', 3, 200, 15360, 2000, 1, 'Ativo', NULL, 'empresa.seed@prazzu.com', '11999998888', NULL, NULL, NULL, '37374CjmDpb7q94AY9AlCPiuHypk7xallwZtJyJh15JmKXxISkrjBBdQcTONanXU', 1, '2027-05-08 15:25:44'),
(13, 'webconta', 'webconta', '11222333000181', 'webconta@webconta.com', '(11) 90000-0000', NULL, 'ativo', '2026-05-01 19:09:37', '2026-05-08 19:01:59', 'profissional', 3, 200, 15360, 2000, 1, 'Ativo', NULL, 'webconta@webconta.com', '(11) 90000-0000', NULL, NULL, NULL, '539173eb4b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(18, 'webconta2', 'webconta2', '12345678000195', 'roni@roni2.com', '(11) 90000-0000', NULL, 'ativo', '2026-05-01 20:35:39', '2026-05-08 19:01:59', 'business_plus', 50, 10000, 20480, 5000, 1, 'Ativo', NULL, 'roni@roni2.com', '(11) 90000-0000', NULL, NULL, NULL, '539174614b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(21, 'ricardo empresa', 'ricardo empresa', '32.724.443/0001-11', 'ricardo-s-a@hotmail.com', '(11) 90000-0000', NULL, 'ativo', '2026-05-01 20:53:08', '2026-05-08 19:01:59', 'business', 25, 5000, 10240, 2000, 1, 'Ativo', NULL, 'ricardo-s-a@hotmail.com', '(11) 90000-0000', NULL, NULL, NULL, '5391749c4b0b11f1933d18a59cb167c9', 1, '2027-05-08 15:25:44'),
(22, 'joyce empresa', 'joyce empresa', '43.061.009/0001-15', 'joyce@joyce.com', '(11) 90000-0000', NULL, 'ativo', '2026-05-01 20:59:09', '2026-06-22 18:10:28', 'starter', 3, 200, 6144, 150, 1, 'Implementação', NULL, 'joyce@joyce.com', '(11) 90000-0000', NULL, NULL, NULL, 'ajlFeRYijcQCzx4cAJCuuZuqDTktgXrcSNS2zCXpuqGnnfCoufQc0s58UuFjJCsL', 1, '2027-05-08 15:25:44');

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
-- Estrutura da tabela `file_retention_events`
--

CREATE TABLE `file_retention_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `file_retention_policy_id` bigint(20) UNSIGNED DEFAULT NULL,
  `arquivo_origem` varchar(255) DEFAULT NULL,
  `arquivo_id` bigint(20) UNSIGNED DEFAULT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'processado',
  `size_bytes` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `due_at` date DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `file_retention_events`
--

INSERT INTO `file_retention_events` (`id`, `file_retention_policy_id`, `arquivo_origem`, `arquivo_id`, `empresa_id`, `file_name`, `action`, `status`, `size_bytes`, `due_at`, `processed_at`, `message`, `created_at`, `updated_at`) VALUES
(1, 1, 'Anexo', 101, 1, 'comprovante_pagamento_cliente_a.pdf', 'excluir', 'processado', 245760, '2026-06-17', '2026-06-19 18:21:16', 'Arquivo excluído automaticamente pela política de 7 dias.', '2026-06-19 18:21:16', '2026-06-19 18:21:16'),
(2, 2, 'Documento', 202, 1, 'balancete_maio_2026.xlsx', 'arquivar', 'processado', 1048576, '2026-06-14', '2026-06-19 18:21:16', 'Arquivo arquivado automaticamente em retencao/arquivados.', '2026-06-19 18:21:16', '2026-06-19 18:21:16'),
(3, 3, 'Portal', 303, 2, 'documentos_fiscais_cliente_b.zip', 'arquivar', 'erro', 5242880, '2026-06-18', '2026-06-19 18:21:16', 'Arquivo físico não encontrado para arquivamento.', '2026-06-19 18:21:16', '2026-06-19 18:21:16');

-- --------------------------------------------------------

--
-- Estrutura da tabela `file_retention_policies`
--

CREATE TABLE `file_retention_policies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `scope_type` varchar(255) NOT NULL DEFAULT 'global',
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `origin` varchar(255) DEFAULT NULL,
  `storage_type` varchar(255) NOT NULL DEFAULT 'temporario',
  `action` varchar(255) NOT NULL DEFAULT 'arquivar',
  `retention_days` int(10) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `last_run_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `file_retention_policies`
--

INSERT INTO `file_retention_policies` (`id`, `name`, `scope_type`, `empresa_id`, `origin`, `storage_type`, `action`, `retention_days`, `is_active`, `notes`, `last_run_at`, `created_at`, `updated_at`) VALUES
(1, 'Anexos temporários - excluir em 7 dias', 'origem', NULL, 'Anexo', 'temporario', 'excluir', 7, 0, 'Anexos de atendimento considerados temporários.', NULL, '2026-06-19 18:21:09', '2026-06-19 18:22:16'),
(2, 'Documentos enviados - arquivar em 30 dias', 'origem', NULL, 'Documento', 'temporario', 'arquivar', 30, 0, 'Documentos operacionais são arquivados após 30 dias.', NULL, '2026-06-19 18:21:09', '2026-06-19 18:22:33'),
(3, 'Portal do Cliente - arquivar em 90 dias', 'origem', NULL, 'Portal', 'temporario', 'arquivar', 90, 0, 'Arquiva documentos enviados pelo portal.', NULL, '2026-06-19 18:21:09', '2026-06-19 18:22:26'),
(4, 'Contratos e permanentes - nunca excluir', 'global', NULL, NULL, 'permanente', 'manter', NULL, 1, 'Regra de segurança para arquivos permanentes.', NULL, '2026-06-19 18:21:09', '2026-06-19 18:22:30'),
(5, 'Regra antiga desativada - excluir em 30 dias', 'global', NULL, NULL, 'temporario', 'excluir', 30, 1, 'Exemplo de política pausada/desativada.', NULL, '2026-06-19 18:21:09', '2026-06-19 18:22:20');

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
  `atendimento_id` bigint(20) UNSIGNED DEFAULT NULL,
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

INSERT INTO `item_controles` (`id`, `titulo`, `descricao`, `tipo`, `categoria_id`, `status`, `status_operacional_at`, `view_type`, `automation_status`, `prioridade`, `urgencia`, `risco_score`, `bloqueado_por_dependencia`, `bloqueado`, `arquivo`, `data_vencimento`, `data_conclusao`, `notificado_3_dias`, `notificado_no_dia`, `notificado_vencido`, `observacao`, `portal_ativo`, `portal_token`, `portal_cliente_nome`, `portal_cliente_email`, `portal_expira_em`, `portal_status`, `ultima_interacao_cliente_em`, `sla_horas`, `sla_inicio_em`, `sla_limite_em`, `sla_prazo_alvo_em`, `sla_concluido_em`, `sla_status`, `contrato_numero`, `contrato_parte_nome`, `contrato_parte_documento`, `contrato_valor`, `valor_tarefa`, `faturado_em`, `pago_em`, `contrato_inicio_em`, `contrato_fim_em`, `contrato_status`, `empresa_id`, `atendimento_id`, `responsavel_id`, `ultimo_alerta_enviado_em`, `ultimo_lembrete_enviado_em`, `qtd_lembretes_enviados`, `ultima_falha_notificacao_em`, `ultima_falha_notificacao_msg`, `created_at`, `updated_at`, `fluxo_operacional_id`, `kanban_order`, `blocked_by_dependency`, `estimated_minutes`, `actual_minutes`, `custom_payload`, `template_id`, `approval_required`, `approval_status`, `document_status`, `signature_status`, `risk_probability`, `risk_impact`, `risk_score`) VALUES
(31, 'Item de Controle - User Alpha 1', 'Registro de controle criado para User Alpha 1', 'documento', NULL, 'pendente', '2026-06-25 20:29:09', NULL, NULL, 'alta', 'critica', 95, 0, 0, NULL, '2026-06-12', NULL, 0, 0, 1, 'Seed redistribuído: obrigação muito atrasada para testar risco crítico na Home. Cliente sem envio recente pelo portal.', 1, '1ef7a8d0880102be8409c4ceca8b4c5f', NULL, NULL, NULL, 'aguardando_cliente', '2026-06-27 20:29:09', NULL, '2026-06-09 20:29:09', '2026-06-12 20:29:09', '2026-06-12 20:29:09', NULL, 'vencido', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 149, '2026-06-13 20:29:09', '2026-07-06 20:29:09', 4, NULL, NULL, '2026-04-24 17:57:10', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(32, 'Item de Controle - User Alpha 2', 'Registro de controle criado para User Alpha 2', 'documento', NULL, 'pendente', '2026-07-06 20:29:09', NULL, NULL, 'alta', 'alta', 72, 0, 0, NULL, '2026-07-07', NULL, 0, 1, 0, 'Seed redistribuído: vence hoje para testar o card Vencem hoje.', 0, '8173ceaad16ef162f4e742df540d8c20', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-05 20:29:09', '2026-07-07 23:00:00', '2026-07-07 23:00:00', NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 150, '2026-07-07 20:29:09', '2026-07-07 20:29:09', 1, NULL, NULL, '2026-04-24 17:57:10', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\",\"is_milestone\":false,\"gantt_start\":\"2026-04-25\",\"timeline_start\":\"2026-04-25T00:00\",\"timeline_end\":\"2026-05-25T23:59\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(33, 'Item de Controle - User Alpha 3', 'Registro de controle criado para User Alpha 3', 'documento', NULL, 'em_andamento', '2026-07-06 20:29:09', NULL, NULL, 'media', 'media', 58, 0, 0, NULL, '2026-07-08', NULL, 1, 0, 0, 'Seed redistribuído: vence amanhã.', 0, 'f1d4f5dd0a3dbe52e3000b689f78a2b1', NULL, NULL, NULL, NULL, NULL, 24, '2026-07-06 20:29:09', '2026-07-08 20:29:09', '2026-07-08 20:29:09', NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 151, '2026-07-07 20:29:09', NULL, 0, NULL, NULL, '2026-04-24 17:57:10', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 'Item de Controle - User Beta 1', 'Registro de controle criado para User Beta 1', 'documento', NULL, 'pendente', '2026-06-29 20:29:09', NULL, NULL, 'alta', 'alta', 88, 0, 0, NULL, '2026-06-25', NULL, 0, 0, 1, 'Seed redistribuído: obrigação atrasada há mais de 10 dias. Cliente sem envio recente pelo portal.', 1, '8GvffdhZO2AjBuKjMafgmtA5ZAxZK1EVCpoZaIqIZ5kyJoBuNYYuTWHmcdX1E3xa', NULL, NULL, NULL, 'aguardando_cliente', '2026-06-27 20:29:09', NULL, '2026-06-22 20:29:09', '2026-06-25 20:29:09', '2026-06-25 20:29:09', NULL, 'vencido', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 156, '2026-06-26 20:29:09', '2026-07-05 20:29:09', 3, NULL, NULL, '2026-04-24 17:57:10', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 'Item de Controle - User Beta 2', 'Registro de controle criado para User Beta 2', 'documento', NULL, 'pendente', '2026-07-07 20:29:09', NULL, NULL, 'media', 'media', 45, 0, 0, NULL, '2026-07-10', NULL, 1, 0, 0, 'Seed redistribuído: vence em 3 dias.', 0, 'f0820ec5ce595b69b629001d23247f12', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-07 20:29:09', '2026-07-10 20:29:09', '2026-07-10 20:29:09', NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 157, '2026-07-07 20:29:09', NULL, 0, NULL, NULL, '2026-04-24 17:57:10', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\",\"gantt_start\":\"2026-04-25\",\"timeline_start\":\"2026-04-25T00:00\",\"timeline_end\":\"2026-05-25T23:59\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(36, 'Item de Controle - User Beta 3', 'Registro de controle criado para User Beta 3', 'documento', NULL, 'pendente', '2026-07-07 20:29:09', NULL, NULL, 'media', 'baixa', 35, 0, 0, NULL, '2026-07-14', NULL, 0, 0, 0, 'Seed redistribuído: vence em 7 dias para testar previsões da Home.', 0, '4c2f9b6523086cb273aeb2cabba4d32a', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-07 20:29:09', '2026-07-14 20:29:09', '2026-07-14 20:29:09', NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 158, NULL, NULL, 0, NULL, NULL, '2026-04-24 17:57:10', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(37, 'Item de Controle - User Gamma 1', 'Registro de controle criado para User Gamma 1', 'documento', NULL, 'pendente', '2026-07-07 20:29:09', NULL, NULL, 'baixa', 'baixa', 20, 0, 0, NULL, '2026-07-22', NULL, 0, 0, 0, 'Seed redistribuído: vencimento futuro sem urgência.', 0, 'e8a9ace189b94c76fb109a4092fa9e93', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-07 20:29:09', '2026-07-22 20:29:09', '2026-07-22 20:29:09', NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, NULL, 163, NULL, NULL, 0, NULL, NULL, '2026-04-24 17:57:10', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"timeline_start\":\"2026-05-20T10:11\",\"timeline_end\":\"2026-05-26T10:11\",\"gantt_start\":\"2026-05-20\",\"baseline_start\":\"2026-05-20\",\"baseline_end\":\"2026-05-26\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(38, 'Item de Controle - User Gamma 2', 'Registro de controle criado para User Gamma 2', 'documento', NULL, 'pendente', '2026-07-01 20:29:09', NULL, NULL, 'alta', 'alta', 82, 0, 0, NULL, '2026-07-02', NULL, 0, 0, 1, 'Seed redistribuído: obrigação vencida recentemente e parada há mais de 5 dias. Cliente sem envio recente pelo portal.', 1, 'e03fe0da604b41106160e88975d39496', NULL, NULL, NULL, 'aguardando_cliente', '2026-06-27 20:29:09', NULL, '2026-06-30 20:29:09', '2026-07-02 20:29:09', '2026-07-02 20:29:09', NULL, 'vencido', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, NULL, 164, '2026-07-03 20:29:09', '2026-07-06 20:29:09', 2, NULL, NULL, '2026-04-24 17:57:10', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(39, 'Item de Controle - User Gamma 3', 'Registro de controle criado para User Gamma 3', 'documento', NULL, 'pendente', '2026-07-07 20:29:09', NULL, NULL, 'media', 'baixa', 35, 0, 0, NULL, '2026-07-14', NULL, 0, 0, 0, 'Seed redistribuído: vence em 7 dias para testar previsões da Home.', 0, 'd6ec56f6bdfcc147050433c7d7a93671', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-07 20:29:09', '2026-07-14 20:29:09', '2026-07-14 20:29:09', NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, NULL, 165, NULL, NULL, 0, NULL, NULL, '2026-04-24 17:57:10', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-24\",\"baseline_end\":\"2026-05-24\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(47, 'SEED - Contrato vencendo hoje', 'Item criado para testar vencimento, portal, contrato, SLA, checklist, timeline e notificações.', 'contrato', NULL, 'pendente', '2026-07-06 20:29:09', NULL, NULL, 'alta', 'alta', 72, 0, 0, NULL, '2026-07-07', NULL, 0, 1, 0, 'Seed redistribuído: vence hoje para testar o card Vencem hoje.', 1, 'portal-seed-d1df4b78-4313-11f1-b74e-18a59cb167c9', 'Cliente Portal Seed', 'cliente.portal.seed@teste.com', '2026-05-28 12:06:22', 'em_execucao', '2026-07-05 20:29:09', 24, '2026-07-05 20:29:09', '2026-07-07 23:00:00', '2026-07-07 23:00:00', NULL, 'em_andamento', 'CT-SEED-001', 'Cliente Seed LTDA', '12345678000100', 3500.00, NULL, NULL, NULL, '2026-04-28', '2026-05-28', 'ativo', 11, NULL, 168, '2026-07-07 20:29:09', '2026-07-07 20:29:09', 1, NULL, NULL, '2026-04-28 15:06:22', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-28\",\"baseline_end\":\"2026-04-28\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(48, 'SEED - Documento atrasado', 'Item criado para testar atrasados, SLA vencido, filtros e dashboard.', 'documento', NULL, 'pendente', '2026-06-25 20:29:09', NULL, NULL, 'alta', 'critica', 95, 0, 0, NULL, '2026-06-12', NULL, 0, 0, 1, 'Seed redistribuído: obrigação muito atrasada para testar risco crítico na Home. Cliente sem envio recente pelo portal.', 1, 'SX0PLefzHTu3TeJ6NaFioTg1WJLGeVVN3svzOqvM3SVGPk6TCBYWNJl3QY4QRdgP', NULL, NULL, NULL, 'aguardando_cliente', '2026-06-27 20:29:09', 8, '2026-06-09 20:29:09', '2026-06-12 20:29:09', '2026-06-12 20:29:09', NULL, 'vencido', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11, NULL, 168, '2026-06-13 20:29:09', '2026-07-06 20:29:09', 4, NULL, NULL, '2026-04-28 15:06:22', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-28\",\"baseline_end\":\"2026-04-23\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(49, 'SEED - Item concluído', 'Item criado para testar indicadores de concluídos e histórico.', 'documento', NULL, 'concluido', NULL, NULL, NULL, 'media', 'baixa', 5, 0, 0, NULL, '2026-07-05', '2026-07-04', 0, 0, 0, 'Seed redistribuído: concluído antes do prazo.', 1, 'portal-seed-d1df50e6-4313-11f1-b74e-18a59cb167c9', 'Cliente Concluído Seed', 'cliente.concluido.seed@teste.com', '2026-06-27 12:06:22', 'concluido', '2026-07-06 20:29:09', 12, '2026-06-29 20:29:09', '2026-07-05 20:29:09', '2026-07-05 20:29:09', '2026-07-04 20:29:09', 'concluido_no_prazo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11, NULL, 168, NULL, NULL, 0, NULL, NULL, '2026-04-28 15:06:22', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-28\",\"baseline_end\":\"2026-05-13\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(50, 'SEED - Contrato vencendo hoje', 'Item criado para testar vencimento, portal, contrato, SLA, checklist, timeline e notificações.', 'contrato', NULL, 'pendente', '2026-07-07 20:29:09', NULL, NULL, 'media', 'media', 45, 0, 0, NULL, '2026-07-10', NULL, 1, 0, 0, 'Seed redistribuído: vence em 3 dias.', 1, 'portal-seed-16c901e2-4314-11f1-b74e-18a59cb167c9', 'Cliente Portal Seed', 'cliente.portal.seed@teste.com', '2026-05-28 12:08:18', NULL, NULL, 24, '2026-07-07 20:29:09', '2026-07-10 20:29:09', '2026-07-10 20:29:09', NULL, 'em_andamento', 'CT-SEED-001', 'Cliente Seed LTDA', '12345678000100', 3500.00, NULL, NULL, NULL, '2026-04-28', '2026-05-28', 'ativo', 11, NULL, 168, '2026-07-07 20:29:09', NULL, 0, NULL, NULL, '2026-04-28 15:08:18', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-28\",\"baseline_end\":\"2026-04-28\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(51, 'SEED - Documento atrasado', 'Item criado para testar atrasados, SLA vencido, filtros e dashboard.', 'documento', NULL, 'pendente', '2026-06-29 20:29:09', NULL, NULL, 'alta', 'alta', 88, 0, 0, NULL, '2026-06-25', NULL, 0, 0, 1, 'Seed redistribuído: obrigação atrasada há mais de 10 dias. Cliente sem envio recente pelo portal.', 1, 't5Q6RMAbVqk7WO5hoC3nqskz9d47wlmTut8W0R6nb7IJteQphRT5INOvMRAhvOeT', NULL, NULL, NULL, 'aguardando_cliente', '2026-06-27 20:29:09', 8, '2026-06-22 20:29:09', '2026-06-25 20:29:09', '2026-06-25 20:29:09', NULL, 'vencido', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11, NULL, 168, '2026-06-26 20:29:09', '2026-07-05 20:29:09', 3, NULL, NULL, '2026-04-28 15:08:18', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-28\",\"baseline_end\":\"2026-04-23\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(52, 'SEED - Item concluído', 'Item criado para testar indicadores de concluídos e histórico.', 'documento', NULL, 'concluido', NULL, NULL, NULL, 'media', 'baixa', 5, 0, 0, NULL, '2026-07-05', '2026-07-04', 0, 0, 0, 'Seed redistribuído: concluído antes do prazo.', 1, 'portal-seed-16c955bd-4314-11f1-b74e-18a59cb167c9', 'Cliente Concluído Seed', 'cliente.concluido.seed@teste.com', '2026-06-27 12:08:18', 'concluido', '2026-07-06 20:29:09', 12, '2026-06-29 20:29:09', '2026-07-05 20:29:09', '2026-07-05 20:29:09', '2026-07-04 20:29:09', 'concluido_no_prazo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11, NULL, 168, NULL, NULL, 0, NULL, NULL, '2026-04-28 15:08:18', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-28\",\"baseline_end\":\"2026-05-13\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(56, 'Contrato RH Interno Portal do Cliente', 'teste', 'documento', 1, 'pendente', '2026-07-01 20:29:09', NULL, NULL, 'alta', 'alta', 82, 0, 0, NULL, '2026-07-02', NULL, 0, 0, 1, 'Seed redistribuído: obrigação vencida recentemente e parada há mais de 5 dias. Cliente sem envio recente pelo portal.', 1, 'BD3W26Thqb2soy0b648UExabhDULsCY1cMHj0JsLXd0GbHf4GqZqPMDJQkfhLppx', 'ricardo', 'ricardo-s-a@hotmail.com', '2026-04-30 00:00:00', 'aguardando_cliente', '2026-06-27 20:29:09', NULL, '2026-06-30 20:29:09', '2026-07-02 20:29:09', '2026-07-02 20:29:09', NULL, 'vencido', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 147, '2026-07-03 20:29:09', '2026-07-06 20:29:09', 2, NULL, NULL, '2026-04-30 20:30:56', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-30\",\"baseline_end\":\"2026-04-30\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(57, 'teste link', 'teste link', 'documento', 1, 'pendente', '2026-07-05 20:29:09', NULL, NULL, 'alta', 'alta', 76, 0, 0, NULL, '2026-07-06', NULL, 0, 0, 1, 'Seed redistribuído: obrigação vencida ontem. Cliente sem envio recente pelo portal.', 1, '9Bkkk5Xram9lvUqJ0pYY1EQxopjK2lm90icRMCpGS9pOl2pFuLIjLDVzMepsR5Ab', 'ricardo', 'ricardo-s-a@hotmail.com', '2026-05-01 00:00:00', 'aguardando_cliente', '2026-06-27 20:29:09', NULL, '2026-07-04 20:29:09', '2026-07-06 20:29:09', '2026-07-06 20:29:09', NULL, 'vencido', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 147, '2026-07-06 20:29:09', '2026-07-07 14:29:09', 1, NULL, NULL, '2026-04-30 22:51:49', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-04-30\",\"baseline_end\":\"2026-05-01\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(58, 'teste link 2', 'teste link 2', 'documento', 1, 'pendente', '2026-07-06 20:29:09', NULL, NULL, 'alta', 'alta', 72, 0, 0, NULL, '2026-07-07', NULL, 0, 1, 0, 'Seed redistribuído: vence hoje para testar o card Vencem hoje.', 1, 'L2K7aLdtNftcNESSQwqcGHlnrglfoT8kQjTY4oTrDH40Rk0Bfxr4xOm844tnZrja', 'ricardo', 'ricardo-s-a@hotmail.com', '2026-05-01 00:00:00', 'em_execucao', '2026-07-05 20:29:09', NULL, '2026-07-05 20:29:09', '2026-07-07 23:00:00', '2026-07-07 23:00:00', NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 147, '2026-07-07 20:29:09', '2026-07-07 20:29:09', 1, NULL, NULL, '2026-04-30 23:30:59', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"gantt_start\":\"2026-04-30\",\"timeline_start\":\"2026-04-30T00:00\",\"timeline_end\":\"2026-05-01T23:59\",\"baseline_start\":\"2026-04-30\",\"baseline_end\":\"2026-05-01\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(59, 'joyce', 'teste', 'documento', 1, 'em_andamento', '2026-07-06 20:29:09', NULL, NULL, 'media', 'media', 58, 0, 0, NULL, '2026-07-08', NULL, 1, 0, 0, 'Seed redistribuído: vence amanhã.', 1, 'cV2XTn0PgmXcxkXuLnA1BeLrlDbBpZ3U19eZbtKp9xSqUdRTyfvf5g8BRzvuBi1E', 'ricardo', 'ricardo@ricardo.com', '2026-05-22 00:00:00', 'em_execucao', '2026-07-05 20:29:09', NULL, '2026-07-06 20:29:09', '2026-07-08 20:29:09', '2026-07-08 20:29:09', NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 147, '2026-07-07 20:29:09', NULL, 0, NULL, NULL, '2026-05-01 21:18:37', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, '{\"baseline_start\":\"2026-05-01\",\"baseline_end\":\"2026-05-23\",\"baseline_saved_at\":\"2026-05-06 13:12:12\"}', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(60, 'Briefing aprovado pelo cliente', 'Documento inicial aprovado com objetivos, escopo e referências do projeto.', 'documento', NULL, 'concluido', NULL, NULL, NULL, 'media', 'baixa', 5, 0, 0, NULL, '2026-07-05', '2026-07-04', 0, 0, 0, 'Seed redistribuído: concluído antes do prazo.', 1, '981813fb-4b07-11f1-933d-18a59cb167c9', 'Cliente Teste', 'cliente.teste@empresa.com', '2026-07-07 14:59:00', 'concluido', '2026-07-06 20:29:09', 24, '2026-06-29 20:29:09', '2026-07-05 20:29:09', '2026-07-05 20:29:09', '2026-07-04 20:29:09', 'concluido_no_prazo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, NULL, 169, NULL, NULL, 0, NULL, NULL, '2026-04-26 17:59:00', '2026-07-07 20:29:09', NULL, 1, 0, 240, 210, '{\"portal_demo\": true, \"etapa\": \"briefing\"}', NULL, 0, 'aprovado', 'aprovado', NULL, 1, 1, 1),
(61, 'Layout da home pronto para revisão', 'Primeira versão visual da página inicial liberada para análise do cliente.', 'design', NULL, 'pendente', '2026-07-06 20:29:09', NULL, NULL, 'alta', 'alta', 72, 0, 0, NULL, '2026-07-07', NULL, 0, 1, 0, 'Seed redistribuído: vence hoje para testar o card Vencem hoje. Cliente sem envio recente pelo portal.', 1, '981844d0-4b07-11f1-933d-18a59cb167c9', 'Cliente Teste', 'cliente.teste@empresa.com', '2026-07-07 14:59:00', 'aguardando_cliente', '2026-06-27 20:29:09', 24, '2026-07-05 20:29:09', '2026-07-07 23:00:00', '2026-07-07 23:00:00', NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, NULL, 169, '2026-07-07 20:29:09', '2026-07-07 20:29:09', 1, NULL, NULL, '2026-05-04 17:59:00', '2026-07-07 20:29:09', NULL, 2, 0, 480, 360, '{\"portal_demo\": true, \"etapa\": \"revisao\"}', NULL, 1, 'pendente', 'em_revisao', NULL, 2, 2, 4),
(62, 'Integração do formulário de contato', 'Configurar envio dos leads para a fila comercial e validar notificação por e-mail.', 'desenvolvimento', NULL, 'em_andamento', '2026-07-06 20:29:09', NULL, NULL, 'media', 'media', 58, 0, 0, NULL, '2026-07-08', NULL, 1, 0, 0, 'Seed redistribuído: vence amanhã.', 1, '9818468c-4b07-11f1-933d-18a59cb167c9', 'Cliente Teste', 'cliente.teste@empresa.com', '2026-07-07 14:59:00', 'em_execucao', '2026-07-05 20:29:09', 48, '2026-07-06 20:29:09', '2026-07-08 20:29:09', '2026-07-08 20:29:09', NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, NULL, 169, '2026-07-07 20:29:09', NULL, 0, NULL, NULL, '2026-05-05 17:59:00', '2026-07-07 20:29:09', NULL, 3, 0, 600, 280, '{\"portal_demo\": true, \"etapa\": \"execucao\"}', NULL, 0, NULL, 'em_andamento', NULL, 2, 3, 6),
(63, 'Publicação da área do cliente', 'Subir a área do cliente em ambiente final com os ajustes aprovados.', 'entrega', NULL, 'pendente', '2026-07-07 20:29:09', NULL, NULL, 'media', 'media', 45, 0, 0, NULL, '2026-07-10', NULL, 1, 0, 0, 'Seed redistribuído: vence em 3 dias.', 1, '9818478a-4b07-11f1-933d-18a59cb167c9', 'Cliente Teste', 'cliente.teste@empresa.com', '2026-07-07 14:59:00', 'em_execucao', '2026-07-05 20:29:09', 72, '2026-07-07 20:29:09', '2026-07-10 20:29:09', '2026-07-10 20:29:09', NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, NULL, 169, '2026-07-07 20:29:09', NULL, 0, NULL, NULL, '2026-05-07 17:59:00', '2026-07-07 20:29:09', NULL, 4, 0, 720, NULL, '{\"portal_demo\": true, \"etapa\": \"entrega\"}', NULL, 0, NULL, 'pendente', NULL, 1, 2, 2),
(64, 'Checklist final de qualidade', 'Revisão final de links, responsividade, formulários e conteúdo antes da entrega.', 'qualidade', NULL, 'pendente', '2026-07-07 20:29:09', NULL, NULL, 'media', 'baixa', 35, 0, 0, NULL, '2026-07-14', NULL, 0, 0, 0, 'Seed redistribuído: vence em 7 dias para testar previsões da Home. Cliente sem envio recente pelo portal.', 1, '98184898-4b07-11f1-933d-18a59cb167c9', 'Cliente Teste', 'cliente.teste@empresa.com', '2026-07-07 14:59:00', 'aguardando_cliente', '2026-06-27 20:29:09', 24, '2026-07-07 20:29:09', '2026-07-14 20:29:09', '2026-07-14 20:29:09', NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, NULL, 169, NULL, NULL, 0, NULL, NULL, '2026-05-08 17:59:00', '2026-07-07 20:29:09', NULL, 5, 0, 300, NULL, '{\"portal_demo\": true, \"etapa\": \"aprovacao\"}', NULL, 1, 'pendente', 'em_aprovacao', NULL, 3, 2, 6),
(65, 'Onboarding - Diagnóstico inicial - Alpha', 'Etapa de onboarding criada pela aba Clientes para organizar a implantação do cliente.', 'tarefa', NULL, 'pendente', '2026-07-06 20:29:09', NULL, NULL, 'alta', 'alta', 72, 0, 0, NULL, '2026-07-07', NULL, 0, 1, 0, 'Seed redistribuído: vence hoje para testar o card Vencem hoje.', 1, '1a884d22ed2ba2715abcabd84c549e65', NULL, NULL, NULL, 'em_execucao', '2026-07-05 20:29:09', NULL, '2026-07-05 20:29:09', '2026-07-07 23:00:00', '2026-07-07 23:00:00', NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 147, '2026-07-07 20:29:09', '2026-07-07 20:29:09', 1, NULL, NULL, '2026-05-11 14:28:48', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(66, 'Onboarding - Documentos e acessos - Alpha', 'Etapa de onboarding criada pela aba Clientes para organizar a implantação do cliente.', 'tarefa', NULL, 'pendente', '2026-07-07 20:29:09', NULL, NULL, 'media', 'media', 45, 0, 0, NULL, '2026-07-10', NULL, 1, 0, 0, 'Seed redistribuído: vence em 3 dias.', 1, '5757cc037fa795a56642c655f8b777c7', NULL, NULL, NULL, 'em_execucao', '2026-07-05 20:29:09', NULL, '2026-07-07 20:29:09', '2026-07-10 20:29:09', '2026-07-10 20:29:09', NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 147, '2026-07-07 20:29:09', NULL, 0, NULL, NULL, '2026-05-11 14:28:48', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(67, 'Onboarding - Primeira entrega para aprovação - Alpha', 'Etapa de onboarding criada pela aba Clientes para organizar a implantação do cliente.', 'tarefa', NULL, 'pendente', '2026-07-07 20:29:09', NULL, NULL, 'media', 'baixa', 35, 0, 0, NULL, '2026-07-14', NULL, 0, 0, 0, 'Seed redistribuído: vence em 7 dias para testar previsões da Home.', 1, '11981ea580fe4991bd18f92fb20f1cbd', NULL, NULL, NULL, 'em_execucao', '2026-07-05 20:29:09', NULL, '2026-07-07 20:29:09', '2026-07-14 20:29:09', '2026-07-14 20:29:09', NULL, 'em_andamento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 147, NULL, NULL, 0, NULL, NULL, '2026-05-11 14:28:48', '2026-07-07 20:29:09', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(69, 'DEMO ARMAZENAMENTO - SPED Fiscal 2025 Ricardo', 'Arquivo pesado para testar ranking de maiores arquivos.', 'documento', NULL, 'pendente', NULL, NULL, NULL, 'alta', NULL, NULL, 0, 0, NULL, '2026-05-30', NULL, 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, NULL, 171, NULL, NULL, 0, NULL, NULL, '2026-05-05 15:54:23', '2026-06-19 15:54:23', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(70, 'DEMO ARMAZENAMENTO - Backup XML NF-e Ricardo', 'Backup compactado para testar espaço recuperável.', 'documento', NULL, 'pendente', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-06-09', NULL, 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, NULL, 171, NULL, NULL, 0, NULL, NULL, '2026-04-20 15:54:23', '2026-06-19 15:54:23', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(71, 'DEMO ARMAZENAMENTO - Folha Pagamento 2026 Ricardo', 'Documento médio dentro do prazo.', 'documento', NULL, 'em_andamento', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-07-14', NULL, 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, NULL, 171, NULL, NULL, 0, NULL, NULL, '2026-06-11 15:54:23', '2026-06-19 15:54:23', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(72, 'DEMO ARMAZENAMENTO - Contrato Social Ricardo', 'Arquivo leve e válido.', 'documento', NULL, 'concluido', NULL, NULL, NULL, 'baixa', NULL, NULL, 0, 0, NULL, '2026-12-16', NULL, 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, NULL, 171, NULL, NULL, 0, NULL, NULL, '2026-06-16 15:54:23', '2026-06-19 15:54:23', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(73, 'DEMO ARMAZENAMENTO - Dossiê Fiscal Joyce', 'Cliente próximo do limite para testar alerta.', 'documento', NULL, 'pendente', NULL, NULL, NULL, 'alta', NULL, NULL, 0, 0, NULL, '2026-06-14', NULL, 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22, NULL, 172, NULL, NULL, 0, NULL, NULL, '2026-05-25 15:54:23', '2026-06-19 15:54:23', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(74, 'DEMO ARMAZENAMENTO - Arquivo Morto Joyce', 'Arquivo antigo expirado para testar limpeza.', 'documento', NULL, 'pendente', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-02-19', NULL, 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22, NULL, 172, NULL, NULL, 0, NULL, NULL, '2025-12-21 15:54:23', '2026-06-19 15:54:23', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(75, 'DEMO ARMAZENAMENTO - Guias e DARFs Joyce', 'Documento comum.', 'documento', NULL, 'em_andamento', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-07-04', NULL, 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22, NULL, 172, NULL, NULL, 0, NULL, NULL, '2026-06-08 15:54:23', '2026-06-19 15:54:23', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(76, 'DEMO ARMAZENAMENTO - SPED Contribuições Webconta', 'Outro arquivo pesado para top consumidores.', 'documento', NULL, 'pendente', NULL, NULL, NULL, 'alta', NULL, NULL, 0, 0, NULL, '2026-05-10', NULL, 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, NULL, 170, NULL, NULL, 0, NULL, NULL, '2026-04-10 15:54:23', '2026-06-19 15:54:23', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(77, 'DEMO ARMAZENAMENTO - Balancete Webconta', 'Documento médio.', 'documento', NULL, 'concluido', NULL, NULL, NULL, 'baixa', NULL, NULL, 0, 0, NULL, '2026-09-17', NULL, 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, NULL, 170, NULL, NULL, 0, NULL, NULL, '2026-06-06 15:54:23', '2026-06-19 15:54:23', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(78, 'DEMO ARMAZENAMENTO - Pasta Trabalhista Seed', 'Arquivo pesado e vencido.', 'documento', NULL, 'pendente', NULL, NULL, NULL, 'alta', NULL, NULL, 0, 0, NULL, '2026-03-31', NULL, 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11, NULL, 168, NULL, NULL, 0, NULL, NULL, '2026-03-16 15:54:23', '2026-06-19 15:54:23', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(79, 'DEMO ARMAZENAMENTO - Obrigações Alpha', 'Empresa maior com consumo relevante.', 'documento', NULL, 'em_andamento', NULL, NULL, NULL, 'media', NULL, NULL, 0, 0, NULL, '2026-08-03', NULL, 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 147, NULL, NULL, 0, NULL, NULL, '2026-05-30 15:54:23', '2026-06-19 15:54:23', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(80, 'DEMO ARMAZENAMENTO - Temporários Beta', 'Arquivos temporários para espaço recuperável.', 'documento', NULL, 'concluido', '2026-07-07 20:11:20', NULL, NULL, 'baixa', 'baixa', NULL, 0, 0, NULL, '2026-06-17', '2026-07-07', 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-07 17:11:20', 'concluido_atrasado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 154, NULL, NULL, 0, NULL, NULL, '2026-05-15 15:54:23', '2026-07-07 20:11:20', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL);

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

--
-- Extraindo dados da tabela `item_controle_anexos`
--

INSERT INTO `item_controle_anexos` (`id`, `item_controle_id`, `user_id`, `categoria`, `nome_original`, `caminho`, `mime_type`, `tamanho_bytes`, `arquivo`, `observacao`, `created_at`, `updated_at`) VALUES
(1, 69, 404, 'Fiscal', 'SPED_Fiscal_2025_Ricardo.zip', 'demo/armazenamento/SPED_Fiscal_2025_Ricardo.zip', 'application/zip', 2147483648, 'SPED_Fiscal_2025_Ricardo.zip', 'Seed demo: arquivo pesado.', '2026-05-05 15:54:23', '2026-06-19 15:54:23'),
(2, 70, 404, 'Backup', 'Backup_XML_NFe_Ricardo.rar', 'demo/armazenamento/Backup_XML_NFe_Ricardo.rar', 'application/x-rar-compressed', 1610612736, 'Backup_XML_NFe_Ricardo.rar', 'Seed demo: expirado e recuperável.', '2026-04-20 15:54:23', '2026-06-19 15:54:23'),
(3, 71, 404, 'Departamento Pessoal', 'Folha_Pagamento_2026_Ricardo.pdf', 'demo/armazenamento/Folha_Pagamento_2026_Ricardo.pdf', 'application/pdf', 734003200, 'Folha_Pagamento_2026_Ricardo.pdf', 'Seed demo: documento médio.', '2026-06-11 15:54:23', '2026-06-19 15:54:23'),
(4, 72, 404, 'Contrato', 'Contrato_Social_Ricardo.pdf', 'demo/armazenamento/Contrato_Social_Ricardo.pdf', 'application/pdf', 52428800, 'Contrato_Social_Ricardo.pdf', 'Seed demo: documento leve.', '2026-06-16 15:54:23', '2026-06-19 15:54:23'),
(5, 73, 405, 'Fiscal', 'Dossie_Fiscal_Joyce.zip', 'demo/armazenamento/Dossie_Fiscal_Joyce.zip', 'application/zip', 3221225472, 'Dossie_Fiscal_Joyce.zip', 'Seed demo: cliente perto do limite.', '2026-05-25 15:54:23', '2026-06-19 15:54:23'),
(6, 74, 405, 'Arquivo Morto', 'Arquivo_Morto_Joyce_2024.zip', 'demo/armazenamento/Arquivo_Morto_Joyce_2024.zip', 'application/zip', 4294967296, 'Arquivo_Morto_Joyce_2024.zip', 'Seed demo: expirado e muito pesado.', '2025-12-21 15:54:23', '2026-06-19 15:54:23'),
(7, 75, 405, 'Fiscal', 'Guias_DARFs_Joyce.pdf', 'demo/armazenamento/Guias_DARFs_Joyce.pdf', 'application/pdf', 471859200, 'Guias_DARFs_Joyce.pdf', 'Seed demo: documento comum.', '2026-06-08 15:54:23', '2026-06-19 15:54:23'),
(8, 76, 398, 'Fiscal', 'SPED_Contribuicoes_Webconta.zip', 'demo/armazenamento/SPED_Contribuicoes_Webconta.zip', 'application/zip', 1879048192, 'SPED_Contribuicoes_Webconta.zip', 'Seed demo: top consumidores.', '2026-04-10 15:54:23', '2026-06-19 15:54:23'),
(9, 77, 398, 'Contábil', 'Balancete_Webconta.pdf', 'demo/armazenamento/Balancete_Webconta.pdf', 'application/pdf', 314572800, 'Balancete_Webconta.pdf', 'Seed demo: válido.', '2026-06-06 15:54:23', '2026-06-19 15:54:23'),
(10, 78, 396, 'Trabalhista', 'Pasta_Trabalhista_Seed.zip', 'demo/armazenamento/Pasta_Trabalhista_Seed.zip', 'application/zip', 2415919104, 'Pasta_Trabalhista_Seed.zip', 'Seed demo: vencido e pesado.', '2026-03-16 15:54:23', '2026-06-19 15:54:23'),
(11, 79, 371, 'Fiscal', 'Obrigacoes_Alpha_2026.zip', 'demo/armazenamento/Obrigacoes_Alpha_2026.zip', 'application/zip', 1153433600, 'Obrigacoes_Alpha_2026.zip', 'Seed demo: consumo relevante.', '2026-05-30 15:54:23', '2026-06-19 15:54:23'),
(12, 80, 376, 'Temporário', 'Temporarios_Beta.zip', 'demo/armazenamento/Temporarios_Beta.zip', 'application/zip', 943718400, 'Temporarios_Beta.zip', 'Seed demo: expirado e recuperável.', '2026-05-15 15:54:23', '2026-06-19 15:54:23');

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
(6, 52, 396, 'comentario', 'Comentário seed: item concluído com sucesso.', '2026-04-28 15:08:18', '2026-04-28 15:08:18'),
(10, 35, 376, 'comentario', 'teste', '2026-07-07 20:28:30', '2026-07-07 20:28:30');

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
(19, 51, 11, 111, 'atualizacao', 'Portal do cliente ativado', 'O acesso externo do cliente foi ativado para este item.', NULL, '2026-05-08 14:30:31', '2026-05-08 14:30:31'),
(20, 80, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Em andamento.', NULL, '2026-07-07 20:02:15', '2026-07-07 20:02:15'),
(21, 80, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"pendente\",\"status_novo\":\"em_andamento\"}', '2026-07-07 20:02:15', '2026-07-07 20:02:15'),
(22, 34, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"pendente\",\"status_novo\":\"pendente\"}', '2026-07-07 20:02:16', '2026-07-07 20:02:16'),
(23, 34, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Em andamento.', NULL, '2026-07-07 20:10:16', '2026-07-07 20:10:16'),
(24, 34, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"pendente\",\"status_novo\":\"em_andamento\"}', '2026-07-07 20:10:16', '2026-07-07 20:10:16'),
(25, 36, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Em andamento.', NULL, '2026-07-07 20:10:20', '2026-07-07 20:10:20'),
(26, 36, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"pendente\",\"status_novo\":\"em_andamento\"}', '2026-07-07 20:10:20', '2026-07-07 20:10:20'),
(27, 80, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Pendente.', NULL, '2026-07-07 20:10:26', '2026-07-07 20:10:26'),
(28, 80, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"em_andamento\",\"status_novo\":\"pendente\"}', '2026-07-07 20:10:26', '2026-07-07 20:10:26'),
(29, 36, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Pendente.', NULL, '2026-07-07 20:10:29', '2026-07-07 20:10:29'),
(30, 36, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"em_andamento\",\"status_novo\":\"pendente\"}', '2026-07-07 20:10:29', '2026-07-07 20:10:29'),
(31, 34, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Pendente.', NULL, '2026-07-07 20:10:31', '2026-07-07 20:10:31'),
(32, 34, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"em_andamento\",\"status_novo\":\"pendente\"}', '2026-07-07 20:10:31', '2026-07-07 20:10:31'),
(33, 80, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Em andamento.', NULL, '2026-07-07 20:10:33', '2026-07-07 20:10:33'),
(34, 80, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"pendente\",\"status_novo\":\"em_andamento\"}', '2026-07-07 20:10:33', '2026-07-07 20:10:33'),
(35, 36, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Em andamento.', NULL, '2026-07-07 20:10:40', '2026-07-07 20:10:40'),
(36, 36, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"pendente\",\"status_novo\":\"em_andamento\"}', '2026-07-07 20:10:40', '2026-07-07 20:10:40'),
(37, 34, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Em andamento.', NULL, '2026-07-07 20:10:43', '2026-07-07 20:10:43'),
(38, 34, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"pendente\",\"status_novo\":\"em_andamento\"}', '2026-07-07 20:10:43', '2026-07-07 20:10:43'),
(39, 35, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Em andamento.', NULL, '2026-07-07 20:10:44', '2026-07-07 20:10:44'),
(40, 35, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"pendente\",\"status_novo\":\"em_andamento\"}', '2026-07-07 20:10:44', '2026-07-07 20:10:44'),
(41, 80, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Pendente.', NULL, '2026-07-07 20:10:51', '2026-07-07 20:10:51'),
(42, 80, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"em_andamento\",\"status_novo\":\"pendente\"}', '2026-07-07 20:10:51', '2026-07-07 20:10:51'),
(43, 34, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Pendente.', NULL, '2026-07-07 20:10:52', '2026-07-07 20:10:52'),
(44, 34, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"em_andamento\",\"status_novo\":\"pendente\"}', '2026-07-07 20:10:52', '2026-07-07 20:10:52'),
(45, 35, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Pendente.', NULL, '2026-07-07 20:10:54', '2026-07-07 20:10:54'),
(46, 35, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"em_andamento\",\"status_novo\":\"pendente\"}', '2026-07-07 20:10:54', '2026-07-07 20:10:54'),
(47, 36, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Pendente.', NULL, '2026-07-07 20:10:58', '2026-07-07 20:10:58'),
(48, 36, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"em_andamento\",\"status_novo\":\"pendente\"}', '2026-07-07 20:10:58', '2026-07-07 20:10:58'),
(49, 80, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Em andamento.', NULL, '2026-07-07 20:11:02', '2026-07-07 20:11:02'),
(50, 80, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"pendente\",\"status_novo\":\"em_andamento\"}', '2026-07-07 20:11:02', '2026-07-07 20:11:02'),
(51, 34, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Em andamento.', NULL, '2026-07-07 20:11:03', '2026-07-07 20:11:03'),
(52, 34, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"pendente\",\"status_novo\":\"em_andamento\"}', '2026-07-07 20:11:03', '2026-07-07 20:11:03'),
(53, 35, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Em andamento.', NULL, '2026-07-07 20:11:05', '2026-07-07 20:11:05'),
(54, 35, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"pendente\",\"status_novo\":\"em_andamento\"}', '2026-07-07 20:11:06', '2026-07-07 20:11:06'),
(55, 36, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Em andamento.', NULL, '2026-07-07 20:11:07', '2026-07-07 20:11:07'),
(56, 36, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"pendente\",\"status_novo\":\"em_andamento\"}', '2026-07-07 20:11:07', '2026-07-07 20:11:07'),
(57, 80, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Concluído.', NULL, '2026-07-07 20:11:10', '2026-07-07 20:11:10'),
(58, 80, 5, 376, 'conclusao', 'Item concluído', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_novo\":\"concluido\"}', '2026-07-07 20:11:10', '2026-07-07 20:11:10'),
(59, 80, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Pendente.', NULL, '2026-07-07 20:11:14', '2026-07-07 20:11:14'),
(60, 80, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_anterior\":\"concluido\",\"status_novo\":\"pendente\"}', '2026-07-07 20:11:14', '2026-07-07 20:11:14'),
(61, 80, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Concluído.', NULL, '2026-07-07 20:11:20', '2026-07-07 20:11:20'),
(62, 80, 5, 376, 'conclusao', 'Item concluído', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_novo\":\"concluido\"}', '2026-07-07 20:11:20', '2026-07-07 20:11:20'),
(63, 34, 5, 376, 'status_operacional', 'Status operacional atualizado', 'Novo status: Concluído.', NULL, '2026-07-07 20:11:25', '2026-07-07 20:11:25'),
(64, 34, 5, 376, 'conclusao', 'Item concluído', 'Status alterado pelo quadro Kanban.', '{\"origem\":\"kanban\",\"status_novo\":\"concluido\"}', '2026-07-07 20:11:25', '2026-07-07 20:11:25');

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
(6, '2026_04_15_150455_add_arquivo_to_item_controles_table', 2),
(7, '2026_06_22_000001_harden_prazzu_permissions_lote1', 3),
(8, '2026_06_22_000002_prepare_users_for_permissions_tests_lote4', 4);

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
  `tamanho_bytes` bigint(20) UNSIGNED DEFAULT NULL,
  `visivel_cliente` tinyint(1) NOT NULL DEFAULT 1,
  `criado_por` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Extraindo dados da tabela `portal_documentos`
--

INSERT INTO `portal_documentos` (`id`, `empresa_id`, `item_controle_id`, `titulo`, `tipo`, `conteudo`, `url`, `arquivo`, `tamanho_bytes`, `visivel_cliente`, `criado_por`, `created_at`, `updated_at`) VALUES
(1, 21, NULL, 'Wiki do Projeto', 'wiki', 'Regras gerais do projeto, canais oficiais, prazos combinados e responsáveis principais.', NULL, NULL, NULL, 1, NULL, '2026-05-08 17:19:14', '2026-05-08 17:19:14'),
(2, 21, NULL, 'Manual de Aprovação de Entregas', 'documento', 'Este documento explica como o cliente deve revisar, aprovar ou solicitar ajustes nas entregas.', NULL, NULL, NULL, 1, NULL, '2026-05-08 17:19:14', '2026-05-08 17:19:14'),
(3, 21, NULL, 'Link do Ambiente de Homologação', 'link', 'Acesso ao ambiente onde o cliente pode revisar as entregas antes da publicação.', 'https://homologacao.exemplo.com.br', NULL, NULL, 1, NULL, '2026-05-08 17:19:14', '2026-05-08 17:19:14'),
(4, 21, NULL, 'Ata da Reunião Inicial', 'ata', 'Foi definido o escopo inicial, prioridades da primeira fase e responsáveis pela validação.', NULL, NULL, NULL, 1, NULL, '2026-05-08 17:19:14', '2026-05-08 17:19:14'),
(5, 21, NULL, 'Ata de Alinhamento Semanal', 'ata', 'Foram revisadas as entregas em andamento e definido que a próxima validação será feita até sexta-feira.', NULL, NULL, NULL, 1, NULL, '2026-05-08 17:19:14', '2026-05-08 17:19:14'),
(6, 21, NULL, 'Wiki do projeto - Regras gerais', 'wiki', 'Escopo aprovado: criação da página inicial, formulário de contato, área do cliente e publicação assistida. Alterações fora do escopo devem ser registradas como solicitação no portal.', NULL, NULL, NULL, 1, NULL, '2026-05-08 17:59:01', '2026-05-08 17:59:01'),
(7, 21, NULL, 'Manual rápido para aprovações', 'documento', 'Para aprovar uma entrega, acesse o bloco Pronto para revisão / aprovação, leia a descrição do item e envie sua resposta pelo chat ou solicitação.', NULL, NULL, NULL, 1, NULL, '2026-05-08 17:59:01', '2026-05-08 17:59:01'),
(8, 21, NULL, 'Link do protótipo navegável', 'link', 'Protótipo visual para validação do cliente.', 'https://www.exemplo.com/prototipo-cliente', NULL, NULL, 1, NULL, '2026-05-08 17:59:01', '2026-05-08 17:59:01'),
(9, 21, 61, 'Ata da reunião - alinhamento de layout', 'ata', 'Decisões: manter destaque principal na home, reduzir quantidade de textos longos, priorizar botão de contato e exibir depoimentos na segunda dobra.', NULL, NULL, NULL, 1, NULL, '2026-05-06 17:59:01', '2026-05-08 17:59:01'),
(10, 21, 62, 'Ata da reunião - integração comercial', 'ata', 'Decisões: leads serão enviados para a equipe comercial, com cópia para o gestor responsável. O cliente validará um teste antes da publicação final.', NULL, NULL, NULL, 1, NULL, '2026-05-07 17:59:01', '2026-05-08 17:59:01');

-- --------------------------------------------------------

--
-- Estrutura da tabela `portal_mensagens`
--

CREATE TABLE `portal_mensagens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa_id` bigint(20) UNSIGNED NOT NULL,
  `atendimento_id` bigint(20) UNSIGNED DEFAULT NULL,
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

INSERT INTO `portal_mensagens` (`id`, `empresa_id`, `atendimento_id`, `item_controle_id`, `user_id`, `nome`, `email`, `mensagem`, `origem`, `conversa_status`, `visualizada_em`, `created_at`, `updated_at`) VALUES
(118, 4, 55, NULL, 111, 'admin', 'admin@admin.com', 'teeste', 'interno', 'aberta', NULL, '2026-06-18 20:28:08', '2026-06-18 20:28:08'),
(119, 4, 55, NULL, 111, 'admin', 'admin@admin.com', '8770', 'interno', 'aberta', NULL, '2026-06-18 20:52:42', '2026-06-18 20:52:42');

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
(10, 6, NULL, NULL, 'teste mensagem', 'teste', 'alta', 'aberto', 'cliente', NULL, NULL, NULL, '2026-05-19 16:56:35', '2026-05-19 16:56:35'),
(11, 4, NULL, NULL, 'teste mensagem problema 2\'', 'problema 2 xxxxx xxx xxxxx xx xxxx xxxxx xxxxx xxxx xxx', 'urgente', 'aguardando_cliente', 'portal_cliente', NULL, NULL, NULL, '2026-06-17 18:00:05', '2026-06-17 18:00:05'),
(12, 4, NULL, NULL, 'teste mensagem', 'teste', 'media', 'aguardando_cliente', 'portal_cliente', NULL, NULL, NULL, '2026-06-17 19:36:24', '2026-06-17 19:36:24'),
(13, 4, NULL, NULL, 'teste mensagem problema 2\'', 'teste12', 'media', 'aguardando_cliente', 'portal_cliente', NULL, NULL, NULL, '2026-06-17 19:41:41', '2026-06-17 19:41:41'),
(14, 4, NULL, NULL, 'teste3', 'teste3', 'media', 'aberto', 'portal_cliente', NULL, NULL, NULL, '2026-06-17 18:05:49', '2026-06-17 18:05:49'),
(15, 4, NULL, NULL, 'teste1', 'teste1', 'media', 'aguardando_cliente', 'portal_cliente', NULL, NULL, NULL, '2026-06-18 18:10:53', '2026-06-18 18:10:53'),
(16, 4, NULL, NULL, 'teste2', 'teste2', 'media', 'aguardando_cliente', 'portal_cliente', NULL, NULL, NULL, '2026-06-18 18:11:04', '2026-06-18 18:11:04'),
(17, 4, NULL, NULL, 'teste1', 'teste1', 'baixa', 'aberto', 'portal_cliente', NULL, NULL, NULL, '2026-06-18 19:53:59', '2026-06-18 19:53:59'),
(18, 4, NULL, NULL, 'teste1', 'teste1', 'media', 'aguardando_cliente', 'portal_cliente', NULL, NULL, NULL, '2026-06-18 20:26:43', '2026-06-18 20:26:43');

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
(5, 1, 'clientes', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Clientes - view'),
(6, 1, 'clientes', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Clientes - create'),
(7, 1, 'clientes', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Clientes - edit'),
(8, 1, 'clientes', 'delete', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Clientes - delete'),
(9, 1, 'clientes', 'export', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Clientes - export'),
(10, 1, 'documentos', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - view'),
(11, 1, 'documentos', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - create'),
(12, 1, 'documentos', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - edit'),
(13, 1, 'documentos', 'delete', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - delete'),
(14, 1, 'documentos', 'approve', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - approve'),
(15, 1, 'documentos', 'export', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - export'),
(16, 1, 'cobrancas', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Cobrancas - view'),
(17, 1, 'cobrancas', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Cobrancas - create'),
(18, 1, 'cobrancas', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Cobrancas - edit'),
(19, 1, 'cobrancas', 'delete', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Cobrancas - delete'),
(20, 1, 'cobrancas', 'approve', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Cobrancas - approve'),
(21, 1, 'cobrancas', 'cancel', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Cobrancas - cancel'),
(22, 1, 'financeiro', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Financeiro - view'),
(23, 1, 'financeiro', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Financeiro - create'),
(24, 1, 'financeiro', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Financeiro - edit'),
(25, 1, 'financeiro', 'delete', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Financeiro - delete'),
(26, 1, 'financeiro', 'approve', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Financeiro - approve'),
(27, 1, 'financeiro', 'export', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Financeiro - export'),
(28, 1, 'atendimentos', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - view'),
(29, 1, 'atendimentos', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - create'),
(30, 1, 'atendimentos', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - edit'),
(31, 1, 'atendimentos', 'reply', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - reply'),
(32, 1, 'atendimentos', 'close', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - close'),
(33, 1, 'atendimentos', 'reassign', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - reassign'),
(34, 1, 'tarefas', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - view'),
(35, 1, 'tarefas', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - create'),
(36, 1, 'tarefas', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - edit'),
(37, 1, 'tarefas', 'delete', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - delete'),
(38, 1, 'tarefas', 'approve', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - approve'),
(39, 1, 'tarefas', 'reassign', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - reassign'),
(40, 1, 'aprovacoes', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Aprovacoes - view'),
(41, 1, 'aprovacoes', 'approve', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Aprovacoes - approve'),
(42, 1, 'armazenamento', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Armazenamento - view'),
(43, 1, 'armazenamento', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Armazenamento - create'),
(44, 1, 'armazenamento', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Armazenamento - edit'),
(45, 1, 'armazenamento', 'delete', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Armazenamento - delete'),
(46, 1, 'armazenamento', 'export', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Armazenamento - export'),
(47, 1, 'relatorios', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Relatorios - view'),
(48, 1, 'relatorios', 'export', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Relatorios - export'),
(49, 1, 'governanca', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Governanca - view'),
(50, 1, 'governanca', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Governanca - create'),
(51, 1, 'governanca', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Governanca - edit'),
(52, 1, 'governanca', 'delete', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Governanca - delete'),
(53, 1, 'governanca', 'approve', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Governanca - approve'),
(68, 11, 'clientes', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Clientes - view'),
(69, 11, 'clientes', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Clientes - create'),
(70, 11, 'clientes', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Clientes - edit'),
(71, 11, 'clientes', 'export', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Clientes - export'),
(72, 11, 'documentos', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - view'),
(73, 11, 'documentos', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - create'),
(74, 11, 'documentos', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - edit'),
(75, 11, 'documentos', 'approve', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - approve'),
(76, 11, 'documentos', 'export', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - export'),
(77, 11, 'cobrancas', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Cobrancas - view'),
(78, 11, 'cobrancas', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Cobrancas - create'),
(79, 11, 'cobrancas', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Cobrancas - edit'),
(80, 11, 'cobrancas', 'approve', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Cobrancas - approve'),
(81, 11, 'cobrancas', 'cancel', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Cobrancas - cancel'),
(82, 11, 'financeiro', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Financeiro - view'),
(83, 11, 'financeiro', 'export', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Financeiro - export'),
(84, 11, 'atendimentos', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - view'),
(85, 11, 'atendimentos', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - create'),
(86, 11, 'atendimentos', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - edit'),
(87, 11, 'atendimentos', 'reply', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - reply'),
(88, 11, 'atendimentos', 'close', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - close'),
(89, 11, 'atendimentos', 'reassign', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - reassign'),
(90, 11, 'tarefas', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - view'),
(91, 11, 'tarefas', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - create'),
(92, 11, 'tarefas', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - edit'),
(93, 11, 'tarefas', 'approve', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - approve'),
(94, 11, 'tarefas', 'reassign', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - reassign'),
(95, 11, 'aprovacoes', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Aprovacoes - view'),
(96, 11, 'aprovacoes', 'approve', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Aprovacoes - approve'),
(97, 11, 'armazenamento', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Armazenamento - view'),
(98, 11, 'armazenamento', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Armazenamento - create'),
(99, 11, 'armazenamento', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Armazenamento - edit'),
(100, 11, 'armazenamento', 'export', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Armazenamento - export'),
(101, 11, 'relatorios', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Relatorios - view'),
(102, 11, 'relatorios', 'export', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Relatorios - export'),
(103, 11, 'governanca', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Governanca - view'),
(131, 13, 'clientes', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Clientes - view'),
(132, 13, 'clientes', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Clientes - edit'),
(133, 13, 'documentos', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - view'),
(134, 13, 'documentos', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - create'),
(135, 13, 'documentos', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - edit'),
(136, 13, 'documentos', 'approve', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - approve'),
(137, 13, 'cobrancas', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Cobrancas - view'),
(138, 13, 'financeiro', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Financeiro - view'),
(139, 13, 'atendimentos', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - view'),
(140, 13, 'atendimentos', 'reply', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - reply'),
(141, 13, 'atendimentos', 'close', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - close'),
(142, 13, 'atendimentos', 'reassign', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - reassign'),
(143, 13, 'tarefas', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - view'),
(144, 13, 'tarefas', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - create'),
(145, 13, 'tarefas', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - edit'),
(146, 13, 'tarefas', 'approve', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - approve'),
(147, 13, 'tarefas', 'reassign', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - reassign'),
(148, 13, 'aprovacoes', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Aprovacoes - view'),
(149, 13, 'aprovacoes', 'approve', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Aprovacoes - approve'),
(150, 13, 'armazenamento', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Armazenamento - view'),
(151, 13, 'armazenamento', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Armazenamento - create'),
(152, 13, 'armazenamento', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Armazenamento - edit'),
(153, 13, 'relatorios', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Relatorios - view'),
(162, 14, 'clientes', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Clientes - view'),
(163, 14, 'clientes', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Clientes - edit'),
(164, 14, 'documentos', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - view'),
(165, 14, 'documentos', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - create'),
(166, 14, 'documentos', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - edit'),
(167, 14, 'cobrancas', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Cobrancas - view'),
(168, 14, 'atendimentos', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - view'),
(169, 14, 'atendimentos', 'reply', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - reply'),
(170, 14, 'tarefas', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - view'),
(171, 14, 'tarefas', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - create'),
(172, 14, 'tarefas', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - edit'),
(173, 14, 'armazenamento', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Armazenamento - view'),
(174, 14, 'armazenamento', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Armazenamento - create'),
(175, 14, 'relatorios', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Relatorios - view'),
(177, 15, 'clientes', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Clientes - view'),
(178, 15, 'documentos', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - view'),
(179, 15, 'documentos', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - create'),
(180, 15, 'atendimentos', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - view'),
(181, 15, 'atendimentos', 'reply', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - reply'),
(182, 15, 'tarefas', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - view'),
(183, 15, 'tarefas', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - create'),
(184, 15, 'tarefas', 'edit', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - edit'),
(185, 15, 'armazenamento', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Armazenamento - view'),
(192, 16, 'documentos', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - view'),
(193, 16, 'documentos', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Documentos - create'),
(194, 16, 'atendimentos', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - view'),
(195, 16, 'atendimentos', 'create', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - create'),
(196, 16, 'atendimentos', 'reply', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Atendimentos - reply'),
(197, 16, 'tarefas', 'view', 'empresa', '2026-06-19 19:41:11', '2026-06-19 19:41:11', 'Tarefas - view'),
(200, 2, 'clientes', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Clientes - view'),
(201, 2, 'clientes', 'create', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Clientes - create'),
(202, 2, 'clientes', 'edit', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Clientes - edit'),
(203, 2, 'documentos', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Documentos - view'),
(204, 2, 'documentos', 'create', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Documentos - create'),
(205, 2, 'documentos', 'edit', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Documentos - edit'),
(206, 2, 'documentos', 'export', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Documentos - export'),
(207, 2, 'atendimentos', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Atendimentos - view'),
(208, 2, 'atendimentos', 'create', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Atendimentos - create'),
(209, 2, 'atendimentos', 'edit', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Atendimentos - edit'),
(210, 2, 'atendimentos', 'reply', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Atendimentos - reply'),
(211, 2, 'atendimentos', 'close', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Atendimentos - close'),
(212, 2, 'atendimentos', 'reassign', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Atendimentos - reassign'),
(213, 2, 'tarefas', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Tarefas - view'),
(214, 2, 'tarefas', 'create', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Tarefas - create'),
(215, 2, 'tarefas', 'edit', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Tarefas - edit'),
(216, 2, 'tarefas', 'reassign', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Tarefas - reassign'),
(217, 2, 'armazenamento', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Armazenamento - view'),
(218, 2, 'armazenamento', 'create', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Armazenamento - create'),
(219, 2, 'armazenamento', 'edit', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Armazenamento - edit'),
(220, 2, 'relatorios', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Relatorios - view'),
(231, 3, 'clientes', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Clientes - view'),
(232, 3, 'clientes', 'export', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Clientes - export'),
(233, 3, 'documentos', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Documentos - view'),
(234, 3, 'cobrancas', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Cobrancas - view'),
(235, 3, 'cobrancas', 'create', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Cobrancas - create'),
(236, 3, 'cobrancas', 'edit', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Cobrancas - edit'),
(237, 3, 'cobrancas', 'delete', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Cobrancas - delete'),
(238, 3, 'cobrancas', 'approve', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Cobrancas - approve'),
(239, 3, 'cobrancas', 'cancel', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Cobrancas - cancel'),
(240, 3, 'financeiro', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Financeiro - view'),
(241, 3, 'financeiro', 'create', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Financeiro - create'),
(242, 3, 'financeiro', 'edit', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Financeiro - edit'),
(243, 3, 'financeiro', 'delete', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Financeiro - delete'),
(244, 3, 'financeiro', 'approve', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Financeiro - approve'),
(245, 3, 'financeiro', 'export', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Financeiro - export'),
(246, 3, 'relatorios', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Relatorios - view'),
(247, 3, 'relatorios', 'export', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Relatorios - export'),
(262, 4, 'clientes', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Clientes - view'),
(263, 4, 'documentos', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Documentos - view'),
(264, 4, 'documentos', 'approve', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Documentos - approve'),
(265, 4, 'documentos', 'export', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Documentos - export'),
(266, 4, 'cobrancas', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Cobrancas - view'),
(267, 4, 'financeiro', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Financeiro - view'),
(268, 4, 'atendimentos', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Atendimentos - view'),
(269, 4, 'tarefas', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Tarefas - view'),
(270, 4, 'tarefas', 'approve', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Tarefas - approve'),
(271, 4, 'aprovacoes', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Aprovacoes - view'),
(272, 4, 'aprovacoes', 'approve', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Aprovacoes - approve'),
(273, 4, 'armazenamento', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Armazenamento - view'),
(274, 4, 'armazenamento', 'export', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Armazenamento - export'),
(275, 4, 'relatorios', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Relatorios - view'),
(276, 4, 'relatorios', 'export', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Relatorios - export'),
(277, 4, 'governanca', 'view', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Governanca - view'),
(278, 4, 'governanca', 'create', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Governanca - create'),
(279, 4, 'governanca', 'edit', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Governanca - edit'),
(280, 4, 'governanca', 'approve', 'empresa', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Governanca - approve'),
(293, 5, 'documentos', 'view', 'proprio', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Documentos - view'),
(294, 5, 'documentos', 'create', 'proprio', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Documentos - create'),
(295, 5, 'atendimentos', 'view', 'proprio', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Atendimentos - view'),
(296, 5, 'atendimentos', 'create', 'proprio', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Atendimentos - create'),
(297, 5, 'atendimentos', 'reply', 'proprio', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Atendimentos - reply'),
(298, 5, 'tarefas', 'view', 'proprio', '2026-06-22 13:02:15', '2026-06-22 13:02:15', 'Tarefas - view'),
(299, 1, 'governanca', 'export', 'empresa', '2026-06-24 14:40:24', '2026-06-24 14:40:24', 'Governança - export'),
(300, 11, 'governanca', 'export', 'empresa', '2026-06-24 14:40:24', '2026-06-24 14:40:24', 'Governança - export'),
(301, 4, 'governanca', 'export', 'empresa', '2026-06-24 14:40:24', '2026-06-24 14:40:24', 'Governança - export');

-- --------------------------------------------------------

--
-- Estrutura da tabela `prazzu_permission_audits`
--

CREATE TABLE `prazzu_permission_audits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `actor_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `target_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `event` varchar(80) NOT NULL,
  `module` varchar(80) DEFAULT NULL,
  `action` varchar(80) DEFAULT NULL,
  `scope` varchar(80) DEFAULT NULL,
  `allowed` tinyint(1) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `before_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`before_payload`)),
  `after_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`after_payload`)),
  `ip_address` varchar(64) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `prazzu_permission_audits`
--

INSERT INTO `prazzu_permission_audits` (`id`, `actor_user_id`, `target_user_id`, `role_id`, `event`, `module`, `action`, `scope`, `allowed`, `reason`, `before_payload`, `after_payload`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 111, NULL, 5, 'role.status.updated', NULL, NULL, NULL, NULL, 'Status do perfil alterado.', '{\"active\":true}', '{\"active\":false}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 13:10:16', '2026-06-22 13:10:16'),
(2, 111, NULL, 14, 'role.status.updated', NULL, NULL, NULL, NULL, 'Status do perfil alterado.', '{\"active\":true}', '{\"active\":false}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 13:30:21', '2026-06-22 13:30:21'),
(3, 111, NULL, 15, 'role.status.updated', NULL, NULL, NULL, NULL, 'Status do perfil alterado.', '{\"active\":true}', '{\"active\":false}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 13:30:25', '2026-06-22 13:30:25'),
(4, 111, NULL, 3, 'role.status.updated', NULL, NULL, NULL, NULL, 'Status do perfil alterado.', '{\"active\":true}', '{\"active\":false}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 14:00:44', '2026-06-22 14:00:44'),
(5, 111, NULL, 11, 'role.status.updated', NULL, NULL, NULL, NULL, 'Status do perfil alterado.', '{\"active\":true}', '{\"active\":false}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 14:00:48', '2026-06-22 14:00:48'),
(6, 111, NULL, 2, 'role.status.updated', NULL, NULL, NULL, NULL, 'Status do perfil alterado.', '{\"active\":true}', '{\"active\":false}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 14:00:55', '2026-06-22 14:00:55'),
(7, 111, NULL, 13, 'role.status.updated', NULL, NULL, NULL, NULL, 'Status do perfil alterado.', '{\"active\":true}', '{\"active\":false}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 14:01:05', '2026-06-22 14:01:05'),
(8, 111, NULL, 6, 'role.status.updated', NULL, NULL, NULL, NULL, 'Status do perfil alterado.', '{\"active\":false}', '{\"active\":true}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 14:01:09', '2026-06-22 14:01:09'),
(9, 111, NULL, 9, 'role.status.updated', NULL, NULL, NULL, NULL, 'Status do perfil alterado.', '{\"active\":true}', '{\"active\":false}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 14:01:13', '2026-06-22 14:01:13'),
(10, 111, NULL, 4, 'role.status.updated', NULL, NULL, NULL, NULL, 'Status do perfil alterado.', '{\"active\":true}', '{\"active\":false}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 14:02:12', '2026-06-22 14:02:12'),
(11, 111, NULL, 4, 'role.status.updated', NULL, NULL, NULL, NULL, 'Status do perfil alterado.', '{\"active\":false}', '{\"active\":true}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 14:02:19', '2026-06-22 14:02:19'),
(12, 111, NULL, 6, 'role.status.updated', NULL, NULL, NULL, NULL, 'Status do perfil alterado.', '{\"active\":true}', '{\"active\":false}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 14:09:02', '2026-06-22 14:09:02');

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
(1, 'Administrador', 'Acesso completo ao ambiente da empresa.', 1, '2026-05-04 20:51:37', '2026-06-22 13:02:15'),
(2, 'Operação', 'Acesso operacional a clientes, atendimentos, pendências e documentos.', 0, '2026-05-04 20:51:37', '2026-06-22 14:00:55'),
(3, 'Financeiro', 'Acesso ao módulo financeiro, cobranças e assinaturas.', 0, '2026-05-04 20:51:37', '2026-06-22 14:00:44'),
(4, 'Compliance', 'Acesso a auditoria, riscos, evidências e SLA.', 1, '2026-05-04 20:51:37', '2026-06-22 14:02:19'),
(5, 'Cliente Portal', 'Perfil externo limitado ao portal do cliente.', 0, '2026-05-04 20:51:37', '2026-06-22 13:10:16'),
(6, 'Admin', 'Perfil legado consolidado em Administrador.', 0, '2026-05-06 11:33:22', '2026-06-22 14:09:02'),
(7, 'Member', 'Perfil legado consolidado em Analista.', 0, '2026-05-06 11:33:22', '2026-06-22 13:02:15'),
(8, 'Guest', 'Perfil legado consolidado em Cliente Portal.', 0, '2026-05-06 11:33:22', '2026-06-22 13:02:15'),
(9, 'Estagiário', 'Perfil operacional restrito, sem exclusão e sem exportação.', 0, '2026-05-06 11:33:22', '2026-06-22 14:01:13'),
(10, 'Visualizador Externo', 'Perfil legado consolidado em Cliente Portal.', 0, '2026-05-06 11:33:22', '2026-06-22 13:02:15'),
(11, 'Gestor', 'Gestão operacional com aprovações e edição ampla.', 0, '2026-05-06 11:37:51', '2026-06-22 14:00:48'),
(12, 'Sistema - Segurança', 'Perfil técnico interno ocultado da tela operacional.', 0, '2026-05-13 11:15:19', '2026-06-22 13:02:15'),
(13, 'Supervisor', 'Acompanha equipe, revisa tarefas e aprova documentos.', 0, '2026-06-19 19:41:11', '2026-06-22 14:01:05'),
(14, 'Analista', 'Executa tarefas, documentos e atendimentos sem ações destrutivas.', 0, '2026-06-19 19:41:11', '2026-06-22 13:30:21'),
(15, 'Assistente', 'Executa rotinas básicas e acompanha pendências.', 0, '2026-06-19 19:41:11', '2026-06-22 13:30:25'),
(16, 'Cliente', 'Perfil legado consolidado em Cliente Portal.', 0, '2026-06-19 19:41:11', '2026-06-22 13:02:15');

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

--
-- Extraindo dados da tabela `prazzu_templates`
--

INSERT INTO `prazzu_templates` (`id`, `module`, `name`, `description`, `payload`, `active`, `created_at`, `updated_at`) VALUES
(1, 'contabil', 'Fechamento Fiscal', 'Template operacional para conduzir o fechamento fiscal mensal com conferência de documentos, apuração de tributos, entrega de obrigações e validação final.', '{\"family\":\"templates_contabeis\",\"area\":\"fiscal\",\"official\":true,\"version\":1,\"tasks\":[{\"title\":\"Solicitar documentos fiscais do período\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":0,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Solicitar documentos fiscais do período\",\"checklist\":[{\"titulo\":\"Enviar relação de documentos ao cliente\"},{\"titulo\":\"Receber notas fiscais de entrada e saída\"},{\"titulo\":\"Conferir XMLs e documentos faltantes\"}]},{\"title\":\"Importar e validar documentos fiscais\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":1,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Importar e validar documentos fiscais\",\"checklist\":[{\"titulo\":\"Importar XML/NF-e/NFS-e\"},{\"titulo\":\"Validar competência\"},{\"titulo\":\"Identificar notas canceladas ou inutilizadas\"}]},{\"title\":\"Apurar impostos do período\",\"type\":\"tarefa\",\"priority\":\"urgente\",\"days_after_start\":2,\"sla_hours\":36,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Apurar impostos do período\",\"checklist\":[{\"titulo\":\"Calcular tributos federais\"},{\"titulo\":\"Calcular tributos estaduais/municipais\"},{\"titulo\":\"Registrar memória de cálculo\"}]},{\"title\":\"Revisar inconsistências fiscais\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":4,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Revisar inconsistências fiscais\",\"checklist\":[{\"titulo\":\"Conferir CFOP/CST/NCM\"},{\"titulo\":\"Validar créditos e retenções\"},{\"titulo\":\"Registrar pendências ao cliente\"}]},{\"title\":\"Emitir guias e obrigações acessórias\",\"type\":\"tarefa\",\"priority\":\"urgente\",\"days_after_start\":5,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Emitir guias e obrigações acessórias\",\"checklist\":[{\"titulo\":\"Emitir guias de pagamento\"},{\"titulo\":\"Preparar arquivos de entrega\"},{\"titulo\":\"Anexar comprovantes no item\"}]},{\"title\":\"Aprovar fechamento fiscal\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":6,\"sla_hours\":12,\"estimated_minutes\":null,\"approval_required\":true,\"description\":\"Aprovar fechamento fiscal\",\"checklist\":[{\"titulo\":\"Revisão pelo responsável fiscal\"},{\"titulo\":\"Aprovação do gestor\"},{\"titulo\":\"Comunicar fechamento ao cliente\"}]}],\"custom_fields\":[{\"name\":\"Competência\",\"type\":\"text\",\"default\":null,\"options\":null},{\"name\":\"Regime tributário\",\"type\":\"text\",\"default\":null,\"options\":null},{\"name\":\"Status da apuração\",\"type\":\"text\",\"default\":null,\"options\":null}],\"views\":[{\"name\":\"Lista operacional\",\"type\":\"list\",\"filter\":\"family:templates_contabeis\"},{\"name\":\"Kanban do processo\",\"type\":\"kanban\",\"filter\":\"status\"},{\"name\":\"Calendário de prazos\",\"type\":\"calendar\",\"filter\":\"data_vencimento\"}],\"automations\":[{\"trigger\":\"Template aplicado\",\"action\":\"Criar tarefas operacionais com SLA e checklist\"},{\"trigger\":\"Tarefa com aprovação obrigatória concluída\",\"action\":\"Enviar para aprovação do responsável\"}],\"docs\":[{\"title\":\"Orientação de uso\",\"content\":\"Use este template como roteiro operacional padrão. Ajuste responsáveis, prazos e documentos conforme o contrato do cliente.\"}],\"mind_map\":[{\"node\":\"Início\",\"parent\":null},{\"node\":\"Execução\",\"parent\":\"Início\"},{\"node\":\"Revisão/Aprovação\",\"parent\":\"Execução\"},{\"node\":\"Entrega ao cliente\",\"parent\":\"Revisão/Aprovação\"}]}', 1, '2026-06-26 17:38:04', '2026-06-26 17:38:04'),
(2, 'contabil', 'Fechamento Contábil', 'Template para fechamento contábil mensal com conciliações, lançamentos, balancete, revisão e entrega gerencial ao cliente.', '{\"family\":\"templates_contabeis\",\"area\":\"contabil\",\"official\":true,\"version\":1,\"tasks\":[{\"title\":\"Solicitar extratos e documentos contábeis\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":0,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Solicitar extratos e documentos contábeis\",\"checklist\":[{\"titulo\":\"Solicitar extratos bancários\"},{\"titulo\":\"Solicitar comprovantes financeiros\"},{\"titulo\":\"Conferir documentos pendentes\"}]},{\"title\":\"Realizar conciliação bancária\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":2,\"sla_hours\":36,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Realizar conciliação bancária\",\"checklist\":[{\"titulo\":\"Conciliar entradas\"},{\"titulo\":\"Conciliar saídas\"},{\"titulo\":\"Classificar pendências\"}]},{\"title\":\"Classificar lançamentos contábeis\",\"type\":\"tarefa\",\"priority\":\"media\",\"days_after_start\":3,\"sla_hours\":36,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Classificar lançamentos contábeis\",\"checklist\":[{\"titulo\":\"Classificar despesas\"},{\"titulo\":\"Classificar receitas\"},{\"titulo\":\"Validar centros de custo\"}]},{\"title\":\"Gerar balancete de verificação\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":5,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Gerar balancete de verificação\",\"checklist\":[{\"titulo\":\"Gerar balancete\"},{\"titulo\":\"Conferir contas críticas\"},{\"titulo\":\"Comparar com período anterior\"}]},{\"title\":\"Revisar demonstrações e relatórios\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":6,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Revisar demonstrações e relatórios\",\"checklist\":[{\"titulo\":\"Revisar DRE\"},{\"titulo\":\"Revisar balanço quando aplicável\"},{\"titulo\":\"Registrar observações gerenciais\"}]},{\"title\":\"Aprovar fechamento contábil\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":7,\"sla_hours\":12,\"estimated_minutes\":null,\"approval_required\":true,\"description\":\"Aprovar fechamento contábil\",\"checklist\":[{\"titulo\":\"Revisão técnica\"},{\"titulo\":\"Aprovação do gestor\"},{\"titulo\":\"Disponibilizar relatório ao cliente\"}]}],\"custom_fields\":[{\"name\":\"Competência\",\"type\":\"text\",\"default\":null,\"options\":null},{\"name\":\"Pendências de conciliação\",\"type\":\"text\",\"default\":null,\"options\":null},{\"name\":\"Responsável técnico\",\"type\":\"text\",\"default\":null,\"options\":null}],\"views\":[{\"name\":\"Lista operacional\",\"type\":\"list\",\"filter\":\"family:templates_contabeis\"},{\"name\":\"Kanban do processo\",\"type\":\"kanban\",\"filter\":\"status\"},{\"name\":\"Calendário de prazos\",\"type\":\"calendar\",\"filter\":\"data_vencimento\"}],\"automations\":[{\"trigger\":\"Template aplicado\",\"action\":\"Criar tarefas operacionais com SLA e checklist\"},{\"trigger\":\"Tarefa com aprovação obrigatória concluída\",\"action\":\"Enviar para aprovação do responsável\"}],\"docs\":[{\"title\":\"Orientação de uso\",\"content\":\"Use este template como roteiro operacional padrão. Ajuste responsáveis, prazos e documentos conforme o contrato do cliente.\"}],\"mind_map\":[{\"node\":\"Início\",\"parent\":null},{\"node\":\"Execução\",\"parent\":\"Início\"},{\"node\":\"Revisão/Aprovação\",\"parent\":\"Execução\"},{\"node\":\"Entrega ao cliente\",\"parent\":\"Revisão/Aprovação\"}]}', 1, '2026-06-26 17:38:04', '2026-06-26 17:38:04'),
(3, 'rh', 'Folha de Pagamento', 'Template de Departamento Pessoal para processamento mensal da folha, conferência de eventos, encargos, recibos e fechamento ao cliente.', '{\"family\":\"templates_contabeis\",\"area\":\"dp\",\"official\":true,\"version\":1,\"tasks\":[{\"title\":\"Coletar variáveis da folha\",\"type\":\"rh\",\"priority\":\"alta\",\"days_after_start\":0,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Coletar variáveis da folha\",\"checklist\":[{\"titulo\":\"Solicitar faltas e atrasos\"},{\"titulo\":\"Coletar horas extras\"},{\"titulo\":\"Coletar comissões e benefícios\"}]},{\"title\":\"Processar folha de pagamento\",\"type\":\"rh\",\"priority\":\"urgente\",\"days_after_start\":2,\"sla_hours\":36,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Processar folha de pagamento\",\"checklist\":[{\"titulo\":\"Lançar eventos\"},{\"titulo\":\"Processar folha\"},{\"titulo\":\"Validar bases de cálculo\"}]},{\"title\":\"Conferir encargos e guias\",\"type\":\"rh\",\"priority\":\"alta\",\"days_after_start\":4,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Conferir encargos e guias\",\"checklist\":[{\"titulo\":\"Conferir INSS\"},{\"titulo\":\"Conferir FGTS\"},{\"titulo\":\"Conferir IRRF quando aplicável\"}]},{\"title\":\"Enviar folha para aprovação\",\"type\":\"rh\",\"priority\":\"alta\",\"days_after_start\":5,\"sla_hours\":12,\"estimated_minutes\":null,\"approval_required\":true,\"description\":\"Enviar folha para aprovação\",\"checklist\":[{\"titulo\":\"Enviar resumo ao cliente\"},{\"titulo\":\"Registrar aprovação ou ajustes\"},{\"titulo\":\"Reprocessar se necessário\"}]},{\"title\":\"Disponibilizar recibos e guias\",\"type\":\"rh\",\"priority\":\"alta\",\"days_after_start\":6,\"sla_hours\":12,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Disponibilizar recibos e guias\",\"checklist\":[{\"titulo\":\"Gerar recibos\"},{\"titulo\":\"Anexar guias\"},{\"titulo\":\"Comunicar cliente\"}]}],\"custom_fields\":[{\"name\":\"Competência\",\"type\":\"text\",\"default\":null,\"options\":null},{\"name\":\"Quantidade de colaboradores\",\"type\":\"text\",\"default\":null,\"options\":null},{\"name\":\"Status da aprovação\",\"type\":\"text\",\"default\":null,\"options\":null}],\"views\":[{\"name\":\"Lista operacional\",\"type\":\"list\",\"filter\":\"family:templates_contabeis\"},{\"name\":\"Kanban do processo\",\"type\":\"kanban\",\"filter\":\"status\"},{\"name\":\"Calendário de prazos\",\"type\":\"calendar\",\"filter\":\"data_vencimento\"}],\"automations\":[{\"trigger\":\"Template aplicado\",\"action\":\"Criar tarefas operacionais com SLA e checklist\"},{\"trigger\":\"Tarefa com aprovação obrigatória concluída\",\"action\":\"Enviar para aprovação do responsável\"}],\"docs\":[{\"title\":\"Orientação de uso\",\"content\":\"Use este template como roteiro operacional padrão. Ajuste responsáveis, prazos e documentos conforme o contrato do cliente.\"}],\"mind_map\":[{\"node\":\"Início\",\"parent\":null},{\"node\":\"Execução\",\"parent\":\"Início\"},{\"node\":\"Revisão/Aprovação\",\"parent\":\"Execução\"},{\"node\":\"Entrega ao cliente\",\"parent\":\"Revisão/Aprovação\"}]}', 1, '2026-06-26 17:38:04', '2026-06-26 17:38:04'),
(4, 'rh', 'Admissão', 'Template para admissão de colaborador com coleta documental, cadastro, contrato, eventos trabalhistas e comunicação ao cliente.', '{\"family\":\"templates_contabeis\",\"area\":\"dp\",\"official\":true,\"version\":1,\"tasks\":[{\"title\":\"Coletar dados e documentos do novo colaborador\",\"type\":\"rh\",\"priority\":\"alta\",\"days_after_start\":0,\"sla_hours\":12,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Coletar dados e documentos do novo colaborador\",\"checklist\":[{\"titulo\":\"Documento de identificação\"},{\"titulo\":\"Dados bancários\"},{\"titulo\":\"Endereço e dependentes\"}]},{\"title\":\"Validar informações admissionais\",\"type\":\"rh\",\"priority\":\"alta\",\"days_after_start\":1,\"sla_hours\":12,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Validar informações admissionais\",\"checklist\":[{\"titulo\":\"Conferir função e salário\"},{\"titulo\":\"Conferir jornada\"},{\"titulo\":\"Conferir data de admissão\"}]},{\"title\":\"Cadastrar colaborador no sistema de folha\",\"type\":\"rh\",\"priority\":\"alta\",\"days_after_start\":1,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Cadastrar colaborador no sistema de folha\",\"checklist\":[{\"titulo\":\"Cadastrar dados pessoais\"},{\"titulo\":\"Cadastrar contrato\"},{\"titulo\":\"Cadastrar benefícios\"}]},{\"title\":\"Gerar documentos admissionais\",\"type\":\"rh\",\"priority\":\"media\",\"days_after_start\":2,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Gerar documentos admissionais\",\"checklist\":[{\"titulo\":\"Contrato de trabalho\"},{\"titulo\":\"Ficha de registro\"},{\"titulo\":\"Termos obrigatórios\"}]},{\"title\":\"Enviar eventos eSocial/admissionais\",\"type\":\"rh\",\"priority\":\"urgente\",\"days_after_start\":2,\"sla_hours\":12,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Enviar eventos eSocial/admissionais\",\"checklist\":[{\"titulo\":\"Transmitir evento\"},{\"titulo\":\"Conferir protocolo\"},{\"titulo\":\"Anexar comprovante\"}]},{\"title\":\"Confirmar admissão concluída\",\"type\":\"rh\",\"priority\":\"media\",\"days_after_start\":3,\"sla_hours\":8,\"estimated_minutes\":null,\"approval_required\":true,\"description\":\"Confirmar admissão concluída\",\"checklist\":[{\"titulo\":\"Avisar cliente\"},{\"titulo\":\"Arquivar documentos\"},{\"titulo\":\"Registrar conclusão\"}]}],\"custom_fields\":[{\"name\":\"Nome do colaborador\",\"type\":\"text\",\"default\":null,\"options\":null},{\"name\":\"CPF\",\"type\":\"text\",\"default\":null,\"options\":null},{\"name\":\"Data de admissão\",\"type\":\"date\",\"default\":null,\"options\":null}],\"views\":[{\"name\":\"Lista operacional\",\"type\":\"list\",\"filter\":\"family:templates_contabeis\"},{\"name\":\"Kanban do processo\",\"type\":\"kanban\",\"filter\":\"status\"},{\"name\":\"Calendário de prazos\",\"type\":\"calendar\",\"filter\":\"data_vencimento\"}],\"automations\":[{\"trigger\":\"Template aplicado\",\"action\":\"Criar tarefas operacionais com SLA e checklist\"},{\"trigger\":\"Tarefa com aprovação obrigatória concluída\",\"action\":\"Enviar para aprovação do responsável\"}],\"docs\":[{\"title\":\"Orientação de uso\",\"content\":\"Use este template como roteiro operacional padrão. Ajuste responsáveis, prazos e documentos conforme o contrato do cliente.\"}],\"mind_map\":[{\"node\":\"Início\",\"parent\":null},{\"node\":\"Execução\",\"parent\":\"Início\"},{\"node\":\"Revisão/Aprovação\",\"parent\":\"Execução\"},{\"node\":\"Entrega ao cliente\",\"parent\":\"Revisão/Aprovação\"}]}', 1, '2026-06-26 17:38:04', '2026-06-26 17:38:04'),
(5, 'rh', 'Demissão', 'Template para desligamento com cálculo rescisório, documentos, guias, conferências e formalização do encerramento.', '{\"family\":\"templates_contabeis\",\"area\":\"dp\",\"official\":true,\"version\":1,\"tasks\":[{\"title\":\"Receber solicitação de desligamento\",\"type\":\"rh\",\"priority\":\"alta\",\"days_after_start\":0,\"sla_hours\":8,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Receber solicitação de desligamento\",\"checklist\":[{\"titulo\":\"Confirmar tipo de desligamento\"},{\"titulo\":\"Confirmar data de aviso\"},{\"titulo\":\"Confirmar último dia trabalhado\"}]},{\"title\":\"Coletar dados para cálculo rescisório\",\"type\":\"rh\",\"priority\":\"alta\",\"days_after_start\":0,\"sla_hours\":12,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Coletar dados para cálculo rescisório\",\"checklist\":[{\"titulo\":\"Saldo de salário\"},{\"titulo\":\"Férias e adicionais\"},{\"titulo\":\"Descontos e benefícios\"}]},{\"title\":\"Calcular rescisão\",\"type\":\"rh\",\"priority\":\"urgente\",\"days_after_start\":1,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Calcular rescisão\",\"checklist\":[{\"titulo\":\"Processar cálculo\"},{\"titulo\":\"Conferir verbas\"},{\"titulo\":\"Conferir bases legais\"}]},{\"title\":\"Gerar documentos e guias rescisórias\",\"type\":\"rh\",\"priority\":\"alta\",\"days_after_start\":2,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Gerar documentos e guias rescisórias\",\"checklist\":[{\"titulo\":\"TRCT\"},{\"titulo\":\"Guia FGTS/GRRF quando aplicável\"},{\"titulo\":\"Demais documentos\"}]},{\"title\":\"Enviar para aprovação do cliente\",\"type\":\"rh\",\"priority\":\"alta\",\"days_after_start\":3,\"sla_hours\":12,\"estimated_minutes\":null,\"approval_required\":true,\"description\":\"Enviar para aprovação do cliente\",\"checklist\":[{\"titulo\":\"Enviar resumo\"},{\"titulo\":\"Registrar aprovação\"},{\"titulo\":\"Ajustar se necessário\"}]},{\"title\":\"Finalizar desligamento\",\"type\":\"rh\",\"priority\":\"media\",\"days_after_start\":4,\"sla_hours\":12,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Finalizar desligamento\",\"checklist\":[{\"titulo\":\"Arquivar comprovantes\"},{\"titulo\":\"Comunicar conclusão\"},{\"titulo\":\"Atualizar status interno\"}]}],\"custom_fields\":[{\"name\":\"Colaborador\",\"type\":\"text\",\"default\":null,\"options\":null},{\"name\":\"Tipo de desligamento\",\"type\":\"text\",\"default\":null,\"options\":null},{\"name\":\"Data de desligamento\",\"type\":\"date\",\"default\":null,\"options\":null}],\"views\":[{\"name\":\"Lista operacional\",\"type\":\"list\",\"filter\":\"family:templates_contabeis\"},{\"name\":\"Kanban do processo\",\"type\":\"kanban\",\"filter\":\"status\"},{\"name\":\"Calendário de prazos\",\"type\":\"calendar\",\"filter\":\"data_vencimento\"}],\"automations\":[{\"trigger\":\"Template aplicado\",\"action\":\"Criar tarefas operacionais com SLA e checklist\"},{\"trigger\":\"Tarefa com aprovação obrigatória concluída\",\"action\":\"Enviar para aprovação do responsável\"}],\"docs\":[{\"title\":\"Orientação de uso\",\"content\":\"Use este template como roteiro operacional padrão. Ajuste responsáveis, prazos e documentos conforme o contrato do cliente.\"}],\"mind_map\":[{\"node\":\"Início\",\"parent\":null},{\"node\":\"Execução\",\"parent\":\"Início\"},{\"node\":\"Revisão/Aprovação\",\"parent\":\"Execução\"},{\"node\":\"Entrega ao cliente\",\"parent\":\"Revisão/Aprovação\"}]}', 1, '2026-06-26 17:38:04', '2026-06-26 17:38:04'),
(6, 'contabil', 'Abertura de Empresa', 'Template societário/contábil para abertura de empresa desde coleta de dados, viabilidade, contrato, CNPJ, inscrições e entrega ao cliente.', '{\"family\":\"templates_contabeis\",\"area\":\"societario\",\"official\":true,\"version\":1,\"tasks\":[{\"title\":\"Coletar dados dos sócios e atividade\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":0,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Coletar dados dos sócios e atividade\",\"checklist\":[{\"titulo\":\"Dados dos sócios\"},{\"titulo\":\"Atividade econômica\"},{\"titulo\":\"Endereço da empresa\"}]},{\"title\":\"Realizar análise de viabilidade\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":1,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Realizar análise de viabilidade\",\"checklist\":[{\"titulo\":\"Validar CNAE\"},{\"titulo\":\"Validar endereço\"},{\"titulo\":\"Validar nome empresarial\"}]},{\"title\":\"Preparar ato constitutivo\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":2,\"sla_hours\":36,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Preparar ato constitutivo\",\"checklist\":[{\"titulo\":\"Elaborar contrato social/requerimento\"},{\"titulo\":\"Revisar cláusulas\"},{\"titulo\":\"Enviar para aprovação\"}]},{\"title\":\"Protocolar abertura nos órgãos competentes\",\"type\":\"tarefa\",\"priority\":\"urgente\",\"days_after_start\":4,\"sla_hours\":48,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Protocolar abertura nos órgãos competentes\",\"checklist\":[{\"titulo\":\"Protocolar Junta/Registro\"},{\"titulo\":\"Acompanhar exigências\"},{\"titulo\":\"Anexar protocolo\"}]},{\"title\":\"Obter CNPJ e inscrições\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":6,\"sla_hours\":48,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Obter CNPJ e inscrições\",\"checklist\":[{\"titulo\":\"Emitir CNPJ\"},{\"titulo\":\"Solicitar inscrição municipal/estadual quando aplicável\"},{\"titulo\":\"Conferir dados cadastrais\"}]},{\"title\":\"Entregar empresa aberta ao cliente\",\"type\":\"tarefa\",\"priority\":\"media\",\"days_after_start\":8,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":true,\"description\":\"Entregar empresa aberta ao cliente\",\"checklist\":[{\"titulo\":\"Organizar documentos finais\"},{\"titulo\":\"Enviar orientações iniciais\"},{\"titulo\":\"Registrar conclusão\"}]}],\"custom_fields\":[{\"name\":\"Razão social pretendida\",\"type\":\"text\",\"default\":null,\"options\":null},{\"name\":\"CNAE principal\",\"type\":\"text\",\"default\":null,\"options\":null},{\"name\":\"Regime tributário sugerido\",\"type\":\"text\",\"default\":null,\"options\":null}],\"views\":[{\"name\":\"Lista operacional\",\"type\":\"list\",\"filter\":\"family:templates_contabeis\"},{\"name\":\"Kanban do processo\",\"type\":\"kanban\",\"filter\":\"status\"},{\"name\":\"Calendário de prazos\",\"type\":\"calendar\",\"filter\":\"data_vencimento\"}],\"automations\":[{\"trigger\":\"Template aplicado\",\"action\":\"Criar tarefas operacionais com SLA e checklist\"},{\"trigger\":\"Tarefa com aprovação obrigatória concluída\",\"action\":\"Enviar para aprovação do responsável\"}],\"docs\":[{\"title\":\"Orientação de uso\",\"content\":\"Use este template como roteiro operacional padrão. Ajuste responsáveis, prazos e documentos conforme o contrato do cliente.\"}],\"mind_map\":[{\"node\":\"Início\",\"parent\":null},{\"node\":\"Execução\",\"parent\":\"Início\"},{\"node\":\"Revisão/Aprovação\",\"parent\":\"Execução\"},{\"node\":\"Entrega ao cliente\",\"parent\":\"Revisão/Aprovação\"}]}', 1, '2026-06-26 17:38:04', '2026-06-26 17:38:04'),
(7, 'contabil', 'Alteração Contratual', 'Template para alteração contratual com coleta de alterações, preparação do ato, aprovação, protocolo, acompanhamento e atualização cadastral.', '{\"family\":\"templates_contabeis\",\"area\":\"societario\",\"official\":true,\"version\":1,\"tasks\":[{\"title\":\"Mapear alteração solicitada\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":0,\"sla_hours\":12,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Mapear alteração solicitada\",\"checklist\":[{\"titulo\":\"Tipo de alteração\"},{\"titulo\":\"Dados novos\"},{\"titulo\":\"Documentos necessários\"}]},{\"title\":\"Coletar documentos e aprovações dos sócios\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":1,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Coletar documentos e aprovações dos sócios\",\"checklist\":[{\"titulo\":\"Documentos dos sócios\"},{\"titulo\":\"Comprovantes\"},{\"titulo\":\"Aprovação formal\"}]},{\"title\":\"Elaborar alteração contratual\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":2,\"sla_hours\":36,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Elaborar alteração contratual\",\"checklist\":[{\"titulo\":\"Redigir ato\"},{\"titulo\":\"Revisar dados cadastrais\"},{\"titulo\":\"Enviar minuta ao cliente\"}]},{\"title\":\"Protocolar alteração\",\"type\":\"tarefa\",\"priority\":\"urgente\",\"days_after_start\":4,\"sla_hours\":48,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Protocolar alteração\",\"checklist\":[{\"titulo\":\"Protocolar órgão competente\"},{\"titulo\":\"Acompanhar exigências\"},{\"titulo\":\"Anexar protocolo\"}]},{\"title\":\"Atualizar cadastros e inscrições\",\"type\":\"tarefa\",\"priority\":\"alta\",\"days_after_start\":6,\"sla_hours\":24,\"estimated_minutes\":null,\"approval_required\":false,\"description\":\"Atualizar cadastros e inscrições\",\"checklist\":[{\"titulo\":\"Atualizar CNPJ quando aplicável\"},{\"titulo\":\"Atualizar inscrições\"},{\"titulo\":\"Conferir comprovantes\"}]},{\"title\":\"Finalizar alteração contratual\",\"type\":\"tarefa\",\"priority\":\"media\",\"days_after_start\":7,\"sla_hours\":12,\"estimated_minutes\":null,\"approval_required\":true,\"description\":\"Finalizar alteração contratual\",\"checklist\":[{\"titulo\":\"Enviar documentos finais\"},{\"titulo\":\"Arquivar no cliente\"},{\"titulo\":\"Registrar conclusão\"}]}],\"custom_fields\":[{\"name\":\"Tipo de alteração\",\"type\":\"text\",\"default\":null,\"options\":null},{\"name\":\"Órgão de registro\",\"type\":\"text\",\"default\":null,\"options\":null},{\"name\":\"Protocolo\",\"type\":\"text\",\"default\":null,\"options\":null}],\"views\":[{\"name\":\"Lista operacional\",\"type\":\"list\",\"filter\":\"family:templates_contabeis\"},{\"name\":\"Kanban do processo\",\"type\":\"kanban\",\"filter\":\"status\"},{\"name\":\"Calendário de prazos\",\"type\":\"calendar\",\"filter\":\"data_vencimento\"}],\"automations\":[{\"trigger\":\"Template aplicado\",\"action\":\"Criar tarefas operacionais com SLA e checklist\"},{\"trigger\":\"Tarefa com aprovação obrigatória concluída\",\"action\":\"Enviar para aprovação do responsável\"}],\"docs\":[{\"title\":\"Orientação de uso\",\"content\":\"Use este template como roteiro operacional padrão. Ajuste responsáveis, prazos e documentos conforme o contrato do cliente.\"}],\"mind_map\":[{\"node\":\"Início\",\"parent\":null},{\"node\":\"Execução\",\"parent\":\"Início\"},{\"node\":\"Revisão/Aprovação\",\"parent\":\"Execução\"},{\"node\":\"Entrega ao cliente\",\"parent\":\"Revisão/Aprovação\"}]}', 1, '2026-06-26 17:38:04', '2026-06-26 17:38:04');

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
-- Estrutura da tabela `prazzu_user_permissions`
--

CREATE TABLE `prazzu_user_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(80) NOT NULL,
  `action` varchar(80) NOT NULL,
  `scope` varchar(80) NOT NULL DEFAULT 'empresa',
  `allowed` tinyint(1) NOT NULL DEFAULT 1,
  `reason` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
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

--
-- Extraindo dados da tabela `prazzu_user_roles`
--

INSERT INTO `prazzu_user_roles` (`id`, `user_id`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 111, 1, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(2, 371, 1, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(3, 372, 11, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(4, 373, 15, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(5, 374, 15, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(6, 375, 15, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(7, 376, 1, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(8, 377, 11, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(9, 378, 15, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(10, 379, 15, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(11, 380, 15, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(12, 381, 1, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(13, 382, 11, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(14, 383, 15, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(15, 384, 15, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(16, 385, 5, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(17, 386, 1, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(18, 387, 11, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(19, 388, 15, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(20, 396, 1, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(21, 398, 1, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(22, 401, 1, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(23, 404, 1, '2026-06-22 13:02:15', '2026-06-22 13:02:15'),
(24, 405, 1, '2026-06-22 13:02:15', '2026-06-22 13:02:15');

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
(169, 'Responsável Portal Cliente Empresa 21', 'responsavel.portal.21@teste.local', NULL, 'Responsável Operacional', 21, NULL, NULL, '2026-05-08 17:59:00', '2026-05-08 17:59:00'),
(170, 'Roni', 'webconta@webconta.com', NULL, 'Administrador', 13, 398, NULL, '2026-06-22 11:59:01', '2026-06-22 11:59:01'),
(171, 'ricardo empresa', 'ricardo-s-a@hotmail.com', NULL, 'Administrador', 21, 404, NULL, '2026-06-22 11:59:01', '2026-06-22 11:59:01'),
(172, 'joyce', 'joyce@joyce.com', NULL, 'Administrador', 22, 405, NULL, '2026-06-22 11:59:01', '2026-06-22 11:59:01');

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
(3, NULL, 111, 'melhoria', 'media', 'recusada', 'tetaet', 'etste', 'teste', 111, '2026-06-22 17:34:39', '2026-06-22 16:11:54', '2026-06-22 17:34:39');

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
  `perfil_contabil` varchar(50) DEFAULT NULL,
  `empresa_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `last_access_at`, `last_login_at`, `last_seen_at`, `created_at`, `updated_at`, `role`, `perfil_contabil`, `empresa_id`) VALUES
(111, 'admin', 'admin@admin.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-17 22:11:30', '2026-05-25 18:21:44', 'super_admin', NULL, NULL),
(371, 'Admin Alpha', 'admin.alpha@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'admin', 'socio', 4),
(372, 'Gestor Alpha', 'gestor.alpha@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'gestor', 'gestor', 4),
(373, 'User Alpha 1', 'user.alpha1@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'user', 'assistente', 4),
(374, 'User Alpha 2', 'user.alpha2@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'user', 'assistente', 4),
(375, 'User Alpha 3', 'user.alpha3@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'user', 'assistente', 4),
(376, 'Admin Beta', 'admin.beta@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', 'S37Em9p6S7VL9ChpNjiQagwPo4XjPo2dmkxC2OwuUVt2ydBRKxtdBrsJVVqn', NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'admin', 'socio', 5),
(377, 'Gestor Beta', 'gestor.beta@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'gestor', 'gestor', 5),
(378, 'User Beta 1', 'user.beta1@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'user', 'assistente', 5),
(379, 'User Beta 2', 'user.beta2@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'user', 'assistente', 5),
(380, 'User Beta 3', 'user.beta3@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'user', 'assistente', 5),
(381, 'Admin Gamma', 'admin.gamma@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'admin', 'socio', 6),
(382, 'Gestor Gamma', 'gestor.gamma@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'gestor', 'gestor', 6),
(383, 'User Gamma 1', 'user.gamma1@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'user', 'assistente', 6),
(384, 'User Gamma 2', 'user.gamma2@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'user', 'assistente', 6),
(385, 'User Gamma 3', 'user.gamma3@empresa.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-24 17:57:10', '2026-05-25 18:21:44', 'guest', 'cliente', 6),
(386, 'Admin Teste Completo', 'admin.teste.completo@prazzu.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-28 14:58:04', '2026-05-25 18:21:44', 'admin', 'socio', 7),
(387, 'Gestor Teste Completo', 'gestor.teste.completo@prazzu.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-28 14:58:04', '2026-05-25 18:21:44', 'gestor', 'gestor', 7),
(388, 'Usuário Teste Completo', 'usuario.teste.completo@prazzu.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-28 14:58:04', '2026-05-25 18:21:44', 'user', 'assistente', 7),
(396, 'Admin Seed Teste', 'admin.seed@prazzu.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-04-28 15:06:22', '2026-05-25 18:21:44', 'admin', 'socio', 11),
(398, 'Roni', 'webconta@webconta.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-05-01 19:09:38', '2026-05-25 18:21:44', 'admin', 'socio', 13),
(401, 'Roni2', 'webconta@webconta2.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-05-01 20:35:39', '2026-05-25 18:21:44', 'admin', 'socio', 18),
(404, 'ricardo empresa', 'ricardo-s-a@hotmail.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-05-01 20:53:08', '2026-05-25 18:21:44', 'admin', 'socio', 21),
(405, 'joyce', 'joyce@joyce.com', NULL, '$2y$12$86cQwU2SuBXe45g0Hoo2Yey13MMPoz5Mi4an8pMJ9gj4Cpzygowby', NULL, NULL, NULL, NULL, '2026-05-01 20:59:10', '2026-05-25 18:21:44', 'admin', 'socio', 22);

-- --------------------------------------------------------

--
-- Estrutura da tabela `user_sidebar_favorites`
--

CREATE TABLE `user_sidebar_favorites` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `item_key` varchar(500) NOT NULL,
  `position` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `user_sidebar_favorites`
--

INSERT INTO `user_sidebar_favorites` (`id`, `user_id`, `item_key`, `position`, `created_at`, `updated_at`) VALUES
(7, 111, 'Clientes e Atendimentos|Clientes e Atendimentos|http://localhost:8000/admin/atendimentos', 1, '2026-07-07 17:30:33', '2026-07-07 17:30:33');

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
  ADD KEY `ai_market_comments_detected_sentiment_index` (`detected_sentiment`),
  ADD KEY `ai_market_comments_detected_category_index` (`detected_category`),
  ADD KEY `ai_market_comments_created_at_index` (`created_at`);

--
-- Índices para tabela `ai_market_sources`
--
ALTER TABLE `ai_market_sources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_market_sources_competitor_name_index` (`competitor_name`),
  ADD KEY `ai_market_sources_source_type_index` (`source_type`),
  ADD KEY `ai_market_sources_is_active_index` (`is_active`);

--
-- Índices para tabela `ai_product_improvement_resolutions`
--
ALTER TABLE `ai_product_improvement_resolutions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ai_product_improvement_resolutions_item_unique` (`item_key`,`item_type`),
  ADD KEY `ai_product_improvement_resolutions_resolved_by_user_id_index` (`resolved_by_user_id`),
  ADD KEY `ai_product_improvement_resolutions_resolved_at_index` (`resolved_at`);

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
-- Índices para tabela `asaas_webhook_events`
--
ALTER TABLE `asaas_webhook_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `asaas_webhook_events_payload_hash_unique` (`payload_hash`),
  ADD UNIQUE KEY `uniq_lote02_asaas_payload_hash` (`payload_hash`),
  ADD KEY `asaas_webhook_events_gateway_payment_id_index` (`gateway_payment_id`),
  ADD KEY `asaas_webhook_events_gateway_subscription_id_index` (`gateway_subscription_id`),
  ADD KEY `asaas_webhook_events_status_index` (`status`),
  ADD KEY `asaas_webhook_events_received_at_index` (`received_at`),
  ADD KEY `idx_lote02_asaas_status_datas` (`status`,`processed_at`,`failed_at`),
  ADD KEY `idx_lote02_asaas_gateway_payment` (`gateway_payment_id`),
  ADD KEY `idx_lote02_asaas_gateway_subscription` (`gateway_subscription_id`);

--
-- Índices para tabela `assinaturas`
--
ALTER TABLE `assinaturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lote02_assinaturas_empresa_status` (`empresa_id`,`status`);

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
  ADD KEY `idx_auditoria_evento` (`evento`,`created_at`),
  ADD KEY `idx_lote02_audit_empresa_modelo_data` (`empresa_id`,`auditable_type`,`auditable_id`,`created_at`);

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
  ADD KEY `idx_empresas_nome_razao` (`nome_fantasia`,`razao_social`),
  ADD KEY `empresas_plano_index` (`plano`);

--
-- Índices para tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Índices para tabela `file_retention_events`
--
ALTER TABLE `file_retention_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_retention_events_file_retention_policy_id_index` (`file_retention_policy_id`),
  ADD KEY `file_retention_events_arquivo_id_index` (`arquivo_id`),
  ADD KEY `file_retention_events_empresa_id_index` (`empresa_id`);

--
-- Índices para tabela `file_retention_policies`
--
ALTER TABLE `file_retention_policies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_retention_policies_empresa_id_index` (`empresa_id`),
  ADD KEY `file_retention_policies_origin_index` (`origin`),
  ADD KEY `file_retention_policies_is_active_index` (`is_active`);

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
  ADD KEY `idx_trabalho_prioridade_status` (`prioridade`,`status`),
  ADD KEY `idx_atendimento_id` (`atendimento_id`),
  ADD KEY `idx_lote02_item_empresa_status_vencimento` (`empresa_id`,`status`,`data_vencimento`),
  ADD KEY `idx_lote02_item_responsavel_status_vencimento` (`responsavel_id`,`status`,`data_vencimento`),
  ADD KEY `idx_lote02_item_portal_token` (`portal_token`);

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
  ADD KEY `idx_pagamentos_empresa_status` (`empresa_id`,`status`),
  ADD KEY `idx_lote02_pagamentos_empresa_status_vencimento` (`empresa_id`,`status`,`vencimento`);

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
  ADD KEY `idx_portal_documentos_empresa_updated` (`empresa_id`,`updated_at`,`created_at`),
  ADD KEY `portal_documentos_tamanho_bytes_index` (`tamanho_bytes`),
  ADD KEY `idx_lote02_portal_doc_empresa_item` (`empresa_id`,`item_controle_id`);

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
  ADD KEY `idx_portal_mensagens_empresa_created` (`empresa_id`,`created_at`),
  ADD KEY `idx_atendimento_id` (`atendimento_id`),
  ADD KEY `idx_lote02_portal_msg_empresa_item` (`empresa_id`,`item_controle_id`);

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
  ADD UNIQUE KEY `prazzu_permissions_role_module_action_scope_unique` (`role_id`,`module`,`action`,`scope`),
  ADD KEY `idx_prazzu_permissions_role` (`role_id`),
  ADD KEY `idx_prazzu_permissions_module` (`module`),
  ADD KEY `idx_prazzu_permissions_role_id` (`role_id`),
  ADD KEY `idx_prazzu_permissions_module_action` (`module`,`action`);

--
-- Índices para tabela `prazzu_permission_audits`
--
ALTER TABLE `prazzu_permission_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prazzu_permission_audits_event_created_at_index` (`event`,`created_at`),
  ADD KEY `prazzu_permission_audits_target_user_id_created_at_index` (`target_user_id`,`created_at`),
  ADD KEY `prazzu_permission_audits_role_id_created_at_index` (`role_id`,`created_at`),
  ADD KEY `prazzu_permission_audits_module_action_index` (`module`,`action`),
  ADD KEY `prazzu_permission_audits_actor_user_id_foreign` (`actor_user_id`);

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
  ADD UNIQUE KEY `uk_prazzu_roles_name` (`name`),
  ADD UNIQUE KEY `prazzu_roles_name_unique` (`name`);

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
-- Índices para tabela `prazzu_user_permissions`
--
ALTER TABLE `prazzu_user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_prazzu_user_permissions_user_module_action_scope` (`user_id`,`module`,`action`,`scope`),
  ADD UNIQUE KEY `prazzu_user_permissions_user_module_action_scope_unique` (`user_id`,`module`,`action`,`scope`),
  ADD KEY `idx_prazzu_user_permissions_module_action` (`module`,`action`),
  ADD KEY `idx_prazzu_user_permissions_created_by` (`created_by`);

--
-- Índices para tabela `prazzu_user_roles`
--
ALTER TABLE `prazzu_user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prazzu_user_roles_user_role_unique` (`user_id`,`role_id`),
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
-- Índices para tabela `user_sidebar_favorites`
--
ALTER TABLE `user_sidebar_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_sidebar_favorites_user_id_item_key_unique` (`user_id`,`item_key`),
  ADD KEY `user_sidebar_favorites_user_id_position_index` (`user_id`,`position`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de tabela `ai_market_comments`
--
ALTER TABLE `ai_market_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ai_market_sources`
--
ALTER TABLE `ai_market_sources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ai_product_improvement_resolutions`
--
ALTER TABLE `ai_product_improvement_resolutions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de tabela `asaas_webhook_events`
--
ALTER TABLE `asaas_webhook_events`
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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de tabela `atendimento_interacoes`
--
ALTER TABLE `atendimento_interacoes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT de tabela `auditoria_detalhada`
--
ALTER TABLE `auditoria_detalhada`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=388;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- AUTO_INCREMENT de tabela `file_retention_events`
--
ALTER TABLE `file_retention_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `file_retention_policies`
--
ALTER TABLE `file_retention_policies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT de tabela `item_controle_alertas`
--
ALTER TABLE `item_controle_alertas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `item_controle_anexos`
--
ALTER TABLE `item_controle_anexos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT de tabela `portal_solicitacoes`
--
ALTER TABLE `portal_solicitacoes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=302;

--
-- AUTO_INCREMENT de tabela `prazzu_permission_audits`
--
ALTER TABLE `prazzu_permission_audits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `prazzu_permission_rules`
--
ALTER TABLE `prazzu_permission_rules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `prazzu_roles`
--
ALTER TABLE `prazzu_roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- AUTO_INCREMENT de tabela `prazzu_user_permissions`
--
ALTER TABLE `prazzu_user_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `prazzu_user_roles`
--
ALTER TABLE `prazzu_user_roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT de tabela `sla_rules`
--
ALTER TABLE `sla_rules`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `sugestoes_melhorias`
--
ALTER TABLE `sugestoes_melhorias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- AUTO_INCREMENT de tabela `user_sidebar_favorites`
--
ALTER TABLE `user_sidebar_favorites`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- Limitadores para a tabela `ai_product_improvement_resolutions`
--
ALTER TABLE `ai_product_improvement_resolutions`
  ADD CONSTRAINT `ai_product_improvement_resolutions_user_fk` FOREIGN KEY (`resolved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
-- Limitadores para a tabela `prazzu_permissions`
--
ALTER TABLE `prazzu_permissions`
  ADD CONSTRAINT `prazzu_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `prazzu_roles` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `prazzu_permission_audits`
--
ALTER TABLE `prazzu_permission_audits`
  ADD CONSTRAINT `prazzu_permission_audits_actor_user_id_foreign` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `prazzu_permission_audits_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `prazzu_roles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `prazzu_permission_audits_target_user_id_foreign` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `prazzu_user_permissions`
--
ALTER TABLE `prazzu_user_permissions`
  ADD CONSTRAINT `prazzu_user_permissions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `prazzu_user_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `prazzu_user_roles`
--
ALTER TABLE `prazzu_user_roles`
  ADD CONSTRAINT `prazzu_user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `prazzu_roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prazzu_user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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

--
-- Limitadores para a tabela `user_sidebar_favorites`
--
ALTER TABLE `user_sidebar_favorites`
  ADD CONSTRAINT `user_sidebar_favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
