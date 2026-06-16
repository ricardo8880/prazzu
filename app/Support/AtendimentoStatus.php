<?php

namespace App\Support;

final class AtendimentoStatus
{
    public const ABERTO = 'aberto';
    public const EM_ANDAMENTO = 'em_andamento';
    public const AGUARDANDO_CLIENTE = 'aguardando_cliente';
    public const AGUARDANDO_SUPORTE = 'aguardando_suporte';
    public const RESOLVIDO = 'resolvido';
    public const FECHADO = 'fechado';
    public const CANCELADO = 'cancelado';

    public const OPTIONS = [
        self::ABERTO => ['label' => 'Aberto', 'tone' => 'info'],
        self::EM_ANDAMENTO => ['label' => 'Em andamento', 'tone' => 'primary'],
        self::AGUARDANDO_CLIENTE => ['label' => 'Aguardando cliente', 'tone' => 'warning'],
        self::AGUARDANDO_SUPORTE => ['label' => 'Aguardando suporte', 'tone' => 'danger'],
        self::RESOLVIDO => ['label' => 'Resolvido', 'tone' => 'success'],
        self::FECHADO => ['label' => 'Fechado', 'tone' => 'neutral'],
        self::CANCELADO => ['label' => 'Cancelado', 'tone' => 'danger'],
    ];

    public const ACTIVE = [
        self::ABERTO,
        self::EM_ANDAMENTO,
        self::AGUARDANDO_CLIENTE,
        self::AGUARDANDO_SUPORTE,
    ];

    public const CLOSED = [
        self::RESOLVIDO,
        self::FECHADO,
        self::CANCELADO,
    ];

    public const WAITING_CLIENT = [
        self::AGUARDANDO_CLIENTE,
    ];

    public const WAITING_OFFICE = [
        self::ABERTO,
        self::EM_ANDAMENTO,
        self::AGUARDANDO_SUPORTE,
    ];

    public static function exists(?string $status): bool
    {
        return is_string($status) && array_key_exists($status, self::OPTIONS);
    }

    public static function label(?string $status): string
    {
        $status = self::normalize($status);

        return self::OPTIONS[$status]['label'] ?? ucfirst(str_replace('_', ' ', (string) $status));
    }

    public static function tone(?string $status): string
    {
        $status = self::normalize($status);

        return self::OPTIONS[$status]['tone'] ?? 'neutral';
    }

    public static function normalize(?string $status, string $fallback = self::ABERTO): string
    {
        $status = trim((string) $status);

        if ($status === '') {
            return $fallback;
        }

        return match ($status) {
            'novo', 'nova', 'aberta' => self::ABERTO,
            'andamento', 'em_atendimento', 'aguardando_equipe' => self::EM_ANDAMENTO,
            'pendente_cliente', 'em_aprovacao' => self::AGUARDANDO_CLIENTE,
            'aguardando_escritorio', 'aguardando_equipe_interna' => self::AGUARDANDO_SUPORTE,
            'concluido', 'concluida', 'finalizado', 'finalizada' => self::RESOLVIDO,
            'encerrado', 'encerrada' => self::FECHADO,
            'cancelada' => self::CANCELADO,
            default => self::exists($status) ? $status : $fallback,
        };
    }

    public static function isClosed(?string $status): bool
    {
        return in_array(self::normalize($status), self::CLOSED, true);
    }

    public static function isActive(?string $status): bool
    {
        return in_array(self::normalize($status), self::ACTIVE, true);
    }

    public static function fromPortalStatus(?string $status): string
    {
        return self::normalize($status, self::ABERTO);
    }

    public static function toPortalStatus(?string $status): string
    {
        return match (self::normalize($status)) {
            self::RESOLVIDO, self::FECHADO => 'concluido',
            self::CANCELADO => 'cancelado',
            self::AGUARDANDO_CLIENTE => 'aguardando_cliente',
            self::AGUARDANDO_SUPORTE, self::EM_ANDAMENTO => 'em_andamento',
            default => 'aberto',
        };
    }
}
