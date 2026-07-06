<?php

namespace App\Services;

use App\Models\ItemControle;
use Illuminate\Support\Carbon;

class OperationalWorkflowService
{
    /**
     * Etapas oficiais do fluxo operacional do Prazzu.
     *
     * Cliente -> Empresa -> ItemControle -> Mesa -> Pendências -> Executor -> Gestor -> Conclusão -> Auditoria -> Relatório.
     */
    public const STAGES = [
        'entrada' => 'Entrada / Triagem',
        'mesa' => 'Mesa Operacional',
        'pendencia' => 'Pendência em Execução',
        'cliente' => 'Aguardando Cliente',
        'aprovacao' => 'Gestor / Aprovação',
        'conclusao' => 'Conclusão',
        'auditoria' => 'Auditoria / Relatório',
    ];

    /** @return array{key:string,label:string,order:int,tone:string,next_action:string,owner_hint:string} */
    public function stageForItem(ItemControle $item, bool $blocked = false, ?string $tone = null): array
    {
        $status = (string) $item->status;
        $tone ??= 'info';

        if ($blocked) {
            return $this->stage('pendencia', 15, 'danger', 'Remover bloqueio ou dependência antes de executar.', 'Executor / gestor da fila');
        }

        if (blank($item->responsavel_id)) {
            return $this->stage('entrada', 10, 'danger', 'Definir responsável para a demanda não ficar sem dono.', 'Gestor operacional');
        }

        if (in_array($status, ['aguardando_cliente', 'cliente_pendente', 'aguardando_documento', 'pendente_cliente'], true)) {
            return $this->stage('cliente', 30, 'warning', 'Cobrar retorno do cliente pelo portal ou canal registrado.', 'Responsável pelo cliente');
        }

        if (in_array($status, ['aguardando_aprovacao', 'em_aprovacao', 'reprovado'], true)) {
            return $this->stage('aprovacao', 40, 'warning', 'Aprovar, reprovar ou devolver com orientação clara.', 'Gestor / aprovador');
        }

        if (in_array($status, ['concluido', 'aprovado', 'assinado'], true)) {
            return $this->stage('auditoria', 80, 'success', 'Conferir auditoria, relatório e eventual faturamento.', 'Gestor / financeiro');
        }

        if ($status === 'cancelado') {
            return $this->stage('conclusao', 90, 'gray', 'Validar se o cancelamento está justificado no histórico.', 'Gestor operacional');
        }

        if ($tone === 'danger' || $item->data_vencimento?->isPast()) {
            return $this->stage('pendencia', 20, 'danger', 'Executar ou reprogramar com justificativa registrada.', 'Executor responsável');
        }

        if ($item->data_vencimento?->isToday()) {
            return $this->stage('mesa', 25, 'warning', 'Priorizar na Mesa Operacional ainda hoje.', 'Executor responsável');
        }

        return $this->stage('mesa', 60, 'info', 'Acompanhar pela Mesa e avançar quando chegar sua vez.', 'Responsável definido');
    }

    /** @param array<string,mixed> $item @return array<string,mixed> */
    public function enrichPayload(array $item): array
    {
        $status = (string) ($item['status_key'] ?? $item['status'] ?? '');
        $blocked = (bool) ($item['blocked'] ?? $item['bloqueado_operacional'] ?? false);
        $tone = (string) ($item['tone'] ?? $item['prioridade_operacional_tone'] ?? 'info');
        $responsavel = trim((string) ($item['responsavel'] ?? ''));
        $semResponsavel = $responsavel === '' || $responsavel === 'Sem responsável' || (bool) ($item['sem_responsavel'] ?? false);
        $due = $this->parseDate($item['due'] ?? $item['vencimento'] ?? null);

        if ($blocked) {
            $stage = $this->stage('pendencia', 15, 'danger', 'Remover bloqueio ou dependência antes de executar.', 'Executor / gestor da fila');
        } elseif ($semResponsavel) {
            $stage = $this->stage('entrada', 10, 'danger', 'Definir responsável antes de seguir.', 'Gestor operacional');
        } elseif (in_array($status, ['aguardando_cliente', 'cliente_pendente', 'aguardando_documento', 'pendente_cliente'], true)) {
            $stage = $this->stage('cliente', 30, 'warning', 'Cobrar retorno do cliente.', 'Responsável pelo cliente');
        } elseif (in_array($status, ['aguardando_aprovacao', 'em_aprovacao', 'reprovado'], true)) {
            $stage = $this->stage('aprovacao', 40, 'warning', 'Registrar decisão do gestor.', 'Gestor / aprovador');
        } elseif (in_array($status, ['concluido', 'aprovado', 'assinado'], true)) {
            $stage = $this->stage('auditoria', 80, 'success', 'Conferir auditoria e relatório.', 'Gestor / financeiro');
        } elseif ($tone === 'danger' || $due?->isPast()) {
            $stage = $this->stage('pendencia', 20, 'danger', 'Executar ou justificar reprogramação.', 'Executor responsável');
        } elseif ($due?->isToday()) {
            $stage = $this->stage('mesa', 25, 'warning', 'Priorizar hoje na Mesa Operacional.', 'Executor responsável');
        } else {
            $stage = $this->stage('mesa', 60, 'info', 'Acompanhar na fila operacional.', 'Responsável definido');
        }

        $item['workflow_stage_key'] = $stage['key'];
        $item['workflow_stage_label'] = $stage['label'];
        $item['workflow_stage_order'] = $stage['order'];
        $item['workflow_stage_tone'] = $stage['tone'];
        $item['workflow_next_action'] = $stage['next_action'];
        $item['workflow_owner_hint'] = $stage['owner_hint'];

        return $item;
    }

    /** @return array{key:string,label:string,order:int,tone:string,next_action:string,owner_hint:string} */
    protected function stage(string $key, int $order, string $tone, string $nextAction, string $ownerHint): array
    {
        return [
            'key' => $key,
            'label' => self::STAGES[$key] ?? 'Fluxo Operacional',
            'order' => $order,
            'tone' => $tone,
            'next_action' => $nextAction,
            'owner_hint' => $ownerHint,
        ];
    }

    protected function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '' || $value === 'Sem prazo') {
            return null;
        }

        foreach (['d/m/Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->startOfDay();
            } catch (\Throwable) {
                // tenta o próximo formato
            }
        }

        return null;
    }
}
