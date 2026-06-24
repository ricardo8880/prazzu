<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesAdvancedPermissions;
use App\Filament\Resources\AuditoriaDetalhada\AuditoriaDetalhadaResource;
use App\Services\AuditoriaAccessService;
use App\Support\AuditoriaFormatter;
use App\Support\CachedSchema;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class AuditoriaAdministrativa extends Page
{
    use UsesAdvancedPermissions;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-magnifying-glass-circle';
    protected static string | UnitEnum | null $navigationGroup = 'Administração';
    protected static ?string $navigationLabel = 'Auditoria Administrativa';
    protected static ?string $title = 'Auditoria Administrativa';
    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.pages.auditoria-administrativa';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return app(AuditoriaAccessService::class)->canView(auth()->user());
    }

    protected function getViewData(): array
    {
        return [
            'resumo' => $this->resumo(),
            'fontes' => $this->fontes(),
            'eventosRecentes' => $this->eventosRecentes(),
            'eventosPorModulo' => $this->eventosPorModulo(),
            'eventosPorUsuario' => $this->eventosPorUsuario(),
            'links' => $this->links(),
        ];
    }

    private function resumo(): array
    {
        return [
            'auditoria_detalhada' => $this->countTable('auditoria_detalhada'),
            'activity_log' => $this->countTable('activity_log'),
            'audit_timeline' => $this->countTable('audit_timeline'),
            'logs_sistema' => $this->countTable('logs_sistema'),
            'permission_audits' => $this->countTable('prazzu_permission_audits'),
            'eventos_hoje' => $this->countToday(),
            'eventos_criticos' => CachedSchema::hasTable('auditoria_detalhada') && CachedSchema::hasColumn('auditoria_detalhada', 'nivel')
                ? (int) DB::table('auditoria_detalhada')->whereIn('nivel', ['alto', 'alta', 'critico', 'crítico'])->count()
                : 0,
        ];
    }

    private function fontes(): array
    {
        return [
            ['nome' => 'Auditoria detalhada', 'tabela' => 'auditoria_detalhada', 'descricao' => 'Alterações campo a campo, empresa, usuário, IP e antes/depois.', 'eventos' => $this->countTable('auditoria_detalhada'), 'ativo' => CachedSchema::hasTable('auditoria_detalhada')],
            ['nome' => 'Activity log', 'tabela' => 'activity_log', 'descricao' => 'Eventos do Spatie Activitylog e alterações gerais de modelos.', 'eventos' => $this->countTable('activity_log'), 'ativo' => CachedSchema::hasTable('activity_log')],
            ['nome' => 'Timeline de auditoria', 'tabela' => 'audit_timeline', 'descricao' => 'Histórico técnico por entidade, ação e usuário.', 'eventos' => $this->countTable('audit_timeline'), 'ativo' => CachedSchema::hasTable('audit_timeline')],
            ['nome' => 'Logs do sistema', 'tabela' => 'logs_sistema', 'descricao' => 'Ações operacionais registradas pela aplicação.', 'eventos' => $this->countTable('logs_sistema'), 'ativo' => CachedSchema::hasTable('logs_sistema')],
            ['nome' => 'Auditoria de permissões', 'tabela' => 'prazzu_permission_audits', 'descricao' => 'Mudanças em perfis, regras, vínculos e exceções de acesso.', 'eventos' => $this->countTable('prazzu_permission_audits'), 'ativo' => CachedSchema::hasTable('prazzu_permission_audits')],
        ];
    }

    private function eventosRecentes(): array
    {
        return collect()
            ->merge($this->rowsAuditoriaDetalhada())
            ->merge($this->rowsActivityLog())
            ->merge($this->rowsAuditTimeline())
            ->merge($this->rowsLogsSistema())
            ->merge($this->rowsPermissionAudits())
            ->sortByDesc('sort_date')
            ->take(20)
            ->values()
            ->all();
    }

    private function eventosPorModulo(): array
    {
        if (! CachedSchema::hasTable('auditoria_detalhada')) {
            return [];
        }

        return DB::table('auditoria_detalhada')
            ->select('auditable_type', DB::raw('COUNT(*) as total'))
            ->groupBy('auditable_type')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => ['label' => AuditoriaFormatter::modulo((string) $row->auditable_type), 'total' => (int) $row->total])
            ->all();
    }

    private function eventosPorUsuario(): array
    {
        if (! CachedSchema::hasTable('auditoria_detalhada')) {
            return [];
        }

        return DB::table('auditoria_detalhada as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->select('a.user_id', 'u.name', DB::raw('COUNT(*) as total'))
            ->groupBy('a.user_id', 'u.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => ['label' => $row->name ?: ($row->user_id ? 'Usuário #' . $row->user_id : 'Sistema'), 'total' => (int) $row->total])
            ->all();
    }

    private function links(): array
    {
        return [
            'auditoria' => Auditoria::getUrl(),
            'detalhada' => class_exists(AuditoriaDetalhadaResource::class) ? AuditoriaDetalhadaResource::getUrl('index') : null,
            'permissoes' => Permissoes::getUrl(['tab' => 'auditoria']),
        ];
    }

    private function rowsAuditoriaDetalhada(): array
    {
        if (! CachedSchema::hasTable('auditoria_detalhada')) {
            return [];
        }

        return DB::table('auditoria_detalhada as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->select('a.id', 'a.evento', 'a.nivel', 'a.auditable_type', 'a.auditable_id', 'a.campo', 'a.ip', 'a.created_at', 'u.name as user_name')
            ->orderByDesc('a.created_at')
            ->limit(12)
            ->get()
            ->map(fn ($row): array => [
                'fonte' => 'Auditoria detalhada',
                'titulo' => AuditoriaFormatter::evento((string) $row->evento),
                'detalhe' => AuditoriaFormatter::modulo((string) $row->auditable_type) . ' #' . $row->auditable_id . ($row->campo ? ' · ' . AuditoriaFormatter::campo((string) $row->campo) : ''),
                'usuario' => $row->user_name ?: 'Sistema',
                'ip' => $row->ip ?: '-',
                'nivel' => $row->nivel ?: 'normal',
                'data' => $this->date($row->created_at),
                'sort_date' => (string) $row->created_at,
            ])->all();
    }

    private function rowsActivityLog(): array
    {
        if (! CachedSchema::hasTable('activity_log')) {
            return [];
        }

        return DB::table('activity_log as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.causer_id')
            ->select('a.description', 'a.event', 'a.subject_type', 'a.subject_id', 'a.created_at', 'u.name as user_name')
            ->orderByDesc('a.created_at')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => [
                'fonte' => 'Activity log',
                'titulo' => $row->description ?: ($row->event ?: 'Evento registrado'),
                'detalhe' => AuditoriaFormatter::modulo((string) $row->subject_type) . ($row->subject_id ? ' #' . $row->subject_id : ''),
                'usuario' => $row->user_name ?: 'Sistema',
                'ip' => '-',
                'nivel' => $row->event ?: 'normal',
                'data' => $this->date($row->created_at),
                'sort_date' => (string) $row->created_at,
            ])->all();
    }

    private function rowsAuditTimeline(): array
    {
        if (! CachedSchema::hasTable('audit_timeline')) {
            return [];
        }

        return DB::table('audit_timeline as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->select('a.action', 'a.entity_type', 'a.entity_id', 'a.created_at', 'u.name as user_name')
            ->orderByDesc('a.created_at')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => [
                'fonte' => 'Timeline de auditoria',
                'titulo' => $row->action ?: 'Ação registrada',
                'detalhe' => AuditoriaFormatter::modulo((string) $row->entity_type) . ($row->entity_id ? ' #' . $row->entity_id : ''),
                'usuario' => $row->user_name ?: 'Sistema',
                'ip' => '-',
                'nivel' => 'timeline',
                'data' => $this->date($row->created_at),
                'sort_date' => (string) $row->created_at,
            ])->all();
    }

    private function rowsLogsSistema(): array
    {
        if (! CachedSchema::hasTable('logs_sistema')) {
            return [];
        }

        return DB::table('logs_sistema as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.user_id')
            ->select('l.acao', 'l.descricao', 'l.ip', 'l.created_at', 'u.name as user_name')
            ->orderByDesc('l.created_at')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => [
                'fonte' => 'Logs do sistema',
                'titulo' => $row->acao ?: 'Log registrado',
                'detalhe' => $row->descricao ?: 'Sem descrição',
                'usuario' => $row->user_name ?: 'Sistema',
                'ip' => $row->ip ?: '-',
                'nivel' => 'sistema',
                'data' => $this->date($row->created_at),
                'sort_date' => (string) $row->created_at,
            ])->all();
    }

    private function rowsPermissionAudits(): array
    {
        if (! CachedSchema::hasTable('prazzu_permission_audits')) {
            return [];
        }

        return DB::table('prazzu_permission_audits as p')
            ->leftJoin('users as u', 'u.id', '=', 'p.actor_user_id')
            ->select('p.event', 'p.module', 'p.action', 'p.scope', 'p.ip_address', 'p.created_at', 'u.name as user_name')
            ->orderByDesc('p.created_at')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => [
                'fonte' => 'Auditoria de permissões',
                'titulo' => AuditoriaFormatter::evento((string) $row->event),
                'detalhe' => trim(collect([$row->module, $row->action, $row->scope])->filter()->implode(' · ')) ?: 'Alteração de acesso',
                'usuario' => $row->user_name ?: 'Sistema',
                'ip' => $row->ip_address ?: '-',
                'nivel' => 'permissões',
                'data' => $this->date($row->created_at),
                'sort_date' => (string) $row->created_at,
            ])->all();
    }

    private function countTable(string $table): int
    {
        return CachedSchema::hasTable($table) ? (int) DB::table($table)->count() : 0;
    }

    private function countToday(): int
    {
        return collect(['auditoria_detalhada', 'activity_log', 'audit_timeline', 'logs_sistema', 'prazzu_permission_audits'])
            ->sum(fn (string $table): int => CachedSchema::hasTable($table) && CachedSchema::hasColumn($table, 'created_at')
                ? (int) DB::table($table)->whereDate('created_at', now()->toDateString())->count()
                : 0);
    }

    private function date($value): string
    {
        return $value ? \Carbon\Carbon::parse($value)->format('d/m/Y H:i') : '-';
    }
}
