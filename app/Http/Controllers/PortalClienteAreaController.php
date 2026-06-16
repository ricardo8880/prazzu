<?php

namespace App\Http\Controllers;

use App\Models\PortalMensagem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortalClienteAreaController extends Controller
{
    public function dashboard(): View
    {
        Log::info('PORTAL_CLIENTE_DEBUG dashboard acessado', [
            'cliente_id' => optional(Auth::guard('portal_cliente')->user())->id,
            'url' => request()->fullUrl(),
            'user_agent' => request()->userAgent(),
        ]);

        return $this->renderArea();
    }

    public function atendimento(int|string $atendimento): View
    {
        Log::info('PORTAL_CLIENTE_DEBUG atendimento acessado', [
            'cliente_id' => optional(Auth::guard('portal_cliente')->user())->id,
            'atendimento_id' => (int) $atendimento,
            'url' => request()->fullUrl(),
            'user_agent' => request()->userAgent(),
        ]);

        return $this->renderArea((int) $atendimento);
    }



    public function mensagem(Request $request, int|string $atendimento): RedirectResponse|JsonResponse
    {
        Log::info('PORTAL_CLIENTE_DEBUG mensagem POST recebido', [
            'cliente_id' => optional(Auth::guard('portal_cliente')->user())->id,
            'atendimento_id' => (int) $atendimento,
            'mensagem_len' => strlen((string) $request->input('mensagem', '')),
            'tem_anexo' => $request->hasFile('anexos') || $request->hasFile('anexo'),
            'headers' => [
                'referer' => $request->headers->get('referer'),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        $cliente = Auth::guard('portal_cliente')->user();
        abort_if(! $cliente || ! $cliente->empresa_id, 403, 'Cliente sem empresa vinculada.');

        abort_if(! Schema::hasTable('atendimentos') || ! Schema::hasTable('atendimento_interacoes') || ! Schema::hasTable('portal_mensagens'), 503, 'Estrutura de atendimentos/portal indisponível.');

        $empresaId = (int) $cliente->empresa_id;
        $atendimentoId = (int) $atendimento;
        $atendimentoAtual = $this->atendimentoDaEmpresa($empresaId, $atendimentoId);

        abort_if(! $atendimentoAtual, 404, 'Atendimento não encontrado para este cliente.');

        if (! empty($atendimentoAtual['is_finalizado'])) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'message' => 'Este atendimento já foi finalizado e não aceita novas mensagens.'], 422);
            }

            return redirect()
                ->route('portal.cliente.atendimentos.show', ['atendimento' => $atendimentoId])
                ->withErrors(['mensagem' => 'Este atendimento já foi finalizado e não aceita novas mensagens.']);
        }

        try {
            $payload = $request->validate([
                'mensagem' => ['nullable', 'string', 'max:6000', 'required_without:anexos.0'],
                'anexos' => ['nullable', 'array', 'max:5'],
                'anexos.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt,csv'],
                'website' => ['nullable', 'prohibited'],
            ], [
                'mensagem.required_without' => 'Digite uma mensagem ou selecione um arquivo antes de enviar.',
                'mensagem.max' => 'A mensagem pode ter no máximo 6000 caracteres.',
                'anexos.array' => 'A lista de anexos enviada é inválida.',
                'anexos.max' => 'Envie no máximo 5 arquivos por mensagem.',
                'anexos.*.file' => 'Um dos anexos enviados é inválido.',
                'anexos.*.max' => 'Cada anexo pode ter no máximo 10 MB.',
                'anexos.*.mimes' => 'Envie apenas imagem, PDF, Word, Excel, TXT ou CSV.',
                'website.prohibited' => 'Requisição inválida. Atualize a página e tente novamente.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            Log::warning('PORTAL_CLIENTE_DEBUG mensagem falhou validacao', [
                'cliente_id' => optional(Auth::guard('portal_cliente')->user())->id,
                'atendimento_id' => (int) $atendimento,
                'errors' => $exception->errors(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => false,
                    'message' => collect($exception->errors())->flatten()->first() ?: 'Revise a mensagem enviada.',
                    'errors' => $exception->errors(),
                ], 422);
            }

            throw $exception;
        }

        Log::info('PORTAL_CLIENTE_DEBUG mensagem validada', [
            'cliente_id' => optional(Auth::guard('portal_cliente')->user())->id,
            'atendimento_id' => $atendimentoId,
            'payload_keys' => array_keys($payload),
        ]);

        $mensagem = trim((string) ($payload['mensagem'] ?? ''));
        $arquivosRecebidos = $request->file('anexos', []);

        if ($arquivosRecebidos instanceof \Illuminate\Http\UploadedFile) {
            $arquivosRecebidos = [$arquivosRecebidos];
        }

        $arquivos = collect(is_array($arquivosRecebidos) ? $arquivosRecebidos : [])
            ->filter(fn ($arquivo) => $arquivo instanceof \Illuminate\Http\UploadedFile)
            ->values();

        if ($mensagem === '' && $arquivos->isEmpty()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'message' => 'Digite uma mensagem ou selecione um arquivo antes de enviar.'], 422);
            }

            return redirect()
                ->route('portal.cliente.atendimentos.show', ['atendimento' => $atendimentoId])
                ->withErrors(['mensagem' => 'Digite uma mensagem ou selecione um arquivo antes de enviar.'])
                ->withInput();
        }

        $anexos = [];
        $arquivosGravados = [];

        foreach ($arquivos as $arquivo) {
            $nomeOriginal = $arquivo->getClientOriginalName();
            $extensao = strtolower((string) $arquivo->getClientOriginalExtension());
            $nomeSeguro = Str::uuid()->toString() . ($extensao !== '' ? '.' . $extensao : '');
            $pasta = 'portal_cliente_anexos/' . $atendimentoId;
            $caminho = $arquivo->storeAs($pasta, $nomeSeguro, 'public');
            $arquivosGravados[] = $caminho;

            $anexos[] = [
                'nome_original' => $nomeOriginal,
                'nome_arquivo' => $nomeSeguro,
                'caminho' => $caminho,
                'mime' => (string) ($arquivo->getMimeType() ?: 'application/octet-stream'),
                'tamanho' => (int) $arquivo->getSize(),
                'extensao' => $extensao,
            ];
        }

        Log::info('PORTAL_CLIENTE_DEBUG mensagem antes transaction', [
            'cliente_id' => (int) $cliente->id,
            'atendimento_id' => $atendimentoId,
            'mensagem_vazia' => $mensagem === '',
            'total_anexos' => count($anexos),
        ]);

        $portalMensagem = null;
        $interacaoId = null;

        try {
            [$portalMensagem, $interacaoId] = DB::transaction(function () use ($atendimentoId, $atendimentoAtual, $cliente, $empresaId, $mensagem, $anexos): array {
                $agora = now();

                $portalMensagemPayload = [
                    'empresa_id' => $empresaId,
                    'user_id' => null,
                    'nome' => (string) ($cliente->nome ?? 'Cliente'),
                    'email' => (string) ($cliente->email ?? ''),
                    'mensagem' => $mensagem !== '' ? $mensagem : $this->textoAnexosParaMensagemPortal($anexos),
                    'origem' => 'cliente',
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ];

                if (Schema::hasColumn('portal_mensagens', 'item_controle_id') && ! empty($atendimentoAtual['item_controle_id'])) {
                    $portalMensagemPayload['item_controle_id'] = (int) $atendimentoAtual['item_controle_id'];
                }

                if (Schema::hasColumn('portal_mensagens', 'conversa_status')) {
                    $portalMensagemPayload['conversa_status'] = 'aberta';
                }

                /** @var PortalMensagem $mensagemPortal */
                $mensagemPortal = PortalMensagem::query()->create($portalMensagemPayload);

                $novoInteracaoId = DB::table('atendimento_interacoes')->insertGetId([
                    'atendimento_id' => $atendimentoId,
                    'user_id' => null,
                    'origem' => 'cliente',
                    'tipo' => count($anexos) > 0 ? 'anexo' : 'resposta',
                    'mensagem' => $mensagem,
                    'metadata' => json_encode([
                        'portal_cliente_user_id' => (int) $cliente->id,
                        'portal_cliente_nome' => (string) ($cliente->nome ?? ''),
                        'portal_cliente_email' => (string) ($cliente->email ?? ''),
                        'origem' => 'portal_cliente_logado',
                        'portal_mensagem_id' => (int) $mensagemPortal->id,
                        'source' => 'portal_mensagens',
                        'anexos' => $anexos,
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ]);

                $atendimentoUpdate = [
                    'status' => 'em_andamento',
                    'updated_at' => $agora,
                ];

                if (Schema::hasColumn('atendimentos', 'portal_mensagem_id')) {
                    $atendimentoUpdate['portal_mensagem_id'] = (int) $mensagemPortal->id;
                }

                DB::table('atendimentos')
                    ->where('id', $atendimentoId)
                    ->update($atendimentoUpdate);

                return [$mensagemPortal, (int) $novoInteracaoId];
            });
        } catch (\Throwable $exception) {
            foreach ($arquivosGravados as $caminhoGravado) {
                Storage::disk('public')->delete($caminhoGravado);
            }

            Log::error('PORTAL_CLIENTE_DEBUG mensagem falhou ao gravar', [
                'cliente_id' => (int) $cliente->id,
                'atendimento_id' => $atendimentoId,
                'erro' => $exception->getMessage(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'message' => 'Não foi possível enviar sua mensagem agora. Tente novamente em alguns instantes.'], 500);
            }

            return redirect()
                ->route('portal.cliente.atendimentos.show', ['atendimento' => $atendimentoId])
                ->withErrors(['mensagem' => 'Não foi possível enviar sua mensagem agora. Tente novamente em alguns instantes.'])
                ->withInput();
        }

        Log::info('PORTAL_CLIENTE_DEBUG mensagem gravada com sucesso', [
            'cliente_id' => (int) $cliente->id,
            'atendimento_id' => $atendimentoId,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => count($anexos) > 0 ? 'Mensagem e arquivo(s) enviados com sucesso.' : 'Mensagem enviada com sucesso.',
                'messages' => $this->mensagensTempoReal($atendimentoId),
                'chat_message' => $this->ultimaInteracaoSocketPayload($atendimentoId, $empresaId, $portalMensagem, $interacaoId),
            ]);
        }

        return redirect()
            ->route('portal.cliente.atendimentos.show', ['atendimento' => $atendimentoId])
            ->with('success', count($anexos) > 0 ? 'Mensagem e arquivo(s) enviados com sucesso.' : 'Mensagem enviada com sucesso.');
    }

    public function debugLog(Request $request): \Illuminate\Http\JsonResponse
    {
        $cliente = Auth::guard('portal_cliente')->user();

        Log::info('PORTAL_CLIENTE_FRONT_DEBUG', [
            'cliente_id' => $cliente?->id,
            'empresa_id' => $cliente?->empresa_id,
            'evento' => (string) $request->input('evento', 'sem_evento'),
            'detalhes' => $request->input('detalhes', []),
            'url' => (string) $request->input('url', ''),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function anexo(Request $request, int|string $atendimento, int|string $interacao)
    {
        $cliente = Auth::guard('portal_cliente')->user();
        abort_if(! $cliente || ! $cliente->empresa_id, 403, 'Cliente sem empresa vinculada.');

        abort_if(! Schema::hasTable('atendimentos') || ! Schema::hasTable('atendimento_interacoes'), 503, 'Estrutura de atendimentos indisponível.');

        $empresaId = (int) $cliente->empresa_id;
        $atendimentoId = (int) $atendimento;
        $interacaoId = (int) $interacao;

        $atendimentoAtual = $this->atendimentoDaEmpresa($empresaId, $atendimentoId);
        abort_if(! $atendimentoAtual, 404, 'Atendimento não encontrado para este cliente.');

        $row = DB::table('atendimento_interacoes')
            ->where('id', $interacaoId)
            ->where('atendimento_id', $atendimentoId)
            ->first(['id', 'metadata']);

        abort_if(! $row, 404, 'Anexo não encontrado.');

        $metadata = $this->metadataArray($row->metadata ?? null);
        $anexo = $metadata['anexos'][0] ?? null;

        abort_if(! is_array($anexo), 404, 'Anexo não encontrado.');

        $caminho = (string) ($anexo['caminho'] ?? '');
        abort_if($caminho === '' || ! Str::startsWith($caminho, 'portal_cliente_anexos/'), 404, 'Anexo inválido.');
        abort_if(! Storage::disk('public')->exists($caminho), 404, 'Arquivo não encontrado no armazenamento.');

        $nome = (string) ($anexo['nome_original'] ?? basename($caminho));
        $mime = (string) ($anexo['mime'] ?? Storage::disk('public')->mimeType($caminho) ?: 'application/octet-stream');
        $path = Storage::disk('public')->path($caminho);

        if ($request->boolean('preview') && Str::startsWith($mime, 'image/')) {
            return response()->file($path, ['Content-Type' => $mime]);
        }

        return response()->download($path, $nome, ['Content-Type' => $mime]);
    }

    public function novo(): View
    {
        return $this->renderArea(null, true);
    }

    public function store(Request $request): RedirectResponse
    {
        $cliente = Auth::guard('portal_cliente')->user();
        abort_if(! $cliente || ! $cliente->empresa_id, 403, 'Cliente sem empresa vinculada.');

        abort_if(! Schema::hasTable('atendimentos') || ! Schema::hasTable('atendimento_interacoes'), 503, 'Estrutura de atendimentos indisponível.');

        $payload = $request->validate([
            'titulo' => ['bail', 'required', 'string', 'min:5', 'max:180'],
            'descricao' => ['bail', 'required', 'string', 'min:10', 'max:6000'],
            'prioridade' => ['bail', 'required', 'string', 'in:baixa,media,alta,urgente'],
            'categoria' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'prohibited'],
        ], [
            'titulo.required' => 'Informe o assunto do atendimento.',
            'titulo.min' => 'O assunto precisa ter pelo menos 5 caracteres.',
            'descricao.required' => 'Descreva o que você precisa para abrir o atendimento.',
            'descricao.min' => 'A descrição precisa ter pelo menos 10 caracteres.',
            'prioridade.in' => 'Selecione uma prioridade válida.',
            'website.prohibited' => 'Requisição inválida. Atualize a página e tente novamente.',
        ]);

        $empresaId = (int) $cliente->empresa_id;
        $titulo = trim((string) $payload['titulo']);
        $descricao = trim((string) $payload['descricao']);
        $prioridade = $this->prioridadeValida((string) $payload['prioridade']);
        $categoria = trim((string) ($payload['categoria'] ?? ''));
        $agora = now();

        $atendimentoId = DB::transaction(function () use ($empresaId, $cliente, $titulo, $descricao, $prioridade, $categoria, $agora): int {
            $atendimentoId = (int) DB::table('atendimentos')->insertGetId([
                'empresa_id' => $empresaId,
                'crm_cliente_id' => $this->crmClienteId($empresaId),
                'portal_solicitacao_id' => null,
                'portal_mensagem_id' => null,
                'item_controle_id' => null,
                'responsavel_id' => null,
                'criado_por' => null,
                'titulo' => Str::limit($titulo, 180, ''),
                'descricao' => $descricao,
                'status' => 'aberto',
                'prioridade' => $prioridade,
                'origem' => 'portal',
                'canal' => 'portal',
                'sla_horas' => $this->slaHoras($prioridade),
                'sla_limite_em' => $agora->copy()->addHours($this->slaHoras($prioridade))->format('Y-m-d H:i:s'),
                'primeira_resposta_em' => null,
                'resolvido_em' => null,
                'fechado_em' => null,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);

            $mensagemAbertura = trim(sprintf(
                "Atendimento aberto pelo cliente no portal.\n\nCliente: %s <%s>\nPrioridade: %s%s\n\n%s",
                (string) ($cliente->nome ?? 'Cliente'),
                (string) ($cliente->email ?? ''),
                $this->prioridadeLabel($prioridade),
                $categoria !== '' ? "\nCategoria: {$categoria}" : '',
                $descricao
            ));

            DB::table('atendimento_interacoes')->insert([
                'atendimento_id' => $atendimentoId,
                'user_id' => null,
                'origem' => 'cliente',
                'tipo' => 'abertura',
                'mensagem' => $mensagemAbertura,
                'metadata' => json_encode([
                    'portal_cliente_user_id' => (int) $cliente->id,
                    'portal_cliente_nome' => (string) ($cliente->nome ?? ''),
                    'portal_cliente_email' => (string) ($cliente->email ?? ''),
                    'categoria' => $categoria !== '' ? $categoria : null,
                    'status_inicial' => 'aberto',
                    'origem' => 'portal_cliente_logado',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);

            return $atendimentoId;
        });

        return redirect()
            ->route('portal.cliente.atendimentos.show', ['atendimento' => $atendimentoId])
            ->with('success', 'Atendimento aberto com sucesso. Protocolo: #ATD-' . str_pad((string) $atendimentoId, 6, '0', STR_PAD_LEFT));
    }

    private function renderArea(?int $atendimentoId = null, bool $abrirFormulario = false): View
    {
        Log::info('PORTAL_CLIENTE_DEBUG renderArea inicio', [
            'atendimento_id_param' => $atendimentoId,
            'abrir_formulario' => $abrirFormulario,
            'cliente_id' => optional(Auth::guard('portal_cliente')->user())->id,
        ]);
        $cliente = Auth::guard('portal_cliente')->user();
        abort_if(! $cliente || ! $cliente->empresa_id, 403, 'Cliente sem empresa vinculada.');

        $empresaId = (int) $cliente->empresa_id;
        $empresa = $this->empresa($empresaId);
        $atendimentos = $this->atendimentos($empresaId);

        $atendimentoAtual = null;
        if ($abrirFormulario) {
            $atendimentoAtual = null;
        } elseif ($atendimentoId) {
            $atendimentoAtual = $this->atendimentoDaEmpresa($empresaId, $atendimentoId);
            abort_if(! $atendimentoAtual, 404, 'Atendimento não encontrado para este cliente.');
        } elseif ($atendimentos !== []) {
            $atendimentoAtual = $atendimentos[0];
        }

        Log::info('PORTAL_CLIENTE_DEBUG renderArea dados montados', [
            'cliente_id' => (int) $cliente->id,
            'empresa_id' => $empresaId,
            'atendimentos_total' => count($atendimentos),
            'atendimento_atual_id' => $atendimentoAtual['id'] ?? null,
            'interacoes_total' => $atendimentoAtual ? count($this->interacoes((int) $atendimentoAtual['id'])) : 0,
            'estrutura_disponivel' => Schema::hasTable('atendimentos') && Schema::hasTable('atendimento_interacoes'),
        ]);

        return view('portal.cliente.dashboard', [
            'cliente' => $cliente,
            'empresa' => $empresa,
            'atendimentos' => $atendimentos,
            'atendimentoAtual' => $atendimentoAtual,
            'interacoes' => $atendimentoAtual ? $this->interacoes((int) $atendimentoAtual['id']) : [],
            'resumo' => $this->resumo($atendimentos),
            'estruturaDisponivel' => Schema::hasTable('atendimentos') && Schema::hasTable('atendimento_interacoes'),
            'abrirFormulario' => $abrirFormulario,
            'prioridades' => $this->prioridadesFormulario(),
            'socketIoConfig' => $this->socketIoConfigClienteLogado($empresaId, (int) ($atendimentoAtual['id'] ?? 0)),
        ]);
    }

    private function empresa(int $empresaId): ?array
    {
        if (! Schema::hasTable('empresas')) {
            return null;
        }

        $empresa = DB::table('empresas')->where('id', $empresaId)->first();

        return $empresa ? (array) $empresa : null;
    }

    private function atendimentos(int $empresaId): array
    {
        if (! Schema::hasTable('atendimentos')) {
            return [];
        }

        $query = DB::table('atendimentos as a')
            ->where('a.empresa_id', $empresaId)
            ->select([
                'a.id',
                'a.titulo',
                'a.descricao',
                'a.status',
                'a.prioridade',
                'a.origem',
                'a.canal',
                'a.responsavel_id',
                'a.portal_solicitacao_id',
                'a.portal_mensagem_id',
                'a.item_controle_id',
                'a.created_at',
                'a.updated_at',
                'a.sla_limite_em',
                'a.primeira_resposta_em',
                'a.resolvido_em',
                'a.fechado_em',
            ]);

        if (Schema::hasTable('users')) {
            $query->leftJoin('users as u', 'u.id', '=', 'a.responsavel_id')
                ->addSelect('u.name as responsavel_nome', 'u.email as responsavel_email');
        }

        $rows = $query
            ->orderByRaw("CASE WHEN a.status IN ('resolvido', 'fechado', 'cancelado') THEN 1 ELSE 0 END ASC")
            ->orderByDesc('a.updated_at')
            ->orderByDesc('a.id')
            ->limit(80)
            ->get();

        $interacoesPorAtendimento = $this->contagemInteracoes($rows->pluck('id')->map(fn ($id) => (int) $id)->all());

        return $rows
            ->map(fn ($row) => $this->formatarAtendimento((array) $row, $interacoesPorAtendimento[(int) $row->id] ?? 0))
            ->all();
    }

    private function atendimentoDaEmpresa(int $empresaId, int $atendimentoId): ?array
    {
        if (! Schema::hasTable('atendimentos')) {
            return null;
        }

        $query = DB::table('atendimentos as a')
            ->where('a.empresa_id', $empresaId)
            ->where('a.id', $atendimentoId)
            ->select([
                'a.id',
                'a.titulo',
                'a.descricao',
                'a.status',
                'a.prioridade',
                'a.origem',
                'a.canal',
                'a.responsavel_id',
                'a.portal_solicitacao_id',
                'a.portal_mensagem_id',
                'a.item_controle_id',
                'a.created_at',
                'a.updated_at',
                'a.sla_limite_em',
                'a.primeira_resposta_em',
                'a.resolvido_em',
                'a.fechado_em',
            ]);

        if (Schema::hasTable('users')) {
            $query->leftJoin('users as u', 'u.id', '=', 'a.responsavel_id')
                ->addSelect('u.name as responsavel_nome', 'u.email as responsavel_email');
        }

        $row = $query->first();

        return $row ? $this->formatarAtendimento((array) $row, $this->contagemInteracoes([$atendimentoId])[$atendimentoId] ?? 0) : null;
    }

    private function interacoes(int $atendimentoId): array
    {
        if (! Schema::hasTable('atendimento_interacoes')) {
            return [];
        }

        $query = DB::table('atendimento_interacoes as i')
            ->where('i.atendimento_id', $atendimentoId)
            ->select(['i.id', 'i.atendimento_id', 'i.user_id', 'i.origem', 'i.tipo', 'i.mensagem', 'i.metadata', 'i.created_at', 'i.updated_at']);

        if (Schema::hasTable('users')) {
            $query->leftJoin('users as u', 'u.id', '=', 'i.user_id')
                ->addSelect('u.name as usuario_nome');
        }

        return $query
            ->orderBy('i.created_at')
            ->orderBy('i.id')
            ->limit(120)
            ->get()
            ->map(function ($row) {
                $item = (array) $row;
                $item['created_at_label'] = $this->dataHora($item['created_at'] ?? null);
                $item['origem_label'] = $this->origemLabel($item['origem'] ?? null);
                $item['tipo_label'] = $this->tipoInteracaoLabel($item['tipo'] ?? null);
                $item['is_cliente'] = in_array((string) ($item['origem'] ?? ''), ['cliente', 'portal', 'publico'], true) || ! empty($item['metadata']) && str_contains((string) $item['metadata'], 'portal_mensagem_id');
                $item['anexos'] = $this->anexosDaInteracao($item);

                return $item;
            })
            ->all();
    }

    private function mensagensTempoReal(int $atendimentoId): array
    {
        return collect($this->interacoes($atendimentoId))
            ->map(function (array $interacao): array {
                return [
                    'id' => (int) ($interacao['id'] ?? 0),
                    'is_cliente' => (bool) ($interacao['is_cliente'] ?? false),
                    'author' => (bool) ($interacao['is_cliente'] ?? false)
                        ? 'Você'
                        : (string) ($interacao['usuario_nome'] ?? 'Equipe de suporte'),
                    'text' => (string) ($interacao['mensagem'] ?? ''),
                    'time' => (string) ($interacao['created_at_label'] ?? ''),
                    'attachments' => collect($interacao['anexos'] ?? [])->map(function (array $anexo): array {
                        return [
                            'name' => (string) ($anexo['nome_original'] ?? 'arquivo'),
                            'ext' => strtoupper((string) ($anexo['extensao'] ?? 'ARQ')),
                            'size' => (string) ($anexo['tamanho_label'] ?? ''),
                            'mime' => (string) ($anexo['mime'] ?? ''),
                            'url' => (string) ($anexo['download_url'] ?? '#'),
                            'preview_url' => (string) ($anexo['preview_url'] ?? ''),
                            'is_image' => (bool) ($anexo['is_imagem'] ?? false),
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Configuração usada pelo cliente logado para autenticar no Socket.IO.
     * Mantém o mesmo padrão HMAC usado pelo portal público e pelo painel da equipe.
     *
     * @return array<string, mixed>
     */
    private function socketIoConfigClienteLogado(int $empresaId, int $atendimentoId = 0): array
    {
        $cliente = Auth::guard('portal_cliente')->user();
        $actor = 'cliente';
        $token = 'portal_cliente_logado:' . (int) ($cliente?->id ?? 0);
        $secret = (string) config('app.key');
        $room = $atendimentoId > 0
            ? 'empresa:' . $empresaId . ':atendimento:' . $atendimentoId
            : 'empresa:' . $empresaId . ':portal-cliente:' . (int) ($cliente?->id ?? 0);

        return [
            'enabled' => $empresaId > 0 && $token !== 'portal_cliente_logado:0',
            'url' => rtrim((string) env('VITE_SOCKET_IO_URL', env('SOCKET_IO_URL', 'http://127.0.0.1:3001')), '/'),
            'empresaId' => $empresaId,
            'atendimentoId' => $atendimentoId,
            'room' => $room,
            'roomScope' => $atendimentoId > 0 ? 'atendimento' : 'portal-cliente',
            'actor' => $actor,
            'token' => $token,
            'signature' => hash_hmac('sha256', $empresaId . '|' . $actor . '|' . $token . '|' . $room, $secret),
            'syncUrl' => $atendimentoId > 0
                ? route('portal.cliente.atendimentos.chat.estado', ['atendimento' => $atendimentoId])
                : null,
        ];
    }

    /**
     * Última interação no formato aceito pelo listener Socket.IO do portal logado.
     *
     * @return array<string, mixed>|null
     */
    private function ultimaInteracaoSocketPayload(int $atendimentoId, int $empresaId, ?PortalMensagem $portalMensagem = null, ?int $interacaoId = null): ?array
    {
        $atendimento = $this->atendimentoDaEmpresa($empresaId, $atendimentoId);

        if (! $atendimento || ! Schema::hasTable('atendimento_interacoes')) {
            return null;
        }

        $query = DB::table('atendimento_interacoes')
            ->where('atendimento_id', $atendimentoId);

        if ($interacaoId) {
            $query->where('id', $interacaoId);
        }

        $interacao = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first(['id', 'atendimento_id', 'user_id', 'origem', 'tipo', 'mensagem', 'metadata', 'created_at']);

        if (! $interacao) {
            return null;
        }

        $item = (array) $interacao;
        $item['created_at_label'] = $this->dataHora($item['created_at'] ?? null);
        $item['is_cliente'] = in_array((string) ($item['origem'] ?? ''), ['cliente', 'portal', 'publico'], true)
            || (! empty($item['metadata']) && str_contains((string) $item['metadata'], 'portal_mensagem_id'));
        $item['anexos'] = $this->anexosDaInteracao($item);

        $metadata = $this->metadataArray($item['metadata'] ?? null);
        $portalMensagemId = $portalMensagem instanceof PortalMensagem
            ? (int) $portalMensagem->id
            : (int) ($metadata['portal_mensagem_id'] ?? 0);

        $messageId = $portalMensagemId > 0 ? $portalMensagemId : (int) ($item['id'] ?? 0);
        $room = 'empresa:' . $empresaId . ':atendimento:' . $atendimentoId;
        $actor = $item['is_cliente'] ? 'cliente' : 'suporte';

        return [
            'id' => $messageId,
            'message_id' => $messageId,
            'interaction_id' => (int) ($item['id'] ?? 0),
            'source' => $portalMensagemId > 0 ? 'portal_mensagens' : 'atendimento_interacoes',
            'scope' => 'portal_cliente_logado',
            'empresa_id' => $empresaId,
            'atendimento_id' => $atendimentoId,
            'room' => $room,
            'room_scope' => 'atendimento',
            'actor' => $actor,
            'server_signature' => $this->socketMessageSignature($empresaId, $room, $actor, $messageId),
            'origem' => $item['is_cliente'] ? 'cliente' : 'suporte',
            'nome' => $item['is_cliente'] ? 'Você' : 'Equipe de suporte',
            'tipo' => (string) ($item['tipo'] ?? 'resposta'),
            'mensagem' => (string) ($item['mensagem'] ?? ''),
            'created_at_label' => (string) ($item['created_at_label'] ?? ''),
            'attachments' => collect($item['anexos'] ?? [])->map(function (array $anexo): array {
                return [
                    'name' => (string) ($anexo['nome_original'] ?? 'arquivo'),
                    'ext' => strtoupper((string) ($anexo['extensao'] ?? 'ARQ')),
                    'size' => (string) ($anexo['tamanho_label'] ?? ''),
                    'mime' => (string) ($anexo['mime'] ?? ''),
                    'url' => (string) ($anexo['download_url'] ?? '#'),
                    'preview_url' => (string) ($anexo['preview_url'] ?? ''),
                    'is_image' => (bool) ($anexo['is_imagem'] ?? false),
                ];
            })->values()->all(),
        ];
    }


    private function socketMessageSignature(int $empresaId, string $room, string $actor, int $messageId): string
    {
        return hash_hmac('sha256', $empresaId . '|' . $room . '|' . $actor . '|' . $messageId, (string) config('app.key'));
    }

    private function textoAnexosParaMensagemPortal(array $anexos): string
    {
        if ($anexos === []) {
            return '';
        }

        $nomes = collect($anexos)
            ->map(fn (array $anexo): string => trim((string) ($anexo['nome_original'] ?? $anexo['nome_arquivo'] ?? 'arquivo')))
            ->filter()
            ->values()
            ->all();

        return 'Anexo(s) enviado(s): ' . implode(', ', $nomes);
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
        } catch (\Throwable) {
            return [];
        }
    }

    private function anexosDaInteracao(array $item): array
    {
        $metadata = $this->metadataArray($item['metadata'] ?? null);
        $anexos = $metadata['anexos'] ?? [];

        if (! is_array($anexos) || $anexos === []) {
            return [];
        }

        $atendimentoId = (int) ($item['atendimento_id'] ?? 0);
        $interacaoId = (int) ($item['id'] ?? 0);

        return collect($anexos)
            ->filter(fn ($anexo) => is_array($anexo) && ! empty($anexo['caminho']))
            ->map(function (array $anexo) use ($atendimentoId, $interacaoId): array {
                $mime = (string) ($anexo['mime'] ?? 'application/octet-stream');
                $tamanho = (int) ($anexo['tamanho'] ?? 0);

                return $anexo + [
                    'nome_original' => (string) ($anexo['nome_original'] ?? 'arquivo'),
                    'mime' => $mime,
                    'tamanho_label' => $this->tamanhoArquivo($tamanho),
                    'is_imagem' => Str::startsWith($mime, 'image/'),
                    'download_url' => route('portal.cliente.atendimentos.anexo', [
                        'atendimento' => $atendimentoId,
                        'interacao' => $interacaoId,
                    ]),
                    'preview_url' => route('portal.cliente.atendimentos.anexo', [
                        'atendimento' => $atendimentoId,
                        'interacao' => $interacaoId,
                        'preview' => 1,
                    ]),
                ];
            })
            ->values()
            ->all();
    }

    private function tamanhoArquivo(int $bytes): string
    {
        if ($bytes <= 0) {
            return 'Tamanho indisponível';
        }

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1, ',', '.') . ' KB';
        }

        return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
    }

    private function contagemInteracoes(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if ($ids === [] || ! Schema::hasTable('atendimento_interacoes')) {
            return [];
        }

        return DB::table('atendimento_interacoes')
            ->whereIn('atendimento_id', $ids)
            ->select('atendimento_id', DB::raw('COUNT(*) as total'))
            ->groupBy('atendimento_id')
            ->pluck('total', 'atendimento_id')
            ->mapWithKeys(fn ($total, $id) => [(int) $id => (int) $total])
            ->all();
    }

    private function formatarAtendimento(array $row, int $interacoes): array
    {
        $status = (string) ($row['status'] ?? 'aberto');
        $prioridade = (string) ($row['prioridade'] ?? 'media');
        $id = (int) ($row['id'] ?? 0);

        return $row + [
            'id' => $id,
            'protocolo' => '#ATD-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT),
            'titulo' => trim((string) ($row['titulo'] ?? 'Atendimento sem título')) ?: 'Atendimento sem título',
            'descricao' => trim((string) ($row['descricao'] ?? '')),
            'status_label' => $this->statusLabel($status),
            'status_badge' => $this->statusClasse($status),
            'prioridade_label' => $this->prioridadeLabel($prioridade),
            'prioridade_badge' => $this->prioridadeClasse($prioridade),
            'created_at_label' => $this->dataHora($row['created_at'] ?? null),
            'updated_at_label' => $this->dataHora($row['updated_at'] ?? null),
            'sla_limite_label' => $this->dataHora($row['sla_limite_em'] ?? null),
            'responsavel_nome' => $row['responsavel_nome'] ?? 'Equipe de suporte',
            'interacoes_total' => $interacoes,
            'is_finalizado' => in_array($status, ['resolvido', 'fechado', 'cancelado'], true),
            'url' => route('portal.cliente.atendimentos.show', ['atendimento' => $id]),
        ];
    }

    private function resumo(array $atendimentos): array
    {
        $abertos = 0;
        $andamento = 0;
        $finalizados = 0;
        $aguardando = 0;

        foreach ($atendimentos as $atendimento) {
            $status = (string) ($atendimento['status'] ?? '');
            if (in_array($status, ['resolvido', 'fechado', 'cancelado'], true)) {
                $finalizados++;
            } elseif (in_array($status, ['em_andamento', 'em_atendimento'], true)) {
                $andamento++;
            } elseif (in_array($status, ['aguardando_cliente', 'aguardando_suporte'], true)) {
                $aguardando++;
            } else {
                $abertos++;
            }
        }

        return [
            'total' => count($atendimentos),
            'abertos' => $abertos,
            'andamento' => $andamento,
            'aguardando' => $aguardando,
            'finalizados' => $finalizados,
        ];
    }

    private function dataHora(mixed $value): string
    {
        if (empty($value)) {
            return '—';
        }

        try {
            return Carbon::parse($value)->format('d/m/Y \à\s H:i');
        } catch (\Throwable) {
            return '—';
        }
    }

    private function prioridadeValida(string $prioridade): string
    {
        return array_key_exists($prioridade, $this->prioridadesFormulario()) ? $prioridade : 'media';
    }

    private function prioridadesFormulario(): array
    {
        return [
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            'urgente' => 'Urgente',
        ];
    }

    private function slaHoras(string $prioridade): int
    {
        return match ($prioridade) {
            'urgente' => 4,
            'alta' => 12,
            'baixa' => 72,
            default => 24,
        };
    }

    private function crmClienteId(int $empresaId): ?int
    {
        if (! Schema::hasTable('crm_clientes')) {
            return null;
        }

        $id = DB::table('crm_clientes')->where('empresa_id', $empresaId)->value('id');

        return $id ? (int) $id : null;
    }

    private function statusLabel(?string $status): string
    {
        return [
            'aberto' => 'Aberto',
            'novo' => 'Novo',
            'em_andamento' => 'Em andamento',
            'em_atendimento' => 'Em atendimento',
            'aguardando_cliente' => 'Aguardando você',
            'aguardando_suporte' => 'Aguardando suporte',
            'resolvido' => 'Resolvido',
            'fechado' => 'Finalizado',
            'cancelado' => 'Cancelado',
        ][$status ?? ''] ?? ucfirst(str_replace('_', ' ', (string) $status));
    }

    private function statusClasse(?string $status): string
    {
        return match ($status) {
            'em_andamento', 'em_atendimento' => 'is-info',
            'aguardando_cliente' => 'is-warning',
            'aguardando_suporte', 'aberto', 'novo' => 'is-success',
            'resolvido', 'fechado' => 'is-done',
            'cancelado' => 'is-danger',
            default => 'is-neutral',
        };
    }

    private function prioridadeLabel(?string $prioridade): string
    {
        return [
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            'urgente' => 'Urgente',
        ][$prioridade ?? ''] ?? ucfirst((string) $prioridade ?: 'Média');
    }

    private function prioridadeClasse(?string $prioridade): string
    {
        return match ($prioridade) {
            'urgente' => 'is-danger',
            'alta' => 'is-warning',
            'baixa' => 'is-neutral',
            default => 'is-info',
        };
    }

    private function origemLabel(?string $origem): string
    {
        return [
            'portal' => 'Cliente',
            'cliente' => 'Cliente',
            'publico' => 'Cliente',
            'admin' => 'Suporte',
            'suporte' => 'Suporte',
            'interno' => 'Suporte',
        ][$origem ?? ''] ?? ucfirst((string) $origem ?: 'Sistema');
    }

    private function tipoInteracaoLabel(?string $tipo): string
    {
        return [
            'abertura' => 'Abertura',
            'resposta' => 'Resposta',
            'comentario' => 'Comentário',
            'status' => 'Atualização',
        ][$tipo ?? ''] ?? ucfirst((string) $tipo ?: 'Interação');
    }
}
