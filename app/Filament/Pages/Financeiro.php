<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesAdvancedPermissions;


use App\Support\CachedSchema;
use App\Support\FinanceiroClienteData;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use UnitEnum;

class Financeiro extends Page
{
    use UsesAdvancedPermissions;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static string | UnitEnum | null $navigationGroup = 'Contratos e Financeiro';
    protected static ?string $navigationLabel = 'Financeiro';
    protected static ?string $title = 'Financeiro';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.pages.financeiro';


    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return static::canAdvancedPermission('financeiro.view');
    }

    public ?int $empresaFiltro = null;
    public bool $modalGatewayAberto = false;

    public array $gatewayForm = [
        'gateway' => 'asaas',
        'nome' => '',
        'ambiente' => 'sandbox',
        'api_token' => '',
        'webhook_secret' => '',
    ];

    public function mount(): void
    {
        $this->empresaFiltro = FinanceiroClienteData::empresaIdAtual();
    }

    public function abrirGateway(): void
    {
        if (! $this->ensureCanDo('financeiro.edit')) {
            return;
        }

        $this->resetValidation();
        $this->gatewayForm = [
            'gateway' => 'asaas',
            'nome' => '',
            'ambiente' => 'sandbox',
            'api_token' => '',
            'webhook_secret' => '',
        ];
        $this->modalGatewayAberto = true;
    }

    public function salvarGateway(): void
    {
        if (! $this->ensureCanDo('financeiro.edit')) {
            return;
        }

        if (! CachedSchema::hasTable('financeiro_gateway_integracoes')) {
            $this->notificarSql();
            return;
        }

        $empresaId = FinanceiroClienteData::empresaIdPermitida($this->empresaFiltro);

        if (! $empresaId) {
            Notification::make()->title('Selecione a empresa antes de salvar')->warning()->send();
            return;
        }

        $dados = $this->validate([
            'gatewayForm.gateway' => ['required', Rule::in(['asaas', 'mercado_pago', 'stripe', 'manual'])],
            'gatewayForm.nome' => ['nullable', 'string', 'max:120'],
            'gatewayForm.ambiente' => ['required', Rule::in(['sandbox', 'producao'])],
            'gatewayForm.api_token' => ['nullable', 'string', 'max:5000'],
            'gatewayForm.webhook_secret' => ['nullable', 'string', 'max:5000'],
        ])['gatewayForm'];

        $existente = DB::table('financeiro_gateway_integracoes')
            ->where('empresa_id', $empresaId)
            ->where('gateway', $dados['gateway'])
            ->first();

        $apiTokenEncrypted = filled($dados['api_token'])
            ? Crypt::encryptString($dados['api_token'])
            : ($existente->api_token_encrypted ?? null);

        $webhookSecretEncrypted = filled($dados['webhook_secret'])
            ? Crypt::encryptString($dados['webhook_secret'])
            : ($existente->webhook_secret_encrypted ?? null);

        DB::table('financeiro_gateway_integracoes')->updateOrInsert(
            ['empresa_id' => $empresaId, 'gateway' => $dados['gateway']],
            [
                'nome' => $dados['nome'] ?: strtoupper(str_replace('_', ' ', $dados['gateway'])),
                'ambiente' => $dados['ambiente'],
                'api_token_encrypted' => $apiTokenEncrypted,
                'webhook_secret_encrypted' => $webhookSecretEncrypted,
                'status' => $apiTokenEncrypted || $dados['gateway'] === 'manual' ? 'configurado' : 'pendente',
                'updated_at' => now(),
                'created_at' => $existente->created_at ?? now(),
                'created_by' => $existente->created_by ?? auth()->id(),
            ]
        );

        $this->modalGatewayAberto = false;
        Notification::make()->title('Integração salva')->body('A estrutura ficou pronta para conectar a API por empresa.')->success()->send();
    }

    public function desativarGateway(int $id): void
    {
        if (! $this->ensureCanDo('financeiro.delete')) {
            return;
        }

        if (! CachedSchema::hasTable('financeiro_gateway_integracoes')) {
            $this->notificarSql();
            return;
        }

        $query = DB::table('financeiro_gateway_integracoes')->where('id', $id);
        FinanceiroClienteData::queryPorEmpresa($query, $this->empresaFiltro);
        $query->update([
            'status' => 'inativo',
            'updated_at' => now(),
        ]);

        Notification::make()->title('Integração desativada')->success()->send();
    }

    protected function getViewData(): array
    {
        return [
            'permissions' => $this->permissionFlags('financeiro'),
            'instalado' => FinanceiroClienteData::moduloInstalado(),
            'faltantes' => FinanceiroClienteData::tabelasFaltantes(),
            'empresas' => FinanceiroClienteData::empresasDisponiveis(),
            'dashboard' => FinanceiroClienteData::dashboard($this->empresaFiltro),
        ];
    }

    protected function notificarSql(): void
    {
        Notification::make()
            ->title('Execute o SQL do módulo financeiro do cliente')
            ->body('As tabelas novas ainda não existem no banco. Use o arquivo sql/financeiro_cliente.sql incluído no zip.')
            ->warning()
            ->send();
    }
}
