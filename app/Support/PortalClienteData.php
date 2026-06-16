<?php

namespace App\Support;

use App\Models\PortalDocumento;
use App\Models\PortalMensagem;
use App\Models\PortalSolicitacao;
use App\Services\ItemControleStatusService;
use App\Support\PortalChatMessageContract;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PortalClienteData
{
    private const STATUS_CONCLUIDOS = ['concluido', 'concluida', 'concluído', 'concluída', 'finalizado', 'finalizada', 'cancelado', 'cancelada'];
    private const STATUS_VISIVEIS_CLIENTE = ['pronto_revisao', 'pronto_para_revisao', 'em_aprovacao', 'aprovacao', 'concluido', 'concluida'];

    public static function data(?int $empresaId = null, bool $somenteAtivos = true): array
    {
        $empresaId ??= self::empresaIdAtual();
        $items = self::items($empresaId);
        $visiveis = self::itemsVisiveisParaCliente($items);

        return [
            'empresa' => self::empresaAtual($empresaId),
            'empresas' => self::empresasDisponiveis(),
            'empresaId' => $empresaId,
            'portalLink' => self::portalLink($empresaId),
            'progress' => self::progresso($visiveis),
            'visibleItems' => array_slice($visiveis, 0, 20),
            'pendingActions' => self::pendenciasCliente($visiveis),
            'statusSummary' => self::resumoStatus($items, $visiveis),
            'calendar' => self::calendario($empresaId),
            'documents' => self::documentos($empresaId),
            'meetingNotes' => self::atas($empresaId),
            'supportQueue' => self::solicitacoes($empresaId),
            'chat' => self::mensagens($empresaId, $somenteAtivos),
            'nextDelivery' => self::proximaEntrega($items),
            'timeline' => self::timeline($items),
            'approvalHistory' => self::historicoAprovacoes($items),
            'supportForm' => [
                'prioridades' => ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'urgente' => 'Urgente'],
            ],
        ];
    }

    public static function empresaIdAtual(): ?int
    {
        $user = auth()->user();

        if (! CachedSchema::hasTable('empresas')) {
            return null;
        }

        if ($user && isset($user->empresa_id) && $user->empresa_id) {
            $empresaId = (int) $user->empresa_id;

            return self::usuarioPodeAcessarEmpresa($empresaId) ? $empresaId : null;
        }

        if (! $user || ! self::usuarioSuperAdmin($user)) {
            return null;
        }

        return DB::table('empresas')
            ->when(CachedSchema::hasColumn('empresas', 'ativo'), fn ($query) => $query->where('ativo', 1))
            ->orderByDesc(CachedSchema::hasColumn('empresas', 'updated_at') ? 'updated_at' : 'id')
            ->value('id');
    }

    public static function empresasDisponiveis(): array
    {
        if (! CachedSchema::hasTable('empresas')) {
            return [];
        }

        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $query = DB::table('empresas')
            ->select(['id', 'razao_social', 'nome_fantasia', 'email'])
            ->when(CachedSchema::hasColumn('empresas', 'ativo'), fn ($query) => $query->where('ativo', 1));

        if (! self::usuarioSuperAdmin($user)) {
            if (! isset($user->empresa_id) || ! $user->empresa_id) {
                return [];
            }

            $query->where('id', (int) $user->empresa_id);
        }

        return $query
            ->orderBy('nome_fantasia')
            ->orderBy('razao_social')
            ->get()
            ->map(fn ($empresa) => (array) $empresa)
            ->all();
    }

    public static function usuarioPodeAcessarEmpresa(?int $empresaId): bool
    {
        if (! $empresaId || ! CachedSchema::hasTable('empresas')) {
            return false;
        }

        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if (! self::empresaExisteAtiva($empresaId)) {
            return false;
        }

        if (self::usuarioSuperAdmin($user)) {
            return true;
        }

        return isset($user->empresa_id) && (int) $user->empresa_id === (int) $empresaId;
    }

    public static function empresaAtual(?int $empresaId): array
    {
        if (! $empresaId || ! CachedSchema::hasTable('empresas')) {
            return [];
        }

        $empresa = DB::table('empresas')->where('id', $empresaId)->first();

        return $empresa ? (array) $empresa : [];
    }

    public static function portalLink(?int $empresaId): ?string
    {
        if (! $empresaId || ! CachedSchema::hasTable('empresas') || ! CachedSchema::hasColumn('empresas', 'portal_token')) {
            return null;
        }

        $empresa = DB::table('empresas')->where('id', $empresaId)->first();

        if (! $empresa || ! self::portalEmpresaDisponivel($empresa)) {
            return null;
        }

        $token = trim((string) ($empresa->portal_token ?? ''));

        return self::tokenPortalValido($token) ? route('portal.cliente.show', ['token' => $token]) : null;
    }

    private static function portalEmpresaDisponivel(object $empresa): bool
    {
        if (CachedSchema::hasColumn('empresas', 'portal_ativo') && ! (bool) ($empresa->portal_ativo ?? false)) {
            return false;
        }

        if (CachedSchema::hasColumn('empresas', 'portal_expira_em') && ! empty($empresa->portal_expira_em)) {
            try {
                if (Carbon::parse($empresa->portal_expira_em)->isPast()) {
                    return false;
                }
            } catch (\Throwable) {
                return false;
            }
        }

        return true;
    }

    private static function tokenPortalValido(?string $token): bool
    {
        $token = trim((string) $token);

        return $token !== '' && preg_match('/\A[A-Za-z0-9]{32,128}\z/', $token) === 1;
    }

    private static function itemPortalDisponivel(array $item): bool
    {
        if (! (bool) ($item['portal_ativo'] ?? false)) {
            return false;
        }

        if (! self::tokenPortalValido($item['portal_token'] ?? null)) {
            return false;
        }

        if (! empty($item['portal_expira_em'])) {
            try {
                return ! Carbon::parse($item['portal_expira_em'])->isPast();
            } catch (\Throwable) {
                return false;
            }
        }

        return true;
    }

    private static function empresaExisteAtiva(int $empresaId): bool
    {
        if (! CachedSchema::hasTable('empresas')) {
            return false;
        }

        return DB::table('empresas')
            ->where('id', $empresaId)
            ->when(CachedSchema::hasColumn('empresas', 'ativo'), fn ($query) => $query->where('ativo', 1))
            ->exists();
    }

    private static function usuarioSuperAdmin(object $user): bool
    {
        if (method_exists($user, 'isSuperAdmin')) {
            return (bool) $user->isSuperAdmin();
        }

        return ($user->role ?? null) === 'super_admin';
    }

    private static function items(?int $empresaId): array
    {
        if (! $empresaId || ! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        $select = ['id', 'titulo', 'descricao', 'tipo', 'status', 'prioridade', 'empresa_id', 'data_vencimento', 'data_conclusao', 'created_at', 'updated_at'];
        foreach (['portal_ativo', 'portal_token', 'portal_expira_em', 'portal_status', 'approval_status', 'document_status', 'signature_status', 'risco_score', 'risk_score'] as $column) {
            if (CachedSchema::hasColumn('item_controles', $column)) {
                $select[] = $column;
            }
        }

        return DB::table('item_controles')
            ->select($select)
            ->where('empresa_id', $empresaId)
            ->orderByDesc(CachedSchema::hasColumn('item_controles', 'updated_at') ? 'updated_at' : 'id')
            ->limit(200)
            ->get()
            ->map(function ($item) {
                $row = (array) $item;
                $row['status_normalizado'] = self::normalizarStatus($row['status'] ?? null);
                $row['status_label'] = self::labelStatus($row['status'] ?? null);
                $row['is_done'] = in_array($row['status_normalizado'], self::STATUS_CONCLUIDOS, true);
                $row['is_late'] = self::estaAtrasado($row);
                $row['progress'] = self::progressoItem($row);
                $row['portal_url'] = self::itemPortalDisponivel($row) ? route('portal.item-controles.show', ['token' => trim((string) $row['portal_token'])]) : null;
                $row['data_vencimento_label'] = self::formatarData($row['data_vencimento'] ?? null);
                $row['updated_at_label'] = self::formatarDataHora($row['updated_at'] ?? null);
                $row['action_label'] = self::acaoClienteLabel($row);
                $row['needs_client_action'] = self::precisaAcaoCliente($row);

                return $row;
            })
            ->all();
    }

    private static function itemsVisiveisParaCliente(array $items): array
    {
        return array_values(array_filter($items, function (array $item): bool {
            if ((bool) ($item['portal_ativo'] ?? false)) {
                return true;
            }

            if (in_array(($item['status_normalizado'] ?? ''), self::STATUS_VISIVEIS_CLIENTE, true)) {
                return true;
            }

            return ($item['approval_status'] ?? null) === 'pendente';
        }));
    }

    private static function progresso(array $items): array
    {
        $total = count($items);
        $done = count(array_filter($items, fn ($item) => (bool) ($item['is_done'] ?? false)));
        $review = count(array_filter($items, fn ($item) => in_array(($item['status_normalizado'] ?? ''), ['pronto_revisao', 'pronto_para_revisao', 'em_aprovacao', 'aprovacao'], true)));
        $percent = $total > 0 ? (int) round(($done / $total) * 100) : 0;

        return ['total' => $total, 'done' => $done, 'review' => $review, 'pending' => max(0, $total - $done), 'percent' => max(0, min(100, $percent))];
    }

    private static function calendario(?int $empresaId): array
    {
        if (! $empresaId || ! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        return DB::table('item_controles')
            ->select(['id', 'titulo', 'status', 'data_vencimento', 'empresa_id'])
            ->where('empresa_id', $empresaId)
            ->whereNotNull('data_vencimento')
            ->orderBy('data_vencimento')
            ->limit(30)
            ->get()
            ->map(fn ($item) => array_merge((array) $item, ['status_label' => self::labelStatus($item->status ?? null), 'data_vencimento_label' => self::formatarData($item->data_vencimento ?? null), 'is_late' => self::estaAtrasado((array) $item)]))
            ->all();
    }

    private static function documentos(?int $empresaId): array
    {
        if (! $empresaId || ! CachedSchema::hasTable('portal_documentos')) {
            return [];
        }

        return PortalDocumento::query()
            ->where('empresa_id', $empresaId)
            ->where('visivel_cliente', true)
            ->whereIn('tipo', ['wiki', 'documento', 'link', 'imagem'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (PortalDocumento $documento) => [
                'id' => $documento->id,
                'titulo' => $documento->titulo,
                'tipo' => $documento->tipo,
                'conteudo' => $documento->conteudo,
                'url' => $documento->url,
                'arquivo' => $documento->arquivo,
                'download_url' => $documento->arquivo ? asset('storage/' . $documento->arquivo) : $documento->url,
                'created_at' => $documento->created_at,
                'created_at_label' => self::formatarDataHora($documento->created_at),
            ])
            ->all();
    }

    private static function atas(?int $empresaId): array
    {
        if (! $empresaId || ! CachedSchema::hasTable('portal_documentos')) {
            return [];
        }

        return PortalDocumento::query()
            ->where('empresa_id', $empresaId)
            ->where('visivel_cliente', true)
            ->where('tipo', 'ata')
            ->latest()
            ->limit(12)
            ->get()
            ->map(fn (PortalDocumento $documento) => ['id' => $documento->id, 'titulo' => $documento->titulo, 'conteudo' => $documento->conteudo, 'created_at' => $documento->created_at, 'created_at_label' => self::formatarDataHora($documento->created_at)])
            ->all();
    }

    private static function solicitacoes(?int $empresaId): array
    {
        if (! $empresaId || ! CachedSchema::hasTable('portal_solicitacoes')) {
            return [];
        }

        return PortalSolicitacao::query()
            ->where('empresa_id', $empresaId)
            ->latest()
            ->limit(60)
            ->get()
            ->filter(fn (PortalSolicitacao $solicitacao): bool => ! in_array(self::normalizarStatus($solicitacao->status), self::STATUS_CONCLUIDOS, true))
            ->take(20)
            ->map(fn (PortalSolicitacao $solicitacao) => [
                'id' => $solicitacao->id,
                'item_controle_id' => $solicitacao->item_controle_id,
                'titulo' => $solicitacao->titulo,
                'descricao' => $solicitacao->descricao,
                'prioridade' => $solicitacao->prioridade,
                'status' => $solicitacao->status,
                'status_label' => self::labelStatus($solicitacao->status),
                'resposta' => $solicitacao->resposta,
                'created_at' => $solicitacao->created_at,
                'created_at_label' => self::formatarDataHora($solicitacao->created_at),
            ])
            ->values()
            ->all();
    }

    private static function mensagens(?int $empresaId, bool $somenteAtivas = true): array
    {
        if (! $empresaId) {
            return [];
        }

        $mensagens = collect();

        if (CachedSchema::hasTable('portal_mensagens')) {
            $mensagens = $mensagens->merge(
                PortalMensagem::query()
                    ->where('empresa_id', $empresaId)
                    ->when(
                        $somenteAtivas,
                        fn ($query) => $query->when(
                            CachedSchema::hasColumn('portal_mensagens', 'conversa_status'),
                            fn ($query) => $query->where('conversa_status', 'aberta'),
                            fn ($query) => CachedSchema::hasColumn('portal_mensagens', 'visualizada_em') ? $query->whereNull('visualizada_em') : $query
                        )
                    )
                    ->oldest()
                    ->limit(80)
                    ->get()
                    ->map(fn (PortalMensagem $mensagem) => self::formatarMensagemChat([
                        'id' => $mensagem->id,
                        'source' => 'portal_mensagens',
                        'nome' => $mensagem->nome,
                        'email' => $mensagem->email,
                        'mensagem' => $mensagem->mensagem,
                        'origem' => $mensagem->origem,
                        'conversa_status' => $mensagem->conversa_status ?? 'aberta',
                        'created_at' => $mensagem->created_at,
                        'created_at_label' => self::formatarDataHora($mensagem->created_at),
                    ]))
            );
        }


        // Lote 5: o chat passa a ter uma única fonte de verdade.
        // Mensagens legadas de prazzu_client_portal_messages/client_portal_messages não entram mais no fluxo do Portal Cliente.


        return $mensagens
            ->filter(fn (array $mensagem): bool => trim((string) ($mensagem['mensagem'] ?? '')) !== '')
            ->sortBy('created_at_timestamp')
            ->take(80)
            ->values()
            ->all();
    }



    /**
     * @param  array<string, mixed>  $mensagem
     * @return array<string, mixed>
     */
    private static function formatarMensagemChat(array $mensagem): array
    {
        $payload = PortalChatMessageContract::fromArray($mensagem, [
            'empresa_id' => (int) ($mensagem['empresa_id'] ?? 0),
            'room' => (string) ($mensagem['room'] ?? ''),
            'room_scope' => (string) ($mensagem['room_scope'] ?? 'portal'),
        ]);

        $payload['autor_label'] = $payload['class'] === 'cliente' ? 'Cliente' : 'Equipe';
        $payload['created_at_timestamp'] = self::timestampSeguro($mensagem['created_at'] ?? null);

        return $payload;
    }

    /**
     * @return array<int, array<string, string|bool>>
     */

    private static function removerBlocoAnexosMensagem(string $texto): string
    {
        if ($texto === '' || ! str_contains($texto, 'Anexos enviados:')) {
            return $texto;
        }

        $limpo = preg_replace('/\n?Anexos enviados:\s*(?:\n-\s*.+?(?:\r?\n|$))+/si', '', $texto) ?? $texto;

        return trim($limpo) !== '' ? trim($limpo) : 'Arquivo(s) enviado(s) pelo cliente.';
    }

    private static function extrairAnexosMensagem(string $texto): array
    {
        if ($texto === '' || ! str_contains($texto, 'Anexos enviados:')) {
            return [];
        }

        preg_match_all('/^-\s*(.+?)\s*\|\s*(https?:\/\/\S+)(?:\s*\|\s*([^|\r\n]+))?(?:\s*\|\s*([^\r\n]+))?/mi', $texto, $matches, PREG_SET_ORDER);

        return collect($matches)
            ->map(function (array $match): array {
                $nome = trim((string) ($match[1] ?? 'Anexo')) ?: 'Anexo';
                $url = trim((string) ($match[2] ?? ''));
                $mime = trim((string) ($match[3] ?? ''));
                $size = (int) trim((string) ($match[4] ?? '0'));

                return [
                    'nome' => $nome,
                    'url' => $url,
                    'mime_type' => $mime,
                    'size' => $size > 0 ? $size : null,
                    'size_label' => $size > 0 ? self::formatarBytes($size) : null,
                    'is_image' => str_starts_with($mime, 'image/'),
                ];
            })
            ->filter(fn (array $anexo): bool => $anexo['url'] !== '')
            ->values()
            ->all();
    }


    private static function formatarBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1, ',', '.') . ' KB';
        }

        return $bytes . ' B';
    }

    private static function timestampSeguro(mixed $data): int
    {
        if (blank($data)) {
            return 0;
        }

        try {
            return Carbon::parse($data)->timestamp;
        } catch (\Throwable) {
            return 0;
        }
    }

    private static function pendenciasCliente(array $items): array
    {
        $pendencias = array_values(array_filter($items, fn (array $item): bool => (bool) ($item['needs_client_action'] ?? false)));

        usort($pendencias, function (array $a, array $b): int {
            if (($a['is_late'] ?? false) !== ($b['is_late'] ?? false)) {
                return ($a['is_late'] ?? false) ? -1 : 1;
            }

            return strcmp((string) ($a['data_vencimento'] ?? '9999-12-31'), (string) ($b['data_vencimento'] ?? '9999-12-31'));
        });

        return array_slice($pendencias, 0, 12);
    }

    private static function resumoStatus(array $items, array $visiveis): array
    {
        return [
            'total' => count($items),
            'visiveis' => count($visiveis),
            'pendencias_cliente' => count(array_filter($visiveis, fn (array $item): bool => (bool) ($item['needs_client_action'] ?? false))),
            'atrasados' => count(array_filter($visiveis, fn (array $item): bool => (bool) ($item['is_late'] ?? false))),
            'documentos' => count(array_filter($visiveis, fn (array $item): bool => ($item['tipo'] ?? null) === 'documento')),
        ];
    }

    private static function precisaAcaoCliente(array $item): bool
    {
        if (($item['is_done'] ?? false) === true) {
            return false;
        }

        $status = $item['status_normalizado'] ?? '';
        $portalStatus = self::normalizarStatus($item['portal_status'] ?? null);
        $documentStatus = self::normalizarStatus($item['document_status'] ?? null);
        $approvalStatus = self::normalizarStatus($item['approval_status'] ?? null);
        $signatureStatus = self::normalizarStatus($item['signature_status'] ?? null);

        return ($item['is_late'] ?? false)
            || in_array($status, ['pendente', 'aberto', 'novo', 'aguardando_cliente', 'cliente'], true)
            || in_array($portalStatus, [ItemControleStatusService::PORTAL_AGUARDANDO_CLIENTE, 'pendente_cliente', 'cliente_pendente'], true)
            || in_array($documentStatus, [ItemControleStatusService::DOCUMENTO_AGUARDANDO, 'pendente', 'sem_anexo', 'reprovado'], true)
            || in_array($approvalStatus, [ItemControleStatusService::APROVACAO_PENDENTE, ItemControleStatusService::APROVACAO_AGUARDANDO_CLIENTE], true)
            || in_array($signatureStatus, ['pendente', 'aguardando_assinatura'], true);
    }

    private static function acaoClienteLabel(array $item): string
    {
        $documentStatus = self::normalizarStatus($item['document_status'] ?? null);
        $signatureStatus = self::normalizarStatus($item['signature_status'] ?? null);
        $approvalStatus = self::normalizarStatus($item['approval_status'] ?? null);

        if (in_array($documentStatus, [ItemControleStatusService::DOCUMENTO_AGUARDANDO, 'pendente', 'sem_anexo', 'reprovado'], true)) {
            return 'Enviar anexo';
        }

        if (in_array($signatureStatus, ['pendente', 'aguardando_assinatura'], true)) {
            return 'Assinar documento';
        }

        if (in_array($approvalStatus, [ItemControleStatusService::APROVACAO_PENDENTE, ItemControleStatusService::APROVACAO_AGUARDANDO_CLIENTE], true)) {
            return 'Responder aprovação';
        }

        if ($item['is_late'] ?? false) {
            return 'Regularizar pendência';
        }

        return 'Acompanhar';
    }

    private static function proximaEntrega(array $items): ?array
    {
        $future = array_filter($items, fn ($item) => ! empty($item['data_vencimento']) && ! ($item['is_done'] ?? false));
        usort($future, fn ($a, $b) => strcmp((string) $a['data_vencimento'], (string) $b['data_vencimento']));
        return $future[0] ?? null;
    }

    private static function timeline(array $items): array
    {
        $eventos = [];

        foreach ($items as $item) {
            $titulo = trim((string) ($item['titulo'] ?? 'Item do portal'));
            $statusLabel = self::labelStatus($item['status'] ?? null);
            $prioridade = trim((string) ($item['prioridade'] ?? ''));
            $dataEvento = $item['updated_at'] ?? $item['created_at'] ?? null;
            $dataLabel = self::formatarDataHora($dataEvento) ?: 'Sem data';

            if ($item['is_late'] ?? false) {
                $eventos[] = [
                    'tipo' => 'pendencia',
                    'icone' => '!',
                    'cor' => 'danger',
                    'titulo' => 'Pendência vencida identificada',
                    'descricao' => $titulo . (! empty($item['data_vencimento_label']) ? ' venceu em ' . $item['data_vencimento_label'] . '.' : ' precisa de regularização.'),
                    'created_at' => $dataEvento,
                    'created_at_label' => $dataLabel,
                ];

                continue;
            }

            if ($item['needs_client_action'] ?? false) {
                $eventos[] = [
                    'tipo' => 'pendencia',
                    'icone' => '!',
                    'cor' => 'warn',
                    'titulo' => 'Ação aguardando cliente',
                    'descricao' => $titulo . ' está aguardando retorno, envio de documento ou aprovação pelo cliente.',
                    'created_at' => $dataEvento,
                    'created_at_label' => $dataLabel,
                ];

                continue;
            }

            if ($item['is_done'] ?? false) {
                $eventos[] = [
                    'tipo' => 'status',
                    'icone' => '✓',
                    'cor' => 'ok',
                    'titulo' => 'Etapa concluída',
                    'descricao' => $titulo . ' foi marcado como ' . mb_strtolower($statusLabel) . '.',
                    'created_at' => $item['data_conclusao'] ?? $dataEvento,
                    'created_at_label' => self::formatarDataHora($item['data_conclusao'] ?? $dataEvento) ?: $dataLabel,
                ];

                continue;
            }

            if (! empty($item['data_vencimento_label'])) {
                $eventos[] = [
                    'tipo' => 'prazo',
                    'icone' => '⏱',
                    'cor' => 'warn',
                    'titulo' => 'Prazo em acompanhamento',
                    'descricao' => $titulo . ' tem vencimento previsto para ' . $item['data_vencimento_label'] . ($prioridade !== '' ? ' • Prioridade: ' . ucfirst($prioridade) . '.' : '.'),
                    'created_at' => $dataEvento,
                    'created_at_label' => $dataLabel,
                ];

                continue;
            }

            $eventos[] = [
                'tipo' => 'status',
                'icone' => '•',
                'cor' => 'muted',
                'titulo' => 'Status atualizado',
                'descricao' => $titulo . ' está com status ' . mb_strtolower($statusLabel) . '.',
                'created_at' => $dataEvento,
                'created_at_label' => $dataLabel,
            ];
        }

        usort($eventos, function (array $a, array $b): int {
            return strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
        });

        return array_slice($eventos, 0, 12);
    }

    private static function historicoAprovacoes(array $items): array
    {
        return array_values(array_slice(array_filter($items, fn ($item) => $item['is_done'] ?? false), 0, 10));
    }

    private static function progressoItem(array $item): int
    {
        return match ($item['status_normalizado'] ?? '') {
            'concluido', 'concluida', 'finalizado', 'finalizada' => 100,
            'em_aprovacao', 'aprovacao', 'pronto_revisao', 'pronto_para_revisao' => 85,
            'em_andamento', 'andamento', 'execucao', 'execução' => 55,
            'aberto', 'novo', 'pendente' => 20,
            default => 35,
        };
    }

    private static function estaAtrasado(array $item): bool
    {
        if (empty($item['data_vencimento']) || in_array(self::normalizarStatus($item['status'] ?? null), self::STATUS_CONCLUIDOS, true)) {
            return false;
        }

        try {
            return Carbon::parse($item['data_vencimento'])->isPast();
        } catch (\Throwable) {
            return false;
        }
    }

    private static function formatarData(mixed $data): ?string
    {
        if (blank($data)) {
            return null;
        }

        try {
            return Carbon::parse($data)->format('d/m/Y');
        } catch (\Throwable) {
            return null;
        }
    }

    private static function formatarDataHora(mixed $data): ?string
    {
        if (blank($data)) {
            return null;
        }

        try {
            return Carbon::parse($data)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    private static function normalizarData(mixed $data): ?Carbon
    {
        if (blank($data)) {
            return null;
        }

        try {
            return Carbon::parse($data);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function normalizarStatus(?string $status): string
    {
        return Str::of((string) $status)->lower()->ascii()->replace([' ', '-'], '_')->toString();
    }

    private static function labelStatus(?string $status): string
    {
        return match (self::normalizarStatus($status)) {
            'pronto_revisao', 'pronto_para_revisao' => 'Pronto para revisão',
            'em_aprovacao', 'aprovacao' => 'Em aprovação',
            'concluido', 'concluida' => 'Concluído',
            'em_andamento', 'andamento' => 'Em andamento',
            'cliente_respondeu' => 'Cliente respondeu',
            'documento_enviado' => 'Documento enviado',
            'recebido_pelo_portal' => 'Recebido pelo portal',
            'solicitacao_aberta' => 'Solicitação aberta',
            'assinado' => 'Assinado',
            'aprovado', 'aprovada' => 'Aprovado',
            'aguardando_cliente' => 'Aguardando cliente',
            'aguardando_equipe' => 'Aguardando equipe',
            'aguardando_documento' => 'Aguardando documento',
            'aberto', 'novo' => 'Aberto',
            'pendente' => 'Pendente',
            default => filled($status) ? Str::headline((string) $status) : 'Sem status',
        };
    }
}
