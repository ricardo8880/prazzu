<?php

namespace App\Console\Commands;

use App\Support\CachedSchema;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DiagnosticoProducao extends Command
{
    protected $signature = 'sistemrh:diagnostico
        {--limite=500 : Quantidade máxima de registros analisados em consultas de amostragem pesada}
        {--arquivo= : Caminho opcional para salvar o relatório em JSON}
        {--sem-arquivos : Não validar existência física de anexos no disco public}
        {--somente-erros : Exibir no console apenas erros e avisos}';

    protected $description = 'Executa diagnóstico profundo e não destrutivo do SistemRH/Prazzu, validando ambiente, schema real, integridade de dados, portal, financeiro, anexos e filas.';

    private int $erros = 0;

    private int $avisos = 0;

    private int $oks = 0;

    private int $limite = 500;

    /**
     * @var array<int, array{nivel:string, secao:string, mensagem:string, detalhe:?string, contexto:array<string, mixed>}>
     */
    private array $achados = [];

    public function handle(): int
    {
        $this->limite = max(10, min(5000, (int) $this->option('limite')));

        $this->newLine();
        $this->info('Diagnóstico profundo SistemRH / Prazzu');
        $this->line('Modo pesado: valida ambiente, banco real, integridade relacional, portal, financeiro, anexos e rotas críticas.');
        $this->line('Execução segura: nenhuma tabela é alterada e nenhum registro é gravado.');
        $this->line('Amostragem máxima por consulta pesada: '.$this->limite.' registro(s).');
        $this->newLine();

        $inicio = microtime(true);

        $this->executarSecao('Ambiente PHP/Laravel', fn () => $this->validarPhpELaravel());
        $this->executarSecao('Diretórios e permissões', fn () => $this->validarDiretorios());
        $this->executarSecao('Banco e schema real', fn () => $this->validarBancoESchema());
        $this->executarSecao('Integridade multiempresa e usuários', fn () => $this->validarEmpresasEUsuarios());
        $this->executarSecao('Itens de controle e estados', fn () => $this->validarItensControle());
        $this->executarSecao('Portal público', fn () => $this->validarPortalPublico());
        $this->executarSecao('Financeiro / Asaas', fn () => $this->validarFinanceiroAsaas());
        $this->executarSecao('Anexos e documentos', fn () => $this->validarAnexosDocumentos());
        $this->executarSecao('Comandos, scheduler e filas', fn () => $this->validarComandosSchedulerFilas());
        $this->executarSecao('Rotas críticas', fn () => $this->validarRotasCriticas());
        $this->executarSecao('Arquivos de aplicação críticos', fn () => $this->validarArquivosCriticos());

        $duracao = round(microtime(true) - $inicio, 2);

        $this->newLine();
        $this->line('Resumo');
        $this->line("OK: {$this->oks} | Avisos: {$this->avisos} | Erros: {$this->erros} | Duração: {$duracao}s");

        $this->registrarRelatorio($duracao);
        $this->exportarJsonSeSolicitado($duracao);

        if ($this->erros > 0) {
            $this->error("Diagnóstico concluído com {$this->erros} erro(s) crítico(s) e {$this->avisos} aviso(s). Consulte storage/logs/diagnostico-sistemrh.log.");

            return self::FAILURE;
        }

        if ($this->avisos > 0) {
            $this->warn("Diagnóstico concluído sem erros críticos, mas com {$this->avisos} aviso(s). Consulte storage/logs/diagnostico-sistemrh.log.");

            return self::SUCCESS;
        }

        $this->info('Diagnóstico concluído sem erros críticos ou avisos.');

        return self::SUCCESS;
    }

    private function validarPhpELaravel(): void
    {
        $this->checar(version_compare(PHP_VERSION, '8.2.0', '>='), 'PHP >= 8.2', 'Atualize o PHP para 8.2 ou superior. Versão atual: '.PHP_VERSION, 'erro');

        foreach (['bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'json', 'mbstring', 'openssl', 'pdo', 'tokenizer', 'xml'] as $extensao) {
            $this->checar(extension_loaded($extensao), "Extensão PHP {$extensao} carregada", "Ative a extensão PHP {$extensao} no servidor.", in_array($extensao, ['mbstring', 'openssl', 'pdo', 'fileinfo'], true) ? 'erro' : 'aviso');
        }

        $this->checar((bool) config('app.key'), 'APP_KEY configurada', 'Configure APP_KEY com php artisan key:generate.', 'erro');
        $this->checar((bool) config('app.url'), 'APP_URL configurada', 'Configure APP_URL com o domínio real do sistema.', 'erro');

        if (app()->environment('production')) {
            $this->checar(! config('app.debug'), 'APP_DEBUG=false em produção', 'APP_DEBUG está ativo em produção. Desative para não expor detalhes internos.', 'erro');
            $this->checar((string) config('session.secure') === '1' || config('session.secure') === true, 'Cookies seguros em produção', 'SESSION_SECURE_COOKIE deve estar ativo em HTTPS/produção.', 'aviso');
        } else {
            $this->registrar('aviso', 'Ambiente PHP/Laravel', 'APP_ENV não está como production.', 'Isso é esperado em desenvolvimento/homologação. Revise antes de publicar.', []);
        }

        $locale = (string) config('app.locale');
        $this->checar(str_starts_with(strtolower($locale), 'pt'), 'Locale configurado para português', 'Recomendado usar APP_LOCALE=pt_BR.', 'aviso', ['locale' => $locale]);
    }

    private function validarDiretorios(): void
    {
        foreach ([storage_path(), storage_path('logs'), storage_path('framework'), storage_path('app/public'), app()->bootstrapPath('cache')] as $diretorio) {
            $this->checar(is_dir($diretorio), "Diretório existe: {$diretorio}", "Diretório ausente: {$diretorio}", 'erro');
            $this->checar(is_dir($diretorio) && is_writable($diretorio), "Diretório gravável: {$diretorio}", "Sem permissão de escrita em: {$diretorio}", 'erro');
        }

        $publicStorage = public_path('storage');
        $this->checar(is_link($publicStorage) || is_dir($publicStorage), 'Link public/storage existe', 'Execute php artisan storage:link para publicar anexos e uploads.', 'aviso');
    }

    private function validarBancoESchema(): void
    {
        try {
            DB::connection()->getPdo();
            $this->registrar('ok', 'Banco e schema real', 'Conexão com banco funcionando.', null, ['connection' => config('database.default')]);
        } catch (Throwable $exception) {
            $this->registrar('erro', 'Banco e schema real', 'Conexão com banco falhou.', $exception->getMessage(), []);

            return;
        }

        $tabelasObrigatorias = [
            'users',
            'empresas',
            'assinaturas',
            'pagamentos',
            'responsaveis',
            'item_controles',
            'item_controle_assinaturas',
            'item_controle_anexos',
            'portal_solicitacoes',
            'prazzu_client_portal_messages',
            'prazzu_permissions',
        ];

        foreach ($tabelasObrigatorias as $tabela) {
            $this->checar(CachedSchema::hasTable($tabela), "Tabela {$tabela} existe", "Tabela crítica ausente: {$tabela}", in_array($tabela, ['prazzu_client_portal_messages', 'prazzu_permissions'], true) ? 'aviso' : 'erro');
        }

        $possuiTabelaClientesLegado = CachedSchema::hasTable('clientes');
        $possuiTabelaClientesCrm = CachedSchema::hasTable('crm_clientes');
        $possuiTabelaClientesFinanceiro = CachedSchema::hasTable('financeiro_clientes');
        $this->checar(
            $possuiTabelaClientesLegado || $possuiTabelaClientesCrm || $possuiTabelaClientesFinanceiro,
            'Estrutura de clientes disponível.',
            'Nenhuma estrutura de clientes encontrada: clientes, crm_clientes ou financeiro_clientes.',
            'aviso',
            [
                'clientes' => $possuiTabelaClientesLegado,
                'crm_clientes' => $possuiTabelaClientesCrm,
                'financeiro_clientes' => $possuiTabelaClientesFinanceiro,
            ]
        );

        $this->validarColunas('empresas', ['id', 'razao_social', 'nome_fantasia', 'cnpj', 'email', 'plano', 'ativo', 'status', 'limite_usuarios', 'limite_itens']);
        $this->validarColunas('users', ['id', 'name', 'email', 'empresa_id']);
        $this->validarColunas('assinaturas', ['id', 'empresa_id', 'gateway', 'gateway_customer_id', 'gateway_subscription_id', 'plano', 'valor', 'status', 'proximo_vencimento', 'cancelado_em']);
        $this->validarColunas('pagamentos', ['id', 'empresa_id', 'assinatura_id', 'gateway_payment_id', 'status', 'valor', 'vencimento', 'pago_em', 'invoice_url']);
        $this->validarColunas('item_controles', ['id', 'empresa_id', 'responsavel_id', 'titulo', 'status', 'data_vencimento', 'portal_ativo', 'portal_token', 'portal_expira_em', 'portal_status', 'approval_status', 'document_status', 'custom_payload', 'bloqueado', 'faturado_em', 'pago_em']);
        $this->validarColunas('item_controle_assinaturas', ['id', 'item_controle_id', 'empresa_id', 'nome', 'email', 'hash_assinatura', 'assinado_em']);
        $this->validarColunas('item_controle_anexos', ['id', 'item_controle_id', 'arquivo', 'caminho', 'nome_original', 'mime_type', 'tamanho_bytes']);
        $this->validarColunas('portal_solicitacoes', ['id', 'empresa_id', 'item_controle_id', 'titulo', 'descricao', 'prioridade', 'status']);
        $this->validarColunas('prazzu_client_portal_messages', ['id', 'empresa_id', 'item_controle_id', 'message', 'sender_type']);
    }

    private function validarEmpresasEUsuarios(): void
    {
        if (! CachedSchema::hasTable('empresas')) {
            return;
        }

        $this->contar('empresas', 'Total de empresas cadastradas');

        if ($this->hasColumns('empresas', ['email'])) {
            $this->contarDuplicados('empresas', 'email', 'Empresas com e-mail duplicado', true);
        }

        if ($this->hasColumns('empresas', ['cnpj'])) {
            $this->contarDuplicados('empresas', 'cnpj', 'Empresas com CNPJ duplicado', true);
        }

        if ($this->hasColumns('empresas', ['ativo', 'status'])) {
            $ativasSuspensas = DB::table('empresas')
                ->where('ativo', 1)
                ->whereIn('status', ['inativo', 'cancelado', 'bloqueado', 'suspenso'])
                ->count();

            $this->checar($ativasSuspensas === 0, 'Empresas ativas não possuem status bloqueante.', 'Existem empresas com ativo=1 e status bloqueante.', 'aviso', ['quantidade' => $ativasSuspensas]);
        }

        if (CachedSchema::hasTable('users') && $this->hasColumns('users', ['empresa_id'])) {
            $usuariosSemEmpresa = DB::table('users')->whereNull('empresa_id')->count();
            $this->checar($usuariosSemEmpresa === 0, 'Usuários possuem empresa vinculada.', 'Existem usuários sem empresa_id.', 'aviso', ['quantidade' => $usuariosSemEmpresa]);

            if ($this->hasColumns('empresas', ['id'])) {
                $usuariosEmpresaInexistente = DB::table('users')
                    ->leftJoin('empresas', 'empresas.id', '=', 'users.empresa_id')
                    ->whereNotNull('users.empresa_id')
                    ->whereNull('empresas.id')
                    ->count();

                $this->checar($usuariosEmpresaInexistente === 0, 'Usuários apontam para empresas existentes.', 'Existem usuários vinculados a empresa inexistente.', 'erro', ['quantidade' => $usuariosEmpresaInexistente]);
            }
        }
    }

    private function validarItensControle(): void
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return;
        }

        $this->contar('item_controles', 'Total de itens de controle');

        if ($this->hasColumns('item_controles', ['empresa_id']) && CachedSchema::hasTable('empresas')) {
            $orfans = DB::table('item_controles')
                ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
                ->whereNotNull('item_controles.empresa_id')
                ->whereNull('empresas.id')
                ->count();

            $this->checar($orfans === 0, 'Itens apontam para empresas existentes.', 'Existem itens vinculados a empresa inexistente.', 'erro', ['quantidade' => $orfans]);
        }

        if ($this->hasColumns('item_controles', ['titulo'])) {
            $semTitulo = DB::table('item_controles')
                ->where(fn (Builder $query) => $query->whereNull('titulo')->orWhereRaw("TRIM(COALESCE(titulo, '')) = ''"))
                ->count();

            $this->checar($semTitulo === 0, 'Itens possuem título.', 'Existem itens sem título.', 'erro', ['quantidade' => $semTitulo]);
        }

        if ($this->hasColumns('item_controles', ['status'])) {
            $semStatus = DB::table('item_controles')
                ->where(fn (Builder $query) => $query->whereNull('status')->orWhereRaw("TRIM(COALESCE(status, '')) = ''"))
                ->count();

            $this->checar($semStatus === 0, 'Itens possuem status.', 'Existem itens sem status.', 'erro', ['quantidade' => $semStatus]);
        }

        if ($this->hasColumns('item_controles', ['data_vencimento', 'status'])) {
            $vencidosNaoMarcados = DB::table('item_controles')
                ->whereDate('data_vencimento', '<', Carbon::today())
                ->whereNotIn('status', ['concluido', 'concluído', 'finalizado', 'cancelado', 'vencido'])
                ->count();

            $this->checar($vencidosNaoMarcados === 0, 'Itens vencidos estão com estado coerente.', 'Existem itens com data vencida e status ainda aberto.', 'aviso', ['quantidade' => $vencidosNaoMarcados]);
        }

        if ($this->hasColumns('item_controles', ['portal_ativo', 'portal_token'])) {
            $portalSemToken = DB::table('item_controles')
                ->where('portal_ativo', 1)
                ->where(fn (Builder $query) => $query->whereNull('portal_token')->orWhereRaw("TRIM(COALESCE(portal_token, '')) = ''"))
                ->count();

            $this->checar($portalSemToken === 0, 'Itens com portal ativo possuem token.', 'Existem itens com portal ativo sem portal_token.', 'erro', ['quantidade' => $portalSemToken]);

            $this->contarDuplicados('item_controles', 'portal_token', 'Itens com portal_token duplicado', true);
        }

        if ($this->hasColumns('item_controles', ['portal_ativo', 'portal_expira_em'])) {
            $portalExpiradoAtivo = DB::table('item_controles')
                ->where('portal_ativo', 1)
                ->whereNotNull('portal_expira_em')
                ->where('portal_expira_em', '<', now())
                ->count();

            $this->checar($portalExpiradoAtivo === 0, 'Portais expirados não permanecem ativos.', 'Existem itens com portal ativo e data expirada.', 'aviso', ['quantidade' => $portalExpiradoAtivo]);
        }

        if ($this->hasColumns('item_controles', ['approval_required', 'approval_status'])) {
            $aprovacaoSemStatus = DB::table('item_controles')
                ->where('approval_required', 1)
                ->where(fn (Builder $query) => $query->whereNull('approval_status')->orWhereRaw("TRIM(COALESCE(approval_status, '')) = ''"))
                ->count();

            $this->checar($aprovacaoSemStatus === 0, 'Itens com aprovação obrigatória possuem approval_status.', 'Itens exigindo aprovação estão sem approval_status.', 'aviso', ['quantidade' => $aprovacaoSemStatus]);
        }
    }

    private function validarPortalPublico(): void
    {
        if (CachedSchema::hasTable('item_controle_assinaturas') && CachedSchema::hasTable('item_controles')) {
            if ($this->hasColumns('item_controle_assinaturas', ['item_controle_id']) && $this->hasColumns('item_controles', ['id'])) {
                $assinaturasOrfas = DB::table('item_controle_assinaturas')
                    ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_assinaturas.item_controle_id')
                    ->whereNull('item_controles.id')
                    ->count();

                $this->checar($assinaturasOrfas === 0, 'Assinaturas do portal apontam para itens existentes.', 'Existem assinaturas órfãs em item_controle_assinaturas.', 'erro', ['quantidade' => $assinaturasOrfas]);
            }

            if ($this->hasColumns('item_controle_assinaturas', ['hash_assinatura'])) {
                $this->contarDuplicados('item_controle_assinaturas', 'hash_assinatura', 'Assinaturas com hash duplicado', true);
            }

            if ($this->hasColumns('item_controle_assinaturas', ['item_controle_id', 'email'])) {
                $duplicadasPorItemEmail = DB::table('item_controle_assinaturas')
                    ->select('item_controle_id', 'email', DB::raw('COUNT(*) as total'))
                    ->whereNotNull('item_controle_id')
                    ->whereNotNull('email')
                    ->groupBy('item_controle_id', 'email')
                    ->havingRaw('COUNT(*) > 1')
                    ->limit($this->limite)
                    ->get()
                    ->count();

                $this->checar($duplicadasPorItemEmail === 0, 'Não há assinaturas repetidas para o mesmo item/e-mail.', 'Existem múltiplas assinaturas para o mesmo item e e-mail.', 'aviso', ['grupos' => $duplicadasPorItemEmail]);
            }
        }

        if (CachedSchema::hasTable('portal_solicitacoes')) {
            if ($this->hasColumns('portal_solicitacoes', ['empresa_id']) && CachedSchema::hasTable('empresas')) {
                $solicitacoesOrfas = DB::table('portal_solicitacoes')
                    ->leftJoin('empresas', 'empresas.id', '=', 'portal_solicitacoes.empresa_id')
                    ->whereNull('empresas.id')
                    ->count();

                $this->checar($solicitacoesOrfas === 0, 'Solicitações do portal apontam para empresas existentes.', 'Existem solicitações do portal sem empresa válida.', 'erro', ['quantidade' => $solicitacoesOrfas]);
            }

            if ($this->hasColumns('portal_solicitacoes', ['item_controle_id']) && CachedSchema::hasTable('item_controles')) {
                $solicitacoesItemInvalido = DB::table('portal_solicitacoes')
                    ->leftJoin('item_controles', 'item_controles.id', '=', 'portal_solicitacoes.item_controle_id')
                    ->whereNotNull('portal_solicitacoes.item_controle_id')
                    ->whereNull('item_controles.id')
                    ->count();

                $this->checar($solicitacoesItemInvalido === 0, 'Solicitações vinculadas apontam para itens existentes.', 'Existem solicitações vinculadas a item inexistente.', 'erro', ['quantidade' => $solicitacoesItemInvalido]);
            }
        }

        if (CachedSchema::hasTable('prazzu_client_portal_messages')) {
            if ($this->hasColumns('prazzu_client_portal_messages', ['item_controle_id']) && CachedSchema::hasTable('item_controles')) {
                $mensagensOrfas = DB::table('prazzu_client_portal_messages')
                    ->leftJoin('item_controles', 'item_controles.id', '=', 'prazzu_client_portal_messages.item_controle_id')
                    ->whereNotNull('prazzu_client_portal_messages.item_controle_id')
                    ->whereNull('item_controles.id')
                    ->count();

                $this->checar($mensagensOrfas === 0, 'Mensagens do portal apontam para itens existentes.', 'Existem mensagens do portal vinculadas a item inexistente.', 'erro', ['quantidade' => $mensagensOrfas]);
            }
        }
    }

    private function validarFinanceiroAsaas(): void
    {
        $apiKey = trim((string) config('services.asaas.api_key', env('ASAAS_API_KEY')));
        $webhookToken = trim((string) env('ASAAS_WEBHOOK_TOKEN'));

        $this->checar($apiKey !== '', 'ASAAS_API_KEY configurada.', 'ASAAS_API_KEY não está configurada. Ignore somente se o financeiro estiver desativado neste ambiente.', 'aviso');
        $this->checar($webhookToken !== '', 'ASAAS_WEBHOOK_TOKEN configurado.', 'ASAAS_WEBHOOK_TOKEN não está configurado; webhooks podem ficar expostos.', $apiKey !== '' ? 'erro' : 'aviso');

        if (! CachedSchema::hasTable('assinaturas')) {
            return;
        }

        if ($this->hasColumns('assinaturas', ['empresa_id']) && CachedSchema::hasTable('empresas')) {
            $assinaturasOrfas = DB::table('assinaturas')
                ->leftJoin('empresas', 'empresas.id', '=', 'assinaturas.empresa_id')
                ->whereNull('empresas.id')
                ->count();

            $this->checar($assinaturasOrfas === 0, 'Assinaturas apontam para empresas existentes.', 'Existem assinaturas com empresa_id inválido.', 'erro', ['quantidade' => $assinaturasOrfas]);
        }

        if ($this->hasColumns('assinaturas', ['status', 'gateway_subscription_id'])) {
            $ativasSemGateway = DB::table('assinaturas')
                ->whereIn('status', ['ACTIVE', 'RECEIVED', 'CONFIRMED'])
                ->where(fn (Builder $query) => $query->whereNull('gateway_subscription_id')->orWhereRaw("TRIM(COALESCE(gateway_subscription_id, '')) = ''"))
                ->count();

            $this->checar($ativasSemGateway === 0, 'Assinaturas ativas possuem gateway_subscription_id.', 'Existem assinaturas ativas sem vínculo com o gateway.', 'aviso', ['quantidade' => $ativasSemGateway]);
        }

        if ($this->hasColumns('assinaturas', ['status', 'cancelado_em'])) {
            $canceladasAtivas = DB::table('assinaturas')
                ->whereNotNull('cancelado_em')
                ->whereIn('status', ['ACTIVE', 'RECEIVED', 'CONFIRMED'])
                ->count();

            $this->checar($canceladasAtivas === 0, 'Assinaturas canceladas não permanecem ativas.', 'Existem assinaturas com cancelado_em preenchido e status ativo.', 'erro', ['quantidade' => $canceladasAtivas]);
        }

        if (CachedSchema::hasTable('pagamentos')) {
            if ($this->hasColumns('pagamentos', ['assinatura_id'])) {
                $pagamentosSemAssinatura = DB::table('pagamentos')
                    ->leftJoin('assinaturas', 'assinaturas.id', '=', 'pagamentos.assinatura_id')
                    ->whereNotNull('pagamentos.assinatura_id')
                    ->whereNull('assinaturas.id')
                    ->count();

                $this->checar($pagamentosSemAssinatura === 0, 'Pagamentos apontam para assinaturas existentes.', 'Existem pagamentos vinculados a assinatura inexistente.', 'erro', ['quantidade' => $pagamentosSemAssinatura]);
            }

            if ($this->hasColumns('pagamentos', ['status', 'pago_em'])) {
                $pagosSemData = DB::table('pagamentos')
                    ->whereIn('status', ['RECEIVED', 'CONFIRMED'])
                    ->whereNull('pago_em')
                    ->count();

                $this->checar($pagosSemData === 0, 'Pagamentos confirmados possuem pago_em.', 'Existem pagamentos confirmados sem data de pagamento.', 'aviso', ['quantidade' => $pagosSemData]);
            }
        }

        if (CachedSchema::hasTable('empresas') && $this->hasColumns('empresas', ['ativo']) && $this->hasColumns('assinaturas', ['empresa_id', 'status'])) {
            $empresasAtivasSemAssinaturaAtiva = DB::table('empresas')
                ->where('empresas.ativo', 1)
                ->whereNotExists(function (Builder $query): void {
                    $query->selectRaw('1')
                        ->from('assinaturas')
                        ->whereColumn('assinaturas.empresa_id', 'empresas.id')
                        ->whereIn('assinaturas.status', ['ACTIVE', 'RECEIVED', 'CONFIRMED']);
                })
                ->count();

            $this->checar($empresasAtivasSemAssinaturaAtiva === 0, 'Empresas ativas possuem assinatura ativa/confirmada.', 'Existem empresas ativas sem assinatura ativa/confirmada.', 'aviso', ['quantidade' => $empresasAtivasSemAssinaturaAtiva]);
        }
    }

    private function validarAnexosDocumentos(): void
    {
        if (! CachedSchema::hasTable('item_controle_anexos')) {
            return;
        }

        if ($this->hasColumns('item_controle_anexos', ['item_controle_id']) && CachedSchema::hasTable('item_controles')) {
            $anexosOrfaos = DB::table('item_controle_anexos')
                ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_anexos.item_controle_id')
                ->whereNull('item_controles.id')
                ->count();

            $this->checar($anexosOrfaos === 0, 'Anexos apontam para itens existentes.', 'Existem anexos órfãos em item_controle_anexos.', 'erro', ['quantidade' => $anexosOrfaos]);
        }

        if (! $this->option('sem-arquivos') && $this->hasColumns('item_controle_anexos', ['arquivo', 'caminho'])) {
            $anexos = DB::table('item_controle_anexos')
                ->select('id', 'arquivo', 'caminho')
                ->orderByDesc('id')
                ->limit($this->limite)
                ->get();

            $faltando = [];

            foreach ($anexos as $anexo) {
                $caminho = trim((string) ($anexo->arquivo ?: $anexo->caminho));

                if ($caminho === '') {
                    $faltando[] = ['id' => $anexo->id, 'motivo' => 'sem caminho'];
                    continue;
                }

                if (! Storage::disk('public')->exists($caminho)) {
                    $faltando[] = ['id' => $anexo->id, 'caminho' => $caminho];
                }

                if (count($faltando) >= 20) {
                    break;
                }
            }

            $this->checar(count($faltando) === 0, 'Amostra de anexos existe fisicamente no disco public.', 'Há anexos da amostra sem arquivo físico no storage público.', 'aviso', ['amostra_com_problema' => $faltando]);
        }
    }

    private function validarComandosSchedulerFilas(): void
    {
        $this->checar(class_exists(\App\Console\Commands\AtualizarItensControleVencidos::class), 'Comando de vencidos oficial existe.', 'Comando AtualizarItensControleVencidos não foi encontrado.', 'erro');
        $this->checar(class_exists(\App\Console\Commands\NotificarVencimentoItensControle::class), 'Comando de notificação de vencimentos existe.', 'Comando NotificarVencimentoItensControle não foi encontrado.', 'aviso');
        $this->checar(class_exists(\App\Console\Commands\ReconciliarAssinaturasAsaas::class), 'Comando de reconciliação Asaas existe.', 'Comando ReconciliarAssinaturasAsaas não foi encontrado.', 'aviso');

        $console = base_path('routes/console.php');
        $kernel = app_path('Console/Kernel.php');

        $this->checar(File::exists($console), 'routes/console.php existe.', 'Arquivo routes/console.php ausente.', 'erro');
        $this->checar(File::exists($kernel), 'app/Console/Kernel.php existe.', 'Arquivo app/Console/Kernel.php ausente.', 'erro');

        if (File::exists($console)) {
            $conteudo = File::get($console);
            foreach (['item-controle:notificar-vencimentos', 'itens-controle:atualizar-vencidos', 'asaas:reconciliar-assinaturas'] as $comando) {
                $this->checar(str_contains($conteudo, $comando), "Scheduler contém {$comando}.", "Scheduler não agenda {$comando}.", 'aviso');
            }
        }

        if (File::exists($kernel)) {
            $conteudoKernel = File::get($kernel);
            $duplicado = str_contains($conteudoKernel, "Schedule::command('itens-controle:atualizar-vencidos") || str_contains($conteudoKernel, 'Schedule::command("itens-controle:atualizar-vencidos');
            $this->checar(! $duplicado, 'Kernel não duplica scheduler de itens vencidos.', 'Kernel ainda agenda itens vencidos além de routes/console.php.', 'aviso');
        }

        $queue = (string) config('queue.default');
        $this->checar($queue !== '', 'QUEUE_CONNECTION configurada.', 'QUEUE_CONNECTION não está configurada.', 'erro');
        if (app()->environment('production')) {
            $this->checar($queue !== 'sync', 'Fila assíncrona configurada em produção.', 'QUEUE_CONNECTION=sync em produção pode travar telas em jobs pesados.', 'aviso');
        }
    }

    private function validarRotasCriticas(): void
    {
        try {
            $rotas = collect(app('router')->getRoutes())->mapWithKeys(function ($route): array {
                return [$route->getName() ?: $route->uri() => $route->uri()];
            });

            foreach ([
                'portal.item-controles.show',
                'portal.item-controles.assinar',
                'portal.item-controles.documentos',
                'portal.cliente.show',
                'portal.cliente.solicitacoes.store',
                'asaas.webhook',
                'billing.cancelar',
            ] as $nome) {
                $this->checar($rotas->has($nome), "Rota crítica {$nome} registrada.", "Rota crítica ausente: {$nome}.", $nome === 'billing.cancelar' ? 'aviso' : 'erro');
            }
        } catch (Throwable $exception) {
            $this->registrar('erro', 'Rotas críticas', 'Não foi possível carregar as rotas.', $exception->getMessage(), []);
        }
    }

    private function validarArquivosCriticos(): void
    {
        $arquivos = [
            app_path('Http/Controllers/PortalItemControleController.php'),
            app_path('Http/Controllers/PortalClientePublicoController.php'),
            app_path('Http/Controllers/AsaasWebhookController.php'),
            app_path('Http/Controllers/BillingController.php'),
            app_path('Services/AsaasService.php'),
            app_path('Services/GlobalSearchService.php'),
            resource_path('views/portal/item-controle-show.blade.php'),
            resource_path('views/components/global-search.blade.php'),
        ];

        foreach ($arquivos as $arquivo) {
            $this->checar(File::exists($arquivo), 'Arquivo crítico presente: '.str_replace(base_path().DIRECTORY_SEPARATOR, '', $arquivo), 'Arquivo crítico ausente: '.$arquivo, 'erro');
        }
    }

    /**
     * @param  callable(): void  $callback
     */
    private function executarSecao(string $secao, callable $callback): void
    {
        if (! $this->option('somente-erros')) {
            $this->line("\n[{$secao}]");
        }

        try {
            $callback();
        } catch (Throwable $exception) {
            $this->registrar('erro', $secao, 'Falha inesperada durante a seção de diagnóstico.', $exception->getMessage(), [
                'arquivo' => $exception->getFile(),
                'linha' => $exception->getLine(),
            ]);
        }
    }

    /**
     * @param  array<int, string>  $colunas
     */
    private function validarColunas(string $tabela, array $colunas): void
    {
        if (! CachedSchema::hasTable($tabela)) {
            return;
        }

        foreach ($colunas as $coluna) {
            $this->checar(CachedSchema::hasColumn($tabela, $coluna), "Coluna {$tabela}.{$coluna} existe", "Coluna crítica ausente: {$tabela}.{$coluna}", 'erro');
        }
    }

    /**
     * @param  array<int, string>  $colunas
     */
    private function hasColumns(string $tabela, array $colunas): bool
    {
        if (! CachedSchema::hasTable($tabela)) {
            return false;
        }

        foreach ($colunas as $coluna) {
            if (! CachedSchema::hasColumn($tabela, $coluna)) {
                return false;
            }
        }

        return true;
    }

    private function contar(string $tabela, string $mensagem): void
    {
        if (! CachedSchema::hasTable($tabela)) {
            return;
        }

        try {
            $total = DB::table($tabela)->count();
            $this->registrar('ok', 'Contagem', $mensagem.': '.$total, null, ['tabela' => $tabela, 'total' => $total]);
        } catch (Throwable $exception) {
            $this->registrar('aviso', 'Contagem', "Não foi possível contar registros de {$tabela}.", $exception->getMessage(), []);
        }
    }

    private function contarDuplicados(string $tabela, string $coluna, string $mensagem, bool $ignorarVazios): void
    {
        if (! $this->hasColumns($tabela, [$coluna])) {
            return;
        }

        $query = DB::table($tabela)
            ->select($coluna, DB::raw('COUNT(*) as total'))
            ->groupBy($coluna)
            ->havingRaw('COUNT(*) > 1');

        if ($ignorarVazios) {
            $query->whereNotNull($coluna)
                ->whereRaw("TRIM(COALESCE({$coluna}, '')) <> ''");
        }

        $duplicados = $query->limit($this->limite)->get();

        $this->checar($duplicados->isEmpty(), $mensagem.': nenhum duplicado encontrado.', $mensagem.'.', 'aviso', [
            'tabela' => $tabela,
            'coluna' => $coluna,
            'grupos' => $duplicados->count(),
            'amostra' => $duplicados->take(10)->values()->all(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $contexto
     */
    private function checar(bool $condicao, string $mensagemOk, string $mensagemFalha, string $nivelFalha = 'erro', array $contexto = []): void
    {
        if ($condicao) {
            $this->registrar('ok', 'Validação', $mensagemOk, null, $contexto);

            return;
        }

        $this->registrar($nivelFalha, 'Validação', $mensagemFalha, null, $contexto);
    }

    /**
     * @param  array<string, mixed>  $contexto
     */
    private function registrar(string $nivel, string $secao, string $mensagem, ?string $detalhe = null, array $contexto = []): void
    {
        $nivel = strtolower($nivel);

        if ($nivel === 'erro') {
            $this->erros++;
        } elseif ($nivel === 'aviso') {
            $this->avisos++;
        } else {
            $this->oks++;
        }

        $this->achados[] = [
            'nivel' => $nivel,
            'secao' => $secao,
            'mensagem' => $mensagem,
            'detalhe' => $detalhe,
            'contexto' => $contexto,
        ];

        if ($this->option('somente-erros') && $nivel === 'ok') {
            return;
        }

        $linha = $mensagem.($detalhe ? ' | '.$detalhe : '');

        match ($nivel) {
            'erro' => $this->components->error($linha),
            'aviso' => $this->components->warn($linha),
            default => $this->components->info($linha),
        };
    }

    private function registrarRelatorio(float $duracao): void
    {
        $payload = $this->payloadRelatorio($duracao);
        $linha = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($linha === false) {
            $linha = '{"erro":"falha ao serializar relatorio de diagnostico"}';
        }

        try {
            File::append(storage_path('logs/diagnostico-sistemrh.log'), '['.now()->toDateTimeString().'] '.$linha.PHP_EOL);
        } catch (Throwable $exception) {
            Log::warning('Falha ao gravar diagnostico-sistemrh.log', [
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    private function exportarJsonSeSolicitado(float $duracao): void
    {
        $arquivo = trim((string) $this->option('arquivo'));

        if ($arquivo === '') {
            return;
        }

        $payload = json_encode($this->payloadRelatorio($duracao), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            $this->registrar('aviso', 'Exportação JSON', 'Não foi possível serializar o relatório em JSON.', null, []);

            return;
        }

        try {
            $diretorio = dirname($arquivo);
            if ($diretorio !== '.' && ! File::exists($diretorio)) {
                File::makeDirectory($diretorio, 0755, true);
            }

            File::put($arquivo, $payload.PHP_EOL);
            $this->line('Relatório JSON salvo em: '.$arquivo);
        } catch (Throwable $exception) {
            $this->registrar('aviso', 'Exportação JSON', 'Não foi possível salvar o relatório JSON.', $exception->getMessage(), ['arquivo' => $arquivo]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadRelatorio(float $duracao): array
    {
        return [
            'executado_em' => now()->toDateTimeString(),
            'ambiente' => app()->environment(),
            'app_url' => config('app.url'),
            'database_connection' => config('database.default'),
            'limite_amostragem' => $this->limite,
            'duracao_segundos' => $duracao,
            'resumo' => [
                'ok' => $this->oks,
                'avisos' => $this->avisos,
                'erros' => $this->erros,
            ],
            'achados' => $this->achados,
        ];
    }
}
