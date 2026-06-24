<?php

namespace App\Observers;

use App\Services\AuditoriaDetalhadaService;
use Illuminate\Database\Eloquent\Model;

class AuditoriaGlobalObserver
{
    /**
     * Guarda os valores originais antes do update para comparação confiável.
     *
     * @var array<int, array<string, mixed>>
     */
    protected static array $originais = [];

    public function created(Model $model): void
    {
        AuditoriaDetalhadaService::registrar($model, 'created', [], $model->getAttributes());
    }

    public function updating(Model $model): void
    {
        self::$originais[spl_object_id($model)] = $model->getOriginal();
    }

    public function updated(Model $model): void
    {
        $objectId = spl_object_id($model);
        $antigos = self::$originais[$objectId] ?? $model->getOriginal();
        unset(self::$originais[$objectId]);

        AuditoriaDetalhadaService::registrar($model, 'updated', $antigos, $model->getAttributes());
    }

    public function deleted(Model $model): void
    {
        AuditoriaDetalhadaService::registrar($model, 'deleted', $model->getOriginal(), []);
    }

    public function restored(Model $model): void
    {
        AuditoriaDetalhadaService::registrar($model, 'restored', [], $model->getAttributes());
    }
}
