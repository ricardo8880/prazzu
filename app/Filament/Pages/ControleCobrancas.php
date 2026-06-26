<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesAdvancedPermissions;


use App\Support\CachedSchema;
use App\Support\FinanceiroClienteData;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use UnitEnum;

class ControleCobrancas extends Page
{
    use UsesAdvancedPermissions;
    protected static string | UnitEnum | null $navigationGroup = 'Financeiro';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Cobranças';
    protected static ?string $title = 'Cobranças';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.controle-cobrancas';


    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return static::canAdvancedPermission('cobrancas.view');
    }

    public ?int $empresaFiltro = null;
    public string $statusFiltro = 'todos';
    public string $busca = '';
    public bool $modalCobrancaAberto = false;
    public bool $modalClienteAberto = false;
    public ?array $cobrancaSelecionada = null;

    public array $form = [
        'financeiro_cliente_id' => null,
        'descricao' => '',
        'referencia' => '',
        'valor' => '',
        'vencimento' => '',
        'forma_pagamento' => 'manual',
        'observacoes' => '',
    ];

    public array $clienteForm = [
        'nome' => '',
        'documento' => '',
        'email' => '',
        'telefone' => '',
        'observacoes' => '',
    ];

    public function mount(): void
    {
        $this->empresaFiltro = FinanceiroClienteData::empresaIdAtual();
    }

    public function updatedEmpresaFiltro(): void
    {
        $this->resetarSelecoes();
    }

    public function abrirNovaCobranca(): void
    {
        if (! $this->ensureCanDo('cobrancas.create')) {
            return;
        }

        $this->resetValidation();
        $this->cobrancaSelecionada = null;
        $this->form = [
            'financeiro_cliente_id' => null,
            'descricao' => '',
            'referencia' => '',
            'valor' => '',
            'vencimento' => now()->addDays(7)->toDateString(),
            'forma_pagamento' => 'manual',
            'observacoes' => '',
        ];
        $this->modalCobrancaAberto = true;
    }

    public function abrirCobranca(int $id): void
    {
        if (! CachedSchema::hasTable('financeiro_cobrancas')) {
            $this->notificarSql();
            return;
        }

        $query = DB::table('financeiro_cobrancas as c')
            ->leftJoin('financeiro_clientes as fc', 'fc.id', '=', 'c.financeiro_cliente_id')
            ->select('c.*', 'fc.nome as cliente_nome', 'fc.email as cliente_email', 'fc.telefone as cliente_telefone')
            ->where('c.id', $id);

        FinanceiroClienteData::queryPorEmpresa($query, $this->empresaFiltro, 'c.empresa_id');

        $cobranca = $query->first();

        if (! $cobranca) {
            Notification::make()->title('Cobrança não encontrada')->warning()->send();
            return;
        }

        $this->cobrancaSelecionada = FinanceiroClienteData::formatarCobranca((array) $cobranca);
        $this->form = [
            'financeiro_cliente_id' => $cobranca->financeiro_cliente_id,
            'descricao' => $cobranca->descricao,
            'referencia' => $cobranca->referencia,
            'valor' => $cobranca->valor,
            'vencimento' => $cobranca->vencimento,
            'forma_pagamento' => $cobranca->forma_pagamento ?: 'manual',
            'observacoes' => $cobranca->observacoes,
        ];
        $this->modalCobrancaAberto = true;
    }

    public function salvarCobranca(): void
    {
        if (! $this->ensureCanDo('cobrancas.create')) {
            return;
        }

        if (! FinanceiroClienteData::moduloInstalado()) {
            $this->notificarSql();
            return;
        }

        $empresaId = FinanceiroClienteData::empresaIdPermitida($this->empresaFiltro);

        if (! $empresaId) {
            Notification::make()->title('Selecione a empresa antes de salvar')->warning()->send();
            return;
        }

        $dados = $this->validate([
            'form.financeiro_cliente_id' => ['required', 'integer', Rule::exists('financeiro_clientes', 'id')->where('empresa_id', $empresaId)],
            'form.descricao' => ['required', 'string', 'max:255'],
            'form.referencia' => ['nullable', 'string', 'max:120'],
            'form.valor' => ['required', 'numeric', 'min:0.01'],
            'form.vencimento' => ['required', 'date'],
            'form.forma_pagamento' => ['nullable', 'string', 'max:40'],
            'form.observacoes' => ['nullable', 'string', 'max:2000'],
        ])['form'];

        $payload = [
            'empresa_id' => $empresaId,
            'financeiro_cliente_id' => (int) $dados['financeiro_cliente_id'],
            'descricao' => trim($dados['descricao']),
            'referencia' => $dados['referencia'] ?: null,
            'valor' => (float) $dados['valor'],
            'vencimento' => $dados['vencimento'],
            'forma_pagamento' => $dados['forma_pagamento'] ?: 'manual',
            'observacoes' => $dados['observacoes'] ?: null,
            'status' => now()->parse($dados['vencimento'])->isPast() && now()->parse($dados['vencimento'])->isBefore(now()->startOfDay()) ? 'vencida' : 'aberta',
            'updated_at' => now(),
        ];

        if ($this->cobrancaSelecionada && ! empty($this->cobrancaSelecionada['id'])) {
            $query = DB::table('financeiro_cobrancas')->where('id', $this->cobrancaSelecionada['id']);
            FinanceiroClienteData::queryPorEmpresa($query, $empresaId);

            if (! $query->exists()) {
                Notification::make()->title('Cobrança não encontrada para esta empresa')->warning()->send();
                return;
            }

            $query->update($payload);
            Notification::make()->title('Cobrança atualizada')->success()->send();
        } else {
            $payload['created_by'] = auth()->id();
            $payload['created_at'] = now();
            DB::table('financeiro_cobrancas')->insert($payload);
            Notification::make()->title('Cobrança criada')->success()->send();
        }

        $this->modalCobrancaAberto = false;
        $this->resetarSelecoes();
    }

    public function registrarPagamento(int $id): void
    {
        if (! $this->ensureCanDo('cobrancas.approve')) {
            return;
        }

        if (! FinanceiroClienteData::moduloInstalado()) {
            $this->notificarSql();
            return;
        }

        $query = DB::table('financeiro_cobrancas')->where('id', $id);
        FinanceiroClienteData::queryPorEmpresa($query, $this->empresaFiltro);
        $cobranca = $query->first();

        if (! $cobranca || in_array($cobranca->status, ['paga', 'cancelada'], true)) {
            return;
        }

        DB::transaction(function () use ($cobranca): void {
            DB::table('financeiro_cobrancas')->where('id', $cobranca->id)->update([
                'status' => 'paga',
                'pago_em' => now(),
                'updated_at' => now(),
            ]);

            DB::table('financeiro_recebimentos')->updateOrInsert(
                ['financeiro_cobranca_id' => $cobranca->id, 'origem' => 'manual'],
                [
                    'empresa_id' => $cobranca->empresa_id,
                    'financeiro_cliente_id' => $cobranca->financeiro_cliente_id,
                    'valor_recebido' => $cobranca->valor,
                    'forma_pagamento' => $cobranca->forma_pagamento ?: 'manual',
                    'recebido_em' => now(),
                    'observacoes' => 'Baixa manual registrada pelo painel.',
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        });

        Notification::make()->title('Pagamento registrado')->success()->send();
        $this->resetarSelecoes();
    }

    public function cancelarCobranca(int $id): void
    {
        if (! $this->ensureCanDo('cobrancas.cancel')) {
            return;
        }

        if (! CachedSchema::hasTable('financeiro_cobrancas')) {
            $this->notificarSql();
            return;
        }

        $query = DB::table('financeiro_cobrancas')->where('id', $id)->whereNotIn('status', ['paga', 'cancelada']);
        FinanceiroClienteData::queryPorEmpresa($query, $this->empresaFiltro);
        $query->update([
            'status' => 'cancelada',
            'updated_at' => now(),
        ]);

        Notification::make()->title('Cobrança cancelada')->success()->send();
    }

    public function abrirNovoCliente(): void
    {
        if (! $this->ensureCanDo('clientes.create')) {
            return;
        }

        $this->resetValidation();
        $this->clienteForm = ['nome' => '', 'documento' => '', 'email' => '', 'telefone' => '', 'observacoes' => ''];
        $this->modalClienteAberto = true;
    }

    public function salvarCliente(): void
    {
        if (! $this->ensureCanDo('clientes.create')) {
            return;
        }

        if (! CachedSchema::hasTable('financeiro_clientes')) {
            $this->notificarSql();
            return;
        }

        $empresaId = FinanceiroClienteData::empresaIdPermitida($this->empresaFiltro);

        if (! $empresaId) {
            Notification::make()->title('Selecione a empresa antes de salvar')->warning()->send();
            return;
        }

        $dados = $this->validate([
            'clienteForm.nome' => ['required', 'string', 'max:255'],
            'clienteForm.documento' => ['nullable', 'string', 'max:40'],
            'clienteForm.email' => ['nullable', 'email', 'max:255'],
            'clienteForm.telefone' => ['nullable', 'string', 'max:40'],
            'clienteForm.observacoes' => ['nullable', 'string', 'max:2000'],
        ])['clienteForm'];

        $id = DB::table('financeiro_clientes')->insertGetId([
            'empresa_id' => $empresaId,
            'nome' => trim($dados['nome']),
            'documento' => $dados['documento'] ?: null,
            'email' => $dados['email'] ?: null,
            'telefone' => $dados['telefone'] ?: null,
            'observacoes' => $dados['observacoes'] ?: null,
            'status' => 'ativo',
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->form['financeiro_cliente_id'] = $id;
        $this->modalClienteAberto = false;
        Notification::make()->title('Cliente financeiro cadastrado')->success()->send();
    }

    public function resetarSelecoes(): void
    {
        $this->cobrancaSelecionada = null;
    }

    protected function getViewData(): array
    {
        FinanceiroClienteData::marcarVencidas($this->empresaFiltro);

        return [
            'permissions' => $this->permissionFlags('cobrancas'),
            'instalado' => FinanceiroClienteData::moduloInstalado(),
            'faltantes' => FinanceiroClienteData::tabelasFaltantes(),
            'empresas' => FinanceiroClienteData::empresasDisponiveis(),
            'clientes' => FinanceiroClienteData::clientes($this->empresaFiltro, $this->busca),
            'cobrancas' => FinanceiroClienteData::cobrancas($this->empresaFiltro, $this->statusFiltro, $this->busca),
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
