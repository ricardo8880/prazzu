<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AtendimentosData
{
    public const STATUS = [
        'aberto' => ['label' => 'Aberto', 'tone' => 'info'],
        'em_andamento' => ['label' => 'Em andamento', 'tone' => 'primary'],
        'aguardando_cliente' => ['label' => 'Aguardando cliente', 'tone' => 'warning'],
        'aguardando_suporte' => ['label' => 'Aguardando suporte', 'tone' => 'danger'],
        'resolvido' => ['label' => 'Resolvido', 'tone' => 'success'],
        'fechado' => ['label' => 'Fechado', 'tone' => 'neutral'],
        'cancelado' => ['label' => 'Cancelado', 'tone' => 'danger'],
    ];

    public const PRIORIDADES = [
        'baixa' => ['label' => 'Baixa', 'tone' => 'neutral', 'sla' => 72],
        'media' => ['label' => 'Média', 'tone' => 'info', 'sla' => 48],
        'alta' => ['label' => 'Alta', 'tone' => 'warning', 'sla' => 24],
        'urgente' => ['label' => 'Urgente', 'tone' => 'danger', 'sla' => 8],
    ];

    public const SLA_OPTIONS = [
        'todos' => 'Todos',
        'vencidos' => 'SLA vencido',
        'vence_hoje' => 'Vence hoje',
        'sem_sla' => 'Sem SLA',
    ];

    public const AGUARDANDO_OPTIONS = [
        'todos' => 'Todos',
        'cliente' => 'Cliente',
        'escritorio' => 'Escritório',
        'concluido' => 'Concluído',
    ];

    public static function data(array $filters = []): array
    {
        if (! CachedSchema::hasTable('atendimentos')) {
            return self::emptyData(true);
        }

        $user = auth()->user();
        $query = self::baseQuery($user);

        self::applyFilters($query, $filters, $user);
        self::applySort($query, (string) ($filters['sort'] ?? 'recentes'));

        $rows = $query->limit(120)->get();
        $ids = $rows->pluck('id')->map(fn ($id) => (int) $id)->all();

        $interacoesCount = empty($ids) || ! CachedSchema::hasTable('atendimento_interacoes')
            ? collect()
            : DB::table('atendimento_interacoes')
                ->select('atendimento_id', DB::raw('COUNT(*) as total'))
                ->whereIn('atendimento_id', $ids)
                ->groupBy('atendimento_id')
                ->pluck('total', 'atendimento_id');

        $atendimentos = $rows
            ->map(fn ($row) => self::formatAtendimento($row, (int) ($interacoesCount[$row->id] ?? 0)))
            ->values()
            ->all();

        return [
            'ready' => true,
            'summary' => self::summary($user),
            'statusBoard' => self::statusBoard($user),
            'prioridadeResumo' => self::prioridadeResumo($user),
            'atendimentos' => $atendimentos,
            'empresas' => self::empresasDisponiveis($user),
            'clientes' => self::clientesDisponiveis($user),
            'responsaveis' => self::responsaveisDisponiveis($user),
            'statusOptions' => self::STATUS,
            'prioridadeOptions' => self::PRIORIDADES,
            'slaOptions' => self::SLA_OPTIONS,
            'aguardandoOptions' => self::AGUARDANDO_OPTIONS,
        ];
    }

    public static function findFormatted(int $atendimentoId, ?User $user = null): ?array
    {
        if (! CachedSchema::hasTable('atendimentos')) {
            return null;
        }

        $user ??= auth()->user();
        $row = self::baseQuery($user)->where('a.id', $atendimentoId)->first();
        if (! $row) {
            return null;
        }

        $interacoesCount = CachedSchema::hasTable('atendimento_interacoes')
            ? (int) DB::table('atendimento_interacoes')->where('atendimento_id', $atendimentoId)->count()
            : 0;

        return self::formatAtendimento($row, $interacoesCount);
    }

    public static function timeline(int $atendimentoId): array
    {
        if (! CachedSchema::hasTable('atendimento_interacoes')) {
            return [];
        }

        return DB::table('atendimento_interacoes as ai')
            ->leftJoin('users as u', 'u.id', '=', 'ai.user_id')
            ->where('ai.atendimento_id', $atendimentoId)
            ->orderBy('ai.created_at')
            ->limit(80)
            ->select('ai.*', 'u.name as usuario_nome', 'u.email as usuario_email')
            ->get()
            ->map(function ($row) {
                $metadata = self::metadataArray($row->metadata ?? null);
                $origem = (string) ($row->origem ?? 'sistema');
                $usuario = $row->usuario_nome ?: ($row->usuario_email ?: 'Sistema');

                if (in_array($origem, ['cliente', 'portal', 'publico'], true)) {
                    $usuario = (string) ($metadata['portal_cliente_nome'] ?? $metadata['cliente_nome'] ?? 'Cliente');
                } elseif (in_array($origem, ['suporte', 'interno'], true)) {
                    $usuario = $row->usuario_nome ?: ($row->usuario_email ?: ($metadata['suporte_nome'] ?? 'Suporte'));
                }

                $anexos = collect($metadata['anexos'] ?? [])
                    ->filter(fn ($anexo) => is_array($anexo) && ! empty($anexo['caminho']))
                    ->map(function (array $anexo): array {
                        $caminho = ltrim((string) ($anexo['caminho'] ?? ''), '/');
                        $tamanho = (int) ($anexo['tamanho'] ?? 0);

                        return [
                            'nome_original' => (string) ($anexo['nome_original'] ?? basename($caminho)),
                            'mime' => (string) ($anexo['mime'] ?? 'application/octet-stream'),
                            'extensao' => strtoupper((string) ($anexo['extensao'] ?? pathinfo($caminho, PATHINFO_EXTENSION) ?: 'ARQ')),
                            'tamanho_label' => self::tamanhoArquivo($tamanho),
                            'hash' => sha1($caminho),
                        ];
                    })
                    ->values()
                    ->all();

                $tipo = (string) $row->tipo;
                $mensagem = (string) ($row->mensagem ?? '');
                $operacional = self::timelineOperacionalMeta($tipo, $origem, $mensagem);

                return [
                    'id' => (int) $row->id,
                    'origem' => $origem,
                    'tipo' => $tipo,
                    'tipo_label' => self::tipoInteracaoLabel($tipo),
                    'mensagem' => $row->mensagem,
                    'usuario' => $usuario,
                    'created_at' => $row->created_at ? Carbon::parse($row->created_at)->format('d/m/Y H:i') : '-',
                    'anexos' => $anexos,
                    'operacional_titulo' => $operacional['titulo'],
                    'operacional_detalhe' => $operacional['detalhe'],
                    'operacional_icon' => $operacional['icon'],
                    'operacional_tone' => $operacional['tone'],
                    'operacional_actor' => $usuario,
                ];
            })->all();
    }



    private static function tamanhoArquivo(int $bytes): string
    {
        if ($bytes <= 0) {
            return 'Tamanho indisponível';
        }

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1, ',', '.') . ' KB';
        }

        return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
    }

    private static function metadataArray(mixed $metadata): array
    {
        if (empty($metadata)) {
            return [];
        }

        if (is_array($metadata)) {
            return $metadata;
        }

        try {
            $decoded = json_decode((string) $metadata, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable) {
            return [];
        }
    }

    public static function empresasDisponiveis(?User $user = null): array
    {
        if (! CachedSchema::hasTable('empresas')) {
            return [];
        }

        $user ??= auth()->user();
        $query = DB::table('empresas')->select('id', 'razao_social', 'nome_fantasia', 'email')->orderBy('nome_fantasia')->orderBy('razao_social');
        self::applyEmpresaScope($query, $user);

        return $query->get()->map(fn ($empresa) => [
            'id' => (int) $empresa->id,
            'nome' => $empresa->nome_fantasia ?: $empresa->razao_social ?: 'Empresa #' . $empresa->id,
            'email' => $empresa->email,
        ])->all();
    }

    public static function clientesDisponiveis(?User $user = null): array
    {
        if (! CachedSchema::hasTable('crm_clientes')) {
            return [];
        }

        $user ??= auth()->user();
        $query = DB::table('crm_clientes as cc')
            ->join('empresas as e', 'e.id', '=', 'cc.empresa_id')
            ->select('cc.id', 'cc.empresa_id', 'cc.situacao', 'cc.risco_churn', 'e.razao_social', 'e.nome_fantasia', 'e.email')
            ->orderBy('e.nome_fantasia')
            ->orderBy('e.razao_social');
        self::applyTenantScope($query, $user, 'cc.empresa_id');

        return $query->get()->map(fn ($cliente) => [
            'id' => (int) $cliente->id,
            'empresa_id' => (int) $cliente->empresa_id,
            'nome' => $cliente->nome_fantasia ?: $cliente->razao_social ?: 'Cliente #' . $cliente->id,
            'email' => $cliente->email,
            'situacao' => $cliente->situacao,
            'risco' => $cliente->risco_churn,
        ])->all();
    }

    public static function responsaveisDisponiveis(?User $user = null): array
    {
        if (! CachedSchema::hasTable('users')) {
            return [];
        }

        $user ??= auth()->user();
        $query = DB::table('users')->select('id', 'name', 'email', 'empresa_id')->orderBy('name');
        if ($user && ! $user->isSuperAdmin()) {
            $query->where('empresa_id', $user->empresa_id);
        }

        return $query->get()->map(fn ($row) => [
            'id' => (int) $row->id,
            'nome' => $row->name ?: $row->email,
            'email' => $row->email,
        ])->all();
    }

    public static function usuarioPodeAcessarEmpresa(int $empresaId, ?User $user = null): bool
    {
        $user ??= auth()->user();
        return (bool) ($user?->isSuperAdmin() || ((int) $user?->empresa_id === $empresaId));
    }

    public static function statusLabel(string $status): string
    {
        return self::STATUS[$status]['label'] ?? ucfirst(str_replace('_', ' ', $status));
    }

    public static function prioridadeLabel(string $prioridade): string
    {
        return self::PRIORIDADES[$prioridade]['label'] ?? ucfirst($prioridade);
    }

    public static function origemLabel(string $origem): string
    {
        return match ($origem) {
            'portal' => 'Portal do Cliente',
            'whatsapp' => 'WhatsApp',
            'email' => 'E-mail',
            'telefone' => 'Telefone',
            default => 'Manual',
        };
    }

    public static function canalLabel(string $canal): string
    {
        return match ($canal) {
            'portal' => 'Portal',
            'whatsapp' => 'WhatsApp',
            'email' => 'E-mail',
            'telefone' => 'Telefone',
            default => 'Interno',
        };
    }

    public static function tipoInteracaoLabel(string $tipo): string
    {
        return match ($tipo) {
            'abertura' => 'Abertura',
            'comentario' => 'Comentário',
            'alteracao' => 'Alteração',
            'responsavel' => 'Responsável',
            'resposta' => 'Resposta',
            'anexo' => 'Anexo',
            'resolucao' => 'Resolução',
            'reabertura' => 'Reabertura',
            'sistema' => 'Sistema',
            default => ucfirst(str_replace('_', ' ', $tipo)),
        };
    }

    private static function timelineOperacionalMeta(string $tipo, string $origem, string $mensagem = ''): array
    {
        $isCliente = in_array($origem, ['cliente', 'portal', 'publico'], true);
        $detalhe = trim($mensagem) !== '' ? Str::limit(trim($mensagem), 120) : '';

        if ($isCliente && in_array($tipo, ['comentario', 'resposta', 'mensagem'], true)) {
            return [
                'titulo' => 'Cliente respondeu',
                'detalhe' => $detalhe,
                'icon' => 'bi-person-lines-fill',
                'tone' => 'warning',
            ];
        }

        return match ($tipo) {
            'abertura' => [
                'titulo' => 'Atendimento criado',
                'detalhe' => $detalhe,
                'icon' => 'bi-plus-circle',
                'tone' => 'primary',
            ],
            'responsavel' => [
                'titulo' => 'Responsável atualizado',
                'detalhe' => $detalhe,
                'icon' => 'bi-person-check',
                'tone' => 'info',
            ],
            'alteracao' => [
                'titulo' => 'Atendimento atualizado',
                'detalhe' => $detalhe,
                'icon' => 'bi-pencil-square',
                'tone' => 'warning',
            ],
            'resposta' => [
                'titulo' => 'Resposta enviada ao cliente',
                'detalhe' => $detalhe,
                'icon' => 'bi-send',
                'tone' => 'success',
            ],
            'comentario' => [
                'titulo' => 'Comentário registrado',
                'detalhe' => $detalhe,
                'icon' => 'bi-chat-left-text',
                'tone' => 'neutral',
            ],
            'anexo' => [
                'titulo' => 'Anexo registrado',
                'detalhe' => $detalhe,
                'icon' => 'bi-paperclip',
                'tone' => 'info',
            ],
            'resolucao' => [
                'titulo' => 'Atendimento resolvido',
                'detalhe' => $detalhe,
                'icon' => 'bi-check2-circle',
                'tone' => 'success',
            ],
            'reabertura' => [
                'titulo' => 'Atendimento reaberto',
                'detalhe' => $detalhe,
                'icon' => 'bi-arrow-counterclockwise',
                'tone' => 'primary',
            ],
            default => [
                'titulo' => self::tipoInteracaoLabel($tipo),
                'detalhe' => $detalhe,
                'icon' => 'bi-activity',
                'tone' => 'neutral',
            ],
        };
    }

    public static function slaHorasPorPrioridade(string $prioridade): int
    {
        return (int) (self::PRIORIDADES[$prioridade]['sla'] ?? self::PRIORIDADES['media']['sla']);
    }

    private static function baseQuery(?User $user)
    {
        $query = DB::table('atendimentos as a')
            ->leftJoin('empresas as e', 'e.id', '=', 'a.empresa_id')
            ->leftJoin('users as u', 'u.id', '=', 'a.responsavel_id')
            ->leftJoin('crm_clientes as cc', 'cc.id', '=', 'a.crm_cliente_id')
            ->select([
                'a.*',
                DB::raw('COALESCE(e.nome_fantasia, e.razao_social, CONCAT("Empresa #", a.empresa_id)) as empresa_nome'),
                'e.email as empresa_email',
                'u.name as responsavel_nome',
                'u.email as responsavel_email',
                'cc.risco_churn as cliente_risco',
                'cc.situacao as cliente_situacao',
            ]);

        self::applyTenantScope($query, $user);

        return $query;
    }

    private static function applyFilters($query, array $filters, ?User $user): void
    {
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('a.titulo', 'like', "%{$search}%")
                    ->orWhere('a.descricao', 'like', "%{$search}%")
                    ->orWhere('e.razao_social', 'like', "%{$search}%")
                    ->orWhere('e.nome_fantasia', 'like', "%{$search}%")
                    ->orWhere('e.email', 'like', "%{$search}%")
                    ->orWhere('u.name', 'like', "%{$search}%")
                    ->orWhere('u.email', 'like', "%{$search}%");
            });
        }

        $status = (string) ($filters['status'] ?? 'todos');
        if ($status === 'ativos') {
            $query->whereIn('a.status', ['aberto', 'em_andamento', 'aguardando_cliente', 'aguardando_suporte']);
        } elseif ($status !== 'todos' && $status !== '') {
            $query->where('a.status', $status);
        }

        $aguardando = (string) ($filters['aguardando'] ?? 'todos');
        if ($aguardando === 'cliente') {
            $query->where('a.status', 'aguardando_cliente');
        } elseif ($aguardando === 'escritorio') {
            $query->whereIn('a.status', ['aberto', 'em_andamento', 'aguardando_suporte']);
        } elseif ($aguardando === 'concluido') {
            $query->whereIn('a.status', ['resolvido', 'fechado', 'cancelado']);
        }

        foreach (['prioridade', 'origem'] as $field) {
            $value = (string) ($filters[$field] ?? 'todos');
            if ($value !== 'todos' && $value !== '') {
                $query->where("a.{$field}", $value);
            }
        }

        $responsavel = (string) ($filters['responsavel'] ?? 'todos');
        if ($responsavel === 'sem_responsavel') {
            $query->whereNull('a.responsavel_id');
        } elseif (ctype_digit($responsavel)) {
            $query->where('a.responsavel_id', (int) $responsavel);
        }

        $empresaId = (int) ($filters['empresa_id'] ?? 0);
        if ($empresaId > 0 && self::usuarioPodeAcessarEmpresa($empresaId, $user)) {
            $query->where('a.empresa_id', $empresaId);
        }

        $sla = (string) ($filters['sla'] ?? 'todos');
        if ($sla === 'vencidos') {
            $query->whereNotIn('a.status', ['resolvido', 'fechado', 'cancelado'])
                ->whereNotNull('a.sla_limite_em')
                ->where('a.sla_limite_em', '<', now());
        } elseif ($sla === 'vence_hoje') {
            $query->whereNotIn('a.status', ['resolvido', 'fechado', 'cancelado'])
                ->whereNotNull('a.sla_limite_em')
                ->whereBetween('a.sla_limite_em', [now()->startOfDay(), now()->endOfDay()]);
        } elseif ($sla === 'sem_sla') {
            $query->whereNull('a.sla_limite_em');
        }
    }

    private static function applySort($query, string $sort): void
    {
        match ($sort) {
            'sla' => $query->orderByRaw('a.sla_limite_em IS NULL, a.sla_limite_em ASC')->orderByDesc('a.updated_at'),
            'prioridade' => $query->orderByRaw("FIELD(a.prioridade, 'urgente','alta','media','baixa')")->orderByRaw('a.sla_limite_em IS NULL, a.sla_limite_em ASC'),
            'cliente' => $query->orderBy('empresa_nome')->orderByDesc('a.updated_at'),
            default => $query->orderByDesc('a.updated_at')->orderByDesc('a.id'),
        };
    }

    private static function summary(?User $user): array
    {
        $base = DB::table('atendimentos');
        self::applyTenantScope($base, $user, 'empresa_id');
        $rows = $base->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');

        $sla = DB::table('atendimentos')
            ->whereNotIn('status', ['resolvido', 'fechado', 'cancelado'])
            ->whereNotNull('sla_limite_em')
            ->where('sla_limite_em', '<', now());
        self::applyTenantScope($sla, $user, 'empresa_id');

        $venceHoje = DB::table('atendimentos')
            ->whereNotIn('status', ['resolvido', 'fechado', 'cancelado'])
            ->whereNotNull('sla_limite_em')
            ->whereBetween('sla_limite_em', [now()->startOfDay(), now()->endOfDay()]);
        self::applyTenantScope($venceHoje, $user, 'empresa_id');

        $semResponsavel = DB::table('atendimentos')
            ->whereNotIn('status', ['resolvido', 'fechado', 'cancelado'])
            ->whereNull('responsavel_id');
        self::applyTenantScope($semResponsavel, $user, 'empresa_id');

        return [
            'total' => (int) $rows->sum(),
            'abertos' => (int) (($rows['aberto'] ?? 0) + ($rows['em_andamento'] ?? 0) + ($rows['aguardando_cliente'] ?? 0) + ($rows['aguardando_suporte'] ?? 0)),
            'aguardando_cliente' => (int) ($rows['aguardando_cliente'] ?? 0),
            'resolvidos' => (int) (($rows['resolvido'] ?? 0) + ($rows['fechado'] ?? 0)),
            'sla_vencido' => (int) $sla->count(),
            'vence_hoje' => (int) $venceHoje->count(),
            'sem_responsavel' => (int) $semResponsavel->count(),
        ];
    }

    private static function statusBoard(?User $user): array
    {
        $rows = DB::table('atendimentos')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status');
        self::applyTenantScope($rows, $user, 'empresa_id');
        $counts = $rows->pluck('total', 'status');

        return collect(self::STATUS)->map(fn ($meta, $key) => [
            'key' => $key,
            'label' => $meta['label'],
            'tone' => $meta['tone'],
            'total' => (int) ($counts[$key] ?? 0),
        ])->values()->all();
    }

    private static function prioridadeResumo(?User $user): array
    {
        $rows = DB::table('atendimentos')
            ->whereNotIn('status', ['resolvido', 'fechado', 'cancelado'])
            ->select('prioridade', DB::raw('COUNT(*) as total'))
            ->groupBy('prioridade');
        self::applyTenantScope($rows, $user, 'empresa_id');
        $counts = $rows->pluck('total', 'prioridade');

        return collect(self::PRIORIDADES)->map(fn ($meta, $key) => [
            'key' => $key,
            'label' => $meta['label'],
            'tone' => $meta['tone'],
            'sla' => $meta['sla'],
            'total' => (int) ($counts[$key] ?? 0),
        ])->values()->all();
    }

    private static function formatAtendimento(object $row, int $interacoesCount): array
    {
        $created = $row->created_at ? Carbon::parse($row->created_at) : null;
        $updated = $row->updated_at ? Carbon::parse($row->updated_at) : null;
        $sla = $row->sla_limite_em ? Carbon::parse($row->sla_limite_em) : null;
        $status = (string) $row->status;
        $prioridade = (string) $row->prioridade;
        $isClosed = in_array($status, ['resolvido', 'fechado', 'cancelado'], true);
        $isLate = (bool) ($sla && ! $isClosed && $sla->isPast());
        $slaDiff = $sla ? $sla->diffForHumans(null, true) : null;
        $aguardando = self::aguardandoMeta($status, $updated, $isClosed);

        return [
            'id' => (int) $row->id,
            'empresa_id' => (int) $row->empresa_id,
            'crm_cliente_id' => $row->crm_cliente_id ? (int) $row->crm_cliente_id : null,
            'portal_solicitacao_id' => $row->portal_solicitacao_id ? (int) $row->portal_solicitacao_id : null,
            'portal_mensagem_id' => $row->portal_mensagem_id ? (int) $row->portal_mensagem_id : null,
            'item_controle_id' => $row->item_controle_id ? (int) $row->item_controle_id : null,
            'empresa_nome' => $row->empresa_nome,
            'empresa_email' => $row->empresa_email,
            'titulo' => $row->titulo,
            'descricao' => $row->descricao,
            'status' => $status,
            'status_label' => self::statusLabel($status),
            'status_tone' => self::STATUS[$status]['tone'] ?? 'neutral',
            'aguardando_key' => $aguardando['key'],
            'aguardando_label' => $aguardando['label'],
            'aguardando_tone' => $aguardando['tone'],
            'tempo_aguardando' => $aguardando['tempo'],
            'tempo_aguardando_detalhe' => $aguardando['detalhe'],
            'prioridade' => $prioridade,
            'prioridade_label' => self::prioridadeLabel($prioridade),
            'prioridade_tone' => self::PRIORIDADES[$prioridade]['tone'] ?? 'neutral',
            'origem' => $row->origem ?: 'manual',
            'origem_label' => self::origemLabel((string) ($row->origem ?: 'manual')),
            'canal' => $row->canal ?: 'interno',
            'canal_label' => self::canalLabel((string) ($row->canal ?: 'interno')), 
            'responsavel_id' => $row->responsavel_id ? (int) $row->responsavel_id : null,
            'responsavel_nome' => $row->responsavel_nome ?: 'Sem responsável',
            'responsavel_email' => $row->responsavel_email,
            'created_at' => $created?->format('d/m/Y H:i') ?? '-',
            'updated_at' => $updated?->format('d/m/Y H:i') ?? '-',
            'tempo_aberto' => $created ? $created->diffForHumans(null, true) : '-',
            'sla_limite' => $sla?->format('d/m/Y H:i'),
            'sla_texto' => $sla ? ($isLate ? 'Venceu ' . $slaDiff : 'Vence ' . $slaDiff) : 'Sem SLA definido',
            'sla_vencido' => $isLate,
            'primeira_resposta_em' => $row->primeira_resposta_em ? Carbon::parse($row->primeira_resposta_em)->format('d/m/Y H:i') : null,
            'resolvido_em' => $row->resolvido_em ? Carbon::parse($row->resolvido_em)->format('d/m/Y H:i') : null,
            'fechado_em' => $row->fechado_em ? Carbon::parse($row->fechado_em)->format('d/m/Y H:i') : null,
            'cliente_situacao' => $row->cliente_situacao,
            'cliente_risco' => $row->cliente_risco,
            'interacoes_count' => $interacoesCount,
        ];
    }

    private static function aguardandoMeta(string $status, ?Carbon $updated, bool $isClosed): array
    {
        $map = match ($status) {
            'aguardando_cliente' => ['key' => 'cliente', 'label' => 'Cliente', 'tone' => 'warning'],
            'aguardando_suporte' => ['key' => 'escritorio', 'label' => 'Escritório', 'tone' => 'danger'],
            'aguardando_terceiro' => ['key' => 'terceiro', 'label' => 'Terceiro', 'tone' => 'info'],
            'resolvido', 'fechado', 'cancelado' => ['key' => 'concluido', 'label' => 'Concluído', 'tone' => 'success'],
            default => ['key' => 'escritorio', 'label' => 'Escritório', 'tone' => 'primary'],
        };

        $tempo = $updated ? $updated->diffForHumans(null, true) : '-';
        $prefixo = $isClosed ? 'Concluído há' : 'Aguardando há';

        return [
            'key' => $map['key'],
            'label' => $map['label'],
            'tone' => $map['tone'],
            'tempo' => $tempo,
            'detalhe' => $tempo === '-' ? '-' : $prefixo . ' ' . $tempo,
        ];
    }

    private static function emptyData(bool $missingTable = false): array
    {
        return [
            'ready' => ! $missingTable,
            'summary' => ['total' => 0, 'abertos' => 0, 'aguardando_cliente' => 0, 'resolvidos' => 0, 'sla_vencido' => 0, 'vence_hoje' => 0, 'sem_responsavel' => 0],
            'statusBoard' => collect(self::STATUS)->map(fn ($meta, $key) => ['key' => $key, 'label' => $meta['label'], 'tone' => $meta['tone'], 'total' => 0])->values()->all(),
            'prioridadeResumo' => collect(self::PRIORIDADES)->map(fn ($meta, $key) => ['key' => $key, 'label' => $meta['label'], 'tone' => $meta['tone'], 'sla' => $meta['sla'], 'total' => 0])->values()->all(),
            'atendimentos' => [],
            'empresas' => [],
            'clientes' => [],
            'responsaveis' => [],
            'statusOptions' => self::STATUS,
            'prioridadeOptions' => self::PRIORIDADES,
            'slaOptions' => self::SLA_OPTIONS,
            'aguardandoOptions' => self::AGUARDANDO_OPTIONS,
        ];
    }

    private static function applyTenantScope($query, ?User $user, string $column = 'a.empresa_id'): void
    {
        if ($user && ! $user->isSuperAdmin()) {
            $query->where($column, $user->empresa_id);
        }
    }

    private static function applyEmpresaScope($query, ?User $user): void
    {
        if ($user && ! $user->isSuperAdmin()) {
            $query->where('id', $user->empresa_id);
        }
    }
}
