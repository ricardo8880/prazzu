<?php

namespace App\Support;

use App\Models\PortalDocumento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortalClienteSecurity
{
    public const TOKEN_PATTERN = '/\A[A-Za-z0-9]{32,128}\z/';

    public static function tokenValido(?string $token): bool
    {
        $token = trim((string) $token);

        return $token !== '' && preg_match(self::TOKEN_PATTERN, $token) === 1;
    }

    public static function sanitizarToken(?string $token): ?string
    {
        $token = trim((string) $token);

        return self::tokenValido($token) ? $token : null;
    }

    public static function empresaPorToken(string $token): ?object
    {
        $token = self::sanitizarToken($token);

        if (! $token || ! CachedSchema::hasTable('empresas') || ! CachedSchema::hasColumn('empresas', 'portal_token')) {
            return null;
        }

        return DB::table('empresas')->where('portal_token', $token)->first();
    }

    public static function portalEmpresaDisponivel(?object $empresa): bool
    {
        if (! $empresa) {
            return false;
        }

        if (CachedSchema::hasColumn('empresas', 'portal_ativo') && ! (bool) ($empresa->portal_ativo ?? false)) {
            return false;
        }

        if (CachedSchema::hasColumn('empresas', 'portal_expira_em') && ! empty($empresa->portal_expira_em)) {
            try {
                return Carbon::parse($empresa->portal_expira_em)->isFuture() || Carbon::parse($empresa->portal_expira_em)->isToday();
            } catch (\Throwable) {
                return false;
            }
        }

        return true;
    }

    public static function downloadDocumentoUrl(int|string|null $documentoId, ?string $token, ?string $fallbackUrl = null): ?string
    {
        $documentoId = (int) $documentoId;
        $token = self::sanitizarToken($token);

        if ($documentoId > 0 && $token) {
            return route('portal.cliente.documentos.download', ['token' => $token, 'documento' => $documentoId]);
        }

        $fallbackUrl = trim((string) $fallbackUrl);

        return $fallbackUrl !== '' ? $fallbackUrl : null;
    }

    public static function caminhoStorageSeguro(?string $path): ?string
    {
        $path = trim(str_replace('\\', '/', (string) $path));
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '../') || str_starts_with($path, '..') || Str::contains($path, ["\0", '://'])) {
            return null;
        }

        return $path;
    }

    public static function documentoAutorizadoParaToken(int|string $documentoId, string $token): ?PortalDocumento
    {
        $empresa = self::empresaPorToken($token);

        if (! self::portalEmpresaDisponivel($empresa)) {
            return null;
        }

        if (! CachedSchema::hasTable('portal_documentos')) {
            return null;
        }

        return PortalDocumento::query()
            ->whereKey((int) $documentoId)
            ->where('empresa_id', (int) $empresa->id)
            ->where('visivel_cliente', true)
            ->first();
    }

    public static function arquivoDocumentoExiste(PortalDocumento $documento): bool
    {
        $path = self::caminhoStorageSeguro($documento->arquivo);

        return $path !== null && Storage::disk('public')->exists($path);
    }
}
