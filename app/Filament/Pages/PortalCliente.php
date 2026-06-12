<?php

namespace App\Filament\Pages;


use App\Support\CachedSchema;
use App\Models\PortalMensagem;
use App\Models\PortalSolicitacao;
use App\Support\PortalClienteData;
use App\Support\AtendimentoPortalService;
use App\Support\PrazzuAccessControl;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class PortalCliente extends Page
{
    use WithFileUploads;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string | UnitEnum | null $navigationGroup = 'Clientes';

    protected static ?string $navigationLabel = 'Portal do Cliente';

    protected static ?string $title = 'Central do Portal do Cliente';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.portal-cliente';

    public ?int $empresaSelecionadaId = null;

    public ?string $solicitacaoTitulo = null;

    public ?string $solicitacaoDescricao = null;

    public string $solicitacaoPrioridade = 'media';

    public ?string $chatMensagem = null;

    public ?string $respostaChat = null;

    public bool $clienteDigitando = false;

    public ?string $clienteDigitandoNome = null;

    public ?int $clienteVisualizouAteId = null;

    public ?int $suporteVisualizouAteId = null;

    /**
     * @var array<int, TemporaryUploadedFile>
     */
    public array $portalAnexos = [];

    public function mount(): void
    {
        $empresaParam = request()->integer('empresa');

        $this->empresaSelecionadaId = ($empresaParam && PortalClienteData::usuarioPodeAcessarEmpresa($empresaParam))
            ? $empresaParam
            : PortalClienteData::empresaIdAtual();

        $this->atualizarEstadoDigitando();
    }

    public function updatedEmpresaSelecionadaId(): void
    {
        if (! $this->empresaIdAtualDaTela(true)) {
            $this->empresaSelecionadaId = PortalClienteData::empresaIdAtual();
        }

        $this->reset(['chatMensagem', 'respostaChat', 'portalAnexos']);
        $this->atualizarEstadoDigitando();
    }


    public function atualizarConversa(): void
    {
        $this->atualizarEstadoDigitando();
        $this->registrarVisualizacaoSuporte();
        $this->atualizarVisualizacoesChat();
    }

    public function updatedRespostaChat(): void
    {
        $empresaId = $this->empresaIdAtualDaTela();

        if (! $empresaId) {
            return;
        }

        if (trim((string) $this->respostaChat) === '') {
            Cache::forget($this->cacheKeySuporteDigitando($empresaId));
            return;
        }

        Cache::put($this->cacheKeySuporteDigitando($empresaId), [
            'nome' => auth()->user()?->name ?: 'Suporte',
            'timestamp' => now()->timestamp,
        ], now()->addSeconds(10));
    }

    private function atualizarEstadoDigitando(): void
    {
        $empresaId = $this->empresaIdAtualDaTela();

        if (! $empresaId) {
            $this->clienteDigitando = false;
            $this->clienteDigitandoNome = null;
            return;
        }

        $estado = Cache::get($this->cacheKeyClienteDigitando($empresaId));

        if (! is_array($estado)) {
            $this->clienteDigitando = false;
            $this->clienteDigitandoNome = null;
            return;
        }

        $timestamp = (int) ($estado['timestamp'] ?? 0);

        if ($timestamp < now()->subSeconds(8)->timestamp) {
            Cache::forget($this->cacheKeyClienteDigitando($empresaId));
            $this->clienteDigitando = false;
            $this->clienteDigitandoNome = null;
            return;
        }

        $nome = trim((string) ($estado['nome'] ?? 'Cliente'));

        $this->clienteDigitando = true;
        $this->clienteDigitandoNome = $nome !== '' ? $nome : 'Cliente';
    }

    private function registrarVisualizacaoSuporte(): void
    {
        $empresaId = $this->empresaIdAtualDaTela();

        if (! $empresaId || ! CachedSchema::hasTable('portal_mensagens')) {
            return;
        }

        $ultimoIdCliente = PortalMensagem::query()
            ->where('empresa_id', $empresaId)
            ->where(function ($query): void {
                $query->where('origem', 'cliente')
                    ->orWhere('origem', 'portal_cliente')
                    ->orWhere('origem', 'client');
            })
            ->max('id');

        if ($ultimoIdCliente) {
            Cache::put($this->cacheKeyVisualizadoSuporte($empresaId), (int) $ultimoIdCliente, now()->addHours(8));
        }
    }

    private function atualizarVisualizacoesChat(): void
    {
        $empresaId = $this->empresaIdAtualDaTela();

        if (! $empresaId) {
            $this->clienteVisualizouAteId = null;
            $this->suporteVisualizouAteId = null;
            return;
        }

        $this->clienteVisualizouAteId = Cache::get($this->cacheKeyVisualizadoCliente($empresaId));
        $this->suporteVisualizouAteId = Cache::get($this->cacheKeyVisualizadoSuporte($empresaId));
    }

    private function cacheKeyClienteDigitando(int $empresaId): string
    {
        return 'portal_cliente_digitando_empresa_' . $empresaId;
    }

    private function cacheKeySuporteDigitando(int $empresaId): string
    {
        return 'portal_suporte_digitando_empresa_' . $empresaId;
    }

    private function cacheKeyVisualizadoCliente(int $empresaId): string
    {
        return 'portal_cliente_visualizou_suporte_empresa_' . $empresaId;
    }

    private function cacheKeyVisualizadoSuporte(int $empresaId): string
    {
        return 'portal_suporte_visualizou_cliente_empresa_' . $empresaId;
    }

    public function criarSolicitacao(): void
    {
        $this->validate([
            'solicitacaoTitulo' => ['required', 'string', 'max:255'],
            'solicitacaoDescricao' => ['required', 'string', 'min:5', 'max:5000'],
            'solicitacaoPrioridade' => ['required', 'in:baixa,media,alta,urgente'],
        ]);

        if (! CachedSchema::hasTable('portal_solicitacoes')) {
            $this->notificarTabelaAusente('portal_solicitacoes');
            return;
        }

        $empresaId = $this->empresaIdAtualDaTela();

        if (! $empresaId) {
            $this->notificarEmpresaAusente();
            return;
        }

        $solicitacao = PortalSolicitacao::create($this->payloadSolicitacao($empresaId));

        $atendimento = app(AtendimentoPortalService::class)->registrarSolicitacao($solicitacao);

        $this->reset(['solicitacaoTitulo', 'solicitacaoDescricao']);
        $this->solicitacaoPrioridade = 'media';

        $notification = Notification::make()
            ->title($atendimento ? 'Solicitação criada' : 'Solicitação criada com alerta')
            ->body($atendimento ? 'A solicitação ficou registrada para o cliente e entrou no fluxo de Atendimentos.' : 'A solicitação ficou salva no portal, mas não foi possível gerar o atendimento operacional automaticamente. Verifique a estrutura do módulo de Atendimentos.');

        ($atendimento ? $notification->success() : $notification->warning())->send();
    }

    public function enviarMensagem(): void
    {
        $this->validarMensagemComAnexos('chatMensagem');

        if (! CachedSchema::hasTable('portal_mensagens')) {
            $this->notificarTabelaAusente('portal_mensagens');
            return;
        }

        $empresaId = $this->empresaIdAtualDaTela();

        if (! $empresaId) {
            $this->notificarEmpresaAusente();
            return;
        }

        PortalMensagem::create($this->payloadMensagem($empresaId, (string) $this->chatMensagem, 'interno', $this->salvarAnexosMensagem($empresaId)));
        Cache::forget($this->cacheKeySuporteDigitando($empresaId));

        $this->reset(['chatMensagem', 'portalAnexos']);

        Notification::make()->title('Mensagem enviada')->body('A resposta ficou visível no portal público do cliente.')->success()->send();
    }

    public function responderChat(): void
    {
        $this->validarMensagemComAnexos('respostaChat');

        if (! CachedSchema::hasTable('portal_mensagens')) {
            $this->notificarTabelaAusente('portal_mensagens');
            return;
        }

        $empresaId = $this->empresaIdAtualDaTela();

        if (! $empresaId) {
            $this->notificarEmpresaAusente();
            return;
        }

        PortalMensagem::create($this->payloadMensagem($empresaId, (string) $this->respostaChat, 'interno', $this->salvarAnexosMensagem($empresaId)));
        Cache::forget($this->cacheKeySuporteDigitando($empresaId));
        $this->reset(['respostaChat', 'portalAnexos']);

        Notification::make()->title('Resposta enviada')->body('A resposta ficou registrada no histórico do portal do cliente.')->success()->send();
    }

    public function finalizarConversa(): void
    {
        if (! CachedSchema::hasTable('portal_mensagens') && ! CachedSchema::hasTable('prazzu_client_portal_messages')) {
            $this->notificarTabelaAusente('portal_mensagens');
            return;
        }

        $empresaId = $this->empresaIdAtualDaTela();

        if (! $empresaId) {
            $this->notificarEmpresaAusente();
            return;
        }

        $mensagensAtivasIds = collect();

        if (CachedSchema::hasTable('portal_mensagens')) {
            $mensagensAtivasIds = PortalMensagem::query()
                ->where('empresa_id', $empresaId)
                ->when(
                    CachedSchema::hasColumn('portal_mensagens', 'conversa_status'),
                    fn ($query) => $query->where('conversa_status', 'aberta'),
                    fn ($query) => CachedSchema::hasColumn('portal_mensagens', 'visualizada_em') ? $query->whereNull('visualizada_em') : $query
                )
                ->oldest()
                ->limit(80)
                ->pluck('id');
        }

        $mensagensLegadasAtivasIds = collect();

        if (CachedSchema::hasTable('prazzu_client_portal_messages') && CachedSchema::hasColumn('prazzu_client_portal_messages', 'read_at')) {
            $mensagensLegadasAtivasIds = DB::table('prazzu_client_portal_messages')
                ->where('empresa_id', $empresaId)
                ->whereNull('read_at')
                ->oldest()
                ->limit(80)
                ->pluck('id');
        }

        if ($mensagensAtivasIds->isEmpty() && $mensagensLegadasAtivasIds->isEmpty()) {
            Notification::make()
                ->title('Nenhuma conversa ativa')
                ->body('Não há mensagens abertas para finalizar neste cliente.')
                ->warning()
                ->send();
            return;
        }

        if ($mensagensAtivasIds->isNotEmpty()) {
            $update = [
                'updated_at' => now(),
            ];

            if (CachedSchema::hasColumn('portal_mensagens', 'visualizada_em')) {
                $update['visualizada_em'] = now();
            }

            if (CachedSchema::hasColumn('portal_mensagens', 'conversa_status')) {
                $update['conversa_status'] = 'finalizada';
            }

            DB::table('portal_mensagens')
                ->where('empresa_id', $empresaId)
                ->whereIn('id', $mensagensAtivasIds->all())
                ->update($update);
        }

        if ($mensagensLegadasAtivasIds->isNotEmpty()) {
            DB::table('prazzu_client_portal_messages')
                ->where('empresa_id', $empresaId)
                ->whereIn('id', $mensagensLegadasAtivasIds->all())
                ->update(['read_at' => now(), 'updated_at' => now()]);
        }

        Notification::make()->title('Conversa finalizada')->body('As mensagens abertas exibidas na tela saíram da fila ativa, mas continuam salvas como histórico no banco.')->success()->send();
    }

    protected function getViewData(): array
    {
        return PortalClienteData::data($this->empresaIdAtualDaTela(), true);
    }

    private function empresaIdAtualDaTela(bool $notificar = false): ?int
    {
        $empresaId = $this->empresaSelecionadaId ?: PortalClienteData::empresaIdAtual();

        if (! $empresaId) {
            return null;
        }

        $empresaId = (int) $empresaId;

        if (PortalClienteData::usuarioPodeAcessarEmpresa($empresaId)) {
            return $empresaId;
        }

        if ($notificar) {
            Notification::make()
                ->title('Acesso bloqueado')
                ->body('Você não tem permissão para acessar o portal desta empresa.')
                ->danger()
                ->send();
        }

        return null;
    }


    private function validarMensagemComAnexos(string $campoMensagem): void
    {
        $this->validate([
            $campoMensagem => ['nullable', 'string', 'max:5000', 'required_without:portalAnexos.0'],
            'portalAnexos' => ['array', 'max:5'],
            'portalAnexos.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt,csv'],
        ], [
            $campoMensagem . '.required_without' => 'Digite uma mensagem ou anexe pelo menos um arquivo.',
            'portalAnexos.max' => 'Envie no máximo 5 arquivos por mensagem.',
            'portalAnexos.*.max' => 'Cada arquivo deve ter no máximo 10 MB.',
            'portalAnexos.*.mimes' => 'Use apenas imagem, PDF, Word, Excel, TXT ou CSV.',
        ]);
    }

    /**
     * @return array<int, array<string, string|int|null>>
     */
    private function salvarAnexosMensagem(int $empresaId): array
    {
        return collect($this->portalAnexos)
            ->filter(fn ($arquivo): bool => $arquivo instanceof TemporaryUploadedFile || $arquivo instanceof UploadedFile)
            ->map(function (TemporaryUploadedFile|UploadedFile $arquivo) use ($empresaId): array {
                $nomeOriginal = $arquivo->getClientOriginalName() ?: 'anexo';
                $nomeSeguro = substr((string) pathinfo($nomeOriginal, PATHINFO_FILENAME), 0, 80);
                $nomeSeguro = preg_replace('/[^A-Za-z0-9_-]+/', '-', $nomeSeguro) ?: 'anexo';
                $extensao = strtolower($arquivo->getClientOriginalExtension() ?: 'bin');
                $arquivoNome = trim($nomeSeguro, '-') . '-' . now()->format('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extensao;
                $caminho = $arquivo->storeAs('portal-chat/' . $empresaId, $arquivoNome, 'public');

                return [
                    'nome' => $nomeOriginal,
                    'url' => asset(Storage::url($caminho)),
                    'mime_type' => $arquivo->getMimeType(),
                    'size' => $arquivo->getSize(),
                ];
            })
            ->values()
            ->all();
    }

    private function blocoAnexosMensagem(array $anexos): string
    {
        $linhas = collect($anexos)
            ->map(fn (array $anexo): string => '- ' . ($anexo['nome'] ?? 'Anexo') . ' | ' . ($anexo['url'] ?? '') . ' | ' . ($anexo['mime_type'] ?? 'application/octet-stream') . ' | ' . ($anexo['size'] ?? ''))
            ->filter(fn (string $linha): bool => ! str_ends_with($linha, ' | '))
            ->implode("\n");

        return $linhas !== '' ? "Anexos enviados:\n" . $linhas : '';
    }

    private function payloadSolicitacao(int $empresaId): array
    {
        $payload = [
            'empresa_id' => $empresaId,
            'user_id' => auth()->id(),
            'titulo' => trim((string) $this->solicitacaoTitulo),
            'descricao' => trim((string) $this->solicitacaoDescricao),
            'prioridade' => $this->solicitacaoPrioridade,
            'status' => 'aberto',
        ];

        if (CachedSchema::hasColumn('portal_solicitacoes', 'origem')) {
            $payload['origem'] = 'interno';
        }

        return $payload;
    }

    private function payloadMensagem(int $empresaId, string $mensagem, string $origem, array $anexos = []): array
    {
        $mensagemFinal = trim($mensagem);

        if ($anexos !== []) {
            $mensagemFinal = trim($mensagemFinal . "\n\n" . $this->blocoAnexosMensagem($anexos));
        }

        $payload = [
            'empresa_id' => $empresaId,
            'user_id' => auth()->id(),
            'nome' => auth()->user()?->name,
            'email' => auth()->user()?->email,
            'mensagem' => $mensagemFinal,
            'origem' => $origem,
        ];

        if (CachedSchema::hasColumn('portal_mensagens', 'conversa_status')) {
            $payload['conversa_status'] = 'aberta';
        }

        return $payload;
    }

    private function notificarTabelaAusente(string $tabela): void
    {
        Notification::make()->title("Tabela {$tabela} não encontrada")->body('Execute o SQL do portal antes de usar esta função.')->danger()->send();
    }

    private function notificarEmpresaAusente(): void
    {
        Notification::make()->title('Nenhuma empresa vinculada')->body('Não foi possível identificar a empresa selecionada.')->danger()->send();
    }

    public static function canAccess(): bool
    {
        return PrazzuAccessControl::canUsePortalCliente();
    }
}
