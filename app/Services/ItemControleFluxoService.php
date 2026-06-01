<?php

namespace App\Services;

use App\Models\ItemControle;
use App\Models\ItemControleAnexo;
use App\Models\ItemControleChecklist;
use App\Models\ItemControleComentario;
use App\Models\User;
use App\Support\ItemControleStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ItemControleFluxoService
{
    public function atualizarStatus(ItemControle $item, string $status, ?User $user = null): ItemControle
    {
        $this->garantirPodeModificar($item, $user);

        if (! in_array($status, ItemControleStatus::KANBAN_STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'Status inválido para o fluxo operacional.']);
        }

        $statusAnterior = (string) $item->status;
        $agora = now();

        $item->update([
            'status' => $status,
            'data_conclusao' => $status === ItemControleStatus::CONCLUIDO ? $agora->toDateString() : null,
            'sla_concluido_em' => $status === ItemControleStatus::CONCLUIDO ? $agora : null,
            'sla_status' => $status === ItemControleStatus::CONCLUIDO ? ItemControleStatus::CONCLUIDO : $item->sla_status,
        ]);

        $item->registrarTimeline(
            'atualizacao',
            $status === ItemControleStatus::CONCLUIDO ? 'Item concluído' : 'Status atualizado',
            'Status alterado de "' . ItemControleStatus::label($statusAnterior) . '" para "' . ItemControleStatus::label($status) . '".',
            ['status_anterior' => $statusAnterior, 'status_novo' => $status],
            $user
        );

        return $item->refresh();
    }

    public function adicionarComentario(ItemControle $item, string $comentario, ?User $user = null): ItemControleComentario
    {
        $this->garantirPodeModificar($item, $user);
        $comentario = trim($comentario);

        if ($comentario === '') {
            throw ValidationException::withMessages(['comentario' => 'Informe um comentário antes de salvar.']);
        }

        $registro = ItemControleComentario::query()->create([
            'item_controle_id' => $item->id,
            'user_id' => $user?->id,
            'comentario' => $comentario,
        ]);

        $item->registrarTimeline('comentario', 'Comentário adicionado', $comentario, ['comentario_id' => $registro->id], $user);

        return $registro;
    }

    public function adicionarChecklist(ItemControle $item, string $titulo, ?User $user = null): ItemControleChecklist
    {
        $this->garantirPodeModificar($item, $user);
        $titulo = trim($titulo);

        if ($titulo === '') {
            throw ValidationException::withMessages(['checklist' => 'Informe o título da etapa antes de adicionar.']);
        }

        $checklist = ItemControleChecklist::query()->create([
            'item_controle_id' => $item->id,
            'titulo' => $titulo,
            'concluido' => false,
            'ordem' => ((int) $item->checklists()->max('ordem')) + 1,
        ]);

        $item->registrarTimeline('checklist', 'Etapa adicionada ao checklist', $titulo, ['checklist_id' => $checklist->id], $user);

        return $checklist;
    }

    public function alternarChecklist(ItemControleChecklist $checklist, ?User $user = null): ItemControleChecklist
    {
        $item = $checklist->itemControle;
        $this->garantirPodeModificar($item, $user);

        if ($checklist->concluido) {
            $checklist->marcarComoPendente();
            $tituloTimeline = 'Etapa reaberta';
        } else {
            $checklist->marcarComoConcluido($user);
            $tituloTimeline = 'Etapa concluída';
        }

        $item->registrarTimeline('checklist', $tituloTimeline, $checklist->titulo, ['checklist_id' => $checklist->id], $user);

        return $checklist->refresh();
    }

    public function adicionarAnexo(ItemControle $item, string $arquivoPath, ?string $observacao = null, ?User $user = null): ItemControleAnexo
    {
        $this->garantirPodeModificar($item, $user);

        $nomeOriginal = basename($arquivoPath);
        $mimeType = null;
        $tamanho = null;

        try {
            if (Storage::disk('public')->exists($arquivoPath)) {
                $mimeType = Storage::disk('public')->mimeType($arquivoPath);
                $tamanho = Storage::disk('public')->size($arquivoPath);
            }
        } catch (\Throwable) {
            $mimeType = null;
            $tamanho = null;
        }

        $anexo = ItemControleAnexo::query()->create([
            'item_controle_id' => $item->id,
            'user_id' => $user?->id,
            'arquivo' => $arquivoPath,
            'nome_original' => $nomeOriginal,
            'mime_type' => $mimeType,
            'tamanho_bytes' => $tamanho,
            'observacao' => $observacao,
        ]);

        $item->registrarTimeline('anexo', 'Anexo adicionado', $nomeOriginal, ['anexo_id' => $anexo->id, 'observacao' => $observacao], $user);

        return $anexo;
    }

    private function garantirPodeModificar(?ItemControle $item, ?User $user): void
    {
        if (! $item || ! $item->exists || ! $item->canBeModifiedBy($user)) {
            abort(403, 'Você não tem permissão para alterar este item.');
        }
    }

}
