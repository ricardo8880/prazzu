<?php

namespace App\Http\Controllers;

use App\Models\Configuracao;
use App\Models\Empresa;
use App\Models\User;
use App\Services\AsaasService;
use App\Services\PlanoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class PublicEmpresaCadastroController extends Controller
{
    public function create(): View
    {
        return view('public.cadastro-empresa');
    }

    public function store(Request $request, AsaasService $asaas): RedirectResponse
    {
        $data = $request->validate([
            'razao_social' => ['required', 'string', 'max:255'],
            'nome_fantasia' => ['nullable', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:20', Rule::unique('empresas', 'cnpj')],
            'email' => ['required', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'responsavel_nome' => ['required', 'string', 'max:255'],
            'plano' => ['required', 'string', Rule::in(array_keys(PlanoService::planos()))],
            'admin_nome' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $plano = PlanoService::normalizarPlano($data['plano']);

        try {
            $empresa = DB::transaction(function () use ($data, $plano): Empresa {
                $empresa = Empresa::query()->create([
                    'razao_social' => $data['razao_social'],
                    'nome_fantasia' => $data['nome_fantasia'] ?? null,
                    'cnpj' => $data['cnpj'] ?? null,
                    'email' => $data['email'],
                    'telefone' => $data['telefone'] ?? null,
                    'responsavel_nome' => $data['responsavel_nome'],
                    'status' => 'pendente_pagamento',
                    'plano' => $plano,
                    'limite_usuarios' => PlanoService::limiteUsuarios($plano),
                    'limite_itens' => PlanoService::limiteItens($plano),
                    'limite_interacoes_ia' => PlanoService::limiteInteracoesIa($plano),
                    'ativo' => false,
                ]);

                Configuracao::forEmpresaId($empresa->id);

                User::withoutEvents(function () use ($data, $empresa): void {
                    User::query()->create([
                        'name' => $data['admin_nome'],
                        'email' => $data['admin_email'],
                        'password' => Hash::make($data['admin_password']),
                        'role' => 'admin',
                        'empresa_id' => $empresa->id,
                    ]);
                });

                return $empresa;
            });
        } catch (Throwable $exception) {
            Log::error('Falha ao cadastrar empresa localmente pelo formulário público.', [
                'message' => $exception->getMessage(),
                'email' => $data['email'] ?? null,
                'admin_email' => $data['admin_email'] ?? null,
            ]);

            return back()
                ->withInput($request->except('admin_password', 'admin_password_confirmation'))
                ->withErrors([
                    'cadastro' => 'Não foi possível concluir o cadastro agora. Revise os dados e tente novamente.',
                ]);
        }

        try {
            $assinatura = $asaas->criarAssinaturaParaEmpresa(
                $empresa,
                (string) config('services.asaas.billing_type', 'UNDEFINED')
            );
        } catch (Throwable $exception) {
            $empresa->forceFill([
                'status' => 'erro_pagamento',
                'ativo' => false,
            ])->save();

            Log::channel('asaas')->error('Empresa cadastrada localmente, mas a cobrança no Asaas falhou.', [
                'empresa_id' => $empresa->id,
                'email' => $empresa->email,
                'plano' => $empresa->plano,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput($request->except('admin_password', 'admin_password_confirmation'))
                ->withErrors([
                    'asaas' => 'Seu cadastro foi recebido, mas não foi possível gerar a cobrança automaticamente. Entre em contato com o suporte para reativar a cobrança deste cadastro.',
                ]);
        }

        $pagamento = $assinatura->pagamentos()->latest('id')->first();

        if ($pagamento?->invoice_url) {
            return redirect()->away($pagamento->invoice_url);
        }

        Log::channel('asaas')->warning('Cadastro público concluído sem URL de cobrança retornada pelo Asaas.', [
            'empresa_id' => $empresa->id,
            'assinatura_id' => $assinatura->id,
            'gateway_subscription_id' => $assinatura->gateway_subscription_id,
        ]);

        return redirect()
            ->route('billing.sucesso')
            ->with('success', 'Cadastro realizado com sucesso. Finalize o pagamento para liberar o acesso ao sistema. Caso o link de pagamento não apareça, entre em contato com o suporte.');
    }
}
