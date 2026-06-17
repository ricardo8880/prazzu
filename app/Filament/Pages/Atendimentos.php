<?php

namespace App\Filament\Pages;

use App\Models\Atendimento;
use App\Models\PortalMensagem;
use App\Models\ItemControle;
use App\Support\AtendimentoPortalService;
use App\Support\AtendimentoStatus;
use App\Support\AtendimentoWorkflowService;
use App\Support\AtendimentosData;
use App\Support\ComplianceModuleData;
use App\Support\CachedSchema;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
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

    /**
     * Anexos do chat de atendimento usando a mesma regra do chat do Portal do Cliente.
     *
     * @var array<int, TemporaryUploadedFile>
     */
    public array $portalAnexos = [];

    public bool $clienteDigitando = false;
    public ?string $clienteDigitandoNome = null;
    public ?int $clienteVisualizouAteId = null;
    public ?int $suporteVisualizouAteId = null;
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

                $this->registrarInteracao(
                    (int) $atendimento->id,
                    'abertura',
                    'Atendimento criado manualmente. ' . $descricao,
                    [
                        'acao' => 'criar_atendimento',
                        'empresa_id' => $empresaId,
                        'crm_cliente_id' => $clienteId,
                        'responsavel_id' => $responsavelId,
                        'status_novo' => AtendimentoStatus::ABERTO,
                        'prioridade_nova' => $prioridade,
                        'origem_coluna' => 'interno',
                    ]
                );

                if ($responsavelId) {
                    $this->registrarInteracao(
                        (int) $atendimento->id,
                        'responsavel',
                        'Responsável definido na abertura do atendimento.',
                        [
                            'acao' => 'definir_responsavel_abertura',
                            'responsavel_novo_id' => $responsavelId,
                            'origem_coluna' => 'sistema',
                        ]
                    );
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
        $this->selectedAtendimento = null;
        $this->selectedAtendimentoId = null;
        $this->timeline = [];
        $this->novaInteracao = '';
        $this->novaRespostaCliente = '';
        $this->anexoRespostaCliente = null;
        $this->portalAnexos = [];
        $this->resolucaoTexto = '';
        $this->motivoEncerramento = 'duvida_resolvida';
        $this->observacaoEncerramento = '';
        $this->novoStatusDetalhe = AtendimentoStatus::ABERTO;
        $this->novaPrioridadeDetalhe = 'media';
        $this->novoResponsavelDetalhe = null;
    }

    public function assumirAtendimento(int $id): void
    {
        $atendimento = $this->findAtendimentoAutorizado($id);
        if (! $atendimento || ! $this->atendimentoPermiteAcaoOperacional($atendimento, 'assumir o atendimento')) {
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

        if (! $this->acaoStatusPermitida($atendimento, $status)) {
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
        if (! $atendimento || ! $this->acaoStatusPermitida($atendimento, AtendimentoStatus::RESOLVIDO)) {
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
        if (! $atendimento || ! $this->acaoStatusPermitida($atendimento, AtendimentoStatus::FECHADO)) {
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
        $atendimento = $this->findAtendimentoAutorizado($id);
        if (! $atendimento) {
            return;
        }

        if (! AtendimentoStatus::isClosed((string) $atendimento->status)) {
            $this->notify('info', 'Este atendimento já está aberto. Use as ações de andamento, resolução ou encerramento.');
            return;
        }

        $this->aplicarStatus($atendimento, AtendimentoStatus::EM_ANDAMENTO);
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

        if (AtendimentoStatus::isClosed((string) $atendimento->status)) {
            $this->notify('danger', 'Atendimento finalizado. Reabra antes de alterar detalhes.');
            return;
        }

        $status = AtendimentoStatus::exists($this->novoStatusDetalhe) ? $this->novoStatusDetalhe : $atendimento->status;
        $prioridade = array_key_exists($this->novaPrioridadeDetalhe, AtendimentosData::PRIORIDADES) ? $this->novaPrioridadeDetalhe : $atendimento->prioridade;
        $responsavelId = $this->usuarioResponsavelValido((int) $this->novoResponsavelDetalhe) ? (int) $this->novoResponsavelDetalhe : null;

        if ($status !== $atendimento->status && ! $this->acaoStatusPermitida($atendimento, $status, $responsavelId)) {
            return;
        }

        $payload = [
            'status' => $status,
            'prioridade' => $prioridade,
            'responsavel_id' => $responsavelId,
        ];

        $this->aplicarCamposStatus($atendimento, $status, $payload);

        if ($prioridade !== $atendimento->prioridade && ! AtendimentoStatus::isClosed($status)) {
            $slaHoras = AtendimentosData::slaHorasPorPrioridade($prioridade);
            $payload['sla_horas'] = $slaHoras;
            $payload['sla_limite_em'] = now()->addHours($slaHoras);
        }

        $mudancas = [];
        $metadataMudancas = ['acao' => 'salvar_detalhes_popup'];
        if ($status !== $atendimento->status) {
            $mudancas[] = 'status de ' . AtendimentosData::statusLabel($atendimento->status) . ' para ' . AtendimentosData::statusLabel($status);
            $metadataMudancas['status_anterior'] = (string) $atendimento->status;
            $metadataMudancas['status_novo'] = $status;
        }
        if ($prioridade !== $atendimento->prioridade) {
            $mudancas[] = 'prioridade de ' . AtendimentosData::prioridadeLabel($atendimento->prioridade) . ' para ' . AtendimentosData::prioridadeLabel($prioridade);
            $metadataMudancas['prioridade_anterior'] = (string) $atendimento->prioridade;
            $metadataMudancas['prioridade_nova'] = $prioridade;
        }
        if ((int) $responsavelId !== (int) $atendimento->responsavel_id) {
            $mudancas[] = 'responsável de ' . $this->nomeUsuarioPorId($atendimento->responsavel_id) . ' para ' . $this->nomeUsuarioPorId($responsavelId);
            $metadataMudancas['responsavel_anterior_id'] = $atendimento->responsavel_id;
            $metadataMudancas['responsavel_novo_id'] = $responsavelId;
        }

        if (empty($mudancas)) {
            $this->notify('success', 'Nenhuma alteração pendente.');
            return;
        }

        DB::transaction(function () use ($atendimento, $payload, $status, $mudancas, $metadataMudancas): void {
            $atendimento->update($payload);
            $this->registrarInteracao($atendimento->id, 'alteracao', 'Atualização: ' . implode(', ', $mudancas) . '.', $metadataMudancas);
            $this->sincronizarPortalVinculado($atendimento->refresh(), $status);
        });

        $this->loadData(true);
        $this->notify('success', 'Atendimento atualizado.');
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

        $this->validarMensagemAtendimentoComAnexos();

        $mensagem = trim($this->novaRespostaCliente);

        $atendimento = $this->findAtendimentoAutorizado($this->selectedAtendimentoId);
        if (! $atendimento) {
            return;
        }

        if (! $this->atendimentoPodeReceberRespostaCliente($atendimento)) {
            return;
        }

        $anexos = $this->salvarAnexosMensagemAtendimento($atendimento);

        if (mb_strlen($mensagem) < 2 && $anexos === []) {
            $this->notify('danger', 'Escreva uma resposta ou selecione um anexo antes de enviar ao cliente.');
            return;
        }

        $portalMensagem = null;

        DB::transaction(function () use ($atendimento, $mensagem, $anexos, &$portalMensagem): void {
            $agora = now();
            $payload = [
                'status' => AtendimentoStatus::AGUARDANDO_CLIENTE,
                'updated_at' => $agora,
            ];

            if (! $atendimento->responsavel_id && auth()->id()) {
                $payload['responsavel_id'] = auth()->id();
            }

            if (! $atendimento->primeira_resposta_em) {
                $payload['primeira_resposta_em'] = $agora;
            }

            $atendimento->update($payload);

            $portalMensagem = $this->registrarMensagemPortalDoAtendimento($atendimento->refresh(), $mensagem, $anexos);

            $this->registrarInteracao(
                (int) $atendimento->id,
                'resposta',
                Str::limit($mensagem !== '' ? $mensagem : 'Resposta enviada apenas com anexo.', 12000, ''),
                [
                    'acao' => 'responder_cliente',
                    'origem' => 'painel_interno_suporte',
                    'status_anterior' => (string) $atendimento->status,
                    'status_novo' => AtendimentoStatus::AGUARDANDO_CLIENTE,
                    'responsavel_anterior_id' => $atendimento->responsavel_id,
                    'responsavel_novo_id' => $payload['responsavel_id'] ?? $atendimento->responsavel_id,
                    'primeira_resposta_registrada' => ! (bool) $atendimento->primeira_resposta_em,
                    'visivel_cliente' => true,
                    'suporte_nome' => auth()->user()?->name,
                    'suporte_email' => auth()->user()?->email,
                    'portal_mensagem_id' => $portalMensagem?->id,
                    'anexos' => $anexos,
                ]
            );

            $this->sincronizarPortalVinculado($atendimento->refresh(), AtendimentoStatus::AGUARDANDO_CLIENTE);
        });

        $atendimentoAtualizado = $atendimento->refresh();
        $this->notificarClienteResposta($atendimentoAtualizado, $mensagem, $anexos !== []);

        if ($portalMensagem) {
            $this->dispatch('atendimento-chat-message-sent', payload: $this->payloadSocketMensagemAtendimento($atendimentoAtualizado, $portalMensagem, $mensagem, $anexos));
        }

        $this->novaRespostaCliente = '';
        $this->anexoRespostaCliente = null;
        $this->portalAnexos = [];
        $this->loadData(true);
        $this->notify('success', $anexos !== [] ? 'Resposta com anexos enviada ao portal do cliente.' : 'Resposta enviada ao portal do cliente.');
    }



    private function socketIoConfigAtendimento(Atendimento $atendimento): array
    {
        $empresaId = (int) $atendimento->empresa_id;

        if ($empresaId <= 0) {
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
        ];
    }

    private function payloadSocketMensagemAtendimento(Atendimento $atendimento, PortalMensagem $portalMensagem, string $mensagem, array $anexos = []): array
    {
        $empresaId = (int) $atendimento->empresa_id;
        $room = 'empresa:' . $empresaId . ':portal';
        $actor = 'suporte';

        return [
            'socket' => $this->socketIoConfigAtendimento($atendimento),
            'message' => [
                'id' => (int) $portalMensagem->id,
                'message_id' => (int) $portalMensagem->id,
                'empresa_id' => $empresaId,
                'atendimento_id' => (int) $atendimento->id,
                'room' => $room,
                'room_scope' => 'portal',
                'class' => 'equipe',
                'actor' => $actor,
                'server_signature' => hash_hmac('sha256', $empresaId . '|' . $room . '|' . $actor . '|' . (int) $portalMensagem->id, (string) config('app.key')),
                'origem' => 'interno',
                'author' => auth()->user()?->name ?: 'Equipe',
                'nome' => auth()->user()?->name ?: 'Equipe',
                'text' => trim($mensagem),
                'mensagem' => trim($mensagem),
                'time' => optional($portalMensagem->created_at)->format('d/m/Y H:i') ?: 'agora',
                'created_at_label' => optional($portalMensagem->created_at)->format('d/m/Y H:i') ?: 'agora',
                'attachments' => collect($anexos)->map(fn (array $anexo): array => [
                    'nome' => $anexo['nome'] ?? $anexo['nome_original'] ?? 'Anexo',
                    'url' => $anexo['url'] ?? null,
                    'mime_type' => $anexo['mime_type'] ?? $anexo['mime'] ?? 'application/octet-stream',
                    'size' => $anexo['size'] ?? $anexo['tamanho'] ?? null,
                    'size_label' => $anexo['size_label'] ?? null,
                    'is_image' => (bool) ($anexo['is_image'] ?? false),
                ])->values()->all(),
            ],
        ];
    }

    private function validarMensagemAtendimentoComAnexos(): void
    {
        $this->validate([
            'novaRespostaCliente' => ['nullable', 'string', 'max:5000', 'required_without:portalAnexos.0'],
            'portalAnexos' => ['array', 'max:5'],
            'portalAnexos.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt,csv'],
        ], [
            'novaRespostaCliente.required_without' => 'Digite uma mensagem ou anexe pelo menos um arquivo.',
            'portalAnexos.max' => 'Envie no máximo 5 arquivos por mensagem.',
            'portalAnexos.*.max' => 'Cada arquivo deve ter no máximo 10 MB.',
            'portalAnexos.*.mimes' => 'Use apenas imagem, PDF, Word, Excel, TXT ou CSV.',
        ]);
    }

    /**
     * @return array<int, array<string, string|int|bool|null>>
     */
    private function salvarAnexosMensagemAtendimento(Atendimento $atendimento): array
    {
        return collect($this->portalAnexos)
            ->filter(fn ($arquivo): bool => $arquivo instanceof TemporaryUploadedFile || $arquivo instanceof UploadedFile)
            ->map(function (TemporaryUploadedFile|UploadedFile $arquivo) use ($atendimento): array {
                $nomeOriginal = $arquivo->getClientOriginalName() ?: 'anexo';
                $nomeSeguro = substr((string) pathinfo($nomeOriginal, PATHINFO_FILENAME), 0, 80);
                $nomeSeguro = preg_replace('/[^A-Za-z0-9_-]+/', '-', $nomeSeguro) ?: 'anexo';
                $extensao = strtolower($arquivo->getClientOriginalExtension() ?: 'bin');
                $arquivoNome = trim($nomeSeguro, '-') . '-' . now()->format('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extensao;
                $pasta = 'portal-chat/' . (int) $atendimento->empresa_id;
                $caminho = $arquivo->storeAs($pasta, $arquivoNome, 'public');
                $mime = $arquivo->getMimeType() ?: 'application/octet-stream';
                $tamanho = (int) $arquivo->getSize();

                return [
                    'nome_original' => $nomeOriginal,
                    'nome_arquivo' => $arquivoNome,
                    'nome' => $nomeOriginal,
                    'caminho' => $caminho,
                    'url' => asset(Storage::url($caminho)),
                    'mime' => $mime,
                    'mime_type' => $mime,
                    'tamanho' => $tamanho,
                    'size' => $tamanho,
                    'size_label' => $tamanho ? number_format($tamanho / 1024, 1, ',', '.') . ' KB' : 'arquivo',
                    'is_image' => str_starts_with((string) $mime, 'image/'),
                    'extensao' => $extensao,
                    'enviado_por' => 'suporte',
                ];
            })
            ->values()
            ->all();
    }

    private function registrarMensagemPortalDoAtendimento(Atendimento $atendimento, string $mensagem, array $anexos = []): ?PortalMensagem
    {
        if (! CachedSchema::hasTable('portal_mensagens')) {
            return null;
        }

        $mensagemFinal = trim($mensagem);

        if ($anexos !== []) {
            $linhas = collect($anexos)
                ->map(fn (array $anexo): string => '- ' . ($anexo['nome'] ?? $anexo['nome_original'] ?? 'Anexo') . ' | ' . ($anexo['url'] ?? '') . ' | ' . ($anexo['mime_type'] ?? $anexo['mime'] ?? 'application/octet-stream') . ' | ' . ($anexo['size'] ?? $anexo['tamanho'] ?? ''))
                ->implode("\n");

            $mensagemFinal = trim($mensagemFinal . "\n\nAnexos enviados:\n" . $linhas);
        }

        if ($mensagemFinal === '') {
            return null;
        }

        $payload = [
            'empresa_id' => (int) $atendimento->empresa_id,
            'item_controle_id' => $atendimento->item_controle_id ? (int) $atendimento->item_controle_id : null,
            'user_id' => auth()->id(),
            'nome' => auth()->user()?->name,
            'email' => auth()->user()?->email,
            'mensagem' => $mensagemFinal,
            'origem' => 'interno',
        ];

        if (CachedSchema::hasColumn('portal_mensagens', 'atendimento_id')) {
            $payload['atendimento_id'] = (int) $atendimento->id;
        }

        if (CachedSchema::hasColumn('portal_mensagens', 'conversa_status')) {
            $payload['conversa_status'] = 'aberta';
        }

        return PortalMensagem::create($payload);
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

            if (! str_starts_with($caminho, 'portal_cliente_anexos/') && ! str_starts_with($caminho, 'portal-chat/')) {
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
        try {
            $caminho = $arquivo->storeAs($pasta, $nomeSeguro, 'public');
        } catch (Throwable $exception) {
            report($exception);
            $this->notify('danger', 'Não foi possível salvar o anexo. Tente novamente.');
            return false;
        }

        if (! is_string($caminho) || $caminho === '' || ! Storage::disk('public')->exists($caminho)) {
            $this->notify('danger', 'O upload do anexo não foi concluído. Remova o arquivo e tente novamente.');
            return false;
        }

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

    public function criarTarefaDoAtendimento(): void
    {
        $atendimento = $this->selectedAtendimentoId ? $this->findAtendimentoAutorizado($this->selectedAtendimentoId) : null;
        if (! $atendimento || ! $this->atendimentoPermiteAcaoOperacional($atendimento, 'criar tarefa')) {
            return;
        }

        if (! $this->itemControlesDisponivel()) {
            return;
        }

        try {
            DB::transaction(function () use ($atendimento): void {
                $item = $this->criarItemControleVinculado(
                    $atendimento,
                    'tarefa',
                    'Tarefa do ticket #' . $atendimento->id . ' - ' . Str::limit((string) $atendimento->titulo, 120, ''),
                    'Tarefa criada a partir do ticket/atendimento #' . $atendimento->id . ".

Contexto do ticket:
" . trim((string) ($atendimento->descricao ?: 'Sem descrição.')),
                    false
                );

                if (! $atendimento->item_controle_id) {
                    $atendimento->update(['item_controle_id' => $item->id, 'updated_at' => now()]);
                } else {
                    $atendimento->touch();
                }

                $this->registrarInteracao(
                    $atendimento->id,
                    'tarefa_criada',
                    'Tarefa criada a partir deste ticket: #' . $item->id . ' - ' . $item->titulo . '.',
                    ['item_controle_id' => $item->id, 'tipo' => 'tarefa', 'atendimento_id' => (int) $atendimento->id]
                );
            });
        } catch (Throwable $exception) {
            report($exception);
            $this->notify('danger', 'Não foi possível criar a tarefa a partir do atendimento.');
            return;
        }

        $this->loadData(true);
        $this->notify('success', 'Tarefa criada e vinculada ao ticket.');
    }

    public function criarPendenciaDoAtendimento(): void
    {
        $atendimento = $this->selectedAtendimentoId ? $this->findAtendimentoAutorizado($this->selectedAtendimentoId) : null;
        if (! $atendimento || ! $this->atendimentoPermiteAcaoOperacional($atendimento, 'criar pendência')) {
            return;
        }

        if (! $this->itemControlesDisponivel()) {
            return;
        }

        try {
            DB::transaction(function () use ($atendimento): void {
                $item = $this->criarItemControleVinculado(
                    $atendimento,
                    'pendencia_compliance',
                    'Pendência do atendimento #' . $atendimento->id . ' - ' . Str::limit((string) $atendimento->titulo, 120, ''),
                    'Pendência criada a partir do atendimento #' . $atendimento->id . ".\n\n" . trim((string) ($atendimento->descricao ?: 'Sem descrição.')),
                    false
                );

                if (! $atendimento->item_controle_id) {
                    $atendimento->update(['item_controle_id' => $item->id, 'updated_at' => now()]);
                } else {
                    $atendimento->touch();
                }

                $this->registrarInteracao(
                    $atendimento->id,
                    'pendencia_criada',
                    'Pendência criada a partir deste atendimento: #' . $item->id . ' - ' . $item->titulo . '.',
                    ['item_controle_id' => $item->id, 'tipo' => 'pendencia_compliance']
                );
            });
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
        if (! $atendimento || ! $this->atendimentoPermiteAcaoOperacional($atendimento, 'solicitar documento')) {
            return;
        }

        if (! $this->clienteTemCanalResposta($atendimento)) {
            $this->notify('danger', 'Não há e-mail/portal do cliente para solicitar documento com segurança. Corrija o cadastro antes.');
            return;
        }

        if (! $this->itemControlesDisponivel()) {
            return;
        }

        try {
            DB::transaction(function () use ($atendimento): void {
                $item = $this->criarItemControleVinculado(
                    $atendimento,
                    'documento',
                    'Documento solicitado no atendimento #' . $atendimento->id,
                    'Solicitação documental criada a partir do atendimento #' . $atendimento->id . '. ' . Str::limit(trim((string) $atendimento->titulo), 180, ''),
                    true
                );

                $payloadAtendimento = ['status' => AtendimentoStatus::AGUARDANDO_CLIENTE, 'updated_at' => now()];
                if (! $atendimento->item_controle_id) {
                    $payloadAtendimento['item_controle_id'] = $item->id;
                }
                if (! $atendimento->responsavel_id && auth()->id()) {
                    $payloadAtendimento['responsavel_id'] = auth()->id();
                }
                if (! $atendimento->primeira_resposta_em) {
                    $payloadAtendimento['primeira_resposta_em'] = now();
                }

                $atendimento->update($payloadAtendimento);

                $this->registrarInteracao(
                    $atendimento->id,
                    'documento_solicitado',
                    'Documento solicitado ao cliente e registrado em Documentos: #' . $item->id . ' - ' . $item->titulo . '.',
                    ['item_controle_id' => $item->id, 'tipo' => 'documento', 'portal_ativo' => true]
                );

                $this->sincronizarPortalVinculado($atendimento->refresh(), AtendimentoStatus::AGUARDANDO_CLIENTE);
            });
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

        $this->registrarInteracao($atendimento->id, 'comentario', $mensagem);
        if (! $atendimento->primeira_resposta_em && auth()->id()) {
            $atendimento->update(['primeira_resposta_em' => now()]);
        }

        $this->novaInteracao = '';
        $this->loadData(true);
        $this->notify('success', 'Interação adicionada.');
    }

    private function nomeUsuarioPorId(null|int|string $userId): string
    {
        $id = (int) $userId;
        if ($id <= 0 || ! CachedSchema::hasTable('users')) {
            return 'Sem responsável';
        }

        $nome = DB::table('users')->where('id', $id)->value('name');
        return $nome ? (string) $nome : 'Usuário #' . $id;
    }

    private function itemControlesDisponivel(): bool
    {
        if (! CachedSchema::hasTable('item_controles')) {
            $this->notify('danger', 'Tabela de pendências/documentos indisponível.');
            return false;
        }

        return true;
    }

    private function criarItemControleVinculado(Atendimento $atendimento, string $tipo, string $titulo, string $descricao, bool $portalAtivo): ItemControle
    {
        $empresaId = (int) $atendimento->empresa_id;
        $responsavelId = ComplianceModuleData::resolveResponsavelId(null, $empresaId);

        if (! $empresaId || ! $responsavelId) {
            throw new \RuntimeException('Empresa ou responsável indisponível para criar item de controle.');
        }

        $payload = [];
        $this->setItemControlePayload($payload, 'titulo', Str::limit(trim($titulo), 255, ''));
        $this->setItemControlePayload($payload, 'descricao', Str::limit(trim($descricao), 5000, ''));
        $this->setItemControlePayload($payload, 'tipo', $tipo);
        $this->setItemControlePayload($payload, 'status', 'pendente');
        $this->setItemControlePayload($payload, 'prioridade', $this->prioridadeItemControle((string) $atendimento->prioridade));
        $this->setItemControlePayload($payload, 'empresa_id', $empresaId);
        $this->setItemControlePayload($payload, 'atendimento_id', (int) $atendimento->id);
        $this->setItemControlePayload($payload, 'responsavel_id', $responsavelId);
        $this->setItemControlePayload($payload, 'data_vencimento', now()->addDays($portalAtivo ? 3 : 2)->toDateString());
        $this->setItemControlePayload($payload, 'portal_ativo', $portalAtivo);

        if ($portalAtivo) {
            $this->setItemControlePayload($payload, 'portal_cliente_nome', $this->nomeClienteAtendimento($atendimento));
            $this->setItemControlePayload($payload, 'portal_cliente_email', $this->emailClienteAtendimento($atendimento));
            $this->setItemControlePayload($payload, 'portal_expira_em', now()->addDays(7));
            $this->setItemControlePayload($payload, 'portal_status', 'pendente');
            $this->setItemControlePayload($payload, 'document_status', 'solicitado');
        }

        return ItemControle::query()->create($payload);
    }

    private function setItemControlePayload(array &$payload, string $column, mixed $value): void
    {
        if (CachedSchema::hasColumn('item_controles', $column)) {
            $payload[$column] = $value;
        }
    }

    private function prioridadeItemControle(string $prioridade): string
    {
        return in_array($prioridade, ['baixa', 'media', 'alta', 'urgente'], true) ? $prioridade : 'media';
    }

    private function nomeClienteAtendimento(Atendimento $atendimento): ?string
    {
        return $this->workflow()->nomeClienteAtendimento($atendimento);
    }


    private function acaoStatusPermitida(Atendimento $atendimento, string $novoStatus, ?int $responsavelIdOverride = null): bool
    {
        $statusAtual = (string) $atendimento->status;

        if (AtendimentoStatus::isClosed($statusAtual) && $novoStatus !== AtendimentoStatus::EM_ANDAMENTO) {
            $this->notify('danger', 'Atendimento finalizado. Reabra antes de executar novas ações.');
            return false;
        }

        if (in_array($novoStatus, [AtendimentoStatus::RESOLVIDO, AtendimentoStatus::FECHADO], true)) {
            $responsavelId = $responsavelIdOverride ?: (int) $atendimento->responsavel_id;
            if (! $responsavelId) {
                $this->notify('danger', 'Atribua um responsável antes de resolver ou encerrar o atendimento.');
                return false;
            }
        }

        return true;
    }

    private function atendimentoPermiteAcaoOperacional(Atendimento $atendimento, string $acao): bool
    {
        if (AtendimentoStatus::isClosed((string) $atendimento->status)) {
            $this->notify('danger', 'Atendimento finalizado. Reabra antes de ' . $acao . '.');
            return false;
        }

        if (! (int) $atendimento->empresa_id || ! AtendimentosData::usuarioPodeAcessarEmpresa((int) $atendimento->empresa_id)) {
            $this->notify('danger', 'Empresa inválida ou sem permissão para ' . $acao . '.');
            return false;
        }

        return true;
    }

    private function atendimentoPodeReceberRespostaCliente(Atendimento $atendimento): bool
    {
        if (! $this->atendimentoPermiteAcaoOperacional($atendimento, 'responder ao cliente')) {
            return false;
        }

        if (! $this->clienteTemCanalResposta($atendimento)) {
            $this->notify('danger', 'Não há e-mail/portal do cliente para enviar resposta com segurança. Corrija o cadastro antes de responder.');
            return false;
        }

        return true;
    }

    private function clienteTemCanalResposta(Atendimento $atendimento): bool
    {
        if (filter_var($this->emailClienteAtendimento($atendimento), FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return (bool) $atendimento->portal_solicitacao_id || (bool) $atendimento->portal_mensagem_id;
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
        $this->portalAnexos = [];
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
