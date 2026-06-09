<?php

namespace App\Filament\Pages;

use App\Models\AiMarketComment;
use App\Models\AiMarketSource;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class InteligenciaProduto extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static string | UnitEnum | null $navigationGroup = 'Governança';

    protected static ?string $navigationLabel = 'Inteligência do Produto';

    protected static ?string $title = 'Inteligência do Produto';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.inteligencia-produto';

    public string $commentsText = '';

    public array $comments = [];

    public int $commentsTotal = 0;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()?->isSuperAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()?->isSuperAdmin();
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $this->loadComments();
    }

    public function importComments(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $this->validate([
            'commentsText' => ['required', 'string', 'min:5'],
        ], [
            'commentsText.required' => 'Informe os comentários antes de importar.',
            'commentsText.min' => 'Informe pelo menos 5 caracteres.',
        ]);

        if (! Schema::hasTable('ai_market_comments') || ! Schema::hasTable('ai_market_sources')) {
            Notification::make()
                ->title('SQL pendente')
                ->body('A tabela de comentários da Inteligência do Produto ainda não existe no banco.')
                ->danger()
                ->send();

            return;
        }

        $source = AiMarketSource::query()->firstOrCreate([
            'name' => 'Importação manual',
            'competitor_name' => null,
            'source_type' => 'manual',
        ], [
            'source_url' => null,
            'is_active' => true,
        ]);

        $comments = $this->splitComments($this->commentsText);
        $created = 0;

        foreach ($comments as $commentText) {
            AiMarketComment::query()->create([
                'source_id' => $source->id,
                'competitor_name' => null,
                'rating' => null,
                'language' => 'pt-BR',
                'original_text' => $commentText,
                'detected_sentiment' => null,
                'detected_category' => null,
                'detected_problem' => null,
                'detected_opportunity' => null,
                'detected_real_pain' => null,
                'detected_impact' => null,
                'recommended_action' => null,
                'metadata' => [
                    'imported_by_user_id' => auth()->id(),
                    'imported_from' => 'inteligencia_produto_importacao_manual',
                    'raw_import' => true,
                ],
            ]);

            $created++;
        }

        $this->commentsText = '';
        $this->loadComments();

        Notification::make()
            ->title('Comentários importados')
            ->body("{$created} comentário(s) salvo(s) no banco sem alterar o texto original.")
            ->success()
            ->send();
    }

    public function exportPrompt(): StreamedResponse|Responsable
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $filename = 'prompt-inteligencia-produto-'.now()->format('Ymd-His').'.txt';

        return response()->streamDownload(function (): void {
            if (! Schema::hasTable('ai_market_comments')) {
                return;
            }

            AiMarketComment::query()
                ->orderBy('id')
                ->select(['id', 'original_text'])
                ->chunk(200, function ($comments): void {
                    foreach ($comments as $index => $comment) {
                        static $printedAny = false;

                        if ($printedAny) {
                            echo "\n\n";
                        }

                        echo (string) $comment->original_text;
                        $printedAny = true;
                    }
                });
        }, $filename, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    private function splitComments(string $text): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", trim($text));

        if ($normalized === '') {
            return [];
        }

        if (preg_match('/\n\s*(?:-{3,}|\*{3,})\s*\n/u', $normalized)) {
            $parts = preg_split('/\n\s*(?:-{3,}|\*{3,})\s*\n/u', $normalized) ?: [$normalized];
        } else {
            $parts = [$normalized];
        }

        return collect($parts)
            ->map(fn (string $item): string => trim($item))
            ->filter(fn (string $item): bool => mb_strlen($item) >= 5)
            ->values()
            ->all();
    }

    private function loadComments(): void
    {
        if (! Schema::hasTable('ai_market_comments')) {
            $this->comments = [];
            $this->commentsTotal = 0;

            return;
        }

        $this->commentsTotal = AiMarketComment::query()->count();

        $this->comments = AiMarketComment::query()
            ->latest('id')
            ->limit(200)
            ->get(['id', 'original_text', 'created_at'])
            ->map(fn (AiMarketComment $comment): array => [
                'id' => $comment->id,
                'original_text' => $comment->original_text,
                'created_at' => $comment->created_at?->format('d/m/Y H:i'),
            ])
            ->all();
    }
}
