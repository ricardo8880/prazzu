<?php

namespace App\Filament\Pages;


use App\Support\CachedSchema;
use App\Models\Configuracao;
use App\Support\WhiteLabelSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class WhiteLabel extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationLabel = 'White Label';

    protected static ?string $title = 'White Label';

    protected static ?string $slug = 'white-label';

    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.white-label';

    public ?array $data = [];

    public function mount(): void
    {
        $whiteLabel = null;

        try {
            if (CachedSchema::hasTable('configuracoes')) {
                $whiteLabel = Configuracao::query()->first()?->white_label;
            }
        } catch (Throwable $exception) {
            Log::warning('Falha ao carregar configuração de White Label.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            $whiteLabel = null;
        }

        $this->form->fill(
            WhiteLabelSettings::mergeDefaults($whiteLabel)
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('restaurarPadrao')
                ->label('Restaurar padrão')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Restaurar White Label padrão?')
                ->modalDescription('Isso vai limpar as personalizações atuais e voltar para os valores iniciais.')
                ->action(function (): void {
                    $this->form->fill(
                        WhiteLabelSettings::defaults()
                    );

                    $this->save();

                    Notification::make()
                        ->title('White Label restaurado')
                        ->success()
                        ->send();
                }),

            Action::make('salvar')
                ->label('Salvar White Label')
                ->icon('heroicon-o-check')
                ->color('warning')
                ->action('save'),
        ];
    }

    public function form($form)
    {
        return $form
            ->schema([
                Tabs::make('White Label')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Geral')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Ativação')
                                    ->description('Controle se a identidade visual personalizada será aplicada no sistema.')
                                    ->schema([
                                        Toggle::make('ativo')
                                            ->label('Ativar White Label')
                                            ->helperText('Quando ativo, o sistema aplica marca, cores, logo, favicon e preferências cadastradas.')
                                            ->live(),

                                        TextInput::make('workspace_padrao')
                                            ->label('Nome do workspace / marca')
                                            ->maxLength(120)
                                            ->required(),
                                    ])
                                    ->columns(2),

                                Section::make('Domínio personalizado')
                                    ->description('Configure domínio e links de convite com a identidade da empresa.')
                                    ->schema([
                                        TextInput::make('dominio_personalizado')
                                            ->label('Domínio personalizado')
                                            ->placeholder('portal.suaempresa.com.br')
                                            ->maxLength(255)
                                            ->helperText('Informe apenas o domínio, sem https://.'),

                                        TextInput::make('url_convite')
                                            ->label('URL de convite personalizada')
                                            ->placeholder('https://portal.suaempresa.com.br/convite')
                                            ->url()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Branding')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Logotipos e ícones')
                                    ->description('Envie os arquivos que serão usados no painel, login, documentos e e-mails.')
                                    ->schema([
                                        FileUpload::make('logo_light')
                                            ->label('Logo Light')
                                            ->helperText(fn (): HtmlString => self::currentImageHelper('logo_light', 'Logo Light'))
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'])
                                            ->storeFiles()
                                            ->fetchFileInformation(false)
                                            ->previewable(false)
                                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => self::storeWhiteLabelFile($file, 'logos'))
                                            ->deleteUploadedFileUsing(fn (string | array | null $file) => WhiteLabelSettings::deletePublicImage($file))
                                            ->getUploadedFileUsing(fn (string $file): ?array => self::getPublicUploadedFileInfo($file))
                                            ->downloadable()
                                            ->openable(),

                                        FileUpload::make('logo_dark')
                                            ->label('Logo Dark')
                                            ->helperText(fn (): HtmlString => self::currentImageHelper('logo_dark', 'Logo Dark'))
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'])
                                            ->storeFiles()
                                            ->fetchFileInformation(false)
                                            ->previewable(false)
                                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => self::storeWhiteLabelFile($file, 'logos'))
                                            ->deleteUploadedFileUsing(fn (string | array | null $file) => WhiteLabelSettings::deletePublicImage($file))
                                            ->getUploadedFileUsing(fn (string $file): ?array => self::getPublicUploadedFileInfo($file))
                                            ->downloadable()
                                            ->openable(),

                                        FileUpload::make('favicon')
                                            ->label('Favicon')
                                            ->helperText(fn (): HtmlString => self::currentImageHelper('favicon', 'Favicon'))
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'])
                                            ->storeFiles()
                                            ->fetchFileInformation(false)
                                            ->previewable(false)
                                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => self::storeWhiteLabelFile($file, 'favicon'))
                                            ->deleteUploadedFileUsing(fn (string | array | null $file) => WhiteLabelSettings::deletePublicImage($file))
                                            ->getUploadedFileUsing(fn (string $file): ?array => self::getPublicUploadedFileInfo($file))
                                            ->downloadable()
                                            ->openable(),

                                        FileUpload::make('logo_login')
                                            ->label('Logo da tela de login')
                                            ->helperText(fn (): HtmlString => self::currentImageHelper('logo_login', 'Logo da tela de login'))
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'])
                                            ->storeFiles()
                                            ->fetchFileInformation(false)
                                            ->previewable(false)
                                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => self::storeWhiteLabelFile($file, 'login'))
                                            ->deleteUploadedFileUsing(fn (string | array | null $file) => WhiteLabelSettings::deletePublicImage($file))
                                            ->getUploadedFileUsing(fn (string $file): ?array => self::getPublicUploadedFileInfo($file))
                                            ->downloadable()
                                            ->openable(),

                                        FileUpload::make('logo_email')
                                            ->label('Logo dos e-mails')
                                            ->helperText(fn (): HtmlString => self::currentImageHelper('logo_email', 'Logo dos e-mails'))
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'])
                                            ->storeFiles()
                                            ->fetchFileInformation(false)
                                            ->previewable(false)
                                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => self::storeWhiteLabelFile($file, 'email'))
                                            ->deleteUploadedFileUsing(fn (string | array | null $file) => WhiteLabelSettings::deletePublicImage($file))
                                            ->getUploadedFileUsing(fn (string $file): ?array => self::getPublicUploadedFileInfo($file))
                                            ->downloadable()
                                            ->openable(),
                                    ])
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                        'xl' => 3,
                                    ]),

                                Section::make('Paleta de cores')
                                    ->description('Essas cores podem ser aplicadas no painel, botões, login e comunicações.')
                                    ->schema([
                                        ColorPicker::make('cor_primaria')
                                            ->label('Cor primária')
                                            ->required(),

                                        ColorPicker::make('cor_secundaria')
                                            ->label('Cor secundária')
                                            ->required(),

                                        ColorPicker::make('cor_destaque')
                                            ->label('Cor de destaque')
                                            ->required(),

                                        ColorPicker::make('cor_sidebar')
                                            ->label('Cor da sidebar')
                                            ->required(),

                                        ColorPicker::make('cor_botoes')
                                            ->label('Cor dos botões')
                                            ->required(),
                                    ])
                                    ->columns(5),
                            ]),

                        Tab::make('Ocultação')
                            ->icon('heroicon-o-eye-slash')
                            ->schema([
                                Section::make('Remoção de referências internas')
                                    ->description('Use essas opções para deixar a experiência mais próxima da marca do cliente.')
                                    ->schema([
                                        Toggle::make('ocultar_nome_sistema')
                                            ->label('Ocultar nome original do sistema'),

                                        Toggle::make('ocultar_rodape')
                                            ->label('Ocultar rodapé do sistema'),

                                        Toggle::make('ocultar_referencias_internas')
                                            ->label('Ocultar referências internas'),

                                        Toggle::make('ocultar_branding_documentos')
                                            ->label('Ocultar branding em documentos públicos'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Login')
                            ->icon('heroicon-o-lock-closed')
                            ->schema([
                                Section::make('Página de login personalizada')
                                    ->description('Personalize a tela de entrada para clientes e usuários internos.')
                                    ->schema([
                                        TextInput::make('login_titulo')
                                            ->label('Título do login')
                                            ->required()
                                            ->maxLength(160),

                                        TextInput::make('login_subtitulo')
                                            ->label('Subtítulo do login')
                                            ->maxLength(255),

                                        ColorPicker::make('login_background')
                                            ->label('Cor de fundo')
                                            ->required(),

                                        FileUpload::make('login_imagem_lateral')
                                            ->label('Imagem lateral do login')
                                            ->helperText(fn (): HtmlString => self::currentImageHelper('login_imagem_lateral', 'Imagem lateral do login'))
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'])
                                            ->storeFiles()
                                            ->fetchFileInformation(false)
                                            ->previewable(false)
                                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => self::storeWhiteLabelFile($file, 'login'))
                                            ->deleteUploadedFileUsing(fn (string | array | null $file) => WhiteLabelSettings::deletePublicImage($file))
                                            ->getUploadedFileUsing(fn (string $file): ?array => self::getPublicUploadedFileInfo($file))
                                            ->downloadable()
                                            ->openable(),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('E-mails')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                Section::make('E-mails de notificação branded')
                                    ->description('Configure remetente, assinatura, logo e cor dos e-mails enviados pelo sistema.')
                                    ->schema([
                                        TextInput::make('email_nome_remetente')
                                            ->label('Nome do remetente')
                                            ->required()
                                            ->maxLength(120),

                                        TextInput::make('email_remetente')
                                            ->label('E-mail do remetente')
                                            ->email()
                                            ->maxLength(255),

                                        ColorPicker::make('email_cor_template')
                                            ->label('Cor do template')
                                            ->required(),

                                        Textarea::make('email_assinatura')
                                            ->label('Assinatura padrão')
                                            ->rows(4)
                                            ->maxLength(1000),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('SSO')
                            ->icon('heroicon-o-key')
                            ->schema([
                                Section::make('Single Sign-On')
                                    ->description('Campos preparados para integração com provedores como Azure, Okta ou outro IdP.')
                                    ->schema([
                                        Toggle::make('sso_ativo')
                                            ->label('Ativar SSO')
                                            ->live(),

                                        Select::make('sso_provider')
                                            ->label('Provider')
                                            ->options([
                                                'azure' => 'Microsoft Azure / Entra ID',
                                                'okta' => 'Okta',
                                                'google' => 'Google Workspace',
                                                'custom' => 'Custom / Outro',
                                            ])
                                            ->searchable()
                                            ->native(false),

                                        TextInput::make('sso_client_id')
                                            ->label('Client ID')
                                            ->maxLength(255),

                                        TextInput::make('sso_tenant_id')
                                            ->label('Tenant ID / Issuer')
                                            ->maxLength(255),

                                        TextInput::make('sso_redirect_url')
                                            ->label('Redirect URL')
                                            ->url()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Documentos')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Documentos com marca própria')
                                    ->description('Controle como documentos públicos e compartilhados exibem identidade visual.')
                                    ->schema([
                                        Toggle::make('documentos_marca_propria')
                                            ->label('Aplicar marca própria em documentos públicos'),

                                        Toggle::make('ocultar_branding_documentos')
                                            ->label('Remover referências da plataforma em documentos'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Workspaces')
                            ->icon('heroicon-o-building-office-2')
                            ->schema([
                                Section::make('Múltiplos workspaces')
                                    ->description('Permite configurar identidades visuais diferentes por área, cliente ou divisão.')
                                    ->schema([
                                        Toggle::make('multi_workspace')
                                            ->label('Ativar múltiplos workspaces')
                                            ->live(),

                                        Repeater::make('workspaces')
                                            ->label('Workspaces personalizados')
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('nome')
                                                            ->label('Nome')
                                                            ->required()
                                                            ->maxLength(120),

                                                        TextInput::make('slug')
                                                            ->label('Identificador')
                                                            ->helperText('Opcional. Use quando quiser abrir o workspace por ?workspace=identificador.')
                                                            ->maxLength(80),

                                                        TextInput::make('dominio')
                                                            ->label('Domínio')
                                                            ->placeholder('cliente.suaempresa.com.br')
                                                            ->maxLength(255),

                                                        ColorPicker::make('cor_primaria')
                                                            ->label('Cor primária'),

                                                        ColorPicker::make('cor_botoes')
                                                            ->label('Cor dos botões'),

                                                        ColorPicker::make('cor_sidebar')
                                                            ->label('Cor da sidebar'),

                                                        FileUpload::make('logo')
                                                            ->label('Logo')
                                                            ->storeFiles()
                                                            ->fetchFileInformation(false)
                                                            ->previewable(false)
                                                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => self::storeWhiteLabelFile($file, 'workspaces'))
                                                            ->deleteUploadedFileUsing(fn (string | array | null $file) => WhiteLabelSettings::deletePublicImage($file))
                                                            ->downloadable()
                                                            ->openable(),
                                                    ]),
                                            ])
                                            ->defaultItems(0)
                                            ->addActionLabel('Adicionar workspace')
                                            ->reorderable()
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['nome'] ?? 'Workspace')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }


    protected static function currentImageHelper(string $field, string $label): HtmlString
    {
        $settings = WhiteLabelSettings::make();
        $path = $settings->get($field);
        $url = $settings->assetUrl($path);

        if (! $url) {
            return new HtmlString('<span class="text-xs text-gray-500">Nenhuma imagem atual salva.</span>');
        }

        $safeUrl = e($url);
        $safeLabel = e($label);

        return new HtmlString(
            '<div style="display:flex;align-items:center;gap:10px;margin-top:6px;">'
            . '<img src="' . $safeUrl . '" alt="' . $safeLabel . '" style="max-height:42px;max-width:150px;border-radius:10px;border:1px solid rgba(148,163,184,.35);background:#fff;padding:6px;object-fit:contain;">'
            . '<span style="font-size:12px;color:#64748b;word-break:break-all;">Atual: ' . $safeUrl . '</span>'
            . '</div>'
            . '<div style="font-size:12px;color:#64748b;margin-top:6px;">Para trocar, envie um novo arquivo. Para apagar, remova o arquivo no campo acima e salve.</div>'
        );
    }
    protected static function clearUploadFieldsForForm(array $data): array
    {
        return $data;
    }


    protected static function getPublicUploadedFileInfo(string $file): ?array
    {
        $normalizedPath = WhiteLabelSettings::normalizeAndRepairPublicUploadPath($file);

        if (! $normalizedPath) {
            return null;
        }

        return [
            'name' => basename($normalizedPath),
            'size' => 0,
            'type' => null,
            'url' => WhiteLabelSettings::publicImageUrl($normalizedPath),
        ];
    }

    protected static function buildUploadFileName(TemporaryUploadedFile $file): string
    {
        $extension = strtolower((string) pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));

        if ($extension === '') {
            $extension = strtolower((string) ($file->extension() ?: ''));
        }

        if ($extension === '') {
            $extension = match ($file->getMimeType()) {
                'image/jpeg', 'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                'image/svg+xml' => 'svg',
                'image/x-icon', 'image/vnd.microsoft.icon' => 'ico',
                default => 'png',
            };
        }

        $extension = preg_replace('/[^a-z0-9]+/i', '', $extension) ?: 'png';

        return now()->format('YmdHis')
            . '-'
            . Str::random(12)
            . '.'
            . $extension;
    }

    protected static function storeWhiteLabelFile(TemporaryUploadedFile $file, string $directory): string
    {
        $directory = trim($directory, '/');
        $fileName = self::buildUploadFileName($file);
        $relativeDirectory = trim('images/white-label/' . $directory, '/');
        $absoluteDirectory = public_path($relativeDirectory);

        File::ensureDirectoryExists($absoluteDirectory, 0755, true);

        $absolutePath = $absoluteDirectory . DIRECTORY_SEPARATOR . $fileName;
        $temporaryPath = $file->getRealPath();

        if ($temporaryPath && is_file($temporaryPath)) {
            File::copy($temporaryPath, $absolutePath);
        } else {
            file_put_contents($absolutePath, $file->get());
        }

        return $relativeDirectory . '/' . $fileName;
    }

    protected static function cleanupRemovedWhiteLabelFiles(array $previousData, array $newData): void
    {
        foreach ([
            'logo_light',
            'logo_dark',
            'favicon',
            'logo_login',
            'logo_email',
            'login_imagem_lateral',
        ] as $uploadField) {
            self::deleteFileWhenChangedOrRemoved(
                $previousData[$uploadField] ?? null,
                $newData[$uploadField] ?? null
            );
        }

        $previousWorkspaces = is_array($previousData['workspaces'] ?? null) ? $previousData['workspaces'] : [];
        $newWorkspaces = is_array($newData['workspaces'] ?? null) ? $newData['workspaces'] : [];

        foreach ($previousWorkspaces as $index => $previousWorkspace) {
            if (! is_array($previousWorkspace)) {
                continue;
            }

            self::deleteFileWhenChangedOrRemoved(
                $previousWorkspace['logo'] ?? null,
                $newWorkspaces[$index]['logo'] ?? null
            );
        }
    }

    protected static function deleteFileWhenChangedOrRemoved(string | array | null $oldPath, string | array | null $newPath): void
    {
        $oldNormalized = WhiteLabelSettings::normalizeAndRepairPublicUploadPath($oldPath);
        $newNormalized = WhiteLabelSettings::normalizeAndRepairPublicUploadPath($newPath);

        if (! $oldNormalized) {
            return;
        }

        if ($newNormalized && $oldNormalized === $newNormalized) {
            return;
        }

        WhiteLabelSettings::deletePublicImage($oldNormalized);
    }

    public function save(): void
    {
        $previousData = [];

        try {
            if (CachedSchema::hasTable('configuracoes')) {
                $previousData = WhiteLabelSettings::mergeDefaults(
                    Configuracao::query()->first()?->white_label
                );
            }
        } catch (Throwable $exception) {
            Log::warning('Falha ao recuperar configuração anterior de White Label antes de salvar.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            $previousData = [];
        }

        $data = WhiteLabelSettings::mergeDefaults(
            $this->form->getState()
        );
        foreach ([
            'logo_light',
            'logo_dark',
            'favicon',
            'logo_login',
            'logo_email',
            'login_imagem_lateral',
        ] as $uploadField) {
            $currentPath = WhiteLabelSettings::normalizeAndRepairPublicUploadPath(
                $data[$uploadField] ?? null
            );

            $previousPath = WhiteLabelSettings::normalizeAndRepairPublicUploadPath(
                $previousData[$uploadField] ?? null
            );

            if (! $currentPath) {
                if ($previousPath) {
                    WhiteLabelSettings::deletePublicImage($previousPath);
                }

                $data[$uploadField] = null;
                continue;
            }

            if ($previousPath && $previousPath !== $currentPath) {
                WhiteLabelSettings::deletePublicImage($previousPath);
            }

            $data[$uploadField] = $currentPath;
        }

        if (!empty($data['workspaces']) && is_array($data['workspaces'])) {
            foreach ($data['workspaces'] as $workspaceIndex => $workspace) {
                if (!is_array($workspace)) {
                    continue;
                }

                $data['workspaces'][$workspaceIndex]['nome'] = trim((string) ($workspace['nome'] ?? ''));
                $data['workspaces'][$workspaceIndex]['slug'] = Str::slug((string) ($workspace['slug'] ?? $workspace['nome'] ?? ''));
                $data['workspaces'][$workspaceIndex]['dominio'] = WhiteLabelSettings::normalizeDomain($workspace['dominio'] ?? '');
                $data['workspaces'][$workspaceIndex]['logo'] = WhiteLabelSettings::normalizeAndRepairPublicUploadPath(
                    $workspace['logo'] ?? null
                );
            }
        }

        self::cleanupRemovedWhiteLabelFiles($previousData, $data);

        if (! CachedSchema::hasTable('configuracoes')) {
            Notification::make()
                ->title('Tabela de configurações não encontrada')
                ->body('Importe o banco de dados completo antes de salvar o White Label.')
                ->danger()
                ->send();

            return;
        }

        $configuracao = Configuracao::query()->first();

        if (! $configuracao) {
            $configuracao = new Configuracao();
        }

        $configuracao->white_label = $data;

        $configuracao->save();

        WhiteLabelSettings::clearCache();

        $this->form->fill($data);

        Notification::make()
            ->title('White Label salvo com sucesso')
            ->body('As configurações foram gravadas e já podem ser aplicadas no sistema.')
            ->success()
            ->send();
    }
}
