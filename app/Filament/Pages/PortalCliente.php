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
use Livewire\Attributes\Renderless;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
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
        // Atualização em tempo real agora é feita pelo Socket.IO.
    }

    #[Renderless]
    public function registrarSuporteDigitando(?string $texto = null): void
    {
        // Digitando agora é emitido pelo Socket.IO no frontend.
    }

    private function atualizarEstadoDigitando(): void
    {
        $this->clienteDigitando = false;
        $this->clienteDigitandoNome = null;
    }

    private function registrarVisualizacaoSuporte(): void
    {
        // Visualização em tempo real agora é emitida pelo Socket.IO.
    }

    private function atualizarVisualizacoesChat(): void
    {
        $this->clienteVisualizouAteId = null;
        $this->suporteVisualizouAteId = null;
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
        $inicio = microtime(true);
        Log::info('[PORTAL_CHAT_LIVEWIRE_ENVIO] inicio_enviarMensagem', [
            'empresa_id' => $this->empresaSelecionadaId,
            'user_id' => auth()->id(),
            'tamanho_mensagem' => strlen((string) $this->chatMensagem),
            'quantidade_anexos' => count($this->portalAnexos),
        ]);

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

        $mensagem = PortalMensagem::create($this->payloadMensagem($empresaId, (string) $this->chatMensagem, 'interno', $this->salvarAnexosMensagem($empresaId)));
        Log::info('[PORTAL_CHAT_LIVEWIRE_ENVIO] mensagem_salva_enviarMensagem', [
            'empresa_id' => $empresaId,
            'user_id' => auth()->id(),
            'mensagem_id' => (int) $mensagem->id,
            'duracao_ms' => round((microtime(true) - $inicio) * 1000, 2),
        ]);

        $this->reset(['chatMensagem', 'portalAnexos']);
        $this->atualizarConversa();

        Log::info('[PORTAL_CHAT_LIVEWIRE_ENVIO] fim_enviarMensagem', [
            'empresa_id' => $empresaId,
            'user_id' => auth()->id(),
            'mensagem_id' => (int) $mensagem->id,
            'duracao_total_ms' => round((microtime(true) - $inicio) * 1000, 2),
        ]);

        Notification::make()->title('Mensagem enviada')->body('A resposta ficou visível no portal público do cliente.')->success()->send();
    }

    public function responderChat(): void
    {
        $inicio = microtime(true);
        Log::info('[PORTAL_CHAT_LIVEWIRE_ENVIO] inicio_responderChat', [
            'empresa_id' => $this->empresaSelecionadaId,
            'user_id' => auth()->id(),
            'tamanho_mensagem' => strlen((string) $this->respostaChat),
            'quantidade_anexos' => count($this->portalAnexos),
        ]);

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

        $mensagem = PortalMensagem::create($this->payloadMensagem($empresaId, (string) $this->respostaChat, 'interno', $this->salvarAnexosMensagem($empresaId)));
        Log::info('[PORTAL_CHAT_LIVEWIRE_ENVIO] mensagem_salva_responderChat', [
            'empresa_id' => $empresaId,
            'user_id' => auth()->id(),
            'mensagem_id' => (int) $mensagem->id,
            'duracao_ms' => round((microtime(true) - $inicio) * 1000, 2),
        ]);
        $this->reset(['respostaChat', 'portalAnexos']);
        $this->atualizarConversa();

        Log::info('[PORTAL_CHAT_LIVEWIRE_ENVIO] fim_responderChat', [
            'empresa_id' => $empresaId,
            'user_id' => auth()->id(),
            'mensagem_id' => (int) $mensagem->id,
            'duracao_total_ms' => round((microtime(true) - $inicio) * 1000, 2),
        ]);

        Notification::make()->title('Resposta enviada')->body('A resposta ficou registrada no histórico do portal do cliente.')->success()->send();
    }

    public function finalizarConversa(): void
    {
        if (! CachedSchema::hasTable('portal_mensagens')) {
            $this->notificarTabelaAusente('portal_mensagens');
            return;
        }

        $empresaId = $this->empresaIdAtualDaTela();

        if (! $empresaId) {
            $this->notificarEmpresaAusente();
            return;
        }

        $mensagensAtivasIds = PortalMensagem::query()
            ->where('empresa_id', $empresaId)
            ->when(
                CachedSchema::hasColumn('portal_mensagens', 'conversa_status'),
                fn ($query) => $query->where('conversa_status', 'aberta'),
                fn ($query) => CachedSchema::hasColumn('portal_mensagens', 'visualizada_em') ? $query->whereNull('visualizada_em') : $query
            )
            ->oldest()
            ->limit(120)
            ->pluck('id');

        if ($mensagensAtivasIds->isEmpty()) {
            Notification::make()
                ->title('Nenhuma conversa ativa')
                ->body('Não há mensagens abertas para finalizar neste cliente.')
                ->warning()
                ->send();
            return;
        }

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

        Notification::make()
            ->title('Conversa finalizada')
            ->body('As mensagens abertas exibidas na tela saíram da fila ativa, mas continuam salvas em portal_mensagens.')
            ->success()
            ->send();
    }

    protected function getViewData(): array
    {
        $empresaId = $this->empresaIdAtualDaTela();

        return array_merge(PortalClienteData::data($empresaId, true), [
            'socketIoConfig' => $this->socketIoConfigEquipe($empresaId),
        ]);
    }

    /**
     * Configuração do Socket.IO usada pela tela da equipe.
     * A assinatura evita que um cliente entre em sala de outra empresa sem conhecer o segredo da aplicação.
     */
    private function socketIoConfigEquipe(?int $empresaId): array
    {
        if (! $empresaId) {
            return ['enabled' => false];
        }

        $actor = 'suporte';
        $secret = (string) config('app.key');
        $token = 'admin:' . (auth()->id() ?: '0');
        $room = 'empresa:' . $empresaId . ':portal';

        return [
            'enabled' => true,
            'url' => rtrim((string) env('VITE_SOCKET_IO_URL', env('SOCKET_IO_URL', 'http://127.0.0.1:3001')), '/'),
            'empresaId' => $empresaId,
            'actor' => $actor,
            'nome' => auth()->user()?->name ?: 'Suporte',
            'token' => $token,
            'room' => $room,
            'roomScope' => 'portal',
            'signature' => hash_hmac('sha256', $empresaId . '|' . $actor . '|' . $token . '|' . $room, $secret),
            'syncUrl' => route('admin.portal-cliente.chat.mensagens-novas'),
            'seenUrl' => url('/admin/portal-cliente/mensagem-visualizada'),
        ];
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
