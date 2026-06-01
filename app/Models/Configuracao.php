<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class Configuracao extends Model
{
    protected $table = 'configuracoes';

    protected $fillable = [
        'empresa_id',
        'dias_alerta',
        'dias_lembrete',
        'enviar_email',
        'enviar_sistema',
        'modulos_ativos',
        'workflow_status',
        'campos_personalizados',
        'notificacoes_granulares',
        'tema',
        'cor_tema',
        'tamanho_fonte',
        'layout_sidebar',
        'automacoes_fluxo',
        'permissoes_acesso',
        'integracoes_terceiros',
        'horas_semanais',
        'feriados',
        'limite_capacidade',
        'templates_estrutura',
        'visualizacao_padrao',
        'onboarding_progresso',
        'onboarding_recursos',
        'onboarding_preferencias',
        'onboarding_finalizado_em',
        'exigir_2fa',
        'sso_provider',
        'registrar_login',
        'white_label',
    ];

    protected $casts = [
        'dias_alerta' => 'integer',
        'dias_lembrete' => 'integer',
        'enviar_email' => 'boolean',
        'enviar_sistema' => 'boolean',
        'modulos_ativos' => 'array',
        'workflow_status' => 'array',
        'campos_personalizados' => 'array',
        'notificacoes_granulares' => 'array',
        'automacoes_fluxo' => 'array',
        'permissoes_acesso' => 'array',
        'integracoes_terceiros' => 'array',
        'horas_semanais' => 'integer',
        'feriados' => 'array',
        'limite_capacidade' => 'integer',
        'templates_estrutura' => 'array',
        'onboarding_progresso' => 'array',
        'onboarding_recursos' => 'array',
        'onboarding_preferencias' => 'array',
        'onboarding_finalizado_em' => 'datetime',
        'exigir_2fa' => 'boolean',
        'registrar_login' => 'boolean',
        'white_label' => 'array',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public static function defaults(): array
    {
        return [
            'dias_alerta' => 3,
            'dias_lembrete' => 2,
            'enviar_email' => true,
            'enviar_sistema' => true,
            'modulos_ativos' => [
                'controle_tempo',
                'metas',
                'prioridades',
                'mapas',
                'documentos',
                'financeiro',
            ],
            'workflow_status' => [
                'A fazer',
                'Em andamento',
                'Em revisão',
                'Concluído',
            ],
            'campos_personalizados' => [
                'Centro de custo: texto',
                'Valor previsto: moeda',
                'Prioridade executiva: lista',
            ],
            'notificacoes_granulares' => [
                'mencoes_email',
                'mencoes_sistema',
                'vencimentos_email',
                'comentarios_sistema',
            ],
            'tema' => 'system',
            'cor_tema' => 'blue',
            'tamanho_fonte' => 'normal',
            'layout_sidebar' => 'expanded',
            'automacoes_fluxo' => [
                'notificar_status_pronto',
                'marcar_risco_sla',
                'arquivar_concluidos',
            ],
            'permissoes_acesso' => [
                'listas' => 'admin_gestor',
                'pastas' => 'admin_gestor',
                'espacos' => 'admin',
            ],
            'integracoes_terceiros' => [],
            'horas_semanais' => 44,
            'feriados' => [],
            'limite_capacidade' => 100,
            'templates_estrutura' => [
                'Onboarding de cliente',
                'Rotina mensal de documentos',
                'Controle de vencimentos',
            ],
            'visualizacao_padrao' => 'lista',
            'onboarding_progresso' => [],
            'onboarding_recursos' => [],
            'onboarding_preferencias' => [],
            'onboarding_finalizado_em' => null,
            'exigir_2fa' => false,
            'sso_provider' => null,
            'registrar_login' => true,
            'white_label' => \App\Support\WhiteLabelSettings::defaults(),
        ];
    }

    public static function forEmpresaId(?int $empresaId): self
    {
        if (! $empresaId) {
            return new self(static::defaults());
        }

        return static::query()->firstOrCreate(
            ['empresa_id' => $empresaId],
            static::defaults()
        );
    }

    /**
     * Compatibilidade com código antigo que lia uma coluna única chamada "onboarding".
     * No banco atual, o onboarding está separado em progresso, recursos, preferências e finalização.
     */
    public function getOnboardingAttribute(): array
    {
        return [
            'progresso' => $this->onboarding_progresso ?? [],
            'recursos' => $this->onboarding_recursos ?? [],
            'preferencias' => $this->onboarding_preferencias ?? [],
            'finalizado_em' => $this->onboarding_finalizado_em,
        ];
    }

    public function setOnboardingAttribute($value): void
    {
        $value = is_array($value) ? $value : [];

        $this->attributes['onboarding_progresso'] = json_encode($value['progresso'] ?? [], JSON_UNESCAPED_UNICODE);
        $this->attributes['onboarding_recursos'] = json_encode($value['recursos'] ?? [], JSON_UNESCAPED_UNICODE);
        $this->attributes['onboarding_preferencias'] = json_encode($value['preferencias'] ?? [], JSON_UNESCAPED_UNICODE);
        $this->attributes['onboarding_finalizado_em'] = $value['finalizado_em'] ?? null;
    }

    public function moduloAtivo(string $modulo): bool
    {
        return in_array($modulo, $this->modulos_ativos ?? [], true);
    }

    public function automacaoAtiva(string $automacao): bool
    {
        return in_array($automacao, $this->automacoes_fluxo ?? [], true);
    }

    public function notificacaoAtiva(string $notificacao): bool
    {
        return in_array($notificacao, $this->notificacoes_granulares ?? [], true);
    }

    public function permissaoPara(string $nivel): ?string
    {
        return Arr::get($this->permissoes_acesso ?? [], $nivel);
    }

    public function aplicarDefaultsFaltantes(): self
    {
        $defaults = static::defaults();

        foreach ($defaults as $campo => $valor) {
            if ($this->{$campo} === null || $this->{$campo} === []) {
                $this->{$campo} = $valor;
            }
        }

        $this->white_label = \App\Support\WhiteLabelSettings::mergeDefaults($this->white_label ?? []);

        return $this;
    }
}
