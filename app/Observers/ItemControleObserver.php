<?php

namespace App\Observers;


use App\Support\CachedSchema;
use App\Models\ItemControle;
use App\Models\Responsavel;
use App\Models\User;
use App\Services\ItemControleCoreService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ItemControleObserver
{
    public function saving(ItemControle $item): void
    {
        app(ItemControleCoreService::class)->normalizeBeforeSave($item);

        $statusMudou = $item->isDirty('status');
        $status = ItemControleCoreService::normalizeStatus($item->status);

        if ($status === 'pronto') {
            $gerente = $this->resolverGerenteResponsavel($item);

            if ($gerente) {
                $item->responsavel_id = $gerente->id;
            }

            $item->status = 'em_revisao';
            $status = 'em_revisao';
        }

        if ($statusMudou && CachedSchema::hasColumn('item_controles', 'status_operacional_at')) {
            $item->status_operacional_at = now();
        }

    }

    public function saved(ItemControle $item): void
    {
        Cache::forget('dashboard.global');

        if ($item->wasChanged('status')) {
            $item->registrarTimeline(
                'status_operacional',
                'Status operacional atualizado',
                'Novo status: ' . $item->getStatusExibicao() . '.',
            );
        }

        if ($item->wasChanged('status') && $item->status === 'em_revisao') {
            $item->gerarNotificacaoInterna(
                'Item entrou em revisão',
                'O item "' . $item->titulo . '" foi enviado automaticamente para revisão.',
                $item->responsavel?->user_id,
                'automacao'
            );
        }
    }

    public function deleted(ItemControle $item): void
    {
        Cache::forget('dashboard.global');
    }

    protected function resolverGerenteResponsavel(ItemControle $item): ?Responsavel
    {
        $empresaId = $item->empresa_id;

        if (! $empresaId) {
            return null;
        }

        $gestorUserIds = User::query()
            ->where('empresa_id', $empresaId)
            ->whereIn('role', ['gestor', 'admin_empresa', 'admin'])
            ->pluck('id')
            ->filter()
            ->values();

        if ($gestorUserIds->isNotEmpty()) {
            $responsavel = Responsavel::query()
                ->where('empresa_id', $empresaId)
                ->whereIn('user_id', $gestorUserIds)
                ->orderByRaw("CASE WHEN user_id = ? THEN 0 ELSE 1 END", [$item->responsavel?->gestor_user_id ?: 0])
                ->orderBy('nome')
                ->first();

            if ($responsavel) {
                return $responsavel;
            }
        }

        if ($item->responsavel?->gestor_user_id) {
            return Responsavel::query()
                ->where('empresa_id', $empresaId)
                ->where('user_id', $item->responsavel->gestor_user_id)
                ->first();
        }

        return null;
    }
}
