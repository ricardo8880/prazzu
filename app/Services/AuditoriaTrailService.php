<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class AuditoriaTrailService
{
    public static function acaoCritica(
        string $evento,
        array $dados = [],
        ?Model $auditable = null,
        ?int $empresaId = null,
        ?int $userId = null,
        string $nivel = 'info'
    ): void {
        AuditoriaManualService::registrarEvento(
            $evento,
            self::normalizarPayload($evento, $dados, 'acao_critica'),
            $auditable,
            $empresaId,
            $userId,
            $nivel
        );
    }

    public static function portalCliente(
        string $evento,
        array $dados = [],
        ?Model $auditable = null,
        ?int $empresaId = null,
        ?int $userId = null,
        string $nivel = 'info'
    ): void {
        AuditoriaManualService::registrarEvento(
            $evento,
            self::normalizarPayload($evento, $dados, 'portal_cliente'),
            $auditable,
            $empresaId,
            $userId,
            $nivel
        );
    }

    public static function documento(
        string $evento,
        array $dados = [],
        ?Model $auditable = null,
        ?int $empresaId = null,
        ?int $userId = null,
        string $nivel = 'info'
    ): void {
        AuditoriaManualService::registrarEvento(
            $evento,
            self::normalizarPayload($evento, $dados, 'documento'),
            $auditable,
            $empresaId,
            $userId,
            $nivel
        );
    }

    public static function financeiro(
        string $evento,
        array $dados = [],
        ?Model $auditable = null,
        ?int $empresaId = null,
        ?int $userId = null,
        string $nivel = 'info'
    ): void {
        AuditoriaManualService::registrarEvento(
            $evento,
            self::normalizarPayload($evento, $dados, 'financeiro'),
            $auditable,
            $empresaId,
            $userId,
            $nivel
        );
    }

    protected static function normalizarPayload(string $evento, array $dados, string $dominio): array
    {
        return [
            'dominio' => $dominio,
            'evento' => $evento,
            'origem' => 'prazzu_app',
            'dados' => $dados,
        ];
    }
}
