<?php

namespace App\Services;


use App\Support\CachedSchema;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class CentralNotificacoesService
{
    public function listar(User $user, array $filtros = []): Collection
    {
        $itens = collect()
            ->merge($this->safeCollect(fn () => $this->notificacoesInternas($user)))
            ->merge($this->safeCollect(fn () => $this->vencimentos($user)))
            ->merge($this->safeCollect(fn () => $this->aprovacoes($user)))
            ->merge($this->safeCollect(fn () => $this->comentarios($user)))
            ->merge($this->safeCollect(fn () => $this->documentosEnviados($user)))
            ->merge($this->safeCollect(fn () => $this->pendenciasCliente($user)))
            ->merge($this->safeCollect(fn () => $this->alertasSla($user)));

        $tipo = $filtros['tipo'] ?? 'todos';
        $status = $filtros['status'] ?? 'ativos';
        $busca = Str::lower(trim((string) ($filtros['busca'] ?? '')));

        return $itens
            ->unique('uid')
            ->when($tipo !== 'todos', fn (Collection $collection) => $collection->where('tipo', $tipo))
            ->when($status === 'nao_lidas', fn (Collection $collection) => $collection->where('lida', false))
            ->when($status === 'criticas', fn (Collection $collection) => $collection->where('criticidade', 'critica'))
            ->when($status === 'importantes', fn (Collection $collection) => $collection->where('criticidade', 'alta'))
            ->when($status === 'informativas', fn (Collection $collection) => $collection->filter(fn (array $item): bool => in_array($item['criticidade'], ['media', 'baixa'], true)))
            ->when($status === 'ativos', fn (Collection $collection) => $collection->filter(fn (array $item): bool => ! $item['lida'] || in_array($item['criticidade'], ['alta', 'critica'], true)))
            ->when($busca !== '', function (Collection $collection) use ($busca): Collection {
                return $collection->filter(function (array $item) use ($busca): bool {
                    return Str::contains(Str::lower(implode(' ', array_filter([
                        $item['titulo'] ?? null,
                        $item['mensagem'] ?? null,
                        $item['empresa'] ?? null,
                        $item['item_titulo'] ?? null,
                        $item['tipo_label'] ?? null,
                        $item['criticidade_label'] ?? null,
                    ]))), $busca);
                });
            })
            ->sortByDesc(fn (array $item): int => $item['ordenacao'])
            ->values();
    }

    private function safeCollect(callable $callback): Collection
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            Log::warning('Falha ao montar grupo da central de notificações.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return collect();
        }
    }

    public function resumo(Collection $notificacoes): array
    {
        return [
            'total' => $notificacoes->count(),
            'nao_lidas' => $notificacoes->where('lida', false)->count(),
            'criticas' => $notificacoes->where('criticidade', 'critica')->count(),
            'importantes' => $notificacoes->where('criticidade', 'alta')->count(),
            'informativas' => $notificacoes->filter(fn (array $item): bool => in_array($item['criticidade'], ['media', 'baixa'], true))->count(),
            'acionaveis' => $notificacoes->filter(fn (array $item): bool => ! empty($item['url']) || ! empty($item['marcavel']))->count(),
            'vencimentos' => $notificacoes->where('tipo', 'vencimento')->count(),
            'aprovacoes' => $notificacoes->where('tipo', 'aprovacao')->count(),
            'comentarios' => $notificacoes->where('tipo', 'comentario')->count(),
            'documentos' => $notificacoes->where('tipo', 'documento')->count(),
            'cliente' => $notificacoes->where('tipo', 'cliente')->count(),
            'sla' => $notificacoes->where('tipo', 'sla')->count(),
        ];
    }

    private function notificacoesInternas(User $user): Collection
    {
        if (! CachedSchema::hasTable('notificacoes_internas')) {
            return collect();
        }

        return DB::table('notificacoes_internas as n')
            ->leftJoin('empresas as e', 'e.id', '=', 'n.empresa_id')
            ->leftJoin('item_controles as i', 'i.id', '=', 'n.item_controle_id')
            ->selectRaw('n.id, n.tipo, n.titulo, n.mensagem, n.lida, n.created_at, n.empresa_id, n.item_controle_id, e.razao_social as empresa, i.titulo as item_titulo, i.status as item_status')
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->where(function ($builder) use ($user): void {
                $builder->where('n.user_id', $user->id)
                    ->orWhere(function ($subQuery) use ($user): void {
                        $subQuery->whereNull('n.user_id')->where('n.empresa_id', $user->empresa_id);
                    });
            }))
            ->orderByDesc('n.id')
            ->limit(150)
            ->get()
            ->map(fn ($row): array => $this->item([
                'uid' => 'notificacao-'.$row->id,
                'source' => 'notificacao_interna',
                'source_id' => (int) $row->id,
                'tipo' => $this->normalizarTipo((string) $row->tipo),
                'titulo' => (string) $row->titulo,
                'mensagem' => (string) $row->mensagem,
                'lida' => (bool) $row->lida,
                'created_at' => $row->created_at,
                'empresa_id' => $row->empresa_id,
                'empresa' => $row->empresa,
                'item_controle_id' => $row->item_controle_id,
                'item_titulo' => $row->item_titulo,
                'criticidade' => $this->criticidadePorTipo((string) $row->tipo, (bool) $row->lida),
            ]));
    }

    private function vencimentos(User $user): Collection
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return collect();
        }

        return $this->baseItens($user)
            ->whereNotNull('i.data_vencimento')
            ->whereNotIn('i.status', ['concluido', 'finalizado', 'cancelado'])
            ->whereDate('i.data_vencimento', '<=', now()->addDays(15)->toDateString())
            ->selectRaw('i.id, i.titulo, i.status, i.prioridade, i.data_vencimento, i.empresa_id, e.razao_social as empresa')
            ->limit(80)
            ->get()
            ->map(function ($row): array {
                $vencimento = $this->asDate($row->data_vencimento);
                $dias = $vencimento ? now()->startOfDay()->diffInDays($vencimento->copy()->startOfDay(), false) : null;
                $atrasado = $dias !== null && $dias < 0;

                return $this->item([
                    'uid' => 'vencimento-'.$row->id,
                    'source' => 'item_vencimento',
                    'tipo' => 'vencimento',
                    'titulo' => $atrasado ? 'Item vencido' : 'Vencimento próximo',
                    'mensagem' => $atrasado
                        ? 'Este item está atrasado há '.abs((int) $dias).' dia(s).'
                        : 'Este item vence '.($dias === 0 ? 'hoje' : 'em '.$dias.' dia(s)').'.',
                    'lida' => false,
                    'created_at' => $row->data_vencimento,
                    'empresa_id' => $row->empresa_id,
                    'empresa' => $row->empresa,
                    'item_controle_id' => $row->id,
                    'item_titulo' => $row->titulo,
                    'criticidade' => $atrasado ? 'critica' : ((int) $dias <= 3 ? 'alta' : 'media'),
                    'prazo' => $vencimento?->format('d/m/Y'),
                ]);
            });
    }

    private function aprovacoes(User $user): Collection
    {
        if (! CachedSchema::hasTable('item_controle_aprovacoes')) {
            return collect();
        }

        return DB::table('item_controle_aprovacoes as a')
            ->leftJoin('item_controles as i', 'i.id', '=', 'a.item_controle_id')
            ->leftJoin('empresas as e', 'e.id', '=', 'a.empresa_id')
            ->leftJoin('users as u', 'u.id', '=', 'a.solicitante_id')
            ->where('a.status', 'pendente')
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->where(function ($builder) use ($user): void {
                $builder->where('a.aprovador_id', $user->id)->orWhere('a.empresa_id', $user->empresa_id);
            }))
            ->selectRaw('a.id, a.item_controle_id, a.empresa_id, a.observacao_solicitacao, a.solicitado_em, e.razao_social as empresa, i.titulo as item_titulo, u.name as solicitante')
            ->orderByDesc('a.id')
            ->limit(80)
            ->get()
            ->map(fn ($row): array => $this->item([
                'uid' => 'aprovacao-'.$row->id,
                'source' => 'aprovacao',
                'tipo' => 'aprovacao',
                'titulo' => 'Aprovação aguardando resposta',
                'mensagem' => trim(($row->solicitante ? $row->solicitante.' solicitou aprovação. ' : '').($row->observacao_solicitacao ?: 'Revise a solicitação pendente.')),
                'lida' => false,
                'created_at' => $row->solicitado_em,
                'empresa_id' => $row->empresa_id,
                'empresa' => $row->empresa,
                'item_controle_id' => $row->item_controle_id,
                'item_titulo' => $row->item_titulo,
                'criticidade' => 'alta',
            ]));
    }

    private function comentarios(User $user): Collection
    {
        if (! CachedSchema::hasTable('item_controle_comentarios')) {
            return collect();
        }

        return DB::table('item_controle_comentarios as c')
            ->leftJoin('item_controles as i', 'i.id', '=', 'c.item_controle_id')
            ->leftJoin('empresas as e', 'e.id', '=', 'i.empresa_id')
            ->leftJoin('users as u', 'u.id', '=', 'c.user_id')
            ->where('c.created_at', '>=', now()->subDays(30))
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('i.empresa_id', $user->empresa_id))
            ->selectRaw('c.id, c.item_controle_id, c.comentario, c.created_at, i.empresa_id, i.titulo as item_titulo, e.razao_social as empresa, u.name as autor')
            ->orderByDesc('c.id')
            ->limit(80)
            ->get()
            ->map(fn ($row): array => $this->item([
                'uid' => 'comentario-'.$row->id,
                'source' => 'comentario',
                'tipo' => 'comentario',
                'titulo' => 'Novo comentário',
                'mensagem' => trim(($row->autor ? $row->autor.': ' : '').Str::limit(strip_tags((string) $row->comentario), 180)),
                'lida' => false,
                'created_at' => $row->created_at,
                'empresa_id' => $row->empresa_id,
                'empresa' => $row->empresa,
                'item_controle_id' => $row->item_controle_id,
                'item_titulo' => $row->item_titulo,
                'criticidade' => 'baixa',
            ]));
    }

    private function documentosEnviados(User $user): Collection
    {
        $documentos = collect();

        if (CachedSchema::hasTable('portal_documentos')) {
            $documentos = $documentos->merge(DB::table('portal_documentos as d')
                ->leftJoin('item_controles as i', 'i.id', '=', 'd.item_controle_id')
                ->leftJoin('empresas as e', 'e.id', '=', 'd.empresa_id')
                ->where('d.created_at', '>=', now()->subDays(45))
                ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('d.empresa_id', $user->empresa_id))
                ->selectRaw('d.id, d.item_controle_id, d.empresa_id, d.titulo, d.tipo as doc_tipo, d.created_at, e.razao_social as empresa, i.titulo as item_titulo')
                ->orderByDesc('d.id')
                ->limit(60)
                ->get()
                ->map(fn ($row): array => $this->item([
                    'uid' => 'portal-documento-'.$row->id,
                    'source' => 'portal_documento',
                    'tipo' => 'documento',
                    'titulo' => 'Documento enviado ao portal',
                    'mensagem' => trim(($row->titulo ?: 'Documento').' foi disponibilizado para o cliente.'),
                    'lida' => false,
                    'created_at' => $row->created_at,
                    'empresa_id' => $row->empresa_id,
                    'empresa' => $row->empresa,
                    'item_controle_id' => $row->item_controle_id,
                    'item_titulo' => $row->item_titulo,
                    'criticidade' => 'media',
                ])));
        }

        if (CachedSchema::hasTable('prazzu_document_versions')) {
            $documentos = $documentos->merge(DB::table('prazzu_document_versions as d')
                ->leftJoin('item_controles as i', 'i.id', '=', 'd.item_controle_id')
                ->leftJoin('empresas as e', 'e.id', '=', 'i.empresa_id')
                ->where('d.created_at', '>=', now()->subDays(45))
                ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('i.empresa_id', $user->empresa_id))
                ->selectRaw('d.id, d.item_controle_id, d.document_type, d.version_number, d.status, d.created_at, i.empresa_id, i.titulo as item_titulo, e.razao_social as empresa')
                ->orderByDesc('d.id')
                ->limit(60)
                ->get()
                ->map(fn ($row): array => $this->item([
                    'uid' => 'document-version-'.$row->id,
                    'source' => 'document_version',
                    'tipo' => 'documento',
                    'titulo' => 'Nova versão documental',
                    'mensagem' => trim(($row->document_type ?: 'Documento').' v'.$row->version_number.' está com status '.($row->status ?: 'registrado').'.'),
                    'lida' => false,
                    'created_at' => $row->created_at,
                    'empresa_id' => $row->empresa_id,
                    'empresa' => $row->empresa,
                    'item_controle_id' => $row->item_controle_id,
                    'item_titulo' => $row->item_titulo,
                    'criticidade' => in_array($row->status, ['reprovado', 'pendente'], true) ? 'alta' : 'media',
                ])));
        }

        return $documentos;
    }

    private function pendenciasCliente(User $user): Collection
    {
        $pendencias = collect();

        if (CachedSchema::hasTable('portal_solicitacoes')) {
            $pendencias = $pendencias->merge(DB::table('portal_solicitacoes as s')
                ->leftJoin('item_controles as i', 'i.id', '=', 's.item_controle_id')
                ->leftJoin('empresas as e', 'e.id', '=', 's.empresa_id')
                ->whereIn('s.status', ['aberto', 'aberta', 'pendente', 'em_aberto', 'aguardando'])
                ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('s.empresa_id', $user->empresa_id))
                ->selectRaw('s.id, s.item_controle_id, s.empresa_id, s.titulo, s.descricao, s.prioridade, s.created_at, e.razao_social as empresa, i.titulo as item_titulo')
                ->orderByDesc('s.id')
                ->limit(80)
                ->get()
                ->map(fn ($row): array => $this->item([
                    'uid' => 'portal-solicitacao-'.$row->id,
                    'source' => 'portal_solicitacao',
                    'tipo' => 'cliente',
                    'titulo' => 'Pendência do cliente',
                    'mensagem' => Str::limit($row->titulo ?: $row->descricao ?: 'Existe uma solicitação aberta no portal do cliente.', 180),
                    'lida' => false,
                    'created_at' => $row->created_at,
                    'empresa_id' => $row->empresa_id,
                    'empresa' => $row->empresa,
                    'item_controle_id' => $row->item_controle_id,
                    'item_titulo' => $row->item_titulo,
                    'criticidade' => $row->prioridade === 'alta' ? 'alta' : 'media',
                ])));
        }

        if (CachedSchema::hasTable('prazzu_client_portal_messages')) {
            $pendencias = $pendencias->merge(DB::table('prazzu_client_portal_messages as m')
                ->leftJoin('item_controles as i', 'i.id', '=', 'm.item_controle_id')
                ->leftJoin('empresas as e', 'e.id', '=', 'm.empresa_id')
                ->whereNull('m.read_at')
                ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('m.empresa_id', $user->empresa_id))
                ->selectRaw('m.id, m.item_controle_id, m.empresa_id, m.client_name, m.client_email, m.message, m.created_at, e.razao_social as empresa, i.titulo as item_titulo')
                ->orderByDesc('m.id')
                ->limit(80)
                ->get()
                ->map(fn ($row): array => $this->item([
                    'uid' => 'portal-mensagem-'.$row->id,
                    'source' => 'portal_mensagem',
                    'source_id' => (int) $row->id,
                    'tipo' => 'cliente',
                    'titulo' => 'Mensagem do cliente',
                    'mensagem' => trim(($row->client_name ?: $row->client_email ?: 'Cliente').': '.Str::limit((string) $row->message, 180)),
                    'lida' => false,
                    'created_at' => $row->created_at,
                    'empresa_id' => $row->empresa_id,
                    'empresa' => $row->empresa,
                    'item_controle_id' => $row->item_controle_id,
                    'item_titulo' => $row->item_titulo,
                    'criticidade' => 'alta',
                ])));
        }

        if (CachedSchema::hasTable('portal_mensagens')) {
            $pendencias = $pendencias->merge(DB::table('portal_mensagens as m')
                ->leftJoin('item_controles as i', 'i.id', '=', 'm.item_controle_id')
                ->leftJoin('empresas as e', 'e.id', '=', 'm.empresa_id')
                ->whereNull('m.visualizada_em')
                ->where('m.origem', 'cliente')
                ->where('m.conversa_status', 'aberta')
                ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('m.empresa_id', $user->empresa_id))
                ->selectRaw('m.id, m.item_controle_id, m.empresa_id, m.nome, m.email, m.mensagem, m.created_at, e.razao_social as empresa, i.titulo as item_titulo')
                ->orderByDesc('m.id')
                ->limit(80)
                ->get()
                ->map(fn ($row): array => $this->item([
                    'uid' => 'portal-mensagem-legado-'.$row->id,
                    'source' => 'portal_mensagem_legado',
                    'source_id' => (int) $row->id,
                    'tipo' => 'cliente',
                    'titulo' => 'Mensagem do cliente',
                    'mensagem' => trim(($row->nome ?: $row->email ?: 'Cliente').': '.Str::limit((string) $row->mensagem, 180)),
                    'lida' => false,
                    'created_at' => $row->created_at,
                    'empresa_id' => $row->empresa_id,
                    'empresa' => $row->empresa,
                    'item_controle_id' => $row->item_controle_id,
                    'item_titulo' => $row->item_titulo,
                    'criticidade' => 'alta',
                ])));
        }

        return $pendencias;
    }

    private function alertasSla(User $user): Collection
    {
        if (! CachedSchema::hasTable('item_controles') || ! CachedSchema::hasColumn('item_controles', 'sla_status')) {
            return collect();
        }

        return $this->baseItens($user)
            ->whereNotNull('i.sla_status')
            ->where(function ($query): void {
                $query->whereIn('i.sla_status', ['vencido', 'em_risco'])
                    ->orWhere(function ($subQuery): void {
                        $subQuery->whereNotNull('i.sla_limite_em')
                            ->where('i.sla_limite_em', '<=', now()->addHours(24))
                            ->whereNull('i.sla_concluido_em');
                    });
            })
            ->selectRaw('i.id, i.titulo, i.status, i.sla_status, i.sla_limite_em, i.empresa_id, e.razao_social as empresa')
            ->orderByRaw('i.sla_limite_em is null, i.sla_limite_em asc')
            ->limit(80)
            ->get()
            ->map(function ($row): array {
                $limite = $this->asDateTime($row->sla_limite_em);
                $vencido = $row->sla_status === 'vencido' || ($limite && $limite->isPast());

                return $this->item([
                    'uid' => 'sla-'.$row->id,
                    'source' => 'sla',
                    'tipo' => 'sla',
                    'titulo' => $vencido ? 'SLA vencido' : 'SLA em risco',
                    'mensagem' => $limite ? 'Limite de SLA: '.$limite->format('d/m/Y H:i').'.' : 'Revise o SLA deste item.',
                    'lida' => false,
                    'created_at' => $row->sla_limite_em ?: now(),
                    'empresa_id' => $row->empresa_id,
                    'empresa' => $row->empresa,
                    'item_controle_id' => $row->id,
                    'item_titulo' => $row->titulo,
                    'criticidade' => $vencido ? 'critica' : 'alta',
                    'prazo' => $limite?->format('d/m/Y H:i'),
                ]);
            });
    }

    private function baseItens(User $user)
    {
        return DB::table('item_controles as i')
            ->leftJoin('empresas as e', 'e.id', '=', 'i.empresa_id')
            ->when(! $user->isSuperAdmin(), function ($query) use ($user): void {
                $query->where(function ($builder) use ($user): void {
                    $builder->where('i.empresa_id', $user->empresa_id)
                        ->orWhereExists(function ($subQuery) use ($user): void {
                            $subQuery->selectRaw('1')
                                ->from('responsaveis as r')
                                ->whereColumn('r.id', 'i.responsavel_id')
                                ->where('r.user_id', $user->id);
                        });
                });
            });
    }

    private function item(array $dados): array
    {
        $createdAt = $this->asDateTime($dados['created_at'] ?? null) ?: now();
        $tipo = $dados['tipo'] ?? 'sistema';

        return array_merge([
            'uid' => Str::uuid()->toString(),
            'source' => null,
            'source_id' => null,
            'tipo' => $tipo,
            'tipo_label' => $this->tipoLabel($tipo),
            'tipo_icon' => $this->tipoIcon($tipo),
            'tipo_classe' => $this->tipoClasse($tipo),
            'titulo' => 'Notificação',
            'mensagem' => null,
            'lida' => false,
            'created_at' => $createdAt,
            'created_at_formatado' => $createdAt->format('d/m/Y H:i'),
            'created_at_humano' => $createdAt->diffForHumans(),
            'empresa_id' => null,
            'empresa' => null,
            'item_controle_id' => null,
            'item_titulo' => null,
            'criticidade' => 'media',
            'criticidade_label' => $this->criticidadeLabel($dados['criticidade'] ?? 'media'),
            'prazo' => null,
            'url' => ! empty($dados['item_controle_id']) ? ItemControleResource::getUrl('edit', ['record' => $dados['item_controle_id']]) : null,
            'marcavel' => in_array($dados['source'] ?? null, ['notificacao_interna', 'portal_mensagem', 'portal_mensagem_legado'], true),
            'ordenacao' => $createdAt->timestamp,
        ], $dados, [
            'tipo_label' => $this->tipoLabel($tipo),
            'tipo_icon' => $this->tipoIcon($tipo),
            'tipo_classe' => $this->tipoClasse($tipo),
            'created_at' => $createdAt,
            'created_at_formatado' => $createdAt->format('d/m/Y H:i'),
            'created_at_humano' => $createdAt->diffForHumans(),
            'criticidade_label' => $this->criticidadeLabel($dados['criticidade'] ?? 'media'),
            'url' => ! empty($dados['item_controle_id']) ? ItemControleResource::getUrl('edit', ['record' => $dados['item_controle_id']]) : null,
            'ordenacao' => $this->pesoCriticidade($dados['criticidade'] ?? 'media') + $createdAt->timestamp,
        ]);
    }

    private function normalizarTipo(string $tipo): string
    {
        return match ($tipo) {
            'prazo', 'contrato' => 'vencimento',
            'manual', 'sistema' => 'sistema',
            default => in_array($tipo, ['aprovacao', 'comentario', 'documento', 'cliente', 'sla'], true) ? $tipo : 'sistema',
        };
    }

    private function tipoLabel(string $tipo): string
    {
        return match ($tipo) {
            'vencimento' => 'Vencimentos',
            'aprovacao' => 'Aprovações',
            'comentario' => 'Comentários',
            'documento' => 'Documentos',
            'cliente' => 'Cliente',
            'sla' => 'SLA',
            default => 'Sistema',
        };
    }

    private function tipoIcon(string $tipo): string
    {
        return match ($tipo) {
            'vencimento' => 'heroicon-o-calendar-days',
            'aprovacao' => 'heroicon-o-check-badge',
            'comentario' => 'heroicon-o-chat-bubble-left-right',
            'documento' => 'heroicon-o-document-text',
            'cliente' => 'heroicon-o-user-group',
            'sla' => 'heroicon-o-bolt',
            default => 'heroicon-o-bell',
        };
    }

    private function tipoClasse(string $tipo): string
    {
        return match ($tipo) {
            'vencimento' => 'tipo-vencimento',
            'aprovacao' => 'tipo-aprovacao',
            'comentario' => 'tipo-comentario',
            'documento' => 'tipo-documento',
            'cliente' => 'tipo-cliente',
            'sla' => 'tipo-sla',
            default => 'tipo-sistema',
        };
    }

    private function criticidadePorTipo(string $tipo, bool $lida = false): string
    {
        if ($lida) {
            return 'baixa';
        }

        return match ($tipo) {
            'sla' => 'critica',
            'prazo', 'aprovacao' => 'alta',
            'contrato' => 'media',
            default => 'baixa',
        };
    }

    private function criticidadeLabel(string $criticidade): string
    {
        return match ($criticidade) {
            'critica' => 'Crítica',
            'alta' => 'Alta',
            'baixa' => 'Baixa',
            default => 'Média',
        };
    }

    private function pesoCriticidade(string $criticidade): int
    {
        return match ($criticidade) {
            'critica' => 4000000000,
            'alta' => 3000000000,
            'media' => 2000000000,
            default => 1000000000,
        };
    }

    private function asDateTime(mixed $value): ?CarbonInterface
    {
        if (! $value) {
            return null;
        }

        return $value instanceof CarbonInterface ? $value : Carbon::parse($value);
    }

    private function asDate(mixed $value): ?CarbonInterface
    {
        return $this->asDateTime($value)?->startOfDay();
    }
}
