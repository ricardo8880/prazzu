<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Str;

class ComplianceInternoFormatter
{
    private const STATUS_LABELS = [
        'pendente' => 'Pendente',
        'aprovado' => 'Aprovado',
        'reprovado' => 'Reprovado',
        'aberto' => 'Aberto',
        'em_andamento' => 'Em andamento',
        'em_aprovacao' => 'Em aprovação',
        'concluido' => 'Concluído',
        'cancelado' => 'Cancelado',
        'assinado' => 'Assinado',
        'nao_assinado' => 'Pendente de assinatura',
        'interno' => 'Uso interno',
        'visivel' => 'Visível para o cliente',
    ];

    private const PRIORITY_LABELS = [
        'baixa' => 'Baixa prioridade',
        'media' => 'Prioridade média',
        'alta' => 'Alta prioridade',
        'urgente' => 'Urgente',
    ];

    private const DOCUMENT_TYPE_LABELS = [
        'wiki' => 'Wiki interna',
        'ata' => 'Ata de reunião',
        'documento' => 'Documento',
        'link' => 'Link útil',
    ];

    private const TONE_BY_STATUS = [
        'pendente' => 'warning',
        'aberto' => 'warning',
        'em_andamento' => 'info',
        'em_aprovacao' => 'warning',
        'aprovado' => 'ok',
        'assinado' => 'ok',
        'visivel' => 'ok',
        'concluido' => 'ok',
        'reprovado' => 'danger',
        'cancelado' => 'danger',
        'nao_assinado' => 'warning',
        'interno' => 'info',
    ];

    public static function status(?string $status, string $fallback = 'Pendente'): string
    {
        $key = self::normalizeKey($status);

        return self::STATUS_LABELS[$key] ?? self::humanize($status, $fallback);
    }

    public static function priority(?string $priority, string $fallback = 'Prioridade média'): string
    {
        $key = self::normalizeKey($priority);

        return self::PRIORITY_LABELS[$key] ?? self::humanize($priority, $fallback);
    }

    public static function documentType(?string $type, string $fallback = 'Documento'): string
    {
        $key = self::normalizeKey($type);

        return self::DOCUMENT_TYPE_LABELS[$key] ?? self::humanize($type, $fallback);
    }

    public static function visibility(bool|int|null $visible): string
    {
        return (bool) $visible ? self::STATUS_LABELS['visivel'] : self::STATUS_LABELS['interno'];
    }

    public static function signatureStatus(bool $signed): string
    {
        return $signed ? self::STATUS_LABELS['assinado'] : self::STATUS_LABELS['nao_assinado'];
    }

    public static function toneForStatus(?string $status, string $default = 'info'): string
    {
        $key = self::normalizeKey($status);

        return self::TONE_BY_STATUS[$key] ?? $default;
    }

    public static function toneForPriority(?string $priority, string $default = 'info'): string
    {
        return in_array(self::normalizeKey($priority), ['alta', 'urgente'], true) ? 'warning' : $default;
    }

    public static function title(?string $title, string $fallback): string
    {
        $title = trim((string) $title);

        return $title !== '' ? $title : $fallback;
    }

    public static function person(?string $name, ?string $email = null, string $fallback = 'Responsável não informado'): string
    {
        $name = trim((string) $name);
        $email = trim((string) $email);

        if ($name !== '' && $email !== '') {
            return $name . ' (' . $email . ')';
        }

        if ($name !== '') {
            return $name;
        }

        if ($email !== '') {
            return $email;
        }

        return $fallback;
    }

    public static function company(?string $company): string
    {
        $company = trim((string) $company);

        return $company !== '' ? $company : 'Empresa não informada';
    }

    public static function description(?string $description, string $fallback, int $limit = 140): string
    {
        $text = trim(strip_tags((string) $description));

        if ($text === '') {
            $text = $fallback;
        }

        return Str::limit($text, $limit, '...');
    }

    public static function date($date, string $fallback = 'Sem data'): string
    {
        if (blank($date)) {
            return $fallback;
        }

        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return $fallback;
        }
    }

    public static function metaTags(array $items): array
    {
        return collect($items)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    public static function meta(array $items): string
    {
        return implode(' · ', self::metaTags($items));
    }

    public static function searchable(array $items): string
    {
        return collect($items)
            ->map(fn ($item) => self::humanize((string) $item, ''))
            ->implode(' ');
    }


    public static function key(?string $value): string
    {
        return self::normalizeKey($value);
    }

    public static function workflowWeight(string $kind, ?string $status = null, ?string $priority = null, $date = null): int
    {
        $statusKey = self::normalizeKey($status);
        $priorityKey = self::normalizeKey($priority);

        $weight = match ($kind) {
            'approval' => 30,
            'signature' => 35,
            'request' => 45,
            'document' => 85,
            default => 70,
        };

        if (in_array($priorityKey, ['urgente', 'alta'], true)) {
            $weight -= $priorityKey === 'urgente' ? 25 : 15;
        }

        if (in_array($statusKey, ['pendente', 'nao_assinado', 'aberto', 'em_aprovacao'], true)) {
            $weight -= 10;
        }

        if (in_array($statusKey, ['concluido', 'aprovado', 'assinado', 'visivel'], true)) {
            $weight += 25;
        }

        if (in_array($statusKey, ['cancelado', 'reprovado'], true)) {
            $weight += 30;
        }

        if (! blank($date)) {
            try {
                $days = Carbon::parse($date)->diffInDays(now(), false);
                $weight += max(0, min(20, $days));
            } catch (\Throwable) {
                // Mantém o peso base caso a data não seja interpretável.
            }
        }

        return max(1, $weight);
    }


    public static function urgency(string $kind, ?string $status = null, ?string $priority = null, $weight = null): array
    {
        $statusKey = self::normalizeKey($status);
        $priorityKey = self::normalizeKey($priority);
        $weight = is_numeric($weight) ? (int) $weight : 70;

        if ($priorityKey === 'urgente') {
            return [
                'label' => 'Urgente',
                'tone' => 'danger',
                'rank' => 10,
                'message' => 'Trate este registro antes dos demais.',
            ];
        }

        if ($kind === 'signature' && $statusKey === 'nao_assinado') {
            return [
                'label' => 'Aguardando você',
                'tone' => 'danger',
                'rank' => 15,
                'message' => 'Assinatura ainda não coletada.',
            ];
        }

        if ($kind === 'approval' && in_array($statusKey, ['pendente', 'em_aprovacao'], true)) {
            return [
                'label' => 'Ação necessária',
                'tone' => 'danger',
                'rank' => 20,
                'message' => 'Precisa de decisão para o fluxo avançar.',
            ];
        }

        if ($priorityKey === 'alta' || in_array($statusKey, ['pendente', 'aberto', 'em_aprovacao'], true)) {
            return [
                'label' => 'Atenção',
                'tone' => 'warning',
                'rank' => 30,
                'message' => 'Merece acompanhamento próximo.',
            ];
        }

        if (in_array($statusKey, ['em_andamento', 'interno'], true) || $weight <= 60) {
            return [
                'label' => 'Acompanhar',
                'tone' => 'info',
                'rank' => 50,
                'message' => 'Não exige decisão imediata, mas precisa de visibilidade.',
            ];
        }

        return [
            'label' => 'Sem ação necessária',
            'tone' => 'ok',
            'rank' => 80,
            'message' => 'Registro disponível para consulta ou evidência.',
        ];
    }

    public static function nextStep(string $kind, ?string $status = null, ?string $priority = null): string
    {
        $statusKey = self::normalizeKey($status);
        $priorityKey = self::normalizeKey($priority);

        if ($kind === 'approval') {
            return match ($statusKey) {
                'pendente', 'em_aprovacao' => 'Próximo passo: revisar e registrar a decisão.',
                'aprovado' => 'Fluxo aprovado. Use como evidência se necessário.',
                'reprovado' => 'Fluxo reprovado. Verifique a observação antes de reabrir.',
                default => 'Acompanhe o status desta aprovação interna.',
            };
        }

        if ($kind === 'signature') {
            return $statusKey === 'assinado'
                ? 'Assinatura coletada e disponível como evidência.'
                : 'Próximo passo: cobrar ou coletar a assinatura pendente.';
        }

        if ($kind === 'request') {
            if (in_array($priorityKey, ['urgente', 'alta'], true)) {
                return 'Prioridade alta: tratar antes das demais solicitações.';
            }

            return match ($statusKey) {
                'em_andamento' => 'Acompanhe o andamento e atualize quando houver resposta.',
                'em_aprovacao' => 'Aguardando validação antes da conclusão.',
                'aberto', 'pendente' => 'Próximo passo: definir responsável e primeira ação.',
                default => 'Acompanhe esta solicitação até a conclusão.',
            };
        }

        if ($kind === 'document') {
            return $statusKey === 'visivel'
                ? 'Documento disponível para consulta dos usuários permitidos.'
                : 'Documento interno: revise antes de compartilhar externamente.';
        }

        return 'Verifique os detalhes para definir a próxima ação.';
    }

    public static function humanize(?string $value, string $fallback = 'Não informado'): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $fallback;
        }

        $value = str_replace(['_', '-'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?: $value;

        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    private static function normalizeKey(?string $value): string
    {
        return Str::of((string) $value)
            ->trim()
            ->lower()
            ->replace('-', '_')
            ->replace(' ', '_')
            ->toString();
    }
}
