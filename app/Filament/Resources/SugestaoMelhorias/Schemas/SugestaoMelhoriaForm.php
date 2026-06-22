<?php

namespace App\Filament\Resources\SugestaoMelhorias\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class SugestaoMelhoriaForm
{
    public static function make(Schema $schema): Schema
    {
        $user = Filament::auth()->user();
        $isSuperAdmin = $user?->isSuperAdmin() === true;

        return $schema
            ->components([
                Section::make('Central de Evolução')
                    ->description($isSuperAdmin
                        ? 'Analise a contribuição recebida e registre uma resposta clara sobre o próximo passo.'
                        : 'Conte sua dor, reclamação ou ideia. Cada contribuição é analisada com foco em impacto, recorrência e escalabilidade para evoluir o Prazzu.'
                    )
                    ->icon('heroicon-o-light-bulb')
                    ->schema([
                        Placeholder::make('mensagem_evolucao')
                            ->label('Sua voz ajuda a evoluir o Prazzu')
                            ->content(fn (): HtmlString => new HtmlString(self::renderMensagemEvolucao($isSuperAdmin)))
                            ->visible(fn (): bool => ! $isSuperAdmin)
                            ->columnSpanFull(),

                        Hidden::make('empresa_id')
                            ->default(fn (): ?int => $user?->empresa_id)
                            ->dehydrated(true),

                        Hidden::make('user_id')
                            ->default(fn (): ?int => $user?->id)
                            ->dehydrated(true),

                        Select::make('tipo')
                            ->label('O que você quer compartilhar?')
                            ->helperText('Escolha a opção que melhor representa sua contribuição.')
                            ->required()
                            ->default('melhoria')
                            ->native(false)
                            ->options([
                                'bug' => 'Relatar uma dor ou problema',
                                'melhoria' => 'Sugerir uma melhoria',
                                'funcionalidade' => 'Enviar uma ideia de funcionalidade',
                                'duvida' => 'Fazer uma dúvida sobre o uso',
                                'outro' => 'Outro tipo de contribuição',
                            ]),

                        Select::make('prioridade')
                            ->label('Impacto na sua rotina')
                            ->helperText('Ajuda a entender o quanto isso atrapalha ou pode melhorar o dia a dia.')
                            ->required()
                            ->default('media')
                            ->native(false)
                            ->options([
                                'baixa' => 'Baixo — seria bom melhorar',
                                'media' => 'Médio — impacta minha rotina',
                                'alta' => 'Alto — atrapalha bastante o trabalho',
                            ]),

                        TextInput::make('titulo')
                            ->label('Resumo da contribuição')
                            ->placeholder('Ex.: Facilitar o envio de documentos pelo cliente')
                            ->helperText('Use uma frase curta para facilitar a análise e evitar contribuições duplicadas.')
                            ->required()
                            ->maxLength(255)
                            ->trim()
                            ->columnSpanFull(),

                        Textarea::make('descricao')
                            ->label('Conte o que aconteceu, o que incomoda ou o que você gostaria de ver')
                            ->placeholder("Explique sua dor, sugestão ou ideia. Se puder, conte em qual tela acontece, qual era o resultado esperado e como isso ajudaria outras empresas.")
                            ->helperText('Priorizar soluções escaláveis fica mais fácil quando você explica o problema real e o impacto na operação.')
                            ->required()
                            ->rows(7)
                            ->maxLength(10000)
                            ->columnSpanFull(),

                        Placeholder::make('criterio_priorizacao')
                            ->label('Como avaliamos')
                            ->content(fn (): HtmlString => new HtmlString(self::renderCriterioPriorizacao()))
                            ->visible(fn (): bool => ! $isSuperAdmin)
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Status da evolução')
                            ->required()
                            ->default('aberta')
                            ->native(false)
                            ->visible(fn (): bool => Filament::auth()->user()?->isSuperAdmin() === true)
                            ->dehydrated(fn (): bool => Filament::auth()->user()?->isSuperAdmin() === true)
                            ->options([
                                'aberta' => 'Recebida',
                                'em_analise' => 'Em análise',
                                'aceita' => 'Planejada',
                                'recusada' => 'Não seguirá agora',
                                'implementada' => 'Implementada',
                            ]),

                        Textarea::make('resposta_admin')
                            ->label('Resposta para o cliente')
                            ->rows(5)
                            ->placeholder('Explique o próximo passo de forma clara: em análise, planejado, implementado ou por que não seguirá agora.')
                            ->visible(fn (): bool => Filament::auth()->user()?->isSuperAdmin() === true)
                            ->disabled(fn (): bool => Filament::auth()->user()?->isSuperAdmin() !== true)
                            ->dehydrated(fn (): bool => Filament::auth()->user()?->isSuperAdmin() === true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    protected static function renderMensagemEvolucao(bool $isSuperAdmin): string
    {
        if ($isSuperAdmin) {
            return '';
        }

        return <<<HTML
<div style="display:grid;gap:14px;">
    <div style="border:1px solid #dbeafe;background:linear-gradient(135deg,#eff6ff,#f8fafc);border-radius:18px;padding:18px;">
        <div style="font-size:15px;font-weight:800;color:#0f172a;margin-bottom:6px;">Aqui você pode falar sua dor, reclamação ou ideia.</div>
        <div style="font-size:14px;line-height:1.65;color:#334155;">O Prazzu evolui a partir de problemas reais dos clientes. As contribuições são avaliadas por impacto, recorrência e potencial de ajudar várias empresas, para que o produto cresça de forma escalável.</div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;">
        <div style="border:1px solid #e5e7eb;border-radius:14px;padding:14px;background:#fff;">
            <div style="font-size:13px;font-weight:800;color:#991b1b;margin-bottom:4px;">Relate uma dor</div>
            <div style="font-size:12px;line-height:1.5;color:#64748b;">Algo trava, confunde ou atrapalha sua rotina?</div>
        </div>
        <div style="border:1px solid #e5e7eb;border-radius:14px;padding:14px;background:#fff;">
            <div style="font-size:13px;font-weight:800;color:#075985;margin-bottom:4px;">Sugira uma melhoria</div>
            <div style="font-size:12px;line-height:1.5;color:#64748b;">Viu algo que poderia ser mais simples, rápido ou claro?</div>
        </div>
        <div style="border:1px solid #e5e7eb;border-radius:14px;padding:14px;background:#fff;">
            <div style="font-size:13px;font-weight:800;color:#166534;margin-bottom:4px;">Envie uma ideia</div>
            <div style="font-size:12px;line-height:1.5;color:#64748b;">Pensou em uma função que ajudaria várias empresas?</div>
        </div>
    </div>
</div>
HTML;
    }

    protected static function renderCriterioPriorizacao(): string
    {
        return <<<HTML
<div style="border:1px solid #e5e7eb;border-radius:16px;padding:16px;background:#f9fafb;">
    <div style="font-size:13px;font-weight:800;color:#111827;margin-bottom:8px;">O que aumenta a chance de uma contribuição virar evolução?</div>
    <div style="display:grid;gap:6px;font-size:13px;line-height:1.55;color:#4b5563;">
        <div>• Resolver uma dor recorrente para várias empresas.</div>
        <div>• Reduzir retrabalho, erro operacional ou tempo perdido.</div>
        <div>• Melhorar clareza, velocidade ou segurança de uma rotina importante.</div>
    </div>
</div>
HTML;
    }
}
