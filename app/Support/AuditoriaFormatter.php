<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Throwable;

class AuditoriaFormatter
{
    private static array $recordLabelCache = [];

    private const EVENT_LABELS = [
        'created' => 'Criado',
        'updated' => 'Alterado',
        'deleted' => 'Excluído',
        'restored' => 'Restaurado',
        'force_deleted' => 'Excluído definitivamente',
        'login' => 'Login',
        'logout' => 'Logout',
        'approved' => 'Aprovado',
        'reproved' => 'Reprovado',
        'rejected' => 'Rejeitado',
        'cancelled' => 'Cancelado',
        'canceled' => 'Cancelado',
    ];

    private const MODULE_LABELS = [
        'ItemControle' => 'Item de controle',
        'AuditoriaDetalhada' => 'Auditoria detalhada',
        'Empresa' => 'Empresa',
        'User' => 'Usuário',
        'Configuracao' => 'Configuração',
        'Plano' => 'Plano',
        'Assinatura' => 'Assinatura',
        'Atendimento' => 'Atendimento',
        'AtendimentoInteracao' => 'Interação do atendimento',
        'CategoriaItemControle' => 'Categoria do item',
        'ChecklistTemplate' => 'Modelo de checklist',
        'ChecklistTemplateItem' => 'Item do modelo de checklist',
        'CrmCliente' => 'Cliente CRM',
        'CrmHistorico' => 'Histórico CRM',
        'CrmPendencia' => 'Pendência CRM',
        'DashboardWidgetConfiguracao' => 'Widget do dashboard',
        'FluxoOperacional' => 'Fluxo operacional',
        'FluxoOperacionalEtapa' => 'Etapa do fluxo operacional',
        'ItemControleAlerta' => 'Alerta do item',
        'ItemControleAprovacao' => 'Aprovação do item',
        'ItemControleAssinatura' => 'Assinatura do item',
        'PortalDocumento' => 'Documento do portal',
        'PortalMensagem' => 'Mensagem do portal',
        'PortalSolicitacao' => 'Solicitação do portal',
    ];

    private const FIELD_LABELS = [
        'id' => 'ID',
        'empresa_id' => 'Empresa',
        'user_id' => 'Usuário',
        'auditable_type' => 'Módulo',
        'auditable_id' => 'Registro',
        'evento' => 'Evento',
        'nivel' => 'Nível',
        'campo' => 'Campo',
        'valor_anterior' => 'Valor anterior',
        'valor_novo' => 'Valor novo',
        'ip' => 'IP',
        'user_agent' => 'Navegador/dispositivo',
        'created_at' => 'Criado em',
        'updated_at' => 'Atualizado em',
        'deleted_at' => 'Excluído em',
        'titulo' => 'Título',
        'descricao' => 'Descrição',
        'tipo' => 'Tipo',
        'categoria_id' => 'Categoria',
        'status' => 'Status',
        'status_operacional_at' => 'Status operacional em',
        'view_type' => 'Visualização',
        'automation_status' => 'Status da automação',
        'prioridade' => 'Prioridade',
        'urgencia' => 'Urgência',
        'risco_score' => 'Score de risco',
        'risk_score' => 'Score de risco',
        'risk_probability' => 'Probabilidade do risco',
        'risk_impact' => 'Impacto do risco',
        'bloqueado_por_dependencia' => 'Bloqueado por dependência',
        'bloqueado' => 'Bloqueado',
        'arquivo' => 'Arquivo',
        'data_vencimento' => 'Data de vencimento',
        'data_conclusao' => 'Data de conclusão',
        'notificado_3_dias' => 'Aviso de 3 dias enviado',
        'notificado_no_dia' => 'Aviso do dia enviado',
        'notificado_vencido' => 'Aviso de vencido enviado',
        'observacao' => 'Observação',
        'portal_ativo' => 'Portal ativo',
        'portal_token' => 'Token do portal',
        'portal_cliente_nome' => 'Cliente do portal',
        'portal_cliente_email' => 'E-mail do cliente do portal',
        'portal_expira_em' => 'Portal expira em',
        'portal_status' => 'Status do portal',
        'ultima_interacao_cliente_em' => 'Última interação do cliente',
        'sla_horas' => 'SLA em horas',
        'sla_inicio_em' => 'Início do SLA',
        'sla_limite_em' => 'Limite do SLA',
        'sla_prazo_alvo_em' => 'Prazo alvo do SLA',
        'sla_concluido_em' => 'SLA concluído em',
        'sla_status' => 'Status do SLA',
        'contrato_numero' => 'Número do contrato',
        'contrato_parte_nome' => 'Parte do contrato',
        'contrato_parte_documento' => 'Documento da parte',
        'contrato_valor' => 'Valor do contrato',
        'valor_tarefa' => 'Valor da tarefa',
        'faturado_em' => 'Faturado em',
        'pago_em' => 'Pago em',
        'contrato_inicio_em' => 'Início do contrato',
        'contrato_fim_em' => 'Fim do contrato',
        'contrato_status' => 'Status do contrato',
        'responsavel_id' => 'Responsável',
        'ultimo_alerta_enviado_em' => 'Último alerta enviado em',
        'ultimo_lembrete_enviado_em' => 'Último lembrete enviado em',
        'qtd_lembretes_enviados' => 'Quantidade de lembretes enviados',
        'ultima_falha_notificacao_em' => 'Última falha de notificação',
        'ultima_falha_notificacao_msg' => 'Mensagem da última falha',
        'fluxo_operacional_id' => 'Fluxo operacional',
        'kanban_order' => 'Ordem no kanban',
        'blocked_by_dependency' => 'Bloqueado por dependência',
        'estimated_minutes' => 'Tempo estimado',
        'actual_minutes' => 'Tempo realizado',
        'custom_payload' => 'Campos personalizados',
        'template_id' => 'Modelo',
        'approval_required' => 'Aprovação obrigatória',
        'approval_status' => 'Status da aprovação',
        'document_status' => 'Status do documento',
        'signature_status' => 'Status da assinatura',
        'razao_social' => 'Razão social',
        'nome_fantasia' => 'Nome fantasia',
        'cnpj' => 'CNPJ',
        'email' => 'E-mail',
        'telefone' => 'Telefone',
        'responsavel_nome' => 'Responsável',
        'ativo' => 'Ativo',
        'plano' => 'Plano',
        'limite_usuarios' => 'Limite de usuários',
        'limite_itens' => 'Limite de itens',
        'limite_interacoes_ia' => 'Limite de interações IA',
        'crm_status_contrato' => 'Status do contrato CRM',
        'crm_contato_nome' => 'Contato CRM',
        'crm_contato_email' => 'E-mail do contato CRM',
        'crm_contato_whatsapp' => 'WhatsApp do contato CRM',
        'crm_health_manual' => 'Saúde CRM manual',
        'crm_observacoes' => 'Observações CRM',
        'crm_ultima_reuniao_em' => 'Última reunião CRM',
    ];

    private const VALUE_LABELS = [
        'em_andamento' => 'Em andamento',
        'em_aprovacao' => 'Em aprovação',
        'aguardando_aprovacao' => 'Aguardando aprovação',
        'pendente' => 'Pendente',
        'pendentes' => 'Pendentes',
        'concluido' => 'Concluído',
        'concluida' => 'Concluída',
        'cancelado' => 'Cancelado',
        'cancelada' => 'Cancelada',
        'aberto' => 'Aberto',
        'aberta' => 'Aberta',
        'fechado' => 'Fechado',
        'fechada' => 'Fechada',
        'aprovado' => 'Aprovado',
        'aprovada' => 'Aprovada',
        'reprovado' => 'Reprovado',
        'reprovada' => 'Reprovada',
        'rascunho' => 'Rascunho',
        'publicado' => 'Publicado',
        'publicada' => 'Publicada',
        'ativo' => 'Ativo',
        'ativa' => 'Ativa',
        'inativo' => 'Inativo',
        'inativa' => 'Inativa',
        'bloqueado' => 'Bloqueado',
        'bloqueada' => 'Bloqueada',
        'nao_iniciado' => 'Não iniciado',
        'nao_enviado' => 'Não enviado',
        'enviado' => 'Enviado',
        'enviada' => 'Enviada',
        'assinado' => 'Assinado',
        'assinada' => 'Assinada',
        'vencido' => 'Vencido',
        'vencida' => 'Vencida',
        'atrasado' => 'Atrasado',
        'atrasada' => 'Atrasada',
        'no_prazo' => 'No prazo',
        'urgente' => 'Urgente',
        'alta' => 'Alta',
        'media' => 'Média',
        'média' => 'Média',
        'baixa' => 'Baixa',
        'critica' => 'Crítica',
        'crítico' => 'Crítico',
        'critico' => 'Crítico',
        'sim' => 'Sim',
        'nao' => 'Não',
        'não' => 'Não',
        'true' => 'Sim',
        'false' => 'Não',
        '1' => 'Sim',
        '0' => 'Não',
    ];

    private const BOOLEAN_FIELDS = [
        'ativo', 'portal_ativo', 'bloqueado', 'bloqueado_por_dependencia', 'blocked_by_dependency',
        'notificado_3_dias', 'notificado_no_dia', 'notificado_vencido', 'approval_required',
        'enviar_email', 'enviar_sistema', 'exigir_2fa', 'registrar_login', 'visivel_cliente',
    ];

    public static function evento(?string $evento): string
    {
        $key = trim((string) $evento);

        return self::EVENT_LABELS[$key] ?? self::humanize($key !== '' ? $key : 'evento');
    }

    public static function modulo(?string $auditableType): string
    {
        $basename = class_basename((string) ($auditableType ?: 'Registro'));

        return self::MODULE_LABELS[$basename] ?? self::humanize($basename ?: 'Registro');
    }

    public static function campo(?string $campo): string
    {
        $key = trim((string) $campo);

        return self::FIELD_LABELS[$key] ?? self::humanize($key !== '' ? $key : 'registro');
    }

    public static function valor(mixed $valor, ?string $campo = null, int $limit = 0): string
    {
        if ($valor === null) {
            return '-';
        }

        $text = trim((string) $valor);

        if ($text === '') {
            return '-';
        }

        $decoded = json_decode($text, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $formatted = collect(self::flatten($decoded))
                ->map(fn ($item, $key): string => self::campo((string) $key) . ': ' . self::valor($item, (string) $key))
                ->implode('; ');

            return self::limit($formatted ?: $text, $limit);
        }

        $normalized = preg_replace('/\s+/', ' ', $text) ?: $text;
        $fieldKey = trim((string) $campo);
        $valueKey = mb_strtolower($normalized);

        if (in_array($fieldKey, self::BOOLEAN_FIELDS, true)) {
            return self::limit(self::VALUE_LABELS[$valueKey] ?? $normalized, $limit);
        }

        if (isset(self::VALUE_LABELS[$valueKey])) {
            return self::limit(self::VALUE_LABELS[$valueKey], $limit);
        }

        if (Str::contains($normalized, '_') && ! Str::contains($normalized, ['/', '\\', '@'])) {
            return self::limit(self::humanize($normalized), $limit);
        }

        return self::limit($normalized, $limit);
    }

    public static function registro(?string $auditableType, mixed $auditableId): string
    {
        $id = trim((string) $auditableId);
        $module = self::modulo($auditableType);

        if ($id === '') {
            return $module;
        }

        $title = self::recordTitle($auditableType, $id);

        return $title !== null && $title !== ''
            ? $module . ': ' . $title
            : $module . ' #' . $id;
    }

    public static function registroCurto(?string $auditableType, mixed $auditableId): string
    {
        return self::limit(self::registro($auditableType, $auditableId), 90);
    }

    public static function diffRows($oldValue, $newValue, ?string $campo = null): array
    {
        $oldDecoded = self::decodePayload($oldValue);
        $newDecoded = self::decodePayload($newValue);

        if (is_array($oldDecoded) || is_array($newDecoded)) {
            $oldFlat = is_array($oldDecoded) ? self::flatten($oldDecoded) : ['valor' => self::valor($oldValue, $campo)];
            $newFlat = is_array($newDecoded) ? self::flatten($newDecoded) : ['valor' => self::valor($newValue, $campo)];
            $keys = collect(array_merge(array_keys($oldFlat), array_keys($newFlat)))->unique()->sort()->values();

            return $keys->map(function ($key) use ($oldFlat, $newFlat): array {
                $oldExists = array_key_exists($key, $oldFlat);
                $newExists = array_key_exists($key, $newFlat);
                $old = $oldExists ? self::valor($oldFlat[$key], (string) $key) : '-';
                $new = $newExists ? self::valor($newFlat[$key], (string) $key) : '-';

                return [
                    'field' => self::campo((string) $key),
                    'old' => $old,
                    'new' => $new,
                    'status' => ! $oldExists ? 'added' : (! $newExists ? 'removed' : ($old === $new ? 'unchanged' : 'changed')),
                ];
            })->values()->all();
        }

        $old = self::valor($oldValue, $campo);
        $new = self::valor($newValue, $campo);

        return [[
            'field' => self::campo($campo ?: 'valor'),
            'old' => $old,
            'new' => $new,
            'status' => $old === $new ? 'unchanged' : ($old === '-' ? 'added' : ($new === '-' ? 'removed' : 'changed')),
        ]];
    }

    public static function isSuspeito($registro): bool
    {
        $campo = mb_strtolower((string) ($registro->campo ?? ''));
        $evento = (string) ($registro->evento ?? '');

        return $evento === 'deleted'
            || str_contains($campo, 'password')
            || str_contains($campo, 'senha')
            || str_contains($campo, 'role')
            || str_contains($campo, 'permiss')
            || str_contains($campo, 'status');
    }

    private static function recordTitle(?string $auditableType, string $id): ?string
    {
        $class = trim((string) $auditableType);
        $cacheKey = $class . ':' . $id;

        if (array_key_exists($cacheKey, self::$recordLabelCache)) {
            return self::$recordLabelCache[$cacheKey];
        }

        if ($class === '' || ! class_exists($class) || ! is_subclass_of($class, Model::class)) {
            return self::$recordLabelCache[$cacheKey] = null;
        }

        try {
            /** @var Model|null $record */
            $record = $class::query()->find($id);

            if (! $record) {
                return self::$recordLabelCache[$cacheKey] = null;
            }

            foreach (['titulo', 'nome', 'name', 'razao_social', 'nome_fantasia', 'email', 'descricao', 'documento'] as $attribute) {
                $value = trim((string) ($record->{$attribute} ?? ''));

                if ($value !== '') {
                    return self::$recordLabelCache[$cacheKey] = self::limit($value, 80);
                }
            }
        } catch (Throwable) {
            return self::$recordLabelCache[$cacheKey] = null;
        }

        return self::$recordLabelCache[$cacheKey] = null;
    }

    private static function humanize(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '-';
        }

        $value = preg_replace('/(?<!^)[A-Z]/', ' $0', $value) ?: $value;
        $value = str_replace(['_', '-', '.'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?: $value;

        return ucfirst(mb_strtolower(trim($value)));
    }

    private static function limit(string $value, int $limit = 0): string
    {
        $value = trim($value);

        if ($value === '') {
            return '-';
        }

        return $limit > 0 ? Str::limit($value, $limit) : $value;
    }

    private static function decodePayload($value)
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        $decoded = json_decode($text, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    private static function flatten(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $fullKey = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $result += self::flatten($value, $fullKey);
                continue;
            }

            $result[$fullKey] = $value;
        }

        return $result;
    }
}
