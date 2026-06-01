<?php

namespace App\Providers;

use App\Models\Empresa;
use App\Models\ItemControle;
use App\Models\User;
use App\Policies\EmpresaPolicy;
use App\Policies\ItemControlePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        ItemControle::class => ItemControlePolicy::class,
        User::class => UserPolicy::class,
        Empresa::class => EmpresaPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
