<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesAdvancedPermissions;


use App\Support\CachedSchema;
use App\Support\ClientesCrmData;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Clientes extends Page
{
    use UsesAdvancedPermissions;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Clientes';

    protected static string|\UnitEnum|null $navigationGroup = 'Clientes';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.clientes';

    public function getHeading(): string
    {
        return 'Clientes';
    }

    public function getSubheading(): ?string
    {
        return 'Visão 360º do cliente: cadastro, contexto, saúde, histórico e vínculos com documentos, contratos, atendimentos e cobranças.';
    }



    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return static::canAdvancedPermission('clientes.view');
    }

    public string $search = '';
    public string $statusFilter = 'todos';
    public string $healthFilter = 'todos';
    public string $actionFilter = 'todos';
    public string $sortBy = 'updated_at';

    public array $crm = [];
    public array $cards = [];
    public array $clientes = [];
    public array $clients = [];
    public array $allClients = [];
    public array $pendencias = [];
    public array $historicos = [];
    public array $documentos = [];
    public array $proximosContatos = [];
    public array $empresas = [];
    public array $statusOptions = [];
    public array $healthOptions = [];

    public int $currentPage = 1;
    public int $perPage = 6;
    public int $totalClientesFiltrados = 0;
    public int $totalPages = 1;
    public string $clientPanelTab = 'overview';

    public ?array $selectedClient = null;
    public ?int $selectedEmpresaId = null;
    public bool $clienteModalAberto = false;

    public string $editStatusContrato = '';
    public string $editContatoNome = '';
    public string $editContatoEmail = '';
    public string $editContatoWhatsapp = '';
    public string $editHealthManual = '';
    public string $editObservacoes = '';

    public ?int $meetingEmpresaId = null;
    public string $meetingTitulo = '';
    public string $meetingDescricao = '';

    public ?int $emailEmpresaId = null;
    public string $emailAssunto = '';
    public string $emailMensagem = '';

    public bool $quickContatoAberto = false;
    public ?int $quickClienteId = null;
    public string $quickContatoTipo = 'contato';
    public string $quickContatoResumo = '';
    public string $quickStatusDepoisContato = '';
    public bool $quickConcluirPendenciaDepois = false;

    public function mount(): void
    {
        $this->statusOptions = ClientesCrmData::statusOptions();
        $this->healthOptions = ClientesCrmData::healthOptions();
        $this->loadData();
    }

    public function updatedSearch(): void
    {
        $this->currentPage = 1;
        $this->loadData(true);
    }

    public function updatedStatusFilter(): void
    {
        $this->currentPage = 1;
        $this->loadData(true);
    }

    public function updatedHealthFilter(): void
    {
        $this->currentPage = 1;
        $this->loadData(true);
    }

    public function updatedActionFilter(): void
    {
        $this->currentPage = 1;
        $this->loadData(true);
    }

    public function updatedSortBy(): void
    {
        $this->currentPage = 1;
        $this->loadData(true);
    }

    public function loadData(bool $keepSelection = false): void
    {
        $selectedId = $keepSelection ? $this->selectedEmpresaId : null;

        $this->crm = ClientesCrmData::get([
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
            'healthFilter' => $this->healthFilter,
            'actionFilter' => $this->actionFilter,
            'sortBy' => $this->sortBy,
        ]);

        $this->cards = $this->crm['cards'] ?? [];
        $this->allClients = array_values($this->crm['clients'] ?? $this->crm['clientes'] ?? []);
        $this->clientes = $this->allClients;
        $this->totalClientesFiltrados = count($this->allClients);
        $this->totalPages = max(1, (int) ceil($this->totalClientesFiltrados / max(1, $this->perPage)));
        $this->currentPage = min(max(1, $this->currentPage), $this->totalPages);
        $offset = ($this->currentPage - 1) * $this->perPage;
        $this->clients = array_slice($this->allClients, $offset, $this->perPage);
        $this->pendencias = $this->crm['pendencias'] ?? [];
        $this->historicos = $this->crm['historicos'] ?? [];
        $this->documentos = $this->crm['documentos'] ?? [];
        $this->proximosContatos = $this->crm['proximosContatos'] ?? [];
        $this->empresas = $this->crm['empresas'] ?? [];

        if ($selectedId) {
            $this->selectedClient = collect($this->allClients)->firstWhere('id', $selectedId);
        }

        if (! $this->selectedClient || ! collect($this->allClients)->firstWhere('id', (int) ($this->selectedClient['id'] ?? 0))) {
            $this->selectedClient = $this->clients[0] ?? ($this->allClients[0] ?? null);
        }

        $this->selectedEmpresaId = $this->selectedClient ? (int) $this->selectedClient['id'] : null;
        $this->fillEditFormFromSelectedClient();
        $this->fillDefaultFormClients();
    }

    protected function getViewData(): array
    {
        return [
            'permissions' => $this->permissionFlags('clientes'),
            'crm' => $this->crm,
            'cards' => $this->cards,
            'clientes' => $this->clientes,
            'clients' => $this->clients,
            'allClients' => $this->allClients,
            'currentPage' => $this->currentPage,
            'perPage' => $this->perPage,
            'totalClientesFiltrados' => $this->totalClientesFiltrados,
            'totalPages' => $this->totalPages,
            'clientPanelTab' => $this->clientPanelTab,
            'pendencias' => $this->pendencias,
            'historicos' => $this->historicos,
            'documentos' => $this->documentos,
            'proximosContatos' => $this->proximosContatos,
            'empresas' => $this->empresas,
            'statusOptions' => $this->statusOptions,
            'healthOptions' => $this->healthOptions,
            'actionFilter' => $this->actionFilter,
            'statusFilter' => $this->statusFilter,
            'healthFilter' => $this->healthFilter,
            'sortBy' => $this->sortBy,
            'selectedClient' => $this->selectedClient,
            'selectedEmpresaId' => $this->selectedEmpresaId,
        ];
    }

    public function resetarFiltros(): void
    {
        $this->search = '';
        $this->statusFilter = 'todos';
        $this->healthFilter = 'todos';
        $this->actionFilter = 'todos';
        $this->sortBy = 'updated_at';
        $this->currentPage = 1;
        $this->loadData();
    }

    public function ordenarPor(string $campo): void
    {
        $allowed = ['name', 'contract_status', 'ltv', 'health_score', 'late_items', 'open_items', 'updated_at', 'action_priority'];
        $this->sortBy = in_array($campo, $allowed, true) ? $campo : 'updated_at';
        $this->currentPage = 1;
        $this->loadData(true);
    }

    public function filtrarCentralAcao(string $tipo): void
    {
        $allowed = ['todos', 'criticos', 'atencao', 'sem_contato', 'followups', 'pendencias'];
        $this->actionFilter = in_array($tipo, $allowed, true) ? $tipo : 'todos';
        $this->sortBy = $this->actionFilter === 'todos' ? $this->sortBy : 'action_priority';
        $this->currentPage = 1;
        $this->loadData(true);
    }

    public function filtrarVisaoClientes(string $tipo): void
    {
        $this->actionFilter = 'todos';
        $this->statusFilter = 'todos';
        $this->healthFilter = 'todos';

        match ($tipo) {
            'ativos' => $this->statusFilter = 'Operando bem',
            'onboarding' => $this->statusFilter = 'Em implementação',
            'pendencias' => $this->actionFilter = 'pendencias',
            'inativos' => $this->statusFilter = 'Cancelado',
            default => null,
        };

        $this->sortBy = $this->actionFilter === 'todos' ? $this->sortBy : 'action_priority';
        $this->currentPage = 1;
        $this->loadData(true);
    }

    public function abrirImportacaoClientes(): void
    {
        if (! $this->ensureCanDo('clientes.create')) {
            return;
        }

        $this->notify('danger', 'Importação de clientes ainda não configurada neste painel. Use Novo cliente para cadastrar manualmente.');
    }

    public function goToPage(int $page): void
    {
        $this->currentPage = min(max(1, $page), max(1, $this->totalPages));
        $this->loadData(true);
    }

    public function previousPage(): void
    {
        $this->goToPage($this->currentPage - 1);
    }

    public function nextPage(): void
    {
        $this->goToPage($this->currentPage + 1);
    }

    public function updatedPerPage(): void
    {
        $allowed = [6, 10, 15, 25];
        $this->perPage = in_array((int) $this->perPage, $allowed, true) ? (int) $this->perPage : 6;
        $this->currentPage = 1;
        $this->loadData(true);
    }

    public function setClientPanelTab(string $tab): void
    {
        $allowed = ['overview', 'relationship', 'history', 'documents'];
        $this->clientPanelTab = in_array($tab, $allowed, true) ? $tab : 'overview';
    }

    public function editarCliente(int $clienteId): void
    {
        if (! $this->ensureCanDo('clientes.edit')) {
            return;
        }

        if (! $this->selectClient($clienteId, true)) {
            return;
        }

        $this->clienteModalAberto = true;
        $this->dispatch('clientes-crm-cliente-aberto');
    }

    public function fecharClienteModal(): void
    {
        $this->clienteModalAberto = false;
    }

    public function selectClient(int|string $clienteId, bool $notifyWhenMissing = false): bool
    {
        $client = $this->findClientById($clienteId);

        if (! $client) {
            if ($notifyWhenMissing) {
                $this->notify('danger', 'Cliente não encontrado ou indisponível para o seu usuário.');
            }

            return false;
        }

        $this->selectedClient = $client;
        $this->selectedEmpresaId = (int) $client['id'];
        $this->clientPanelTab = 'overview';
        $this->fillEditFormFromSelectedClient();
        $this->fillDefaultFormClients();

        return true;
    }

    public function mudarStatusContrato(int $clienteId, string $status): void
    {
        if (! $this->ensureCanDo('clientes.edit')) {
            return;
        }

        if (! $this->selectClient($clienteId, true)) {
            return;
        }

        $statusKey = ClientesCrmData::normalizeStatus($status);
        $this->updateCrmStatus((int) $this->selectedClient['id'], (int) $this->selectedClient['empresa_id'], $statusKey);
        $this->loadData(true);
        $this->notify('success', 'Status atualizado com sucesso.');
    }

    public function salvarClienteCrm(): void
    {
        if (! $this->ensureCanDo('clientes.edit')) {
            return;
        }

        if (! $this->selectedClient || ! $this->findClientById((int) ($this->selectedClient['id'] ?? 0))) {
            $this->notify('danger', 'Selecione um cliente válido antes de salvar.');
            return;
        }

        $empresaId = (int) $this->selectedClient['empresa_id'];
        $crmId = (int) $this->selectedClient['id'];
        $statusKey = $this->editStatusContrato !== '' ? ClientesCrmData::normalizeStatus($this->editStatusContrato) : null;
        $healthKey = $this->editHealthManual !== '' ? ClientesCrmData::normalizeHealth($this->editHealthManual) : null;

        if (CachedSchema::hasTable('empresas')) {
            $payload = [];
            $this->putIfColumn($payload, 'empresas', 'crm_status_contrato', $statusKey);
            $this->putIfColumn($payload, 'empresas', 'crm_contato_nome', trim($this->editContatoNome));
            $this->putIfColumn($payload, 'empresas', 'crm_contato_email', trim($this->editContatoEmail));
            $this->putIfColumn($payload, 'empresas', 'crm_contato_whatsapp', trim($this->editContatoWhatsapp));
            $this->putIfColumn($payload, 'empresas', 'crm_health_manual', $healthKey);
            $this->putIfColumn($payload, 'empresas', 'crm_observacoes', trim($this->editObservacoes));
            $this->putIfColumn($payload, 'empresas', 'updated_at', now());

            if ($payload) {
                DB::table('empresas')->where('id', $empresaId)->update($payload);
            }
        }

        if (CachedSchema::hasTable('crm_clientes')) {
            $payload = [];
            $this->putIfColumn($payload, 'crm_clientes', 'situacao', $statusKey);
            $this->putIfColumn($payload, 'crm_clientes', 'risco_churn', $this->healthToRisk($healthKey));
            $this->putIfColumn($payload, 'crm_clientes', 'proxima_acao', trim($this->editObservacoes) ?: null);
            $this->putIfColumn($payload, 'crm_clientes', 'updated_at', now());

            if ($payload) {
                DB::table('crm_clientes')->where('id', $crmId)->update($payload);
            }
        }

        $this->loadData(true);
        $this->notify('success', 'CRM salvo com sucesso.');
    }

    public function criarOnboarding(int $clienteId): void
    {
        if (! $this->ensureCanDo('tarefas.create')) {
            return;
        }

        if (! $this->selectClient($clienteId, true)) {
            return;
        }

        if (! CachedSchema::hasTable('crm_pendencias')) {
            $this->notify('danger', 'Não foi possível criar o onboarding. Verifique a tabela crm_pendencias.');
            return;
        }

        $crmId = (int) $this->selectedClient['id'];
        $now = now();
        $templates = [
            'Reunião de kick-off com o cliente',
            'Coletar acessos, documentos e responsáveis',
            'Definir primeira entrega e prazo de validação',
            'Configurar canal de comunicação e rotina de acompanhamento',
        ];

        foreach ($templates as $titulo) {
            $exists = DB::table('crm_pendencias')
                ->where('crm_cliente_id', $crmId)
                ->where('titulo', $titulo)
                ->exists();

            if (! $exists) {
                DB::table('crm_pendencias')->insert([
                    'crm_cliente_id' => $crmId,
                    'titulo' => $titulo,
                    'status' => 'pendente',
                    'created_at' => $now,
                ]);
            }
        }

        $this->registrarHistorico($crmId, 'onboarding', 'Checklist de onboarding criado/atualizado.');
        $this->loadData(true);
        $this->notify('success', 'Onboarding criado com sucesso.');
    }

    public function registrarReuniao(): void
    {
        if (! $this->ensureCanDo('clientes.edit')) {
            return;
        }

        $crmId = (int) ($this->meetingEmpresaId ?: $this->selectedEmpresaId ?: 0);
        $client = $this->findClientById($crmId);

        if (! $client || trim($this->meetingDescricao) === '') {
            $this->notify('danger', 'Selecione um cliente válido e informe a ata da reunião.');
            return;
        }

        $titulo = trim($this->meetingTitulo) ?: 'Reunião registrada';
        $descricao = $titulo . ': ' . trim($this->meetingDescricao);

        $this->registrarHistorico((int) $client['id'], 'reuniao', $descricao);

        if ($client) {
            $empresaId = (int) $client['empresa_id'];

            if (CachedSchema::hasTable('empresas') && CachedSchema::hasColumn('empresas', 'crm_ultima_reuniao_em')) {
                DB::table('empresas')->where('id', $empresaId)->update(['crm_ultima_reuniao_em' => now(), 'updated_at' => now()]);
            }

            if (CachedSchema::hasTable('crm_clientes') && CachedSchema::hasColumn('crm_clientes', 'ultimo_contato_em')) {
                DB::table('crm_clientes')->where('id', (int) $client['id'])->update(['ultimo_contato_em' => now(), 'updated_at' => now()]);
            }
        }

        $this->meetingTitulo = '';
        $this->meetingDescricao = '';
        $this->loadData(true);
        $this->notify('success', 'Reunião registrada no histórico.');
    }

    public function registrarEmailHistorico(): void
    {
        if (! $this->ensureCanDo('clientes.edit')) {
            return;
        }

        $crmId = (int) ($this->emailEmpresaId ?: $this->selectedEmpresaId ?: 0);
        $client = $this->findClientById($crmId);

        if (! $client || trim($this->emailMensagem) === '') {
            $this->notify('danger', 'Selecione um cliente válido e informe a mensagem.');
            return;
        }

        $assunto = trim($this->emailAssunto) ?: 'Contato registrado';
        $descricao = $assunto . ': ' . trim($this->emailMensagem);

        $this->registrarHistorico((int) $client['id'], 'contato', $descricao);
        $this->emailAssunto = '';
        $this->emailMensagem = '';
        $this->loadData(true);
        $this->notify('success', 'Contato registrado no histórico.');
    }

    public function abrirContatoRapido(int $clienteId): void
    {
        if (! $this->ensureCanDo('atendimentos.create')) {
            return;
        }

        if (! $this->selectClient($clienteId, true)) {
            return;
        }

        $this->quickContatoAberto = true;
        $this->quickClienteId = (int) $this->selectedClient['id'];
        $this->quickContatoTipo = 'contato';
        $this->quickContatoResumo = '';
        $this->quickStatusDepoisContato = '';
        $this->quickConcluirPendenciaDepois = ((int) ($this->selectedClient['open_items'] ?? 0)) > 0;
    }

    public function cancelarContatoRapido(): void
    {
        $this->quickContatoAberto = false;
        $this->quickClienteId = null;
        $this->quickContatoTipo = 'contato';
        $this->quickContatoResumo = '';
        $this->quickStatusDepoisContato = '';
        $this->quickConcluirPendenciaDepois = false;
    }

    public function registrarContatoRapido(): void
    {
        if (! $this->ensureCanDo('atendimentos.create')) {
            return;
        }

        $client = $this->findClientById((int) ($this->quickClienteId ?? 0));

        if (! $client) {
            $this->notify('danger', 'Selecione um cliente válido antes de registrar o contato.');
            return;
        }

        $resumo = trim($this->quickContatoResumo);

        if ($resumo === '') {
            $this->notify('danger', 'Informe um resumo objetivo do contato.');
            return;
        }

        $tipo = in_array($this->quickContatoTipo, ['contato', 'reuniao', 'email', 'whatsapp', 'ligacao'], true)
            ? $this->quickContatoTipo
            : 'contato';

        $crmId = (int) $client['id'];
        $empresaId = (int) $client['empresa_id'];
        $this->registrarHistorico($crmId, $tipo, ucfirst($tipo) . ' rápido: ' . $resumo);
        $this->atualizarUltimoContato($crmId, $empresaId, $tipo);

        if ($this->quickStatusDepoisContato !== '') {
            $this->updateCrmStatus($crmId, $empresaId, ClientesCrmData::normalizeStatus($this->quickStatusDepoisContato));
        }

        if ($this->quickConcluirPendenciaDepois) {
            $this->concluirPrimeiraPendenciaAberta($crmId, false);
        }

        $this->cancelarContatoRapido();
        $this->loadData(true);
        $this->notify('success', 'Contato registrado sem sair da lista.');
    }

    public function concluirProximaPendencia(int $clienteId): void
    {
        if (! $this->ensureCanDo('tarefas.edit')) {
            return;
        }

        if (! $this->selectClient($clienteId, true)) {
            return;
        }

        if ($this->concluirPrimeiraPendenciaAberta((int) $this->selectedClient['id'], true)) {
            $this->loadData(true);
            $this->notify('success', 'Pendência concluída com sucesso.');
        }
    }

    protected function concluirPrimeiraPendenciaAberta(int $crmId, bool $notifyWhenMissing = true): bool
    {
        if (! CachedSchema::hasTable('crm_pendencias') || ! CachedSchema::hasColumn('crm_pendencias', 'crm_cliente_id')) {
            if ($notifyWhenMissing) {
                $this->notify('danger', 'Não foi possível concluir. A tabela crm_pendencias não está disponível.');
            }

            return false;
        }

        $query = DB::table('crm_pendencias')
            ->where('crm_cliente_id', $crmId)
            ->where(function ($query): void {
                $query->whereNull('status')->orWhereNotIn('status', ['concluido', 'concluído', 'finalizado', 'cancelado']);
            })
            ->orderByRaw("CASE WHEN status = 'pendente' OR status IS NULL THEN 1 ELSE 2 END");

        if (CachedSchema::hasColumn('crm_pendencias', 'created_at')) {
            $query->orderBy('created_at');
        }

        $pendencia = $query->first();

        if (! $pendencia) {
            if ($notifyWhenMissing) {
                $this->notify('danger', 'Este cliente não possui pendências abertas para concluir.');
            }

            return false;
        }

        $payload = [];
        $this->putIfColumn($payload, 'crm_pendencias', 'status', 'concluido');
        $this->putIfColumn($payload, 'crm_pendencias', 'concluido_em', now());
        $this->putIfColumn($payload, 'crm_pendencias', 'updated_at', now());

        if (! $payload) {
            if ($notifyWhenMissing) {
                $this->notify('danger', 'Não há coluna de status disponível para atualizar esta pendência.');
            }

            return false;
        }

        DB::table('crm_pendencias')->where('id', (int) $pendencia->id)->update($payload);
        $this->registrarHistorico($crmId, 'pendencia', 'Pendência concluída: ' . (string) ($pendencia->titulo ?? 'Pendência'));

        return true;
    }

    protected function atualizarUltimoContato(int $crmId, int $empresaId, string $tipo): void
    {
        if (CachedSchema::hasTable('crm_clientes')) {
            $payload = [];
            $this->putIfColumn($payload, 'crm_clientes', 'ultimo_contato_em', now());
            $this->putIfColumn($payload, 'crm_clientes', 'updated_at', now());

            if ($payload) {
                DB::table('crm_clientes')->where('id', $crmId)->update($payload);
            }
        }

        if (CachedSchema::hasTable('empresas')) {
            $payload = [];
            $this->putIfColumn($payload, 'empresas', 'crm_ultimo_contato_em', now());
            $this->putIfColumn($payload, 'empresas', 'updated_at', now());

            if ($tipo === 'reuniao') {
                $this->putIfColumn($payload, 'empresas', 'crm_ultima_reuniao_em', now());
            }

            if ($payload) {
                DB::table('empresas')->where('id', $empresaId)->update($payload);
            }
        }
    }

    protected function fillEditFormFromSelectedClient(): void
    {
        if (! $this->selectedClient) {
            $this->editStatusContrato = '';
            $this->editContatoNome = '';
            $this->editContatoEmail = '';
            $this->editContatoWhatsapp = '';
            $this->editHealthManual = '';
            $this->editObservacoes = '';
            return;
        }

        $this->editStatusContrato = (string) ($this->selectedClient['contract_status'] ?? '');
        $this->editContatoNome = (string) ($this->selectedClient['contact_name'] ?? '');
        $this->editContatoEmail = (string) ($this->selectedClient['contact_email'] ?? '');
        $this->editContatoWhatsapp = (string) ($this->selectedClient['contact_whatsapp'] ?? '');
        $this->editHealthManual = (string) ($this->selectedClient['health_label'] ?? '');
        $this->editObservacoes = (string) ($this->selectedClient['observacoes'] ?? $this->selectedClient['next_action'] ?? '');
    }

    protected function fillDefaultFormClients(): void
    {
        $id = $this->selectedEmpresaId;

        if (! $this->findClientById((int) ($this->meetingEmpresaId ?? 0))) {
            $this->meetingEmpresaId = $id;
        }

        if (! $this->findClientById((int) ($this->emailEmpresaId ?? 0))) {
            $this->emailEmpresaId = $id;
        }
    }

    protected function findClientById(int|string|null $clienteId): ?array
    {
        $id = (int) $clienteId;

        if ($id <= 0) {
            return null;
        }

        $client = collect($this->clients)->firstWhere('id', $id)
            ?: collect($this->allClients)->firstWhere('id', $id)
            ?: collect($this->clientes)->firstWhere('id', $id);

        return is_array($client) ? $client : null;
    }

    protected function updateCrmStatus(int $crmId, int $empresaId, string $statusKey): void
    {
        if (CachedSchema::hasTable('crm_clientes') && CachedSchema::hasColumn('crm_clientes', 'situacao')) {
            $payload = ['situacao' => $statusKey];
            $this->putIfColumn($payload, 'crm_clientes', 'updated_at', now());
            DB::table('crm_clientes')->where('id', $crmId)->update($payload);
        }

        if (CachedSchema::hasTable('empresas') && CachedSchema::hasColumn('empresas', 'crm_status_contrato')) {
            $payload = ['crm_status_contrato' => $statusKey];
            $this->putIfColumn($payload, 'empresas', 'updated_at', now());
            DB::table('empresas')->where('id', $empresaId)->update($payload);
        }

        $this->registrarHistorico($crmId, 'status', 'Status alterado para ' . ClientesCrmData::statusLabel($statusKey) . '.');
    }

    protected function registrarHistorico(int $crmId, string $tipo, string $descricao): void
    {
        if (! CachedSchema::hasTable('crm_historicos')) {
            return;
        }

        DB::table('crm_historicos')->insert([
            'crm_cliente_id' => $crmId,
            'tipo' => Str::limit($tipo, 50, ''),
            'descricao' => $descricao,
            'created_at' => now(),
        ]);
    }

    protected function putIfColumn(array &$payload, string $table, string $column, mixed $value): void
    {
        if (CachedSchema::hasColumn($table, $column)) {
            $payload[$column] = $value;
        }
    }

    protected function healthToRisk(?string $health): ?string
    {
        return match ($health) {
            'critico' => 'alto',
            'atencao' => 'medio',
            'saudavel' => 'baixo',
            default => null,
        };
    }

    protected function notify(string $status, string $message): void
    {
        $notification = Notification::make()->title($message);

        if ($status === 'success') {
            $notification->success();
        } else {
            $notification->danger();
        }

        $notification->send();
    }
}
