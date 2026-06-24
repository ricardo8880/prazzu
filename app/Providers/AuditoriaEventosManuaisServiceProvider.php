<?php

namespace App\Providers;

use App\Services\AuditoriaManualService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AuditoriaEventosManuaisServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            AuditoriaManualService::registrarEvento('login.success', [
                'guard' => $event->guard,
                'remember' => $event->remember,
                'email' => $event->user?->email ?? null,
            ], $event->user, userId: $event->user?->id, nivel: 'info');
        });

        Event::listen(Failed::class, function (Failed $event): void {
            AuditoriaManualService::registrarEvento('login.failed', [
                'guard' => $event->guard,
                'email' => $event->credentials['email'] ?? null,
                'has_user' => (bool) $event->user,
            ], $event->user, userId: $event->user?->id, nivel: 'warning');
        });

        Event::listen(Logout::class, function (Logout $event): void {
            AuditoriaManualService::registrarEvento('logout', [
                'guard' => $event->guard,
                'email' => $event->user?->email ?? null,
            ], $event->user, userId: $event->user?->id, nivel: 'info');
        });

        Event::listen(PasswordReset::class, function (PasswordReset $event): void {
            AuditoriaManualService::registrarEvento('password.reset', [
                'email' => $event->user?->email ?? null,
                'origem' => 'laravel_event',
            ], $event->user, userId: $event->user?->id, nivel: 'warning');
        });
    }
}
