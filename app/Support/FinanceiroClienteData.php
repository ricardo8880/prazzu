<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinanceiroClienteData
{
    public static function tabelasObrigatorias(): array
    {
        return [
            'financeiro_clientes',
            'financeiro_cobrancas',
            'financeiro_recebimentos',
            'financeiro_assinaturas_cliente',
            'financeiro_gateway_integracoes',
            'financeiro_webhook_logs',
        ];
    }

    public static function moduloInstalado(): bool
    {
        foreach (self::tabelasObrigatorias() as $tabela) {
            if (! CachedSchema::hasTable($tabela)) {
                return false;
            }
        }

        return true;
    }

    public static function tabelasFaltantes(): array
    {
        return array_values(array_filter(self::tabelasObrigatorias(), fn (string $tabela): bool => ! CachedSchema::hasTable($tabela)));
    }

    public static function empresaIdAtual(): ?int
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return null;
        }

        return $user->empresa_id ? (int) $user->empresa_id : null;
    }

    public static function empresaIdPermitida(?int $empresaId = null): ?int
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $empresaId ? (int) $empresaId : null;
        }

        return $user->empresa_id ? (int) $user->empresa_id : 0;
    }

    public static function queryPorEmpresa($query, ?int $empresaId = null, string $coluna = 'empresa_id')
    {
        $empresaId = self::empresaIdPermitida($empresaId);

        if ($empresaId !== null) {
            $query->where($coluna, $empresaId);
        }

        return $query;
    }

    public static function empresasDisponiveis(): array
    {
        if (! CachedSchema::hasTable('empresas')) {
            return [];
        }

        $user = auth()->user();
        $query = DB::table('empresas')->select('id', 'razao_social', 'nome_fantasia')->orderByRaw('COALESCE(nome_fantasia, razao_social) ASC');

        if ($user && (! method_exists($user, 'isSuperAdmin') || ! $user->isSuperAdmin()) && $user->empresa_id) {
            $query->where('id', $user->empresa_id);
        }

        return $query->get()->map(fn ($empresa): array => [
            'id' => (int) $empresa->id,
            'nome' => $empresa->nome_fantasia ?: $empresa->razao_social,
        ])->values()->all();
    }

    public static function clientes(?int $empresaId = null, ?string $busca = null): array
    {
        if (! CachedSchema::hasTable('financeiro_clientes')) {
            return [];
        }

        $query = DB::table('financeiro_clientes')
            ->select('id', 'empresa_id', 'nome', 'documento', 'email', 'telefone', 'status')
            ->orderBy('nome');

        self::queryPorEmpresa($query, $empresaId);

        if (filled($busca)) {
            $busca = self::likeTerm($busca);
            $query->where(function ($q) use ($busca): void {
                $q->where('nome', 'like', $busca)
                    ->orWhere('documento', 'like', $busca)
                    ->orWhere('email', 'like', $busca);
            });
        }

        return $query->limit(200)->get()->map(fn ($cliente): array => (array) $cliente)->values()->all();
    }

    public static function cobrancas(?int $empresaId = null, string $status = 'todos', ?string $busca = null, int $limit = 80): array
    {
        if (! CachedSchema::hasTable('financeiro_cobrancas')) {
            return [];
        }

        $query = DB::table('financeiro_cobrancas as c')
            ->leftJoin('financeiro_clientes as fc', 'fc.id', '=', 'c.financeiro_cliente_id')
            ->leftJoin('financeiro_assinaturas_cliente as a', 'a.id', '=', 'c.financeiro_assinatura_id')
            ->select([
                'c.*',
                'fc.nome as cliente_nome',
                'fc.documento as cliente_documento',
                'fc.email as cliente_email',
                'a.nome as assinatura_nome',
            ])
            ->orderByRaw("CASE WHEN c.status = 'vencida' THEN 0 WHEN c.status = 'aberta' THEN 1 WHEN c.status = 'paga' THEN 2 ELSE 3 END")
            ->orderBy('c.vencimento');

        self::queryPorEmpresa($query, $empresaId, 'c.empresa_id');

        if ($status !== 'todos') {
            $query->where('c.status', $status);
        }

        if (filled($busca)) {
            $busca = self::likeTerm($busca);
            $query->where(function ($q) use ($busca): void {
                $q->where('c.descricao', 'like', $busca)
                    ->orWhere('c.referencia', 'like', $busca)
                    ->orWhere('fc.nome', 'like', $busca)
                    ->orWhere('fc.documento', 'like', $busca);
            });
        }

        return $query->limit($limit)->get()->map(fn ($row): array => self::formatarCobranca((array) $row))->values()->all();
    }

    public static function assinaturas(?int $empresaId = null, string $status = 'todas', ?string $busca = null): array
    {
        if (! CachedSchema::hasTable('financeiro_assinaturas_cliente')) {
            return [];
        }

        $query = DB::table('financeiro_assinaturas_cliente as a')
            ->leftJoin('financeiro_clientes as fc', 'fc.id', '=', 'a.financeiro_cliente_id')
            ->select('a.*', 'fc.nome as cliente_nome', 'fc.email as cliente_email', 'fc.documento as cliente_documento')
            ->orderByRaw("CASE WHEN a.status = 'ativa' THEN 0 WHEN a.status = 'pausada' THEN 1 ELSE 2 END")
            ->orderBy('a.proxima_cobranca_em');

        self::queryPorEmpresa($query, $empresaId, 'a.empresa_id');

        if ($status !== 'todas') {
            $query->where('a.status', $status);
        }

        if (filled($busca)) {
            $busca = self::likeTerm($busca);
            $query->where(function ($q) use ($busca): void {
                $q->where('a.nome', 'like', $busca)
                    ->orWhere('fc.nome', 'like', $busca)
                    ->orWhere('a.descricao', 'like', $busca);
            });
        }

        return $query->limit(120)->get()->map(fn ($row): array => self::formatarAssinatura((array) $row))->values()->all();
    }

    public static function dashboard(?int $empresaId = null): array
    {
        if (! self::moduloInstalado()) {
            return [
                'instalado' => false,
                'faltantes' => self::tabelasFaltantes(),
                'stats' => [],
                'fluxo' => [],
                'vencimentos' => [],
                'integracoes' => [],
            ];
        }

        $hoje = Carbon::today();
        self::marcarVencidas($empresaId);

        $cobrancas = DB::table('financeiro_cobrancas');
        self::queryPorEmpresa($cobrancas, $empresaId);

        $recebimentos = DB::table('financeiro_recebimentos');
        self::queryPorEmpresa($recebimentos, $empresaId);

        $recebidoMes = (clone $recebimentos)->whereBetween('recebido_em', [$hoje->copy()->startOfMonth(), $hoje->copy()->endOfMonth()])->sum('valor_recebido');
        $aberto = (clone $cobrancas)->whereIn('status', ['aberta', 'vencida'])->sum('valor');
        $vencido = (clone $cobrancas)->where('status', 'vencida')->sum('valor');
        $assinaturasAtivas = DB::table('financeiro_assinaturas_cliente');
        self::queryPorEmpresa($assinaturasAtivas, $empresaId);

        return [
            'instalado' => true,
            'faltantes' => [],
            'stats' => [
                ['label' => 'Recebido no mês', 'value' => self::money($recebidoMes), 'hint' => 'Baixas registradas em recebimentos', 'tone' => 'ok'],
                ['label' => 'Em aberto', 'value' => self::money($aberto), 'hint' => 'Cobranças abertas e vencidas', 'tone' => 'warning'],
                ['label' => 'Vencido', 'value' => self::money($vencido), 'hint' => 'Precisa de ação de cobrança', 'tone' => $vencido > 0 ? 'danger' : 'ok'],
                ['label' => 'Assinaturas ativas', 'value' => (string) $assinaturasAtivas->where('status', 'ativa')->count(), 'hint' => 'Recorrências de clientes', 'tone' => 'info'],
            ],
            'fluxo' => self::fluxoProximosDias($empresaId),
            'vencimentos' => self::cobrancas($empresaId, 'todos', null, 10),
            'integracoes' => self::integracoes($empresaId),
            'clientes' => self::clientes($empresaId),
        ];
    }

    public static function integracoes(?int $empresaId = null): array
    {
        if (! CachedSchema::hasTable('financeiro_gateway_integracoes')) {
            return [];
        }

        $query = DB::table('financeiro_gateway_integracoes')->select('id', 'empresa_id', 'gateway', 'nome', 'ambiente', 'status', 'ultima_sincronizacao_em', 'updated_at')->orderBy('gateway');
        self::queryPorEmpresa($query, $empresaId);

        return $query->get()->map(fn ($row): array => (array) $row)->values()->all();
    }

    public static function fluxoProximosDias(?int $empresaId = null): array
    {
        if (! CachedSchema::hasTable('financeiro_cobrancas')) {
            return [];
        }

        $inicio = Carbon::today();
        $fim = Carbon::today()->addDays(30);

        $query = DB::table('financeiro_cobrancas')
            ->selectRaw('DATE(vencimento) as dia, SUM(valor) as total, COUNT(*) as quantidade')
            ->whereIn('status', ['aberta', 'vencida'])
            ->whereBetween('vencimento', [$inicio->toDateString(), $fim->toDateString()])
            ->groupBy('dia')
            ->orderBy('dia');

        self::queryPorEmpresa($query, $empresaId);

        return $query->get()->map(fn ($row): array => [
            'dia' => Carbon::parse($row->dia)->format('d/m'),
            'total' => self::money($row->total),
            'quantidade' => (int) $row->quantidade,
        ])->values()->all();
    }

    public static function marcarVencidas(?int $empresaId = null): void
    {
        if (! CachedSchema::hasTable('financeiro_cobrancas')) {
            return;
        }

        $query = DB::table('financeiro_cobrancas')
            ->where('status', 'aberta')
            ->whereDate('vencimento', '<', Carbon::today()->toDateString());

        self::queryPorEmpresa($query, $empresaId);
        $query->update(['status' => 'vencida', 'updated_at' => now()]);
    }


    protected static function likeTerm(?string $value): string
    {
        $value = trim((string) $value);
        $value = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);

        return '%' . $value . '%';
    }

    public static function money($value): string
    {
        return 'R$ ' . number_format((float) $value, 2, ',', '.');
    }

    public static function formatarData($data): string
    {
        return $data ? Carbon::parse($data)->format('d/m/Y') : '-';
    }

    public static function formatarCobranca(array $row): array
    {
        $row['valor_formatado'] = self::money($row['valor'] ?? 0);
        $row['vencimento_formatado'] = self::formatarData($row['vencimento'] ?? null);
        $row['pago_em_formatado'] = self::formatarData($row['pago_em'] ?? null);
        $row['status_label'] = self::statusCobrancaLabel($row['status'] ?? 'aberta');
        $row['status_tone'] = self::statusTone($row['status'] ?? 'aberta');
        return $row;
    }

    public static function formatarAssinatura(array $row): array
    {
        $row['valor_formatado'] = self::money($row['valor'] ?? 0);
        $row['proxima_cobranca_formatada'] = self::formatarData($row['proxima_cobranca_em'] ?? null);
        $row['status_tone'] = match ($row['status'] ?? 'ativa') {
            'ativa' => 'ok',
            'pausada' => 'warning',
            'cancelada' => 'danger',
            default => 'info',
        };
        $row['ciclo_label'] = match ($row['ciclo'] ?? 'mensal') {
            'semanal' => 'Semanal',
            'quinzenal' => 'Quinzenal',
            'trimestral' => 'Trimestral',
            'semestral' => 'Semestral',
            'anual' => 'Anual',
            default => 'Mensal',
        };
        return $row;
    }

    public static function statusCobrancaLabel(string $status): string
    {
        return match ($status) {
            'aberta' => 'Aberta',
            'vencida' => 'Vencida',
            'paga' => 'Paga',
            'cancelada' => 'Cancelada',
            default => ucfirst($status),
        };
    }

    public static function statusTone(string $status): string
    {
        return match ($status) {
            'paga' => 'ok',
            'vencida' => 'danger',
            'cancelada' => 'muted',
            default => 'warning',
        };
    }

    public static function somarCiclo(string $ciclo, ?string $base = null): string
    {
        $data = $base ? Carbon::parse($base) : Carbon::today();

        return match ($ciclo) {
            'semanal' => $data->addWeek()->toDateString(),
            'quinzenal' => $data->addDays(15)->toDateString(),
            'trimestral' => $data->addMonthsNoOverflow(3)->toDateString(),
            'semestral' => $data->addMonthsNoOverflow(6)->toDateString(),
            'anual' => $data->addYearNoOverflow()->toDateString(),
            default => $data->addMonthNoOverflow()->toDateString(),
        };
    }
}
