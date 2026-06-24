<?php

namespace App\Providers;

use App\Observers\AuditoriaGlobalObserver;
use App\Support\AuditoriaModelInspector;
use Illuminate\Support\ServiceProvider;

class AuditoriaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! (bool) config('auditoria.global_enabled', true)) {
            return;
        }

        foreach (AuditoriaModelInspector::modelClasses() as $modelClass) {
            if (! AuditoriaModelInspector::shouldAuditGlobally($modelClass)) {
                continue;
            }

            $modelClass::observe(AuditoriaGlobalObserver::class);
        }
    }
}
