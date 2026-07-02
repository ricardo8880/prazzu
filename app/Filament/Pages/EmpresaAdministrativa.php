<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesAdvancedPermissions;
use App\Filament\Resources\Empresas\EmpresaResource;
use App\Models\Empresa;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class EmpresaAdministrativa extends Page
{
    use UsesAdvancedPermissions;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string | UnitEnum | null $navigationGroup = 'Administração';

    protected static ?string $navigationLabel = 'Empresa';

    protected static ?string $title = 'Empresa';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.empresa-administrativa';

    public ?int $empresaId = null;
    public ?string $razao_social = null;
    public ?string $nome_fantasia = null;
    public ?string $cnpj = null;
    public ?string $email = null;
    public ?string $telefone = null;
    public ?string $responsavel_nome = null;
    public ?string $status = null;
    public ?string $plano = null;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->isAdminEmpresa()
            || static::canAdvancedPermission('configuracoes.view')
            || static::canAdvancedPermission('governanca.view');
    }

    public function mount(): void
    {
        $this->empresaId = $this->empresaAtual()?->id;
        $this->carregarEmpresa();
    }

    public function updatedEmpresaId(): void
    {
        $this->carregarEmpresa();
    }

    public function salvarEmpresa(): void
    {
        if (! $this->podeEditar()) {
            Notification::make()->title('Você não tem permissão para alterar os dados da empresa.')->danger()->send();
            return;
        }

        $empresa = $this->empresaSelecionada();

        if (! $empresa) {
            Notification::make()->title('Empresa não encontrada.')->danger()->send();
            return;
        }

        $empresa->forceFill([
            'razao_social' => $this->razao_social ?: $empresa->razao_social,
            'nome_fantasia' => $this->nome_fantasia,
            'cnpj' => $this->cnpj,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'responsavel_nome' => $this->responsavel_nome,
            'status' => $this->status ?: $empresa->status,
            'plano' => $this->plano ?: $empresa->plano,
        ])->save();

        Notification::make()->title('Dados da empresa atualizados.')->success()->send();
    }

    public function empresasDisponiveis(): array
    {
        $user = Auth::user();

        if (! $user?->isSuperAdmin()) {
            $empresa = $this->empresaAtual();

            return $empresa ? [$empresa->id => $empresa->nome_fantasia ?: $empresa->razao_social] : [];
        }

        return Empresa::query()
            ->orderBy('razao_social')
            ->limit(100)
            ->get(['id', 'razao_social', 'nome_fantasia'])
            ->mapWithKeys(fn (Empresa $empresa) => [$empresa->id => $empresa->nome_fantasia ?: $empresa->razao_social])
            ->all();
    }

    public function resumo(): array
    {
        $empresa = $this->empresaSelecionada();

        if (! $empresa) {
            return [];
        }

        return [
            ['label' => 'Usuários', 'value' => $empresa->users()->count(), 'hint' => 'vinculados à empresa'],
            ['label' => 'Clientes', 'value' => method_exists($empresa, 'responsaveis') ? $empresa->responsaveis()->count() : 0, 'hint' => 'responsáveis/clientes'],
            ['label' => 'Tarefas', 'value' => method_exists($empresa, 'itemControles') ? $empresa->itemControles()->count() : 0, 'hint' => 'itens operacionais'],
            ['label' => 'Plano', 'value' => $empresa->plano ?: '-', 'hint' => 'configuração atual'],
        ];
    }

    public function resourceUrl(): ?string
    {
        try {
            if (! $this->empresaId || ! EmpresaResource::canViewAny()) {
                return null;
            }

            return EmpresaResource::getUrl('edit', ['record' => $this->empresaId]);
        } catch (\Throwable) {
            return null;
        }
    }

    public function podeEditar(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin()
            || ($user->isAdminEmpresa() && (int) $user->empresa_id === (int) $this->empresaId)
            || static::canAdvancedPermission('configuracoes.edit');
    }

    private function carregarEmpresa(): void
    {
        $empresa = $this->empresaSelecionada();

        if (! $empresa) {
            return;
        }

        $this->razao_social = $empresa->razao_social;
        $this->nome_fantasia = $empresa->nome_fantasia;
        $this->cnpj = $empresa->cnpj;
        $this->email = $empresa->email;
        $this->telefone = $empresa->telefone;
        $this->responsavel_nome = $empresa->responsavel_nome;
        $this->status = $empresa->status;
        $this->plano = $empresa->plano;
    }

    private function empresaSelecionada(): ?Empresa
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        if ($user->isSuperAdmin()) {
            return $this->empresaId ? Empresa::query()->find($this->empresaId) : Empresa::query()->orderBy('id')->first();
        }

        return Empresa::query()->find($user->empresa_id);
    }

    private function empresaAtual(): ?Empresa
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        if ($user->isSuperAdmin()) {
            return Empresa::query()->orderBy('id')->first();
        }

        return Empresa::query()->find($user->empresa_id);
    }
}
