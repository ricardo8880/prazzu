<?php

namespace App\Filament\Pages;


use App\Support\CachedSchema;
use App\Support\FinanceiroClienteData;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use UnitEnum;

class Assinaturas extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';
    protected static string | UnitEnum | null $navigationGroup = 'Contratos';
    protected static ?string $navigationLabel = 'Assinaturas';
    protected static ?string $title = 'Assinaturas Contratuais';
    protected static ?int $navigationSort = 20;
    protected string $view = 'filament.pages.assinaturas';

    public function getHeading(): string
    {
        return 'Assinaturas contratuais';
    }

    public function getSubheading(): ?string
    {
        return 'Área interna de Contratos para assinaturas, cobrança recorrente e vínculo com planos contratados.';
    }


    public ?int $empresaFiltro = null;
    public string $statusFiltro = 'todas';
    public string $busca = '';
    public bool $modalAssinaturaAberto = false;
    public ?array $assinaturaSelecionada = null;

    public array $form = [
        'financeiro_cliente_id' => null,
        'nome' => '',
        'descricao' => '',
        'valor' => '',
        'ciclo' => 'mensal',
        'proxima_cobranca_em' => '',
        'forma_pagamento' => 'manual',
    ];

    public function mount(): void
    {
        $this->empresaFiltro = FinanceiroClienteData::empresaIdAtual();
    }

    public function abrirNovaAssinatura(): void
    {
        $this->resetValidation();
        $this->assinaturaSelecionada = null;
        $this->form = [
            'financeiro_cliente_id' => null,
            'nome' => '',
            'descricao' => '',
            'valor' => '',
            'ciclo' => 'mensal',
            'proxima_cobranca_em' => now()->addMonthNoOverflow()->toDateString(),
            'forma_pagamento' => 'manual',
        ];
        $this->modalAssinaturaAberto = true;
    }

    public function abrirAssinatura(int $id): void
    {
        if (! CachedSchema::hasTable('financeiro_assinaturas_cliente')) {
            $this->notificarSql();
            return;
        }

        $query = DB::table('financeiro_assinaturas_cliente')->where('id', $id);
        FinanceiroClienteData::queryPorEmpresa($query, $this->empresaFiltro);
        $assinatura = $query->first();

        if (! $assinatura) {
            Notification::make()->title('Assinatura não encontrada')->warning()->send();
            return;
        }

        $this->assinaturaSelecionada = FinanceiroClienteData::formatarAssinatura((array) $assinatura);
        $this->form = [
            'financeiro_cliente_id' => $assinatura->financeiro_cliente_id,
            'nome' => $assinatura->nome,
            'descricao' => $assinatura->descricao,
            'valor' => $assinatura->valor,
            'ciclo' => $assinatura->ciclo ?: 'mensal',
            'proxima_cobranca_em' => $assinatura->proxima_cobranca_em,
            'forma_pagamento' => $assinatura->forma_pagamento ?: 'manual',
        ];
        $this->modalAssinaturaAberto = true;
    }

    public function salvarAssinatura(): void
    {
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
            'form.nome' => ['required', 'string', 'max:255'],
            'form.descricao' => ['nullable', 'string', 'max:2000'],
            'form.valor' => ['required', 'numeric', 'min:0.01'],
            'form.ciclo' => ['required', Rule::in(['semanal', 'quinzenal', 'mensal', 'trimestral', 'semestral', 'anual'])],
            'form.proxima_cobranca_em' => ['required', 'date'],
            'form.forma_pagamento' => ['nullable', 'string', 'max:40'],
        ])['form'];

        $payload = [
            'empresa_id' => $empresaId,
            'financeiro_cliente_id' => (int) $dados['financeiro_cliente_id'],
            'nome' => trim($dados['nome']),
            'descricao' => $dados['descricao'] ?: null,
            'valor' => (float) $dados['valor'],
            'ciclo' => $dados['ciclo'],
            'proxima_cobranca_em' => $dados['proxima_cobranca_em'],
            'forma_pagamento' => $dados['forma_pagamento'] ?: 'manual',
            'updated_at' => now(),
        ];

        if ($this->assinaturaSelecionada && ! empty($this->assinaturaSelecionada['id'])) {
            $query = DB::table('financeiro_assinaturas_cliente')->where('id', $this->assinaturaSelecionada['id']);
            FinanceiroClienteData::queryPorEmpresa($query, $empresaId);

            if (! $query->exists()) {
                Notification::make()->title('Assinatura não encontrada para esta empresa')->warning()->send();
                return;
            }

            $query->update($payload);
            Notification::make()->title('Assinatura atualizada')->success()->send();
        } else {
            $payload['status'] = 'ativa';
            $payload['created_by'] = auth()->id();
            $payload['created_at'] = now();
            DB::table('financeiro_assinaturas_cliente')->insert($payload);
            Notification::make()->title('Assinatura criada')->success()->send();
        }

        $this->modalAssinaturaAberto = false;
    }

    public function gerarCobranca(int $id): void
    {
        if (! FinanceiroClienteData::moduloInstalado()) {
            $this->notificarSql();
            return;
        }

        $query = DB::table('financeiro_assinaturas_cliente')->where('id', $id);
        FinanceiroClienteData::queryPorEmpresa($query, $this->empresaFiltro);
        $assinatura = $query->first();

        if (! $assinatura || $assinatura->status !== 'ativa') {
            Notification::make()->title('A assinatura precisa estar ativa')->warning()->send();
            return;
        }

        DB::transaction(function () use ($assinatura): void {
            $vencimento = $assinatura->proxima_cobranca_em ?: now()->toDateString();

            $referencia = 'Assinatura #' . $assinatura->id . ' - ' . now()->format('m/Y');

            $jaExiste = DB::table('financeiro_cobrancas')
                ->where('empresa_id', $assinatura->empresa_id)
                ->where('financeiro_assinatura_id', $assinatura->id)
                ->where('referencia', $referencia)
                ->where('status', '!=', 'cancelada')
                ->exists();

            if ($jaExiste) {
                return;
            }

            DB::table('financeiro_cobrancas')->insert([
                'empresa_id' => $assinatura->empresa_id,
                'financeiro_cliente_id' => $assinatura->financeiro_cliente_id,
                'financeiro_assinatura_id' => $assinatura->id,
                'descricao' => $assinatura->nome,
                'referencia' => $referencia,
                'valor' => $assinatura->valor,
                'vencimento' => $vencimento,
                'status' => now()->parse($vencimento)->isPast() && now()->parse($vencimento)->isBefore(now()->startOfDay()) ? 'vencida' : 'aberta',
                'forma_pagamento' => $assinatura->forma_pagamento ?: 'manual',
                'observacoes' => 'Cobrança gerada a partir da assinatura recorrente.',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('financeiro_assinaturas_cliente')->where('id', $assinatura->id)->update([
                'ultima_cobranca_em' => now(),
                'proxima_cobranca_em' => FinanceiroClienteData::somarCiclo($assinatura->ciclo ?: 'mensal', $vencimento),
                'updated_at' => now(),
            ]);
        });

        Notification::make()->title('Cobrança gerada')->success()->send();
    }

    public function alterarStatus(int $id, string $status): void
    {
        if (! CachedSchema::hasTable('financeiro_assinaturas_cliente')) {
            $this->notificarSql();
            return;
        }

        if (! in_array($status, ['ativa', 'pausada', 'cancelada'], true)) {
            return;
        }

        $query = DB::table('financeiro_assinaturas_cliente')->where('id', $id);
        FinanceiroClienteData::queryPorEmpresa($query, $this->empresaFiltro);
        $query->update([
            'status' => $status,
            'cancelada_em' => $status === 'cancelada' ? now() : null,
            'updated_at' => now(),
        ]);

        Notification::make()->title('Status atualizado')->success()->send();
    }

    protected function getViewData(): array
    {
        return [
            'instalado' => FinanceiroClienteData::moduloInstalado(),
            'faltantes' => FinanceiroClienteData::tabelasFaltantes(),
            'empresas' => FinanceiroClienteData::empresasDisponiveis(),
            'clientes' => FinanceiroClienteData::clientes($this->empresaFiltro),
            'assinaturasCliente' => FinanceiroClienteData::assinaturas($this->empresaFiltro, $this->statusFiltro, $this->busca),
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

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
