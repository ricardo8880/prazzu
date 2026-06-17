<?php

namespace App\Support;

use App\Models\Atendimento;
use Illuminate\Support\Facades\DB;

class AtendimentoClienteResolver
{
    public static function dadosPorAtendimento(Atendimento $atendimento): array
    {
        return self::dadosPorEmpresaECliente(
            $atendimento->empresa_id ? (int) $atendimento->empresa_id : null,
            $atendimento->crm_cliente_id ? (int) $atendimento->crm_cliente_id : null,
        );
    }

    public static function dadosPorEmpresaECliente(?int $empresaId, ?int $clienteId = null): array
    {
        $cliente = self::cliente($clienteId, $empresaId);
        $empresa = self::empresa($empresaId);

        return [
            'nome' => self::firstFilled([
                self::value($empresa, 'nome_fantasia'),
                self::value($empresa, 'razao_social'),
                self::value($empresa, 'responsavel_nome'),
                self::value($empresa, 'crm_contato_nome'),
                $clienteId ? 'Cliente #' . $clienteId : null,
                $empresaId ? 'Empresa #' . $empresaId : null,
            ]),
            'email' => self::firstValidEmail([
                self::value($empresa, 'crm_contato_email'),
                self::value($empresa, 'email'),
            ]),
            'situacao' => self::value($cliente, 'situacao'),
            'risco_churn' => self::value($cliente, 'risco_churn'),
            'empresa' => $empresa,
            'cliente' => $cliente,
        ];
    }

    public static function nomePorAtendimento(Atendimento $atendimento): ?string
    {
        return self::dadosPorAtendimento($atendimento)['nome'];
    }

    public static function emailPorAtendimento(Atendimento $atendimento): ?string
    {
        return self::dadosPorAtendimento($atendimento)['email'];
    }

    private static function empresa(?int $empresaId): ?object
    {
        if (! $empresaId || ! CachedSchema::hasTable('empresas')) {
            return null;
        }

        return DB::table('empresas')->where('id', $empresaId)->first();
    }

    private static function cliente(?int $clienteId, ?int $empresaId): ?object
    {
        if (! $clienteId || ! CachedSchema::hasTable('crm_clientes')) {
            return null;
        }

        $query = DB::table('crm_clientes')->where('id', $clienteId);

        if ($empresaId && CachedSchema::hasColumn('crm_clientes', 'empresa_id')) {
            $query->where('empresa_id', $empresaId);
        }

        return $query->first();
    }

    private static function value(?object $row, string $field): ?string
    {
        if (! $row || ! property_exists($row, $field)) {
            return null;
        }

        $value = trim((string) $row->{$field});

        return $value !== '' ? $value : null;
    }

    private static function firstFilled(array $values): ?string
    {
        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private static function firstValidEmail(array $values): ?string
    {
        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return $value;
            }
        }

        return null;
    }
}
