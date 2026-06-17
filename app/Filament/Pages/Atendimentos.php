<?php

namespace App\Filament\Pages;

use App\Models\Atendimento;
use App\Models\AtendimentoInteracao;
use App\Support\AtendimentoActionService;
use App\Support\AtendimentoPortalService;
use App\Support\AtendimentoStatus;
use App\Support\AtendimentoWorkflowService;
use App\Support\AtendimentosData;
use App\Support\CachedSchema;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Throwable;
use UnitEnum;

class Atendimentos extends Page
{
    use WithFileUploads;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string | UnitEnum | null $navigationGroup = 'Clientes';
    protected static ?string $navigationLabel = 'Atendimentos';
    protected static ?string $title = 'Central de Atendimentos';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.pages.atendimentos';

    public string $search = '';
    public string $statusFilter = 'todos';
    public string $prioridadeFilter = 'todos';
    public string $origemFilter = 'todos';
    public string $responsavelFilter = 'todos';
    public string $aguardandoFilter = 'todos';
    public string $slaFilter = 'todos';
    public string $sortBy = 'recentes';
    public ?int $empresaFilter = null;

    public array $data = [];
    public array $summary = [];
    public array $statusBoard = [];
    public array $prioridadeResumo = [];
    public array $atendimentos = [];
    public array $empresas = [];
    public array $clientes = [];
    public array $responsaveis = [];
    public array $timeline = [];
    public array $atendimentosSelecionados = [];
    public ?array $selectedAtendimento = null;
    public ?int $selectedAtendimentoId = null;
    public ?string $lastRefreshAt = null;

    public bool $createModalAberto = false;
    public bool $detailModalAberto = false;

    public ?int $novoEmpresaId = null;
    public ?int $novoClienteId = null;
    public ?int $novoResponsavelId = null;
    public string $novoTitulo = '';
    public string $novoDescricao = '';
    public string $novoPrioridade = 'media';
    public string $novoOrigem = 'manual';
    public string $novoCanal = 'interno';

    public string $novaInteracao = '';
    public string $novaRespostaCliente = '';
    public $anexoRespostaCliente = null;
    public string $resolucaoTexto = '';
    public string $motivoEncerramento = 'duvida_resolvida';
    public string $observacaoEncerramento = '';
    public string $novoStatusDetalhe = 'aberto';
    public string $novaPrioridadeDetalhe = 'media';
    public ?int $novoResponsavelDetalhe = null;

    public function mount(): void
    {
        $this->loadData();
    }

    public function updatedSearch(): void { $this->loadData(true); }
    public function updatedStatusFilter(): void { $this->loadData(true); }
    public function updatedPrioridadeFilter(): void { $this->loadData(true); }
    public function updatedOrigemFilter(): void { $this->loadData(true); }
    public function updatedResponsavelFilter(): void { $this->loadData(true); }
    public function updatedAguardandoFilter(): void { $this->loadData(true); }
    public function updatedSlaFilter(): void { $this->loadData(true); }
    public function updatedSortBy(): void { $this->loadData(true); }
    public function updatedEmpresaFilter(): void { $this->loadData(true); }

    public function updatedAtendimentosSelecionados(): void
    {
        $idsVisiveis = collect($this->atendimentos)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $this->atendimentosSelecionados = collect($this->atendimentosSelecionados)
            ->map(fn ($id) => (string) $id)
            ->intersect($idsVisiveis)
            ->values()
            ->all();
    }

    public function alternarSelecaoVisivel(): void
    {
        $idsVisiveis = collect($this->atendimentos)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();

        if (empty($idsVisiveis)) {
            $this->atendimentosSelecionados = [];
            return;
        }

        $selecionadosVisiveis = collect($this->atendimentosSelecionados)
            ->map(fn ($id) => (string) $id)
            ->intersect($idsVisiveis)
            ->values()
            ->all();

        $this->atendimentosSelecionados = count($selecionadosVisiveis) === count($idsVisiveis)
            ? []
            : $idsVisiveis;
    }

    public function updatedNovoEmpresaId(): void
    {
        $this->novoClienteId = null;
    }

    public function loadData(bool $keepSelection = false): void
    {
        $selectedId = $keepSelection ? $this->selectedAtendimentoId : null;

        $this->data = AtendimentosData::data([
            'search' => $this->search,
            'status' => $this->statusFilter,
            'prioridade' => $this->prioridadeFilter,
            'origem' => $this->origemFilter,
            'responsavel' => $this->responsavelFilter,
            'aguardando' => $this->aguardandoFilter,
            'sla' => $this->slaFilter,
            'sort' => $this->sortBy,
            'empresa_id' => $this->empresaFilter ?: 0,
        ]);

        $this->summary = $this->data['summary'] ?? [];
        $this->statusBoard = $this->data['statusBoard'] ?? [];
        $this->prioridadeResumo = $this->data['prioridadeResumo'] ?? [];
        $this->atendimentos = $this->data['atendimentos'] ?? [];
        $this->empresas = $this->data['empresas'] ?? [];
        $this->clientes = $this->data['clientes'] ?? [];
        $this->responsaveis = $this->data['responsaveis'] ?? [];

        if (! $this->novoEmpresaId && count($this->empresas) === 1) {
            $this->novoEmpresaId = (int) $this->empresas[0]['id'];
        }

        if ($selectedId) {
            $this->refreshSelectedAtendimento((int) $selectedId);
        }

        $this->lastRefreshAt = now()->format('d/m/Y H:i:s');
    }

    protected function getViewData(): array
    {
        return $this->data;
    }


    public function sincronizarPortal(): void
    {
        if (! $this->bancoDisponivel()) {
            return;
        }

        $user = auth()->user();
        $empresaId = $user && ! $user->isSuperAdmin() ? (int) $user->empresa_id : null;

        $resultado = app(AtendimentoPortalService::class)->sincronizarPendentes($empresaId);
        $total = (int) ($resultado['solicitacoes'] ?? 0) + (int) ($resultado['mensagens'] ?? 0);

        $this->loadData(true);
        $this->notify($total > 0 ? 'success' : 'info', $total > 0
            ? "Portal sincronizado: {$resultado['solicitacoes']} solicitação(ões) e {$resultado['mensagens']} mensagem(ns) viraram atendimento."
            : 'Portal já estava sincronizado. Nenhum atendimento novo encontrado.');
    }

    public function resetarFiltros(): void
    {
        $this->search = '';
        $this->statusFilter = 'todos';
        $this->prioridadeFilter = 'todos';
        $this->origemFilter = 'todos';
        $this->responsavelFilter = 'todos';
        $this->aguardandoFilter = 'todos';
        $this->slaFilter = 'todos';
        $this->empresaFilter = null;
        $this->sortBy = 'recentes';
        $this->loadData(true);
    }

    public function filtrarStatus(string $status): void
    {
        $this->statusFilter = ($status === 'ativos' || AtendimentoStatus::exists($status)) ? $status : 'todos';
        $this->loadData(true);
    }

    public function filtrarFilaAtiva(): void
    {
        $this->statusFilter = 'ativos';
        $this->loadData(true);
    }

    public function filtrarSemResponsavel(): void
    {
        $this->responsavelFilter = 'sem_responsavel';
        $this->loadData(true);
    }

    public function filtrarPrioridade(string $prioridade): void
    {
        $this->prioridadeFilter = array_key_exists($prioridade, AtendimentosData::PRIORIDADES) ? $prioridade : 'todos';
        $this->loadData(true);
    }

    public function filtrarSla(string $sla): void
    {
        $this->slaFilter = array_key_exists($sla, AtendimentosData::SLA_OPTIONS) ? $sla : 'todos';
        $this->loadData(true);
    }

    public function abrirCriacao(): void
    {
        $this->resetFormCriacao();
        $this->createModalAberto = true;
    }

    public function fecharCriacao(): void
    {
        $this->createModalAberto = false;
    }

    public function criarAtendimento(): void
    {
        if (! $this->bancoDisponivel()) {
            return;
        }

        $empresaId = (int) $this->novoEmpresaId;
        if (! $empresaId || ! AtendimentosData::usuarioPodeAcessarEmpresa($empresaId)) {
            $this->notify('danger', 'Empresa inválida ou sem permissão.');
            return;
        }

        $titulo = Str::limit(trim($this->novoTitulo), 180, '');
        $descricao = trim($this->novoDescricao);
        if (mb_strlen($titulo) < 3 || mb_strlen($descricao) < 5) {
            $this->notify('danger', 'Informe um título e uma descrição úteis para o atendimento.');
            return;
        }

        $descricao = Str::limit($descricao, 12000, '');
        $prioridade = array_key_exists($this->novoPrioridade, AtendimentosData::PRIORIDADES) ? $this->novoPrioridade : 'media';
        $slaHoras = AtendimentosData::slaHorasPorPrioridade($prioridade);
        $clienteId = $this->clientePertenceEmpresa((int) $this->novoClienteId, $empresaId) ? (int) $this->novoClienteId : null;
        $responsavelId = $this->usuarioResponsavelValido((int) $this->novoResponsavelId) ? (int) $this->novoResponsavelId : null;
        $origem = in_array($this->novoOrigem, ['manual', 'portal', 'whatsapp', 'email', 'telefone'], true) ? $this->novoOrigem : 'manual';
        $canal = in_array($this->novoCanal, ['interno', 'portal', 'whatsapp', 'email', 'telefone'], true) ? $this->novoCanal : 'interno';

        try {
            $atendimento = DB::transaction(function () use ($empresaId, $clienteId, $responsavelId, $titulo, $descricao, $prioridade, $origem, $canal, $slaHoras) {
                $atendimento = Atendimento::query()->create([
                    'empresa_id' => $empresaId,
                    'crm_cliente_id' => $clienteId,
                    'responsavel_id' => $responsavelId,
                    'criado_por' => auth()->id(),
                    'titulo' => $titulo,
                    'descricao' => $descricao,
                    'status' => AtendimentoStatus::ABERTO,
                    'prioridade' => $prioridade,
                    'origem' => $origem,
                    'canal' => $canal,
                    'sla_horas' => $slaHoras,
                    'sla_limite_em' => now()->addHours($slaHoras),
                ]);

                AtendimentoInteracao::query()->create([
                    'atendimento_id' => $atendimento->id,
                    'user_id' => auth()->id(),
                    'origem' => 'interno',
                    'tipo' => 'abertura',
                    'mensagem' => 'Atendimento criado manualmente. ' . $descricao,
                ]);

                if ($responsavelId) {
                    AtendimentoInteracao::query()->create([
                        'atendimento_id' => $atendimento->id,
                        'user_id' => auth()->id(),
                        'origem' => 'sistema',
                        'tipo' => 'responsavel',
                        'mensagem' => 'Responsável definido na abertura do atendimento.',
                    ]);
                }

                return $atendimento;
            });
        } catch (Throwable $exception) {
            report($exception);
            $this->notify('danger', 'Não foi possível criar o atendimento. Verifique o banco e tente novamente.');
            return;
        }

        $this->createModalAberto = false;
        $this->resetFormCriacao();
        $this->loadData();
        $this->selecionarAtendimento((int) $atendimento->id);
        $this->notificarResponsavelInterno($atendimento->refresh(), 'Novo atendimento criado', $descricao);
        $this->notify('success', 'Atendimento criado com sucesso.');
    }

    public function selecionarAtendimento(int $id): void
    {
        $atendimento = $this->findAtendimentoAutorizado($id, false);
        if (! $atendimento) {
            $this->notify('danger', 'Atendimento não encontrado ou sem permissão.');
            return;
        }

        $this->refreshSelectedAtendimento($id);
        $this->detailModalAberto = true;
    }

    public function fecharDetalhe(): void
    {
        $this->detailModalAberto = false;
    }

    public function assumirAtendimento(int $id): void
    {
        $atendimento = $this->findAtendimentoAutorizado($id);
        if (! $atendimento) {
            return;
        }

        try {
            $this->workflow()->assumir($atendimento);
        } catch (Throwable $exception) {
            report($exception);
            $this->notify('danger', 'Não foi possível assumir o atendimento.');
            return;
        }

        $this->loadData(true);
        $this->notify('success', 'Atendimento assumido.');
    }

    public function mudarStatusRapido(int $id, string $status): void
    {
        $atendimento = $this->findAtendimentoAutorizado($id);
        if (! $atendimento) {
            return;
        }

        if (! AtendimentoStatus::exists($status)) {
            $this->notify('danger', 'Status inválido.');
            return;
        }

        $this->aplicarStatus($atendimento, $status);
    }

    public function resolverAtendimento(int $id): void
    {
        $this->mudarStatusRapido($id, AtendimentoStatus::RESOLVIDO);
    }

    public function resolverComResumo(): void
    {
        if (! $this->selectedAtendimentoId) {
            return;
        }

        $resumo = trim($this->resolucaoTexto);
        if (mb_strlen($resumo) < 5) {
            $this->notify('danger', 'Informe um resumo curto da resolução antes de finalizar.');
            return;
        }

        $atendimento = $this->findAtendimentoAutorizado($this->selectedAtendimentoId);
        if (! $atendimento) {
            return;
        }

        $this->aplicarStatus($atendimento, AtendimentoStatus::RESOLVIDO, Str::limit($resumo, 8000, ''));
        $this->resolucaoTexto = '';
    }

    public function encerrarComMotivo(): void
    {
        if (! $this->selectedAtendimentoId) {
            return;
        }

        $motivos = [
            'duvida_resolvida' => 'Dúvida resolvida',
            'documento_recebido' => 'Documento recebido',
            'pendencia_concluida' => 'Pendência concluída',
            'erro_corrigido' => 'Erro corrigido',
            'solicitacao_cancelada' => 'Solicitação cancelada',
            'outro' => 'Outro motivo',
        ];

        $motivoKey = array_key_exists($this->motivoEncerramento, $motivos) ? $this->motivoEncerramento : 'outro';
        $observacao = trim($this->observacaoEncerramento);

        if ($motivoKey === 'outro' && mb_strlen($observacao) < 5) {
            $this->notify('danger', 'Informe uma observação curta para encerrar como outro motivo.');
            return;
        }

        $atendimento = $this->findAtendimentoAutorizado($this->selectedAtendimentoId);
        if (! $atendimento) {
            return;
        }

        $mensagem = 'Encerramento: ' . $motivos[$motivoKey] . '.';
        if ($observacao !== '') {
            $mensagem .= "\nObservação: " . Str::limit($observacao, 1200, '');
        }

        $this->aplicarStatus($atendimento, AtendimentoStatus::FECHADO, $mensagem);
        $this->motivoEncerramento = 'duvida_resolvida';
        $this->observacaoEncerramento = '';
    }

    public function reabrirAtendimento(int $id): void
    {
        $this->mudarStatusRapido($id, AtendimentoStatus::EM_ANDAMENTO);
    }

    public function aguardarCliente(int $id): void
    {
        $this->mudarStatusRapido($id, AtendimentoStatus::AGUARDANDO_CLIENTE);
    }

    public function salvarDetalhe(): void
    {
        if (! $this->selectedAtendimentoId) {
            return;
        }

        $atendimento = $this->findAtendimentoAutorizado($this->selectedAtendimentoId);
        if (! $atendimento) {
            return;
        }

        try {
            $resultado = $this->actions()->salvarDetalhe(
                $atendimento,
                $this->novoStatusDetalhe,
                $this->novaPrioridadeDetalhe,
                $this->novoResponsavelDetalhe ? (int) $this->novoResponsavelDetalhe : null,
            );
        } catch (Throwable $exception) {
            report($exception);
            $this->notify('danger', 'Não foi possível atualizar o atendimento.');
            return;
        }

        $this->loadData(true);
        $this->notify($resultado['changed'] ? 'success' : 'success', $resultado['message']);
    }

    public function atribuirResponsavelDetalhe(int $responsavelId): void
    {
        if (! $this->selectedAtendimentoId) {
            return;
        }

        if (! $this->usuarioResponsavelValido($responsavelId)) {
            $this->notify('danger', 'Responsável inválido ou indisponível.');
            return;
        }

        $this->novoResponsavelDetalhe = $responsavelId;
        $this->salvarDetalhe();
    }

    public function responderCliente(): void
    {
        if (! $this->selectedAtendimentoId) {
            return;
        }

        $mensagem = trim($this->novaRespostaCliente);
        $anexo = $this->prepararAnexoRespostaCliente();
        if ($anexo === false) {
            return;
        }

        if (mb_strlen($mensagem) < 2 && ! $anexo) {
            $this->notify('danger', 'Escreva uma resposta ou selecione um anexo antes de enviar ao cliente.');
            return;
        }

        $atendimento = $this->findAtendimentoAutorizado($this->selectedAtendimentoId);
        if (! $atendimento) {
            return;
        }

        try {
            $this->actions()->responderCliente($atendimento, $mensagem, $anexo ?: null);
        } catch (Throwable $exception) {
            report($exception);
            $this->notify('danger', $exception->getMessage() ?: 'Não foi possível responder ao cliente.');
            return;
        }

        $this->notificarClienteResposta($atendimento->refresh(), $mensagem, (bool) $anexo);

        $this->novaRespostaCliente = '';
        $this->anexoRespostaCliente = null;
        $this->loadData(true);
        $this->notify('success', $anexo ? 'Resposta com anexo enviada ao portal do cliente.' : 'Resposta enviada ao portal do cliente.');
    }


    public function baixarAnexoHistorico(int $interacaoId, string $hash)
    {
        if (! CachedSchema::hasTable('atendimento_interacoes')) {
            $this->notify('danger', 'Histórico indisponível para download.');
            return null;
        }

        $interacao = DB::table('atendimento_interacoes')->where('id', $interacaoId)->first();
        if (! $interacao) {
            $this->notify('danger', 'Anexo não encontrado.');
            return null;
        }

        $atendimento = $this->findAtendimentoAutorizado((int) $interacao->atendimento_id);
        if (! $atendimento) {
            return null;
        }

        $metadata = $this->metadataArray($interacao->metadata ?? null);
        $anexos = is_array($metadata['anexos'] ?? null) ? $metadata['anexos'] : [];

        foreach ($anexos as $anexo) {
            if (! is_array($anexo) || empty($anexo['caminho'])) {
                continue;
            }

            $caminho = ltrim((string) $anexo['caminho'], '/');
            if (! hash_equals(sha1($caminho), $hash)) {
                continue;
            }

            if (! str_starts_with($caminho, 'portal_cliente_anexos/')) {
                $this->notify('danger', 'Arquivo fora da área permitida do portal.');
                return null;
            }

            if (! Storage::disk('public')->exists($caminho)) {
                $this->notify('danger', 'Arquivo não localizado no storage.');
                return null;
            }

            $nome = (string) ($anexo['nome_original'] ?? basename($caminho));
            return Storage::disk('public')->download($caminho, $nome);
        }

        $this->notify('danger', 'Anexo inválido ou sem permissão.');
        return null;
    }

    private function notificarClienteResposta(Atendimento $atendimento, string $mensagem, bool $temAnexo = false): void
    {
        $destinatario = $this->emailClienteAtendimento($atendimento);
        if (! $destinatario) {
            return;
        }

        $titulo = 'Atualização no atendimento #' . $atendimento->id;
        $texto = "Olá,\n\nA equipe de suporte respondeu seu atendimento.\n\n";
        if (trim($mensagem) !== '') {
            $texto .= "Mensagem:\n" . trim($mensagem) . "\n\n";
        }
        if ($temAnexo) {
            $texto .= "A resposta também possui anexo. Acesse a Área do Cliente para baixar com segurança.\n\n";
        }
        $texto .= "Acesse a Área do Cliente para acompanhar o histórico completo.\n\n";
        $texto .= config('app.name') . "\n";

        $this->enviarEmailSeguro($destinatario, $titulo, $texto);
    }

    private function notificarResponsavelInterno(Atendimento $atendimento, string $assunto, string $mensagem = ''): void
    {
        if (! $atendimento->responsavel_id || ! CachedSchema::hasTable('users')) {
            return;
        }

        $email = DB::table('users')->where('id', $atendimento->responsavel_id)->value('email');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $texto = "Olá,\n\nHá um atendimento vinculado a você.\n\n";
        $texto .= "Atendimento: #{$atendimento->id} - {$atendimento->titulo}\n";
        $texto .= "Status: " . AtendimentosData::statusLabel((string) $atendimento->status) . "\n";
        $texto .= "Prioridade: " . AtendimentosData::prioridadeLabel((string) $atendimento->prioridade) . "\n\n";
        if (trim($mensagem) !== '') {
            $texto .= trim($mensagem) . "\n\n";
        }
        $texto .= "Acesse o painel interno para tratar o chamado.\n\n";
        $texto .= config('app.name') . "\n";

        $this->enviarEmailSeguro((string) $email, $assunto, $texto);
    }

    private function enviarEmailSeguro(string $to, string $subject, string $body): void
    {
        $this->workflow()->enviarEmailSeguro($to, $subject, $body);
    }

    private function emailClienteAtendimento(Atendimento $atendimento): ?string
    {
        return $this->workflow()->emailClienteAtendimento($atendimento);
    }

    private function metadataArray(mixed $metadata): array
    {
        if (empty($metadata)) {
            return [];
        }

        if (is_array($metadata)) {
            return $metadata;
        }

        try {
            $decoded = json_decode((string) $metadata, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        } catch (Throwable) {
            return [];
        }
    }


    private function prepararAnexoRespostaCliente(): array|false|null
    {
        if (! $this->anexoRespostaCliente) {
            return null;
        }

        $arquivo = $this->anexoRespostaCliente;
        $nomeOriginal = (string) $arquivo->getClientOriginalName();
        $extensao = strtolower((string) $arquivo->getClientOriginalExtension());
        $mime = (string) ($arquivo->getMimeType() ?: 'application/octet-stream');
        $tamanho = (int) $arquivo->getSize();

        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv'];
        if (! in_array($extensao, $extensoesPermitidas, true)) {
            $this->notify('danger', 'Anexo inválido. Envie imagem, PDF, Word, Excel, TXT ou CSV.');
            return false;
        }

        if ($tamanho > 10 * 1024 * 1024) {
            $this->notify('danger', 'O anexo pode ter no máximo 10 MB.');
            return false;
        }

        $atendimentoId = (int) ($this->selectedAtendimentoId ?: 0);
        if ($atendimentoId <= 0) {
            $this->notify('danger', 'Abra um atendimento antes de anexar arquivos.');
            return false;
        }

        $nomeSeguro = Str::uuid()->toString() . ($extensao !== '' ? '.' . $extensao : '');
        $pasta = 'portal_cliente_anexos/' . $atendimentoId;
        $caminho = $arquivo->storeAs($pasta, $nomeSeguro, 'public');

        return [
            'nome_original' => $nomeOriginal,
            'nome_arquivo' => $nomeSeguro,
            'caminho' => $caminho,
            'mime' => $mime,
            'tamanho' => $tamanho,
            'extensao' => $extensao,
            'enviado_por' => 'suporte',
        ];
    }

    public function criarPendenciaDoAtendimento(): void
    {
        $atendimento = $this->selectedAtendimentoId ? $this->findAtendimentoAutorizado($this->selectedAtendimentoId) : null;
        if (! $atendimento) {
            return;
        }

        if (! $this->itemControlesDisponivel()) {
            return;
        }

        try {
            $this->actions()->criarPendencia($atendimento);
        } catch (Throwable $exception) {
            report($exception);
            $this->notify('danger', 'Não foi possível criar a pendência a partir do atendimento.');
            return;
        }

        $this->loadData(true);
        $this->notify('success', 'Pendência criada e registrada na timeline do atendimento.');
    }

    public function solicitarDocumentoDoAtendimento(): void
    {
        $atendimento = $this->selectedAtendimentoId ? $this->findAtendimentoAutorizado($this->selectedAtendimentoId) : null;
        if (! $atendimento) {
            return;
        }

        if (! $this->itemControlesDisponivel()) {
            return;
        }

        try {
            $this->actions()->solicitarDocumento($atendimento);
        } catch (Throwable $exception) {
            report($exception);
            $this->notify('danger', 'Não foi possível solicitar o documento a partir do atendimento.');
            return;
        }

        $this->loadData(true);
        $this->notify('success', 'Documento solicitado e atendimento marcado como aguardando cliente.');
    }


    public function adicionarInteracao(): void
    {
        if (! $this->selectedAtendimentoId) {
            return;
        }

        $mensagem = trim($this->novaInteracao);
        if (mb_strlen($mensagem) < 2) {
            $this->notify('danger', 'Escreva uma mensagem antes de adicionar ao histórico.');
            return;
        }

        $atendimento = $this->findAtendimentoAutorizado($this->selectedAtendimentoId);
        if (! $atendimento) {
            return;
        }

        try {
            $this->actions()->adicionarComentario($atendimento, $mensagem);
        } catch (Throwable $exception) {
            report($exception);
            $this->notify('danger', 'Não foi possível adicionar a interação.');
            return;
        }

        $this->novaInteracao = '';
        $this->loadData(true);
        $this->notify('success', 'Interação adicionada.');
    }

    private function itemControlesDisponivel(): bool
    {
        if (! CachedSchema::hasTable('item_controles')) {
            $this->notify('danger', 'Tabela de pendências/documentos indisponível.');
            return false;
        }

        return true;
    }

    private function nomeClienteAtendimento(Atendimento $atendimento): ?string
    {
        return $this->workflow()->nomeClienteAtendimento($atendimento);
    }


    private function aplicarStatus(Atendimento $atendimento, string $status, ?string $mensagemOperacional = null): void
    {
        if ($status === $atendimento->status) {
            $this->notify('success', 'O atendimento já está com esse status.');
            return;
        }

        try {
            $this->workflow()->aplicarStatus($atendimento, $status, $mensagemOperacional);
        } catch (Throwable $exception) {
            report($exception);
            $this->notify('danger', 'Não foi possível atualizar o status do atendimento.');
            return;
        }

        $this->loadData(true);
        $this->notify('success', 'Status atualizado.');
    }

    private function aplicarCamposStatus(Atendimento $atendimento, string $status, array &$payload): void
    {
        $this->workflow()->aplicarCamposStatus($atendimento, $status, $payload);
    }

    private function sincronizarPortalVinculado(Atendimento $atendimento, string $status, ?string $mensagemOperacional = null): void
    {
        $this->workflow()->sincronizarPortalVinculado($atendimento, $status, $mensagemOperacional);
    }

    private function refreshSelectedAtendimento(int $id): void
    {
        $this->selectedAtendimento = AtendimentosData::findFormatted($id);
        $this->selectedAtendimentoId = $this->selectedAtendimento ? $id : null;
        $this->timeline = $this->selectedAtendimento ? AtendimentosData::timeline($id) : [];

        if ($this->selectedAtendimento) {
            $this->syncDetailFields();
        }
    }

    private function findAtendimentoAutorizado(int $id, bool $notify = true): ?Atendimento
    {
        return $this->workflow()->findAutorizado(
            $id,
            $notify,
            fn (string $type, string $message) => $this->notify($type, $message),
        );
    }

    private function registrarInteracao(int $atendimentoId, string $tipo, string $mensagem, ?array $metadata = null): void
    {
        $this->workflow()->registrarInteracao($atendimentoId, $tipo, $mensagem, $metadata);
    }

    private function resetFormCriacao(): void
    {
        $this->novoEmpresaId = count($this->empresas) === 1 ? (int) $this->empresas[0]['id'] : ($this->empresaFilter ?: null);
        $this->novoClienteId = null;
        $this->novoResponsavelId = auth()->id();
        $this->novoTitulo = '';
        $this->novoDescricao = '';
        $this->novoPrioridade = 'media';
        $this->novoOrigem = 'manual';
        $this->novoCanal = 'interno';
        $this->resolucaoTexto = '';
    }

    private function syncDetailFields(): void
    {
        $this->novoStatusDetalhe = (string) ($this->selectedAtendimento['status'] ?? AtendimentoStatus::ABERTO);
        $this->novaPrioridadeDetalhe = (string) ($this->selectedAtendimento['prioridade'] ?? 'media');
        $this->novoResponsavelDetalhe = $this->selectedAtendimento['responsavel_id'] ?? null;
        $this->resolucaoTexto = '';
        $this->motivoEncerramento = 'duvida_resolvida';
        $this->observacaoEncerramento = '';
        $this->novaRespostaCliente = '';
        $this->anexoRespostaCliente = null;
    }

    private function clientePertenceEmpresa(int $clienteId, int $empresaId): bool
    {
        if (! $clienteId || ! CachedSchema::hasTable('crm_clientes')) {
            return false;
        }

        return DB::table('crm_clientes')->where('id', $clienteId)->where('empresa_id', $empresaId)->exists();
    }

    private function usuarioResponsavelValido(int $userId): bool
    {
        return $this->workflow()->usuarioResponsavelValido($userId);
    }

    private function bancoDisponivel(): bool
    {
        if (! CachedSchema::hasTable('atendimentos')) {
            $this->notify('danger', 'Tabela atendimentos não encontrada. Execute o SQL do Lote 1 antes de usar o módulo.');
            return false;
        }

        if (! CachedSchema::hasTable('atendimento_interacoes')) {
            $this->notify('danger', 'Tabela atendimento_interacoes não encontrada. Execute o SQL do Lote 1 antes de usar o módulo.');
            return false;
        }

        return true;
    }


    private function actions(): AtendimentoActionService
    {
        return app(AtendimentoActionService::class);
    }

    private function workflow(): AtendimentoWorkflowService
    {
        return app(AtendimentoWorkflowService::class);
    }

    private function notify(string $type, string $message): void
    {
        Notification::make()
            ->title($message)
            ->{$type}()
            ->send();
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
