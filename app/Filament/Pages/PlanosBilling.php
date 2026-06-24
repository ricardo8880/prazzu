<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesAdvancedPermissions;
use App\Models\Empresa;
use App\Services\PlanoService;
use App\Support\CachedSchema;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class PlanosBilling extends Page
{
    use UsesAdvancedPermissions;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';
    protected static string | UnitEnum | null $navigationGroup = 'Administração';
    protected static ?string $navigationLabel = 'Assinatura';
    protected static ?string $title = 'Assinatura';
    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.planos-billing';

    public string $search = '';
    public string $planFilter = 'todos';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->isSuperAdmin() === true || static::canAdvancedPermission('governanca.view');
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->planFilter = 'todos';
    }

    protected function getViewData(): array
    {
        return [
            'resumo' => $this->resumo(),
            'planos' => $this->planos(),
            'empresas' => $this->empresas(),
            'assinaturas' => $this->assinaturas(),
            'pagamentos' => $this->pagamentos(),
            'bloqueios' => $this->bloqueios(),
            'regras' => $this->regras(),
            'planOptions' => PlanoService::options(),
            'temBilling' => CachedSchema::hasTable('assinaturas') || CachedSchema::hasTable('pagamentos'),
        ];
    }

    private function resumo(): array
    {
        return [
            'empresas' => CachedSchema::hasTable('empresas') ? (int) Empresa::query()->count() : 0,
            'assinaturas_ativas' => CachedSchema::hasTable('assinaturas') ? (int) DB::table('assinaturas')->whereIn('status', ['ACTIVE', 'RECEIVED', 'CONFIRMED', 'ativa'])->count() : 0,
            'mrr_previsto' => CachedSchema::hasTable('assinaturas') ? (float) DB::table('assinaturas')->whereIn('status', ['ACTIVE', 'RECEIVED', 'CONFIRMED', 'ativa'])->sum('valor') : 0,
            'pagamentos_abertos' => CachedSchema::hasTable('pagamentos') ? (int) DB::table('pagamentos')->whereNull('pago_em')->whereNotIn('status', ['RECEIVED', 'CONFIRMED', 'PAID', 'CANCELED'])->count() : 0,
            'pagamentos_vencidos' => CachedSchema::hasTable('pagamentos') ? (int) DB::table('pagamentos')->whereNull('pago_em')->whereDate('vencimento', '<', now()->toDateString())->count() : 0,
            'valor_vencido' => CachedSchema::hasTable('pagamentos') ? (float) DB::table('pagamentos')->whereNull('pago_em')->whereDate('vencimento', '<', now()->toDateString())->sum('valor') : 0,
            'bloqueios_ativos' => CachedSchema::hasTable('prazzu_billing_locks') ? (int) DB::table('prazzu_billing_locks')->whereNull('unlocked_at')->count() : 0,
            'regras_ativas' => CachedSchema::hasTable('prazzu_billing_rules') ? (int) DB::table('prazzu_billing_rules')->where('active', true)->count() : 0,
        ];
    }

    private function planos(): array
    {
        return collect(PlanoService::options())->map(function (string $nome, string $codigo): array {
            return [
                'codigo' => $codigo,
                'nome' => $nome,
                'preco' => PlanoService::preco($codigo),
                'usuarios' => PlanoService::limiteUsuarios($codigo),
                'itens' => PlanoService::limiteItens($codigo),
                'armazenamento' => $this->formatMb(PlanoService::limiteArmazenamentoMb($codigo)),
                'ia' => PlanoService::limiteInteracoesIa($codigo),
                'features' => array_slice(PlanoService::features($codigo), 0, 7),
            ];
        })->values()->all();
    }

    private function empresas(): array
    {
        if (! CachedSchema::hasTable('empresas')) {
            return [];
        }

        $user = auth()->user();
        $query = Empresa::query()
            ->withCount(['users', 'itemControles'])
            ->with('assinaturaAtual')
            ->when(! $user?->isSuperAdmin(), fn ($query) => $query->whereKey((int) $user?->empresa_id))
            ->when($this->planFilter !== 'todos', fn ($query) => $query->where('plano', PlanoService::normalizarPlano($this->planFilter)))
            ->when(trim($this->search) !== '', function ($query): void {
                $search = '%' . trim($this->search) . '%';
                $query->where(fn ($sub) => $sub->where('nome_fantasia', 'like', $search)->orWhere('razao_social', 'like', $search)->orWhere('email', 'like', $search)->orWhere('cnpj', 'like', $search));
            })
            ->orderBy('nome_fantasia')
            ->orderBy('razao_social')
            ->limit(12);

        return $query->get()->map(fn (Empresa $empresa): array => [
            'nome' => $empresa->nome_fantasia ?: $empresa->razao_social,
            'email' => $empresa->email,
            'plano' => PlanoService::nome($empresa->plano),
            'status' => $empresa->assinaturaAtual?->status ?: 'sem assinatura',
            'status_tone' => $this->tone($empresa->assinaturaAtual?->status),
            'usuarios' => ($empresa->users_count ?? 0) . ' / ' . ($empresa->limite_usuarios ?: PlanoService::limiteUsuarios($empresa->plano)),
            'itens' => ($empresa->item_controles_count ?? 0) . ' / ' . ($empresa->limite_itens ?: PlanoService::limiteItens($empresa->plano)),
            'armazenamento' => $this->formatMb((int) ($empresa->limite_armazenamento_mb ?: PlanoService::limiteArmazenamentoMb($empresa->plano))),
        ])->all();
    }

    private function assinaturas(): array
    {
        if (! CachedSchema::hasTable('assinaturas')) {
            return [];
        }

        return DB::table('assinaturas as a')
            ->leftJoin('empresas as e', 'e.id', '=', 'a.empresa_id')
            ->select('a.id', 'a.plano', 'a.valor', 'a.ciclo', 'a.status', 'a.proximo_vencimento', 'a.gateway', 'e.nome_fantasia', 'e.razao_social')
            ->when($this->planFilter !== 'todos', fn ($query) => $query->where('a.plano', PlanoService::normalizarPlano($this->planFilter)))
            ->when(trim($this->search) !== '', function ($query): void {
                $search = '%' . trim($this->search) . '%';
                $query->where(fn ($sub) => $sub->where('e.nome_fantasia', 'like', $search)->orWhere('e.razao_social', 'like', $search)->orWhere('a.gateway_subscription_id', 'like', $search));
            })
            ->orderByRaw('CASE WHEN a.proximo_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('a.proximo_vencimento')
            ->limit(10)
            ->get()
            ->map(fn ($row): array => [
                'empresa' => $row->nome_fantasia ?: ($row->razao_social ?: 'Empresa #' . $row->id),
                'plano' => PlanoService::nome($row->plano),
                'valor' => $this->money((float) $row->valor),
                'ciclo' => $row->ciclo ?: 'MONTHLY',
                'status' => $row->status ?: 'sem status',
                'tone' => $this->tone($row->status),
                'vencimento' => $this->date($row->proximo_vencimento),
                'gateway' => $row->gateway ?: 'manual',
            ])->all();
    }

    private function pagamentos(): array
    {
        if (! CachedSchema::hasTable('pagamentos')) {
            return [];
        }

        return DB::table('pagamentos as p')
            ->leftJoin('empresas as e', 'e.id', '=', 'p.empresa_id')
            ->leftJoin('assinaturas as a', 'a.id', '=', 'p.assinatura_id')
            ->select('p.id', 'p.valor', 'p.status', 'p.billing_type', 'p.vencimento', 'p.pago_em', 'e.nome_fantasia', 'e.razao_social', 'a.plano')
            ->when($this->planFilter !== 'todos', fn ($query) => $query->where('a.plano', PlanoService::normalizarPlano($this->planFilter)))
            ->when(trim($this->search) !== '', function ($query): void {
                $search = '%' . trim($this->search) . '%';
                $query->where(fn ($sub) => $sub->where('e.nome_fantasia', 'like', $search)->orWhere('e.razao_social', 'like', $search)->orWhere('p.gateway_payment_id', 'like', $search));
            })
            ->orderByRaw('CASE WHEN p.pago_em IS NULL THEN 0 ELSE 1 END')
            ->orderBy('p.vencimento')
            ->limit(10)
            ->get()
            ->map(fn ($row): array => [
                'empresa' => $row->nome_fantasia ?: ($row->razao_social ?: 'Empresa #' . $row->id),
                'plano' => $row->plano ? PlanoService::nome($row->plano) : 'Sem plano',
                'valor' => $this->money((float) $row->valor),
                'status' => $row->status ?: 'sem status',
                'tone' => $row->pago_em ? 'success' : ($row->vencimento && $row->vencimento < now()->toDateString() ? 'danger' : $this->tone($row->status)),
                'vencimento' => $this->date($row->vencimento),
                'pago_em' => $this->date($row->pago_em),
                'tipo' => $row->billing_type ?: 'manual',
            ])->all();
    }

    private function bloqueios(): array
    {
        if (! CachedSchema::hasTable('prazzu_billing_locks')) {
            return [];
        }

        return DB::table('prazzu_billing_locks as l')
            ->leftJoin('empresas as e', 'e.id', '=', 'l.empresa_id')
            ->select('l.reason', 'l.locked_at', 'e.nome_fantasia', 'e.razao_social')
            ->whereNull('l.unlocked_at')
            ->orderByDesc('l.locked_at')
            ->limit(6)
            ->get()
            ->map(fn ($row): array => ['empresa' => $row->nome_fantasia ?: ($row->razao_social ?: 'Empresa sem nome'), 'reason' => $row->reason ?: 'Sem motivo informado', 'locked_at' => $this->date($row->locked_at)])->all();
    }

    private function regras(): array
    {
        if (! CachedSchema::hasTable('prazzu_billing_rules')) {
            return [];
        }

        return DB::table('prazzu_billing_rules')
            ->orderBy('days_after_due')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => ['name' => $row->name, 'days' => (int) $row->days_after_due, 'action' => $row->action_type, 'active' => (bool) $row->active, 'message' => $row->message])->all();
    }

    private function tone(?string $status): string
    {
        return match (strtoupper((string) $status)) {
            'ACTIVE', 'RECEIVED', 'CONFIRMED', 'PAID', 'ATIVA' => 'success',
            'PENDING', 'CREATED', 'PAYMENT_CREATED' => 'warning',
            'OVERDUE', 'PAYMENT_OVERDUE', 'CANCELED', 'CANCELLED' => 'danger',
            default => 'muted',
        };
    }

    private function money(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    private function formatMb(int $mb): string
    {
        return $mb >= 1024 ? number_format($mb / 1024, 1, ',', '.') . ' GB' : number_format($mb, 0, ',', '.') . ' MB';
    }

    private function date($value): string
    {
        if (! $value) {
            return '—';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }
}
