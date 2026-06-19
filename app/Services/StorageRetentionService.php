<?php

namespace App\Services;

use App\Models\ItemControle;
use App\Support\CachedSchema;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StorageRetentionService
{
    public function ready(): bool
    {
        return CachedSchema::hasTable('file_retention_policies') && CachedSchema::hasTable('file_retention_events');
    }

    /** @return array<int, array<string, mixed>> */
    public function policies(): array
    {
        if (! $this->ready()) {
            return [];
        }

        return DB::table('file_retention_policies')
            ->orderByDesc('is_active')
            ->orderBy('scope_type')
            ->orderBy('name')
            ->get()
            ->map(fn ($policy): array => $this->formatPolicy((array) $policy))
            ->all();
    }

    /** @return array<string, mixed> */
    public function summary(?Authenticatable $user = null): array
    {
        $files = collect($this->files($user));
        $policies = collect($this->policies())->where('is_active', true)->values();
        $candidates = $this->candidates($files, $policies);

        return [
            'ready' => $this->ready(),
            'policies' => $policies->all(),
            'all_policies' => $this->policies(),
            'candidates' => $candidates->take(30)->values()->all(),
            'counts' => [
                'policies' => $policies->count(),
                'temporary' => $policies->where('storage_type', 'temporario')->count(),
                'permanent' => $policies->where('storage_type', 'permanente')->count(),
                'due_archive' => $candidates->where('action', 'arquivar')->count(),
                'due_delete' => $candidates->where('action', 'excluir')->count(),
                'keep' => $files->count() - $candidates->count(),
                'space' => $this->formatBytes((int) $candidates->sum('tamanho_bytes')),
            ],
            'recent_events' => $this->recentEvents(),
            'supports_processing' => true,
        ];
    }

    /** @param array<string, mixed> $data */
    public function createPolicy(array $data): void
    {
        if (! $this->ready()) {
            return;
        }

        $scopeType = (string) ($data['scope_type'] ?? 'global');
        $action = (string) ($data['action'] ?? 'arquivar');
        $storageType = (string) ($data['storage_type'] ?? 'temporario');
        $retentionDays = $action === 'manter' ? null : max(1, (int) ($data['retention_days'] ?? 7));

        DB::table('file_retention_policies')->insert([
            'name' => trim((string) ($data['name'] ?? 'Política de retenção')) ?: 'Política de retenção',
            'scope_type' => in_array($scopeType, ['global', 'empresa', 'origem'], true) ? $scopeType : 'global',
            'empresa_id' => $scopeType === 'empresa' ? ((int) ($data['empresa_id'] ?? 0) ?: null) : null,
            'origin' => $scopeType === 'origem' ? ((string) ($data['origin'] ?? '') ?: null) : null,
            'storage_type' => in_array($storageType, ['temporario', 'permanente'], true) ? $storageType : 'temporario',
            'action' => in_array($action, ['arquivar', 'excluir', 'manter'], true) ? $action : 'arquivar',
            'retention_days' => $retentionDays,
            'is_active' => true,
            'notes' => trim((string) ($data['notes'] ?? '')) ?: null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function togglePolicy(int $policyId): void
    {
        if (! $this->ready()) {
            return;
        }

        $policy = DB::table('file_retention_policies')->where('id', $policyId)->first();
        if (! $policy) {
            return;
        }

        DB::table('file_retention_policies')->where('id', $policyId)->update([
            'is_active' => ! (bool) $policy->is_active,
            'updated_at' => now(),
        ]);
    }

    /** @return array<string, int> */
    public function process(?Authenticatable $user = null, int $limit = 100): array
    {
        $summary = $this->summary($user);
        $processed = ['arquivados' => 0, 'excluidos' => 0, 'erros' => 0];

        foreach (array_slice($summary['candidates'], 0, $limit) as $candidate) {
            try {
                $result = $candidate['action'] === 'excluir'
                    ? $this->deleteFile($candidate)
                    : $this->archiveFile($candidate);

                $this->recordEvent($candidate, $result['status'], $result['message']);

                if ($candidate['action'] === 'excluir' && $result['status'] === 'processado') { $processed['excluidos']++; }
                if ($candidate['action'] === 'arquivar' && $result['status'] === 'processado') { $processed['arquivados']++; }
                if ($result['status'] !== 'processado') { $processed['erros']++; }
            } catch (Throwable $e) {
                $this->recordEvent($candidate, 'erro', $e->getMessage());
                $processed['erros']++;
            }
        }

        return $processed;
    }

    /** @return array<int, array<string, mixed>> */
    public function files(?Authenticatable $user = null): array
    {
        $files = collect();

        if (CachedSchema::hasTable('item_controle_anexos')) {
            $query = DB::table('item_controle_anexos as anexos')
                ->leftJoin('item_controles as itens', 'itens.id', '=', 'anexos.item_controle_id')
                ->leftJoin('empresas', 'empresas.id', '=', 'itens.empresa_id')
                ->select(['anexos.id', DB::raw("COALESCE(anexos.nome_original, anexos.arquivo, anexos.caminho, 'Arquivo sem nome') as nome"), DB::raw('COALESCE(anexos.caminho, anexos.arquivo) as caminho'), DB::raw('COALESCE(anexos.tamanho_bytes, 0) as tamanho_bytes'), 'anexos.created_at', 'anexos.updated_at', 'empresas.id as empresa_id', DB::raw("COALESCE(empresas.nome_fantasia, empresas.razao_social, 'Sem empresa') as empresa_nome")]);

            if ($user && CachedSchema::hasTable('item_controles')) {
                $query->whereIn('itens.id', ItemControle::query()->visibleForUser($user)->pluck('id'));
            }

            $files = $files->merge($query->limit(1000)->get()->map(fn ($row): array => $this->normalizeFile((array) $row, 'Anexo')));
        }

        if (CachedSchema::hasTable('item_controles') && CachedSchema::hasColumn('item_controles', 'arquivo')) {
            $query = ItemControle::query()->select(['id', 'titulo', 'arquivo', 'empresa_id', 'created_at', 'updated_at'])->whereNotNull('arquivo')->where('arquivo', '<>', '');
            if ($user) { $query->visibleForUser($user); }
            $files = $files->merge($query->limit(1000)->get()->map(fn (ItemControle $item): array => $this->normalizeFile([
                'id' => $item->id,
                'nome' => basename((string) $item->arquivo),
                'caminho' => (string) $item->arquivo,
                'tamanho_bytes' => 0,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'empresa_id' => $item->empresa_id,
                'empresa_nome' => 'Empresa #' . $item->empresa_id,
            ], 'Documento')));
        }

        if (CachedSchema::hasTable('portal_documentos') && CachedSchema::hasColumn('portal_documentos', 'arquivo')) {
            $rows = DB::table('portal_documentos')
                ->leftJoin('empresas', 'empresas.id', '=', 'portal_documentos.empresa_id')
                ->select(['portal_documentos.id', DB::raw("COALESCE(portal_documentos.titulo, portal_documentos.arquivo, 'Documento do portal') as nome"), 'portal_documentos.arquivo as caminho', DB::raw('0 as tamanho_bytes'), 'portal_documentos.created_at', 'portal_documentos.updated_at', 'empresas.id as empresa_id', DB::raw("COALESCE(empresas.nome_fantasia, empresas.razao_social, 'Sem empresa') as empresa_nome")])
                ->whereNotNull('portal_documentos.arquivo')
                ->where('portal_documentos.arquivo', '<>', '')
                ->limit(1000)
                ->get();
            $files = $files->merge($rows->map(fn ($row): array => $this->normalizeFile((array) $row, 'Portal')));
        }

        return $files->unique(fn (array $file): string => $file['origem'] . ':' . $file['id'] . ':' . $file['caminho'])->values()->all();
    }

    private function candidates(Collection $files, Collection $policies): Collection
    {
        return $files->map(function (array $file) use ($policies): ?array {
            $policy = $this->matchingPolicy($file, $policies);
            if (! $policy || $policy['action'] === 'manter') {
                return null;
            }

            $dueAt = Carbon::parse($file['created_at'] ?: $file['updated_at'] ?: now())->addDays((int) $policy['retention_days']);
            if ($dueAt->isFuture()) {
                return null;
            }

            $file['policy'] = $policy;
            $file['policy_id'] = $policy['id'];
            $file['policy_name'] = $policy['name'];
            $file['action'] = $policy['action'];
            $file['due_at'] = $dueAt->format('d/m/Y');
            $file['dias_vencido'] = max(0, (int) now()->diffInDays($dueAt));

            return $file;
        })->filter()->sortByDesc('dias_vencido')->values();
    }

    private function matchingPolicy(array $file, Collection $policies): ?array
    {
        return $policies->first(fn (array $policy): bool => $policy['scope_type'] === 'empresa' && (int) $policy['empresa_id'] === (int) ($file['empresa_id'] ?? 0))
            ?? $policies->first(fn (array $policy): bool => $policy['scope_type'] === 'origem' && $policy['origin'] === ($file['origem'] ?? null))
            ?? $policies->first(fn (array $policy): bool => $policy['scope_type'] === 'global');
    }

    private function normalizeFile(array $file, string $origin): array
    {
        $path = trim((string) ($file['caminho'] ?? ''));
        $size = (int) ($file['tamanho_bytes'] ?? 0);
        if ($size <= 0 && $path !== '') { $size = $this->realSize($path); }

        return [
            'id' => $file['id'] ?? null,
            'nome' => (string) ($file['nome'] ?? basename($path) ?: 'Arquivo sem nome'),
            'caminho' => $path,
            'origem' => $origin,
            'empresa_id' => $file['empresa_id'] ?? null,
            'empresa_nome' => (string) ($file['empresa_nome'] ?? 'Sem empresa'),
            'tamanho_bytes' => max(0, $size),
            'tamanho_formatado' => $this->formatBytes(max(0, $size)),
            'created_at' => $file['created_at'] ?? null,
            'updated_at' => $file['updated_at'] ?? null,
        ];
    }

    private function archiveFile(array $file): array
    {
        $path = $this->existingPath((string) $file['caminho']);
        if (! $path) {
            return ['status' => 'erro', 'message' => 'Arquivo físico não encontrado para arquivamento.'];
        }

        $target = 'retencao/arquivados/' . now()->format('Y/m') . '/' . uniqid('', true) . '-' . basename($path);
        Storage::move($path, $target);
        $this->updatePathReference($file, $target);

        return ['status' => 'processado', 'message' => 'Arquivo arquivado em ' . $target . '.'];
    }

    private function deleteFile(array $file): array
    {
        $path = $this->existingPath((string) $file['caminho']);
        if ($path) { Storage::delete($path); }
        $this->clearPathReference($file);

        return ['status' => 'processado', 'message' => $path ? 'Arquivo excluído e referência limpa.' : 'Referência limpa; arquivo físico não estava no disco.'];
    }

    private function updatePathReference(array $file, string $target): void
    {
        match ($file['origem']) {
            'Anexo' => CachedSchema::hasTable('item_controle_anexos') && CachedSchema::hasColumn('item_controle_anexos', 'caminho') ? DB::table('item_controle_anexos')->where('id', $file['id'])->update(['caminho' => $target, 'updated_at' => now()]) : null,
            'Documento' => CachedSchema::hasTable('item_controles') && CachedSchema::hasColumn('item_controles', 'arquivo') ? DB::table('item_controles')->where('id', $file['id'])->update(['arquivo' => $target, 'updated_at' => now()]) : null,
            'Portal' => CachedSchema::hasTable('portal_documentos') && CachedSchema::hasColumn('portal_documentos', 'arquivo') ? DB::table('portal_documentos')->where('id', $file['id'])->update(['arquivo' => $target, 'updated_at' => now()]) : null,
            default => null,
        };
    }

    private function clearPathReference(array $file): void
    {
        match ($file['origem']) {
            'Anexo' => CachedSchema::hasTable('item_controle_anexos') ? DB::table('item_controle_anexos')->where('id', $file['id'])->delete() : null,
            'Documento' => CachedSchema::hasTable('item_controles') && CachedSchema::hasColumn('item_controles', 'arquivo') ? DB::table('item_controles')->where('id', $file['id'])->update(['arquivo' => null, 'updated_at' => now()]) : null,
            'Portal' => CachedSchema::hasTable('portal_documentos') ? DB::table('portal_documentos')->where('id', $file['id'])->delete() : null,
            default => null,
        };
    }

    private function recordEvent(array $file, string $status, string $message): void
    {
        if (! $this->ready()) { return; }
        DB::table('file_retention_events')->insert([
            'file_retention_policy_id' => $file['policy_id'] ?? null,
            'arquivo_origem' => $file['origem'] ?? null,
            'arquivo_id' => $file['id'] ?? null,
            'empresa_id' => $file['empresa_id'] ?? null,
            'file_name' => $file['nome'] ?? null,
            'action' => $file['action'] ?? null,
            'status' => $status,
            'size_bytes' => $file['tamanho_bytes'] ?? 0,
            'due_at' => isset($file['due_at']) ? Carbon::createFromFormat('d/m/Y', $file['due_at'])->toDateString() : null,
            'processed_at' => now(),
            'message' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function recentEvents(): array
    {
        if (! $this->ready()) { return []; }
        return DB::table('file_retention_events')
            ->leftJoin('file_retention_policies', 'file_retention_policies.id', '=', 'file_retention_events.file_retention_policy_id')
            ->select('file_retention_events.*', 'file_retention_policies.name as policy_name')
            ->orderByDesc('file_retention_events.created_at')
            ->limit(8)
            ->get()
            ->map(fn ($event): array => (array) $event)
            ->all();
    }

    private function formatPolicy(array $policy): array
    {
        $policy['is_active'] = (bool) ($policy['is_active'] ?? false);
        $policy['retention_label'] = ($policy['action'] ?? '') === 'manter' ? 'Nunca excluir' : (($policy['action'] === 'excluir' ? 'Excluir' : 'Arquivar') . ' após ' . (int) ($policy['retention_days'] ?? 0) . ' dia(s)');
        $policy['scope_label'] = match ($policy['scope_type'] ?? 'global') {
            'empresa' => 'Cliente #' . ($policy['empresa_id'] ?? '-'),
            'origem' => 'Origem: ' . ($policy['origin'] ?? '-'),
            default => 'Global',
        };
        return $policy;
    }

    private function realSize(string $path): int
    {
        $existing = $this->existingPath($path);
        return $existing ? (int) Storage::size($existing) : 0;
    }

    private function existingPath(string $path): ?string
    {
        foreach (array_unique([$path, ltrim($path, '/'), 'public/' . ltrim($path, '/'), str_replace('storage/', 'public/', ltrim($path, '/'))]) as $candidate) {
            try { if ($candidate && Storage::exists($candidate)) { return $candidate; } } catch (Throwable) { }
        }
        return null;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) { return '0 B'; }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        return number_format($bytes / (1024 ** $power), $power === 0 ? 0 : 1, ',', '.') . ' ' . $units[$power];
    }
}
