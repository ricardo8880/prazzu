<?php

namespace App\Traits;

use App\Services\AuditoriaDetalhadaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait Loggable
{
    protected static array $loggableOldAttributes = [];

    protected static function bootLoggable(): void
    {
        static::updating(function (Model $model): void {
            self::$loggableOldAttributes[spl_object_id($model)] = $model->getOriginal();
        });

        static::updated(function (Model $model): void {
            $oldAttributes = self::$loggableOldAttributes[spl_object_id($model)] ?? [];
            unset(self::$loggableOldAttributes[spl_object_id($model)]);

            AuditoriaDetalhadaService::registrar($model, 'updated', $oldAttributes, $model->getAttributes());

            if (method_exists($model, 'activities')) {
                return;
            }

            if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                activity()->performedOn($model)->causedBy(Auth::user())->withProperties([
                    'old' => $oldAttributes,
                    'attributes' => $model->getAttributes(),
                ])->event('updated')->log('Registro atualizado');
            }
        });

        static::created(function (Model $model): void {
            AuditoriaDetalhadaService::registrar($model, 'created', [], $model->getAttributes());

            if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                activity()->performedOn($model)->causedBy(Auth::user())->withProperties([
                    'attributes' => $model->getAttributes(),
                ])->event('created')->log('Registro criado');
            }
        });

        static::deleted(function (Model $model): void {
            AuditoriaDetalhadaService::registrar($model, 'deleted', $model->getOriginal(), []);

            if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                activity()->performedOn($model)->causedBy(Auth::user())->withProperties([
                    'old' => $model->getOriginal(),
                ])->event('deleted')->log('Registro excluido');
            }
        });
    }
}
