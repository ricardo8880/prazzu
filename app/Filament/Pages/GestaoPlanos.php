<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesAdvancedPermissions;
use App\Models\Empresa;
use App\Services\PlanoService;
use App\Support\CachedSchema;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class GestaoPlanos extends Page
{
    use UsesAdvancedPermissions;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | UnitEnum | null $navigationGroup = 'Conta';
    protected static ?string $navigationLabel = 'Gestão de Planos';
    protected static ?string $title = 'Gestão de Planos';
    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.gestao-planos';

    public string $search = '';
    public string $planFilter = 'todos';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->isSuperAdmin() === true || static::canAdvancedPermission('governanca.view');
    }

    public function updateEmpresaPlano(int $empresaId, string $plano): void
    {
        if (! $this->ensureCanDo('governanca.edit')) {
            return;
        }

        $plano = PlanoService::normalizarPlano($plano);
        $empresa = Empresa::query()->find($empresaId);

        if (! $empresa) {
            Notification::make()->title('Empresa não encontrada.')->danger()->send();
            return;
        }

        $authUser = auth()->user();
        if (! $authUser?->isSuperAdmin() && (int) $authUser?->empresa_id !== (int) $empresa->id) {
            Notification::make()->title('Você não tem permissão para alterar este plano.')->danger()->send();
            return;
        }

        $empresa->forceFill([
            'plano' => $plano,
            'limite_usuarios' => PlanoService::limiteUsuarios($plano),
            'limite_itens' => PlanoService::limiteItens($plano),
            'limite_armazenamento_mb' => PlanoService::limiteArmazenamentoMb($plano),
            'limite_interacoes_ia' => PlanoService::limiteInteracoesIa($plano),
        ])->save();

        if (CachedSchema::hasTable('assinaturas')) {
            $assinatura = $empresa->assinaturaAtual()->first();

            if ($assinatura) {
                $assinatura->forceFill([
                    'plano' => $plano,
                    'valor' => PlanoService::valorMensal($plano),
                ])->save();
            }
        }

        Notification::make()
            ->title('Plano atualizado')
            ->body('Limites de usuários, armazenamento, itens e recursos foram sincronizados.')
            ->success()
            ->send();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->planFilter = 'todos';
    }

    protected function getViewData(): array
    {
        return [
            'planos' => $this->planosInternos(),
            'empresas' => $this->empresas(),
            'resumo' => $this->resumo(),
            'planOptions' => $this->planOptions(),
            'temColunaArmazenamento' => CachedSchema::hasTable('empresas') && CachedSchema::hasColumn('empresas', 'limite_armazenamento_mb'),
        ];
    }

    private function planOptions(): array
    {
        return [
            PlanoService::STARTER => PlanoService::nome(PlanoService::STARTER),
            PlanoService::PROFISSIONAL => PlanoService::nome(PlanoService::PROFISSIONAL),
            PlanoService::ENTERPRISE => PlanoService::nome(PlanoService::ENTERPRISE),
        ];
    }

    private function planosInternos(): array
    {
        return collect($this->planOptions())
            ->map(function (string $nome, string $codigo): array {
                $dados = PlanoService::dados($codigo);

                return [
                    'codigo' => $codigo,
                    'nome' => $nome,
                    'descricao' => $dados['descricao'] ?? '',
                    'usuarios' => PlanoService::limiteUsuarios($codigo),
                    'itens' => PlanoService::limiteItens($codigo),
                    'armazenamento' => $this->formatMb(PlanoService::limiteArmazenamentoMb($codigo)),
                    'ia' => PlanoService::limiteInteracoesIa($codigo),
                    'features' => PlanoService::features($codigo),
                    'preco' => PlanoService::preco($codigo),
                ];
            })
            ->values()
            ->all();
    }

    private function empresas(): array
    {
        if (! CachedSchema::hasTable('empresas')) {
            return [];
        }

        $authUser = auth()->user();
        $query = Empresa::query()
            ->withCount(['users', 'itemControles'])
            ->with('assinaturaAtual')
            ->when(! $authUser?->isSuperAdmin(), fn ($query) => $query->whereKey((int) $authUser?->empresa_id))
            ->when(trim($this->search) !== '', function ($query): void {
                $search = '%' . trim($this->search) . '%';
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('razao_social', 'like', $search)
                        ->orWhere('nome_fantasia', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('cnpj', 'like', $search);
                });
            })
            ->when($this->planFilter !== 'todos', fn ($query) => $query->where('plano', PlanoService::normalizarPlano($this->planFilter)))
            ->orderBy('nome_fantasia')
            ->orderBy('razao_social')
            ->limit(100);

        return $query->get()
            ->map(function (Empresa $empresa): array {
                $storageUsedMb = $this->storageUsedMb((int) $empresa->id);
                $storageLimitMb = (int) ($empresa->limite_armazenamento_mb ?: PlanoService::limiteArmazenamentoMb($empresa->plano));

                return [
                    'id' => (int) $empresa->id,
                    'nome' => $empresa->nome_fantasia ?: $empresa->razao_social,
                    'email' => $empresa->email,
                    'plano' => PlanoService::normalizarPlano($empresa->plano),
                    'plano_nome' => PlanoService::nome($empresa->plano),
                    'usuarios_usados' => (int) ($empresa->users_count ?? 0),
                    'usuarios_limite' => (int) ($empresa->limite_usuarios ?: PlanoService::limiteUsuarios($empresa->plano)),
                    'itens_usados' => (int) ($empresa->item_controles_count ?? 0),
                    'itens_limite' => (int) ($empresa->limite_itens ?: PlanoService::limiteItens($empresa->plano)),
                    'storage_usado' => $this->formatMb($storageUsedMb),
                    'storage_limite' => $this->formatMb($storageLimitMb),
                    'storage_percentual' => $storageLimitMb > 0 ? min(999, round(($storageUsedMb / $storageLimitMb) * 100, 1)) : 0,
                    'ia_limite' => (int) ($empresa->limite_interacoes_ia ?: PlanoService::limiteInteracoesIa($empresa->plano)),
                    'assinatura_status' => $empresa->assinaturaAtual?->status,
                ];
            })
            ->all();
    }

    private function resumo(): array
    {
        if (! CachedSchema::hasTable('empresas')) {
            return ['empresas' => 0, 'starter' => 0, 'profissional' => 0, 'enterprise' => 0];
        }

        return [
            'empresas' => Empresa::query()->count(),
            'starter' => Empresa::query()->where('plano', PlanoService::STARTER)->count(),
            'profissional' => Empresa::query()->where('plano', PlanoService::PROFISSIONAL)->count(),
            'enterprise' => Empresa::query()->where('plano', PlanoService::ENTERPRISE)->count(),
        ];
    }

    private function storageUsedMb(int $empresaId): int
    {
        $bytes = 0;

        if (CachedSchema::hasTable('item_controle_anexos')) {
            $bytes += (int) DB::table('item_controle_anexos as anexos')
                ->join('item_controles as itens', 'itens.id', '=', 'anexos.item_controle_id')
                ->where('itens.empresa_id', $empresaId)
                ->sum('anexos.tamanho_bytes');
        }

        if (CachedSchema::hasTable('portal_documentos')) {
            $column = CachedSchema::hasColumn('portal_documentos', 'tamanho_bytes') ? 'tamanho_bytes' : null;
            if ($column) {
                $bytes += (int) DB::table('portal_documentos')->where('empresa_id', $empresaId)->sum($column);
            }
        }

        return (int) ceil($bytes / 1024 / 1024);
    }

    private function formatMb(int $mb): string
    {
        if ($mb >= 1024) {
            return number_format($mb / 1024, 1, ',', '.') . ' GB';
        }

        return number_format($mb, 0, ',', '.') . ' MB';
    }
}
