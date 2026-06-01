<?php

namespace App\Support;

use App\Models\ItemControle;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ItemControleQuery
{
    public static function scoped(?User $user): Builder
    {
        $query = ItemControle::query()->with(['empresa', 'responsavel']);

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isGestor()) {
            return $query->whereHas('responsavel', function (Builder $builder) use ($user) {
                $builder->where('gestor_user_id', $user->id);
            });
        }

        return $query->whereHas('responsavel', function (Builder $builder) use ($user) {
            $builder->where('user_id', $user->id);
        });
    }

    public static function applyFilters(Builder $query, array $filters = []): Builder
    {
        return $query
            ->when(filled($filters['tipo'] ?? null), fn (Builder $builder) => $builder->where('tipo', $filters['tipo']))
            ->when(filled($filters['status'] ?? null), function (Builder $builder) use ($filters) {
                if (($filters['status'] ?? null) === 'vencido') {
                    return $builder
                        ->whereDate('data_vencimento', '<', now()->toDateString())
                        ->whereNotIn('status', ['concluido', 'cancelado']);
                }

                return $builder->where('status', $filters['status']);
            })
            ->when(filled($filters['empresa_id'] ?? null), fn (Builder $builder) => $builder->where('empresa_id', $filters['empresa_id']))
            ->when(filled($filters['responsavel_id'] ?? null), fn (Builder $builder) => $builder->where('responsavel_id', $filters['responsavel_id']))
            ->when(filled($filters['data_inicial'] ?? null), fn (Builder $builder) => $builder->whereDate('data_vencimento', '>=', $filters['data_inicial']))
            ->when(filled($filters['data_final'] ?? null), fn (Builder $builder) => $builder->whereDate('data_vencimento', '<=', $filters['data_final']))
            ->when(($filters['somente_abertos'] ?? false) === true, fn (Builder $builder) => $builder->whereNotIn('status', ['concluido', 'cancelado']));
    }
}