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
        $isAdminReview = $isSuperAdmin && ! self::isCreateRequest();

        return $schema
            ->components([
                Section::make($isAdminReview ? 'Análise da contribuição' : 'Central de Evolução')
                    ->description($isAdminReview
                        ? 'Avalie a contribuição, atualize o status da evolução e registre uma resposta clara para o cliente.'
                        : 'Conte sua dor, reclamação ou ideia. Cada contribuição é analisada com foco em impacto, recorrência e escalabilidade para evoluir o Prazzu.'
                    )
                    ->icon($isAdminReview ? 'heroicon-o-clipboard-document-check' : 'heroicon-o-light-bulb')
                    ->schema([
                        Placeholder::make('mensagem_evolucao')
                            ->label('Sua voz ajuda a evoluir o Prazzu')
                            ->content(fn (): HtmlString => new HtmlString(self::renderMensagemEvolucao()))
                            ->visible(fn (): bool => ! self::isAdminReviewForm())
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
                            ->placeholder("Explique com liberdade. Se você veio com uma dor ou reclamação, conte o cenário, em qual tela acontece, o que você tentou fazer e como isso atrapalha sua rotina.\n\nEx.: Eu estava em Documentos, tentei enviar um arquivo para o cliente, mas o caminho não ficou claro. Isso me fez perder tempo e eu gostaria que fosse mais direto.")
                            ->helperText('Quanto mais claro for o problema real e o impacto, mais fácil será priorizar uma solução escalável.')
                            ->required()
                            ->rows(10)
                            ->maxLength(10000)
                            ->columnSpanFull(),

                        Placeholder::make('criterio_priorizacao')
                            ->label('Como sua contribuição será avaliada')
                            ->content(fn (): HtmlString => new HtmlString(self::renderCriterioPriorizacao()))
                            ->visible(fn (): bool => ! self::isAdminReviewForm())
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Status da evolução')
                            ->helperText('Este campo é decidido apenas pela equipe do Prazzu.')
                            ->required()
                            ->default('aberta')
                            ->native(false)
                            ->visible(fn (): bool => self::isAdminReviewForm())
                            ->dehydrated(fn (): bool => self::isAdminReviewForm())
                            ->options([
                                'aberta' => 'Recebida',
                                'em_analise' => 'Em análise',
                                'aceita' => 'Planejada',
                                'recusada' => 'Não seguirá agora',
                                'implementada' => 'Implementada',
                            ]),

                        Textarea::make('resposta_admin')
                            ->label('Resposta para o cliente')
                            ->rows(6)
                            ->placeholder('Explique o próximo passo de forma clara: em análise, planejado, implementado ou por que não seguirá agora.')
                            ->visible(fn (): bool => self::isAdminReviewForm())
                            ->disabled(fn (): bool => ! self::isAdminReviewForm())
                            ->dehydrated(fn (): bool => self::isAdminReviewForm())
                            ->columnSpanFull(),
                    ])
                    ->columns(['default' => 1, 'lg' => 2]),
            ]);
    }

    protected static function isCreateRequest(): bool
    {
        return str_contains((string) request()->route()?->getName(), '.create')
            || str_ends_with(trim((string) request()->path(), '/'), '/create');
    }

    protected static function isAdminReviewForm(): bool
    {
        return Filament::auth()->user()?->isSuperAdmin() === true && ! self::isCreateRequest();
    }

    protected static function renderMensagemEvolucao(): string
    {
        return <<<HTML
<div style="display:grid;gap:16px;">
    <div style="border:1px solid #dbeafe;background:linear-gradient(135deg,#eff6ff,#ffffff 58%,#f8fafc);border-radius:22px;padding:24px;box-shadow:0 18px 45px rgba(15,23,42,.06);">
        <div style="display:flex;gap:14px;align-items:flex-start;">
            <div style="width:44px;height:44px;border-radius:16px;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:900;flex:0 0 auto;">!</div>
            <div>
                <div style="font-size:18px;font-weight:900;color:#0f172a;margin-bottom:6px;">Aqui você pode falar sua dor, reclamação ou ideia.</div>
                <div style="font-size:14px;line-height:1.7;color:#334155;max-width:920px;">O Prazzu evolui a partir de problemas reais dos clientes. Pode escrever com clareza, inclusive se algo te deixou irritado ou travou sua rotina. Tudo é analisado com foco em impacto, recorrência e escalabilidade para virar melhoria de produto.</div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;">
        <div style="border:1px solid #fecaca;border-radius:18px;padding:16px;background:#fff;">
            <div style="font-size:13px;font-weight:900;color:#991b1b;margin-bottom:5px;">Relate uma dor</div>
            <div style="font-size:12px;line-height:1.55;color:#64748b;">Algo trava, confunde ou atrapalha sua rotina?</div>
        </div>
        <div style="border:1px solid #bfdbfe;border-radius:18px;padding:16px;background:#fff;">
            <div style="font-size:13px;font-weight:900;color:#075985;margin-bottom:5px;">Sugira uma melhoria</div>
            <div style="font-size:12px;line-height:1.55;color:#64748b;">Viu algo que poderia ser mais simples, rápido ou claro?</div>
        </div>
        <div style="border:1px solid #bbf7d0;border-radius:18px;padding:16px;background:#fff;">
            <div style="font-size:13px;font-weight:900;color:#166534;margin-bottom:5px;">Envie uma ideia</div>
            <div style="font-size:12px;line-height:1.55;color:#64748b;">Pensou em uma função que ajudaria várias empresas?</div>
        </div>
    </div>
</div>
HTML;
    }

    protected static function renderCriterioPriorizacao(): string
    {
        return <<<HTML
<div style="border:1px solid #e5e7eb;border-radius:18px;padding:18px;background:#f9fafb;">
    <div style="font-size:13px;font-weight:900;color:#111827;margin-bottom:8px;">O que aumenta a chance de virar evolução?</div>
    <div style="display:grid;gap:7px;font-size:13px;line-height:1.6;color:#4b5563;">
        <div>• Resolver uma dor recorrente para várias empresas.</div>
        <div>• Reduzir retrabalho, erro operacional ou tempo perdido.</div>
        <div>• Melhorar clareza, velocidade ou segurança de uma rotina importante.</div>
    </div>
</div>
HTML;
    }
}
