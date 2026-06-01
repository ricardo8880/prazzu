<?php

namespace App\Filament\Pages;

use App\Models\AiMarketComment;
use App\Models\AiMarketSource;
use App\Services\ProductIntelligenceReportService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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

    public string $sourceName = 'Importação manual';

    public string $competitorName = '';

    public string $sourceType = 'reddit';

    public string $sourceUrl = '';

    public ?int $rating = null;

    public string $language = 'pt-BR';

    public string $commentsText = '';

    public int $periodDays = 365;

    public array $report = [];

    public array $latestComments = [];

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()?->isSuperAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()?->isSuperAdmin();
    }

    public function mount(ProductIntelligenceReportService $service): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $this->report = $service->buildReport($this->periodDays);
        $this->loadLatestComments();
    }

    public function importComments(ProductIntelligenceReportService $service): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $this->validate([
            'sourceName' => ['required', 'string', 'max:150'],
            'competitorName' => ['nullable', 'string', 'max:150'],
            'sourceType' => ['required', 'string', 'max:50'],
            'sourceUrl' => ['nullable', 'string', 'max:500'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'language' => ['required', 'string', 'max:20'],
            'commentsText' => ['required', 'string', 'min:5'],
        ]);

        if (! Schema::hasTable('ai_market_comments') || ! Schema::hasTable('ai_market_sources')) {
            Notification::make()
                ->title('SQL pendente')
                ->body('Execute database/sql/2026_05_31_ai_product_intelligence.sql antes de importar comentários.')
                ->danger()
                ->send();

            return;
        }

        $source = AiMarketSource::query()->firstOrCreate([
            'name' => trim($this->sourceName),
            'competitor_name' => filled($this->competitorName) ? trim($this->competitorName) : null,
            'source_type' => $this->sourceType,
        ], [
            'source_url' => filled($this->sourceUrl) ? trim($this->sourceUrl) : null,
            'is_active' => true,
        ]);

        $comments = $this->splitComments($this->commentsText);
        $created = 0;

        foreach ($comments as $commentText) {
            $classification = $service->classifyText($commentText, $this->rating);

            AiMarketComment::query()->create([
                'source_id' => $source->id,
                'competitor_name' => filled($this->competitorName) ? trim($this->competitorName) : $source->competitor_name,
                'rating' => $this->rating,
                'language' => $this->language,
                'original_text' => $commentText,
                'detected_sentiment' => $classification['sentiment'],
                'detected_category' => $classification['category'],
                'detected_problem' => $classification['problem'],
                'detected_opportunity' => $classification['opportunity'],
                'detected_real_pain' => $classification['real_pain'],
                'detected_impact' => $classification['impact'],
                'recommended_action' => $classification['recommended_action'],
                'metadata' => [
                    'classification' => $classification,
                    'imported_by_user_id' => auth()->id(),
                    'imported_from_url' => $this->sourceUrl ?: null,
                ],
            ]);

            $created++;
        }

        $this->commentsText = '';
        $this->report = $service->buildReport($this->periodDays);
        $this->loadLatestComments();

        Notification::make()
            ->title('Comentários importados')
            ->body("{$created} comentário(s) arquivado(s) e classificado(s) sem IA externa.")
            ->success()
            ->send();
    }

    public function generateReport(ProductIntelligenceReportService $service): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $this->periodDays = max(1, min(3650, (int) $this->periodDays));
        $this->report = $service->buildReport($this->periodDays);
        $this->loadLatestComments();

        Notification::make()
            ->title('Relatório atualizado')
            ->body('O relatório foi gerado com base nos comentários arquivados, aprendizados de mercado, pontos fortes e contradições detectadas.')
            ->success()
            ->send();
    }

    public function exportJson(): StreamedResponse|Responsable
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $payload = $this->report ?: app(ProductIntelligenceReportService::class)->buildReport($this->periodDays);
        $filename = 'inteligencia-produto-'.now()->format('Ymd-His').'.json';

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, $filename, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    public function exportPrompt(): StreamedResponse|Responsable
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $payload = $this->report ?: app(ProductIntelligenceReportService::class)->buildReport($this->periodDays);
        $filename = 'prompt-inteligencia-produto-'.now()->format('Ymd-His').'.txt';

        return response()->streamDownload(function () use ($payload): void {
            echo $payload['prompt_for_chatgpt'] ?? '';
        }, $filename, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    private function splitComments(string $text): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", trim($text));

        if (str_contains($normalized, "\n---\n") || str_contains($normalized, "\n***\n")) {
            $parts = preg_split('/\n\s*(?:-{3,}|\*{3,})\s*\n/u', $normalized) ?: [$normalized];
        } else {
            $paragraphs = preg_split('/\n\s*\n/u', $normalized) ?: [$normalized];
            $looksLikeSinglePost = count($paragraphs) <= 8
                && ! preg_match('/\n\s*[•\-\*]\s+|\n\s*\d+[\.)]\s+/u', $normalized)
                && mb_strlen($normalized) <= 6000;

            $parts = $looksLikeSinglePost
                ? [$normalized]
                : $paragraphs;
        }

        if (count($parts) <= 1 && preg_match('/\n\s*[•\-\*]\s+|\n\s*\d+[\.)]\s+/u', $normalized)) {
            $parts = preg_split('/\n(?=\s*[•\-\*]\s+|\s*\d+[\.)]\s+)/u', $normalized) ?: [$normalized];
        }

        return collect($parts)
            ->map(fn (string $item): string => trim(preg_replace('/^\s*[•\-\*]\s+|^\s*\d+[\.)]\s+/u', '', $item) ?? $item))
            ->filter(fn (string $item): bool => mb_strlen($item) >= 5)
            ->unique()
            ->values()
            ->all();
    }

    private function loadLatestComments(): void
    {
        if (! Schema::hasTable('ai_market_comments')) {
            $this->latestComments = [];

            return;
        }

        $this->latestComments = AiMarketComment::query()
            ->with('source')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (AiMarketComment $comment): array => [
                'id' => $comment->id,
                'competitor' => $comment->competitor_name ?: ($comment->source?->competitor_name ?: 'Não informado'),
                'source' => $comment->source?->name ?: 'Não informado',
                'rating' => $comment->rating,
                'sentiment' => $comment->detected_sentiment,
                'category' => $comment->detected_category,
                'real_pain' => $comment->detected_real_pain ?: data_get($comment->metadata, 'classification.real_pain'),
                'impact' => $comment->detected_impact ?: data_get($comment->metadata, 'classification.impact'),
                'recommended_action' => $comment->recommended_action ?: data_get($comment->metadata, 'classification.recommended_action'),
                'insight' => data_get($comment->metadata, 'classification.insight'),
                'market_learning' => data_get($comment->metadata, 'classification.market_learning'),
                'what_to_do' => data_get($comment->metadata, 'classification.what_to_do'),
                'what_not_to_do' => data_get($comment->metadata, 'classification.what_not_to_do'),
                'confidence' => data_get($comment->metadata, 'classification.confidence'),
                'text' => Str::limit($comment->original_text, 180),
                'created_at' => $comment->created_at?->format('d/m/Y H:i'),
            ])
            ->all();
    }
}
