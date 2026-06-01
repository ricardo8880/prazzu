<?php

namespace App\Filament\Pages;

use App\Models\ItemControle;
use App\Support\ComplianceModuleData;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

class Riscos extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static string | UnitEnum | null $navigationGroup = 'Governança';
    protected static ?string $navigationLabel = 'Riscos';
    protected static ?string $title = 'Riscos';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.compliance-riscos';

    public $empresaId = null;
    public $responsavelId = null;
    public string $titulo = '';
    public string $descricao = '';
    public string $prioridade = 'alta';
    public ?string $dataVencimento = null;
    public int $probabilidade = 3;
    public int $impacto = 3;

    protected function getViewData(): array
    {
        return ['data' => ComplianceModuleData::riscos()];
    }

    public function criarRisco(): void
    {
        $this->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'prioridade' => ['required', 'in:baixa,media,alta,urgente'],
            'dataVencimento' => ['nullable', 'date'],
            'probabilidade' => ['required', 'integer', 'min:1', 'max:5'],
            'impacto' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $empresaId = ComplianceModuleData::resolveEmpresaId($this->empresaId);
        $responsavelId = ComplianceModuleData::resolveResponsavelId($this->responsavelId, $empresaId);

        if (! $empresaId || ! $responsavelId) {
            Notification::make()->title('Não foi possível criar o risco')->body('Cadastre uma empresa e um responsável antes de criar itens de compliance.')->danger()->send();
            return;
        }

        ItemControle::query()->create([
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'tipo' => 'risco_compliance',
            'status' => 'pendente',
            'prioridade' => $this->prioridade,
            'empresa_id' => $empresaId,
            'responsavel_id' => $responsavelId,
            'data_vencimento' => $this->dataVencimento ?: null,
            'risk_probability' => $this->probabilidade,
            'risk_impact' => $this->impacto,
            'risk_score' => $this->probabilidade * $this->impacto,
            'risco_score' => $this->probabilidade * $this->impacto,
        ]);

        $this->reset(['empresaId', 'responsavelId', 'titulo', 'descricao', 'dataVencimento']);
        $this->prioridade = 'alta';
        $this->probabilidade = 3;
        $this->impacto = 3;

        Notification::make()->title('Risco criado com sucesso')->success()->send();
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
