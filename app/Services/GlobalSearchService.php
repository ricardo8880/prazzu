<?php

namespace App\Services;


use App\Support\CachedSchema;
use App\Filament\Pages\Clientes;
use App\Filament\Resources\AuditoriaDetalhada\AuditoriaDetalhadaResource;
use App\Filament\Resources\Empresas\EmpresaResource;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Filament\Resources\Responsaveis\ResponsavelResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\AuditoriaDetalhada;
use App\Models\Comentario;
use App\Models\Empresa;
use App\Models\ItemControle;
use App\Models\ItemControleAprovacao;
use App\Models\ItemControleAnexo;
use App\Models\ItemControleComentario;
use App\Models\PrazzuDocumentVersion;
use App\Models\Responsavel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GlobalSearchService
{
    /**
     * @var array<int, array{group: string, message: string}>
     */
    private array $groupFailures = [];

    public function search(?User $user, string $term, int $limitPerGroup = 6): array
    {
        $term = trim($term);
        $this->groupFailures = [];

        if (! $user) {
            return $this->emptyResult($term);
        }

        $limitPerGroup = max(3, min($limitPerGroup, 10));

        if (strlen($term) < 2) {
            return [
                'term' => $term,
                'total' => 0,
                'groups' => [],
                'recent_groups' => $this->recentGroups($user, $limitPerGroup),
                'quick_links' => $this->quickLinks(),
                'search_shortcuts' => $this->searchShortcuts(),
                'has_partial_errors' => false,
                'partial_error_message' => null,
                'partial_error_groups' => [],
            ];
        }

        $groups = [
            $this->safeGroup(fn () => $this->clientes($user, $term, $limitPerGroup), 'Clientes', 'Empresas e clientes cadastrados', 'bi-buildings'),
            $this->safeGroup(fn () => $this->itensControle($user, $term, $limitPerGroup), 'Pendências', 'Tarefas, controles e ações operacionais', 'bi-check2-circle'),
            $this->safeGroup(fn () => $this->documentos($user, $term, $limitPerGroup), 'Documentos e anexos', 'Arquivos, anexos e versões documentais vinculadas aos controles', 'bi-folder2-open'),
            $this->safeGroup(fn () => $this->contratos($user, $term, $limitPerGroup), 'Contratos', 'Contratos vinculados aos itens de controle', 'bi-file-earmark-text'),
            $this->safeGroup(fn () => $this->usuarios($user, $term, $limitPerGroup), 'Usuários', 'Usuários do sistema', 'bi-people'),
            $this->safeGroup(fn () => $this->responsaveis($user, $term, $limitPerGroup), 'Responsáveis', 'Pessoas responsáveis por controles e clientes', 'bi-person-badge'),
            $this->safeGroup(fn () => $this->comentarios($user, $term, $limitPerGroup), 'Comentários', 'Conversas, observações e comentários internos', 'bi-chat-left-text'),
            $this->safeGroup(fn () => $this->protocolosCodigos($user, $term, $limitPerGroup), 'Protocolos e códigos', 'IDs, contratos, tokens e códigos internos pesquisáveis', 'bi-upc-scan'),
            $this->safeGroup(fn () => $this->aprovacoes($user, $term, $limitPerGroup), 'Aprovações', 'Fluxos de aprovação', 'bi-patch-check'),
            $this->safeGroup(fn () => $this->auditoria($user, $term, $limitPerGroup), 'Auditoria', 'Eventos de auditoria', 'bi-shield-check'),
        ];

        $groups = array_values(array_filter($groups, fn (array $group): bool => filled($group['items'] ?? [])));
        $total = collect($groups)->sum(fn (array $group): int => count($group['items'] ?? []));

        return [
            'term' => $term,
            'total' => $total,
            'groups' => $groups,
            'recent_groups' => [],
            'quick_links' => $this->quickLinks(),
            'search_shortcuts' => $this->searchShortcuts(),
            'has_partial_errors' => $this->groupFailures !== [],
            'partial_error_message' => $this->partialErrorMessage(),
            'partial_error_groups' => array_column($this->groupFailures, 'group'),
        ];
    }

    private function clientes(User $user, string $term, int $limit): array
    {
        $items = collect();

        if ($user->isSuperAdmin() || $user->isAdminEmpresa()) {
            $empresas = Empresa::query()
                ->select($this->existingColumns('empresas', [
                    'id', 'razao_social', 'nome_fantasia', 'cnpj', 'email', 'telefone', 'status', 'ativo', 'updated_at',
                ]))
                ->when(! $user->isSuperAdmin(), fn (Builder $query): Builder => $query->whereKey($user->empresa_id))
                ->where(function (Builder $query) use ($term): void {
                    $this->applyLike($query, ['id', 'razao_social', 'nome_fantasia', 'cnpj', 'email', 'telefone', 'status'], $term, 'empresas');
                })
                ->latest('updated_at')
                ->limit($limit)
                ->get();

            $items = $empresas->map(function (Empresa $empresa): array {
                $title = $empresa->nome_fantasia ?: $empresa->razao_social ?: 'Empresa #' . $empresa->id;

                return $this->item(
                    title: $title,
                    subtitle: trim(($empresa->razao_social && $empresa->razao_social !== $title ? $empresa->razao_social . ' · ' : '') . ($empresa->cnpj ?: 'Cliente/empresa')),
                    type: 'Cliente',
                    icon: 'bi-buildings',
                    color: 'indigo',
                    url: $this->safeResourceUrl(EmpresaResource::class, 'edit', ['record' => $empresa]) ?: Clientes::getUrl(),
                    meta: $empresa->email ?: $empresa->telefone,
                );
            });
        }

        return $this->group('Clientes', 'Empresas e clientes cadastrados', 'bi-buildings', $items);
    }

    private function itensControle(User $user, string $term, int $limit): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return $this->group('Pendências', 'Tarefas, controles e ações operacionais', 'bi-check2-circle', collect());
        }

        $items = ItemControle::query()
            ->select($this->existingColumns('item_controles', [
                'id', 'empresa_id', 'responsavel_id', 'titulo', 'descricao', 'tipo', 'status', 'prioridade', 'data_vencimento', 'updated_at',
            ]))
            ->with(['empresa:id,razao_social,nome_fantasia', 'responsavel:id,nome'])
            ->visibleForUser($user)
            ->where(function (Builder $query) use ($term): void {
                $this->applyLike($query, ['id', 'titulo', 'descricao', 'tipo', 'status', 'prioridade', 'observacao'], $term, 'item_controles');
            })
            ->orderByRaw("CASE WHEN data_vencimento IS NULL THEN 1 ELSE 0 END")
            ->orderBy('data_vencimento')
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (ItemControle $item): array => $this->item(
                title: $item->titulo ?: 'Item #' . $item->id,
                subtitle: trim(($item->empresa?->nome_fantasia ?: $item->empresa?->razao_social ?: 'Sem empresa') . ' · ' . ($item->responsavel?->nome ?: 'Sem responsável')),
                type: 'Pendência',
                icon: 'bi-check2-circle',
                color: $this->statusColor($item->status),
                url: ItemControleResource::getUrl('edit', ['record' => $item]),
                meta: collect(['#' . $item->id, $item->status, $item->prioridade, optional($item->data_vencimento)->format('d/m/Y')])->filter()->implode(' · '),
            ));

        return $this->group('Pendências', 'Tarefas, controles e ações operacionais', 'bi-check2-circle', $items);
    }

    private function documentos(User $user, string $term, int $limit): array
    {
        $anexos = collect();
        $versoes = collect();

        if (CachedSchema::hasTable('item_controle_anexos')) {
            $anexos = ItemControleAnexo::query()
                ->with(['itemControle.empresa:id,razao_social,nome_fantasia'])
                ->whereHas('itemControle', fn (Builder $query): Builder => $query->visibleForUser($user))
                ->where(function (Builder $query) use ($term): void {
                    $this->applyLike($query, ['id', 'nome_original', 'arquivo', 'observacao', 'mime_type'], $term, 'item_controle_anexos');
                })
                ->latest('created_at')
                ->limit($limit)
                ->get()
                ->map(fn (ItemControleAnexo $anexo): array => $this->item(
                    title: $anexo->nome_original ?: basename((string) $anexo->arquivo) ?: 'Anexo #' . $anexo->id,
                    subtitle: $anexo->itemControle?->titulo ?: 'Documento de item de controle',
                    type: 'Anexo',
                    icon: 'bi-paperclip',
                    color: 'sky',
                    url: $anexo->itemControle ? ItemControleResource::getUrl('edit', ['record' => $anexo->itemControle]) : ItemControleResource::getUrl('index'),
                    meta: collect(['#' . $anexo->id, optional($anexo->created_at)->format('d/m/Y H:i')])->filter()->implode(' · '),
                ));
        }

        if (CachedSchema::hasTable('prazzu_document_versions')) {
            $versoes = PrazzuDocumentVersion::query()
                ->with(['itemControle.empresa:id,razao_social,nome_fantasia'])
                ->whereHas('itemControle', fn (Builder $query): Builder => $query->visibleForUser($user))
                ->where(function (Builder $query) use ($term): void {
                    $this->applyLike($query, ['id', 'document_type', 'file_path', 'status', 'notes'], $term, 'prazzu_document_versions');
                })
                ->latest('updated_at')
                ->limit($limit)
                ->get()
                ->map(fn (PrazzuDocumentVersion $version): array => $this->item(
                    title: trim(($version->document_type ?: 'Documento') . ' v' . ($version->version_number ?: 1)),
                    subtitle: $version->itemControle?->titulo ?: 'Versão documental',
                    type: 'Documento',
                    icon: 'bi-files',
                    color: $this->statusColor($version->status),
                    url: $version->itemControle ? ItemControleResource::getUrl('edit', ['record' => $version->itemControle]) : ItemControleResource::getUrl('index'),
                    meta: collect(['#' . $version->id, $version->status, optional($version->updated_at)->format('d/m/Y H:i')])->filter()->implode(' · '),
                ));
        }

        return $this->group(
            'Documentos e anexos',
            'Arquivos, anexos e versões documentais vinculadas aos controles',
            'bi-folder2-open',
            $anexos->merge($versoes)->take($limit)->values()
        );
    }

    private function contratos(User $user, string $term, int $limit): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return $this->group('Contratos', 'Contratos vinculados aos itens de controle', 'bi-file-earmark-text', collect());
        }

        $items = ItemControle::query()
            ->select($this->existingColumns('item_controles', [
                'id', 'empresa_id', 'titulo', 'status', 'contrato_numero', 'contrato_parte_nome', 'contrato_parte_documento', 'contrato_valor', 'contrato_status', 'contrato_fim_em', 'updated_at',
            ]))
            ->with('empresa:id,razao_social,nome_fantasia')
            ->visibleForUser($user)
            ->where(function (Builder $query): void {
                $query->whereNotNull('contrato_numero')
                    ->orWhereNotNull('contrato_parte_nome')
                    ->orWhereNotNull('contrato_parte_documento')
                    ->orWhereNotNull('contrato_status');
            })
            ->where(function (Builder $query) use ($term): void {
                $this->applyLike($query, ['id', 'titulo', 'contrato_numero', 'contrato_parte_nome', 'contrato_parte_documento', 'contrato_status'], $term, 'item_controles');
            })
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (ItemControle $item): array => $this->item(
                title: $item->contrato_numero ?: $item->titulo ?: 'Contrato #' . $item->id,
                subtitle: trim(($item->contrato_parte_nome ?: $item->titulo ?: 'Contrato') . ' · ' . ($item->empresa?->nome_fantasia ?: $item->empresa?->razao_social ?: 'Sem empresa')),
                type: 'Contrato',
                icon: 'bi-file-earmark-text',
                color: $this->statusColor($item->contrato_status ?: $item->status),
                url: ItemControleResource::getUrl('edit', ['record' => $item]),
                meta: collect(['#' . $item->id, $item->contrato_status, optional($item->contrato_fim_em)->format('d/m/Y')])->filter()->implode(' · '),
            ));

        return $this->group('Contratos', 'Contratos vinculados aos itens de controle', 'bi-file-earmark-text', $items);
    }

    private function usuarios(User $user, string $term, int $limit): array
    {
        if (! CachedSchema::hasTable('users') || (! $user->isSuperAdmin() && ! $user->isAdminEmpresa())) {
            return $this->group('Usuários', 'Usuários do sistema', 'bi-people', collect());
        }

        $items = User::query()
            ->select($this->existingColumns('users', ['id', 'name', 'email', 'role', 'empresa_id', 'last_access_at', 'updated_at']))
            ->with('empresa:id,razao_social,nome_fantasia')
            ->when(! $user->isSuperAdmin(), fn (Builder $query): Builder => $query->where('empresa_id', $user->empresa_id))
            ->where(function (Builder $query) use ($term): void {
                $this->applyLike($query, ['id', 'name', 'email', 'role'], $term, 'users');
            })
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (User $usuario): array => $this->item(
                title: $usuario->name ?: 'Usuário #' . $usuario->id,
                subtitle: trim(($usuario->email ?: 'Sem e-mail') . ' · ' . ($usuario->empresa?->nome_fantasia ?: $usuario->empresa?->razao_social ?: 'Sem empresa')),
                type: 'Usuário',
                icon: 'bi-person',
                color: 'violet',
                url: $this->safeResourceUrl(UserResource::class, 'edit', ['record' => $usuario]) ?: $this->safeResourceUrl(UserResource::class, 'index') ?: '#',
                meta: collect([$usuario->role, $this->formatDateTime($usuario->last_access_at)])->filter()->implode(' · '),
            ));

        return $this->group('Usuários', 'Pessoas com acesso ao sistema', 'bi-people', $items);
    }

    private function responsaveis(User $user, string $term, int $limit): array
    {
        if (! CachedSchema::hasTable('responsaveis')) {
            return $this->group('Responsáveis', 'Pessoas responsáveis por controles e clientes', 'bi-person-badge', collect());
        }

        $items = Responsavel::query()
            ->select($this->existingColumns('responsaveis', ['id', 'nome', 'email', 'telefone', 'cargo', 'empresa_id', 'updated_at']))
            ->with('empresa:id,razao_social,nome_fantasia')
            ->when(! $user->isSuperAdmin(), fn (Builder $query): Builder => $query->where('empresa_id', $user->empresa_id))
            ->where(function (Builder $query) use ($term): void {
                $this->applyLike($query, ['id', 'nome', 'email', 'telefone', 'cargo'], $term, 'responsaveis');
            })
            ->orderBy('nome')
            ->limit($limit)
            ->get()
            ->map(fn (Responsavel $responsavel): array => $this->item(
                title: $responsavel->nome ?: 'Responsável #' . $responsavel->id,
                subtitle: trim(($responsavel->cargo ?: 'Responsável') . ' · ' . ($responsavel->empresa?->nome_fantasia ?: $responsavel->empresa?->razao_social ?: 'Sem empresa')),
                type: 'Responsável',
                icon: 'bi-person-badge',
                color: 'emerald',
                url: $this->safeResourceUrl(ResponsavelResource::class, 'edit', ['record' => $responsavel]) ?: Clientes::getUrl(),
                meta: $responsavel->email ?: $responsavel->telefone,
            ));

        return $this->group('Responsáveis', 'Pessoas responsáveis por controles e clientes', 'bi-person-badge', $items);
    }

    private function comentarios(User $user, string $term, int $limit): array
    {
        $comentariosGerais = collect();
        $comentariosKanban = collect();

        if (CachedSchema::hasTable('comentarios')) {
            $comentariosGerais = Comentario::query()
                ->with(['itemControle.empresa:id,razao_social,nome_fantasia', 'user:id,name'])
                ->whereHas('itemControle', fn (Builder $query): Builder => $query->visibleForUser($user))
                ->where(function (Builder $query) use ($term): void {
                    $this->applyLike($query, ['id', 'comentario'], $term, 'comentarios');
                })
                ->latest('created_at')
                ->limit($limit)
                ->get()
                ->map(fn (Comentario $comentario): array => $this->comentarioItem($comentario->itemControle, $comentario->comentario, $comentario->user?->name, $comentario->created_at, 'Comentário'));
        }

        if (CachedSchema::hasTable('item_controle_comentarios')) {
            $comentariosKanban = ItemControleComentario::query()
                ->with(['itemControle.empresa:id,razao_social,nome_fantasia', 'user:id,name'])
                ->whereHas('itemControle', fn (Builder $query): Builder => $query->visibleForUser($user))
                ->where(function (Builder $query) use ($term): void {
                    $this->applyLike($query, ['id', 'comentario'], $term, 'item_controle_comentarios');
                })
                ->latest('created_at')
                ->limit($limit)
                ->get()
                ->map(fn (ItemControleComentario $comentario): array => $this->comentarioItem($comentario->itemControle, $comentario->comentario, $comentario->user?->name, $comentario->created_at, 'Comentário Kanban'));
        }

        return $this->group(
            'Comentários',
            'Conversas, observações e comentários internos',
            'bi-chat-left-text',
            $comentariosGerais->merge($comentariosKanban)->take($limit)->values()
        );
    }

    private function protocolosCodigos(User $user, string $term, int $limit): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return $this->group('Protocolos e códigos', 'IDs, contratos, tokens e códigos internos pesquisáveis', 'bi-upc-scan', collect());
        }

        $items = ItemControle::query()
            ->select($this->existingColumns('item_controles', [
                'id', 'empresa_id', 'responsavel_id', 'titulo', 'status', 'tipo', 'portal_token', 'portal_cliente_email', 'contrato_numero', 'created_at', 'updated_at',
            ]))
            ->with(['empresa:id,razao_social,nome_fantasia', 'responsavel:id,nome'])
            ->visibleForUser($user)
            ->where(function (Builder $query) use ($term): void {
                $this->applyLike($query, ['id', 'portal_token', 'portal_cliente_email', 'contrato_numero'], $term, 'item_controles');
            })
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (ItemControle $item): array => $this->item(
                title: 'Código #' . $item->id . ($item->contrato_numero ? ' · Contrato ' . $item->contrato_numero : ''),
                subtitle: trim(($item->titulo ?: 'Item de controle') . ' · ' . ($item->empresa?->nome_fantasia ?: $item->empresa?->razao_social ?: 'Sem empresa')),
                type: 'Protocolo/código',
                icon: 'bi-upc-scan',
                color: 'slate',
                url: ItemControleResource::getUrl('edit', ['record' => $item]),
                meta: collect([$item->status, $item->portal_cliente_email])->filter()->implode(' · '),
            ));

        return $this->group('Protocolos e códigos', 'IDs, contratos, tokens e códigos internos pesquisáveis', 'bi-upc-scan', $items);
    }

    private function aprovacoes(User $user, string $term, int $limit): array
    {
        if (! CachedSchema::hasTable('item_controle_aprovacoes')) {
            return $this->group('Aprovações', 'Fluxos de aprovação', 'bi-patch-check', collect());
        }

        $items = ItemControleAprovacao::query()
            ->with(['itemControle.empresa:id,razao_social,nome_fantasia', 'solicitante:id,name', 'aprovador:id,name'])
            ->where(function (Builder $query) use ($user): void {
                if (! $user->isSuperAdmin()) {
                    $query->where('empresa_id', $user->empresa_id);
                }
            })
            ->where(function (Builder $query) use ($term): void {
                $this->applyLike($query, ['id', 'status', 'observacao_solicitacao', 'observacao_resposta', 'motivo_reprovacao'], $term, 'item_controle_aprovacoes');
                $query->orWhereHas('itemControle', function (Builder $itemQuery) use ($term): void {
                    $this->applyLike($itemQuery, ['titulo', 'descricao'], $term, 'item_controles');
                });
            })
            ->latest('solicitado_em')
            ->limit($limit)
            ->get()
            ->map(fn (ItemControleAprovacao $aprovacao): array => $this->item(
                title: $aprovacao->itemControle?->titulo ?: 'Aprovação #' . $aprovacao->id,
                subtitle: trim('Solicitante: ' . ($aprovacao->solicitante?->name ?: 'Não informado') . ' · Aprovador: ' . ($aprovacao->aprovador?->name ?: 'Não informado')),
                type: 'Aprovação',
                icon: 'bi-patch-check',
                color: $this->statusColor($aprovacao->status),
                url: $aprovacao->itemControle ? ItemControleResource::getUrl('edit', ['record' => $aprovacao->itemControle]) : ItemControleResource::getUrl('index'),
                meta: collect(['#' . $aprovacao->id, method_exists($aprovacao, 'getStatusExibicao') ? $aprovacao->getStatusExibicao() : $aprovacao->status, optional($aprovacao->solicitado_em)->format('d/m/Y H:i')])->filter()->implode(' · '),
            ));

        return $this->group('Aprovações', 'Solicitações, aprovações e reprovações', 'bi-patch-check', $items);
    }

    private function auditoria(User $user, string $term, int $limit): array
    {
        if (! CachedSchema::hasTable('auditoria_detalhada')) {
            return $this->group('Auditoria', 'Eventos de auditoria', 'bi-shield-check', collect());
        }

        $items = AuditoriaDetalhada::query()
            ->with(['empresa:id,razao_social,nome_fantasia', 'user:id,name'])
            ->visibleForUser($user)
            ->where(function (Builder $query) use ($term): void {
                $this->applyLike($query, ['id', 'evento', 'campo', 'valor_anterior', 'valor_novo', 'ip', 'auditable_type', 'auditable_id'], $term, 'auditoria_detalhada');
            })
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (AuditoriaDetalhada $log): array => $this->item(
                title: trim(($log->evento ?: 'Evento') . ' · ' . class_basename((string) $log->auditable_type) . ' #' . $log->auditable_id),
                subtitle: trim(($log->user?->name ?: 'Sistema') . ' · ' . ($log->empresa?->nome_fantasia ?: $log->empresa?->razao_social ?: 'Sem empresa')),
                type: 'Auditoria',
                icon: 'bi-shield-check',
                color: $this->statusColor($log->evento),
                url: AuditoriaDetalhadaResource::getUrl('index'),
                meta: collect([$log->campo, optional($log->created_at)->format('d/m/Y H:i')])->filter()->implode(' · '),
            ));

        return $this->group('Auditoria', 'Rastreabilidade de ações no sistema', 'bi-shield-check', $items);
    }

    private function recentGroups(User $user, int $limit): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        $pendencias = ItemControle::query()
            ->select($this->existingColumns('item_controles', ['id', 'empresa_id', 'responsavel_id', 'titulo', 'status', 'prioridade', 'data_vencimento', 'updated_at']))
            ->with(['empresa:id,razao_social,nome_fantasia', 'responsavel:id,nome'])
            ->visibleForUser($user)
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (ItemControle $item): array => $this->item(
                title: $item->titulo ?: 'Item #' . $item->id,
                subtitle: trim(($item->empresa?->nome_fantasia ?: $item->empresa?->razao_social ?: 'Sem empresa') . ' · ' . ($item->responsavel?->nome ?: 'Sem responsável')),
                type: 'Atualizado recentemente',
                icon: 'bi-clock-history',
                color: $this->statusColor($item->status),
                url: ItemControleResource::getUrl('edit', ['record' => $item]),
                meta: collect([$item->status, optional($item->updated_at)->format('d/m/Y H:i')])->filter()->implode(' · '),
            ));

        $documentos = collect();

        if (CachedSchema::hasTable('item_controle_anexos')) {
            $documentos = ItemControleAnexo::query()
                ->with('itemControle:id,titulo')
                ->whereHas('itemControle', fn (Builder $query): Builder => $query->visibleForUser($user))
                ->latest('created_at')
                ->limit($limit)
                ->get()
                ->map(fn (ItemControleAnexo $anexo): array => $this->item(
                    title: $anexo->nome_original ?: basename((string) $anexo->arquivo) ?: 'Anexo #' . $anexo->id,
                    subtitle: $anexo->itemControle?->titulo ?: 'Documento anexado recentemente',
                    type: 'Anexo recente',
                    icon: 'bi-paperclip',
                    color: 'sky',
                    url: $anexo->itemControle ? ItemControleResource::getUrl('edit', ['record' => $anexo->itemControle]) : ItemControleResource::getUrl('index'),
                    meta: optional($anexo->created_at)->format('d/m/Y H:i'),
                ));
        }

        return array_values(array_filter([
            $this->group('Recentes', 'Últimas pendências movimentadas que você pode acessar rapidamente', 'bi-clock-history', $pendencias),
            $this->group('Anexos recentes', 'Últimos arquivos vinculados aos controles', 'bi-paperclip', $documentos),
        ], fn (array $group): bool => filled($group['items'] ?? [])));
    }

    private function comentarioItem(?ItemControle $itemControle, ?string $comentario, ?string $autor, mixed $createdAt, string $tipo): array
    {
        return $this->item(
            title: $itemControle?->titulo ?: 'Comentário sem item vinculado',
            subtitle: trim(($autor ?: 'Sistema') . ' · ' . Str::limit((string) $comentario, 90)),
            type: $tipo,
            icon: 'bi-chat-left-text',
            color: 'cyan',
            url: $itemControle ? ItemControleResource::getUrl('edit', ['record' => $itemControle]) : ItemControleResource::getUrl('index'),
            meta: $this->formatDateTime($createdAt),
        );
    }

    private function quickLinks(): array
    {
        return array_values(array_filter([
            ['label' => 'Novo item', 'url' => $this->safeResourceUrl(ItemControleResource::class, 'create') ?: '#', 'icon' => 'bi-plus-circle'],
            ['label' => 'Pendências', 'url' => $this->safeResourceUrl(ItemControleResource::class, 'index') ?: '#', 'icon' => 'bi-check2-square'],
            ['label' => 'Clientes', 'url' => Clientes::getUrl(), 'icon' => 'bi-buildings'],
            ['label' => 'Usuários', 'url' => $this->safeResourceUrl(UserResource::class, 'index') ?: '#', 'icon' => 'bi-people'],
            ['label' => 'Auditoria', 'url' => AuditoriaDetalhadaResource::getUrl('index'), 'icon' => 'bi-shield-check'],
        ], fn (array $link): bool => ($link['url'] ?? '#') !== '#'));
    }

    private function searchShortcuts(): array
    {
        return [
            ['label' => 'Documentos vencidos', 'query' => 'vencido', 'icon' => 'bi-exclamation-octagon'],
            ['label' => 'Aprovações pendentes', 'query' => 'pendente', 'icon' => 'bi-patch-check'],
            ['label' => 'Contratos ativos', 'query' => 'ativo', 'icon' => 'bi-file-earmark-text'],
            ['label' => 'Anexos', 'query' => 'pdf', 'icon' => 'bi-paperclip'],
            ['label' => 'Comentários', 'query' => 'comentário', 'icon' => 'bi-chat-left-text'],
        ];
    }

    private function safeGroup(callable $callback, string $title, string $description, string $icon): array
    {
        try {
            return $callback();
        } catch (\Throwable $exception) {
            $this->groupFailures[] = [
                'group' => $title,
                'message' => $exception->getMessage(),
            ];

            Log::error('Falha em grupo da busca global.', [
                'group' => $title,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return $this->group($title, $description, $icon, collect());
        }
    }

    private function partialErrorMessage(): ?string
    {
        if ($this->groupFailures === []) {
            return null;
        }

        $groups = collect($this->groupFailures)
            ->pluck('group')
            ->unique()
            ->values();

        $prefix = $groups->count() === 1
            ? 'Um grupo da busca não pôde ser carregado'
            : $groups->count() . ' grupos da busca não puderam ser carregados';

        return $prefix . ': ' . $groups->implode(', ') . '. Os demais resultados foram exibidos normalmente.';
    }

    private function group(string $title, string $description, string $icon, Collection $items): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'icon' => $icon,
            'items' => $items->values()->all(),
        ];
    }

    private function item(string $title, string $subtitle, string $type, string $icon, string $color, string $url, ?string $meta = null): array
    {
        return [
            'title' => Str::limit($title, 90),
            'subtitle' => Str::limit($subtitle, 120),
            'type' => $type,
            'icon' => $icon,
            'color' => $color,
            'url' => $url,
            'meta' => $meta ? Str::limit($meta, 80) : null,
        ];
    }

    private function applyLike(Builder $query, array $columns, string $term, string $table): void
    {
        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%';
        $first = true;

        foreach ($columns as $column) {
            if (! CachedSchema::hasColumn($table, $column)) {
                continue;
            }

            $method = $first ? 'where' : 'orWhere';
            $query->{$method}($column, 'like', $like);
            $first = false;
        }
    }

    private function existingColumns(string $table, array $columns): array
    {
        return array_values(array_filter($columns, fn (string $column): bool => CachedSchema::hasColumn($table, $column)));
    }

    private function statusColor(?string $status): string
    {
        return match (Str::lower((string) $status)) {
            'concluido', 'aprovado', 'ativo', 'assinado', 'pago', 'created', 'concluido_no_prazo' => 'emerald',
            'pendente', 'em_andamento', 'em andamento', 'aguardando', 'updated', 'em_aprovacao', 'em aprovação' => 'amber',
            'atrasado', 'vencido', 'reprovado', 'cancelado', 'deleted', 'falha' => 'rose',
            'documento', 'contrato', 'arquivo' => 'sky',
            default => 'slate',
        };
    }

    private function formatDateTime(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            if ($value instanceof \DateTimeInterface) {
                return $value->format('d/m/Y H:i');
            }

            return \Illuminate\Support\Carbon::parse($value)->format('d/m/Y H:i');
        } catch (\Throwable $exception) {
            Log::warning('Falha ao formatar data/hora na busca global.', [
                'value' => is_scalar($value) ? (string) $value : get_debug_type($value),
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function safeResourceUrl(string $resourceClass, string $page, array $parameters = []): ?string
    {
        try {
            return $resourceClass::getUrl($page, $parameters);
        } catch (\Throwable $exception) {
            Log::warning('Falha ao gerar URL de resource na busca global.', [
                'resource' => $resourceClass,
                'page' => $page,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function emptyResult(string $term): array
    {
        return [
            'term' => $term,
            'total' => 0,
            'groups' => [],
            'recent_groups' => [],
            'quick_links' => $this->quickLinks(),
            'search_shortcuts' => $this->searchShortcuts(),
            'has_partial_errors' => false,
            'partial_error_message' => null,
            'partial_error_groups' => [],
        ];
    }
}
