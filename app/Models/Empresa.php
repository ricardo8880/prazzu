<?php

namespace App\Models;

use App\Services\PlanoService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Empresa extends Model
{
    protected $fillable = [
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'email',
        'telefone',
        'status',
        'plano',
        'limite_usuarios',
        'limite_itens',
        'limite_armazenamento_mb',
        'limite_interacoes_ia',
        'ativo',
    ];

    protected static function booted(): void
    {
        static::saving(function (Empresa $empresa): void {
            $empresa->sincronizarLimitesDoPlano();
        });
    }

    public function assinaturas(): HasMany
    {
        return $this->hasMany(Assinatura::class);
    }

    public function assinaturaAtual(): HasOne
    {
        return $this->hasOne(Assinatura::class)->latestOfMany();
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }

    public function itemControles()
    {
        return $this->hasMany(ItemControle::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function clientePortalUsers()
    {
        return $this->hasMany(ClientePortalUser::class);
    }

    public function responsaveis()
    {
        return $this->hasMany(Responsavel::class);
    }

    public function relatoriosPersonalizados()
    {
        return $this->hasMany(RelatorioPersonalizado::class);
    }

    public function fluxosOperacionais()
    {
        return $this->hasMany(FluxoOperacional::class);
    }

    public function auditoriasDetalhadas()
    {
        return $this->hasMany(AuditoriaDetalhada::class);
    }

    public function getPlanoCodigoAttribute(): string
    {
        return PlanoService::normalizarPlano($this->plano);
    }

    public function getPlanoNomeAttribute(): string
    {
        return PlanoService::nome($this->plano);
    }

    public function getLimiteUsuariosPlanoAttribute(): int
    {
        return (int) ($this->limite_usuarios ?: PlanoService::limiteUsuarios($this->plano));
    }

    public function getLimiteItensPlanoAttribute(): int
    {
        return (int) ($this->limite_itens ?: PlanoService::limiteItens($this->plano));
    }

    public function getLimiteArmazenamentoMbPlanoAttribute(): int
    {
        return (int) ($this->limite_armazenamento_mb ?: PlanoService::limiteArmazenamentoMb($this->plano));
    }

    public function getLimiteInteracoesIaPlanoAttribute(): int
    {
        return (int) ($this->limite_interacoes_ia ?: PlanoService::limiteInteracoesIa($this->plano));
    }

    public function sincronizarLimitesDoPlano(): void
    {
        $this->plano = PlanoService::normalizarPlano($this->plano);
        $this->limite_usuarios = PlanoService::limiteUsuarios($this->plano);
        $this->limite_itens = PlanoService::limiteItens($this->plano);
        $this->limite_armazenamento_mb = PlanoService::limiteArmazenamentoMb($this->plano);
        $this->limite_interacoes_ia = PlanoService::limiteInteracoesIa($this->plano);
    }

    public function possuiFeature(string $feature): bool
    {
        return PlanoService::empresaPossuiFeature($this, $feature);
    }

    public function atingiuLimiteUsuarios(): bool
    {
        return $this->users()->count() >= $this->limite_usuarios_plano;
    }

    public function atingiuLimiteItens(): bool
    {
        return $this->itemControles()->count() >= $this->limite_itens_plano;
    }

    public function isAtivo(): bool
    {
        return (bool) $this->ativo;
    }

    public function possuiAssinaturaAtiva(): bool
    {
        $assinatura = $this->relationLoaded('assinaturaAtual')
            ? $this->assinaturaAtual
            : $this->assinaturaAtual()->first();

        return $assinatura?->estaAtiva() === true;
    }
}
