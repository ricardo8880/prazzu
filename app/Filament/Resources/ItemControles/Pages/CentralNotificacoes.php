<?php

namespace App\Filament\Resources\ItemControles\Pages;


use App\Support\CachedSchema;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\NotificacaoInterna;
use App\Services\CentralNotificacoesService;
use App\Services\PlanoService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CentralNotificacoes extends Page
{
    public string $filtroTipo = 'todos';

    public string $filtroStatus = 'ativos';

    public string $busca = '';

    public static function canAccess(array $parameters = []): bool
    {
        return PlanoService::usuarioPossuiFeature(
            Filament::auth()->user(),
            PlanoService::FEATURE_NOTIFICACOES_BASICAS
        );
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return static::canAccess();
    }

    protected static string $resource = ItemControleResource::class;

    protected string $view = 'filament.resources.item-controles.pages.central-notificacoes';

    protected static ?string $title = 'Central de Notificações';

    public function getNotificacoesProperty(): Collection
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return collect();
        }

        return app(CentralNotificacoesService::class)->listar($user, [
            'tipo' => $this->filtroTipo,
            'status' => $this->filtroStatus,
            'busca' => $this->busca,
        ]);
    }

    public function getResumoProperty(): array
    {
        return app(CentralNotificacoesService::class)->resumo($this->notificacoes);
    }

    public function limparFiltros(): void
    {
        $this->filtroTipo = 'todos';
        $this->filtroStatus = 'ativos';
        $this->busca = '';
    }

    public function filtrarTipo(string $tipo): void
    {
        $this->filtroTipo = $tipo;
    }

    public function marcarComoLida(string $source, int $sourceId): void
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return;
        }

        if ($source === 'notificacao_interna') {
            $notificacao = $this->buscarNotificacaoPermitida($sourceId);

            if (! $notificacao) {
                Notification::make()
                    ->title('Notificação não encontrada.')
                    ->danger()
                    ->send();

                return;
            }

            $notificacao->marcarComoLida();

            Notification::make()
                ->title('Notificação marcada como lida.')
                ->success()
                ->send();

            return;
        }

        if ($source === 'portal_mensagem' && CachedSchema::hasTable('prazzu_client_portal_messages')) {
            DB::table('prazzu_client_portal_messages')
                ->where('id', $sourceId)
                ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('empresa_id', $user->empresa_id))
                ->whereNull('read_at')
                ->update([
                    'read_at' => now(),
                    'updated_at' => now(),
                ]);

            Notification::make()
                ->title('Mensagem marcada como lida.')
                ->success()
                ->send();

            return;
        }

        if ($source === 'portal_mensagem_legado' && CachedSchema::hasTable('portal_mensagens')) {
            DB::table('portal_mensagens')
                ->where('id', $sourceId)
                ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('empresa_id', $user->empresa_id))
                ->whereNull('visualizada_em')
                ->update([
                    'visualizada_em' => now(),
                    'updated_at' => now(),
                ]);

            Notification::make()
                ->title('Mensagem marcada como lida.')
                ->success()
                ->send();

            return;
        }

        Notification::make()
            ->title('Esta origem ainda não possui marcação de leitura individual.')
            ->info()
            ->send();
    }

    public function excluirNotificacao(int $notificacaoId): void
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return;
        }

        $notificacao = $this->buscarNotificacaoPermitida($notificacaoId);

        if (! $notificacao) {
            Notification::make()
                ->title('Notificação não encontrada.')
                ->danger()
                ->send();

            return;
        }

        $notificacao->delete();

        Notification::make()
            ->title('Notificação excluída com sucesso.')
            ->success()
            ->send();
    }

    public function marcarTodasComoLidas(): void
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return;
        }

        if (CachedSchema::hasTable('notificacoes_internas')) {
            NotificacaoInterna::query()
                ->where('lida', false)
                ->when(! $user->isSuperAdmin(), function ($query) use ($user): void {
                    $query->where(function ($builder) use ($user): void {
                        $builder->where('user_id', $user->id)
                            ->orWhere(function ($subQuery) use ($user): void {
                                $subQuery->whereNull('user_id')
                                    ->where('empresa_id', $user->empresa_id);
                            });
                    });
                })
                ->update([
                    'lida' => true,
                    'lida_em' => now(),
                    'updated_at' => now(),
                ]);
        }

        if (CachedSchema::hasTable('prazzu_client_portal_messages')) {
            DB::table('prazzu_client_portal_messages')
                ->whereNull('read_at')
                ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('empresa_id', $user->empresa_id))
                ->update([
                    'read_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        if (CachedSchema::hasTable('portal_mensagens')) {
            DB::table('portal_mensagens')
                ->whereNull('visualizada_em')
                ->where('origem', 'cliente')
                ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('empresa_id', $user->empresa_id))
                ->update([
                    'visualizada_em' => now(),
                    'updated_at' => now(),
                ]);
        }

        Notification::make()
            ->title('Notificações marcáveis foram atualizadas como lidas.')
            ->success()
            ->send();
    }

    protected function buscarNotificacaoPermitida(int $notificacaoId): ?NotificacaoInterna
    {
        $user = Filament::auth()->user();

        if (! $user || ! CachedSchema::hasTable('notificacoes_internas')) {
            return null;
        }

        return NotificacaoInterna::query()
            ->whereKey($notificacaoId)
            ->when(! $user->isSuperAdmin(), function ($query) use ($user): void {
                $query->where(function ($builder) use ($user): void {
                    $builder->where('user_id', $user->id)
                        ->orWhere(function ($subQuery) use ($user): void {
                            $subQuery->whereNull('user_id')
                                ->where('empresa_id', $user->empresa_id);
                        });
                });
            })
            ->first();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('marcar_todas_lidas')
                ->label('Marcar lidas')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action('marcarTodasComoLidas'),
        ];
    }
}
