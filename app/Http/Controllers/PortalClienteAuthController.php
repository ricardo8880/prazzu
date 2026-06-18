<?php

namespace App\Http\Controllers;

use App\Models\ClientePortalUser;
use App\Models\Empresa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PortalClienteAuthController extends Controller
{
    public function loginForm(): View
    {
        return view('portal.cliente.auth.login');
    }


    public function cadastroForm(string $token): View
    {
        $empresa = $this->empresaCadastroPorToken($token);

        abort_unless($empresa, 404);

        return view('portal.cliente.auth.cadastro', [
            'empresa' => $empresa,
            'token' => $token,
        ]);
    }

    public function cadastrar(Request $request, string $token): RedirectResponse
    {
        $empresa = $this->empresaCadastroPorToken($token);

        abort_unless($empresa, 404);

        $dados = $request->validate([
            'nome' => ['bail', 'required', 'string', 'max:255'],
            'email' => ['bail', 'required', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'password' => ['bail', 'required', 'confirmed', Password::min(8)],
            'website' => ['nullable', 'prohibited'],
        ], [
            'nome.required' => 'Informe seu nome para criar o acesso.',
            'email.required' => 'Informe seu e-mail para criar o acesso.',
            'email.email' => 'Informe um e-mail válido.',
            'password.required' => 'Crie uma senha para acessar o portal.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'website.prohibited' => 'Requisição inválida. Atualize a página e tente novamente.',
        ]);

        $email = strtolower(trim((string) $dados['email']));

        $clienteExistente = ClientePortalUser::query()
            ->where('email', $email)
            ->first();

        if ($clienteExistente) {
            throw ValidationException::withMessages([
                'email' => ((int) $clienteExistente->empresa_id === (int) $empresa->id)
                    ? 'Este e-mail já possui acesso ao portal desta empresa. Faça login ou use a recuperação de senha.'
                    : 'Este e-mail já está cadastrado em outro portal. Use outro e-mail ou entre em contato com o suporte.',
            ]);
        }

        $cliente = ClientePortalUser::query()->create([
            'empresa_id' => (int) $empresa->id,
            'nome' => trim((string) $dados['nome']),
            'email' => $email,
            'telefone' => $dados['telefone'] ? trim((string) $dados['telefone']) : null,
            'password' => Hash::make((string) $dados['password']),
            'ativo' => true,
            'email_verified_at' => now(),
            'ultimo_acesso_em' => now(),
        ]);

        Auth::guard('portal_cliente')->login($cliente);

        $request->session()->regenerate();

        return redirect()
            ->to(route('portal.cliente.show', ['token' => $token]) . '#chat')
            ->withInput([
                'nome' => (string) $cliente->nome,
                'email' => (string) $cliente->email,
            ])
            ->with('success', 'Cadastro concluído. Bem-vindo ao Portal do Cliente.');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['bail', 'required', 'email', 'max:255'],
            'password' => ['bail', 'required', 'string', 'max:255'],
            'remember' => ['nullable', 'boolean'],
            'website' => ['nullable', 'prohibited'],
        ], [
            'email.required' => 'Informe seu e-mail para acessar o portal.',
            'email.email' => 'Informe um e-mail válido.',
            'password.required' => 'Informe sua senha para acessar o portal.',
            'website.prohibited' => 'Requisição inválida. Atualize a página e tente novamente.',
        ]);

        $this->ensureIsNotRateLimited($request);

        $cliente = ClientePortalUser::query()
            ->where('email', strtolower(trim((string) $credentials['email'])))
            ->first();

        if (! $cliente || ! Hash::check((string) $credentials['password'], (string) $cliente->password)) {
            RateLimiter::hit($this->throttleKey($request), 60);

            throw ValidationException::withMessages([
                'email' => 'As credenciais informadas não conferem.',
            ]);
        }

        if (! $cliente->estaAtivo()) {
            RateLimiter::hit($this->throttleKey($request), 60);

            throw ValidationException::withMessages([
                'email' => 'Seu acesso ao portal está inativo. Entre em contato com o suporte.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        Auth::guard('portal_cliente')->login($cliente, (bool) ($credentials['remember'] ?? false));

        $request->session()->regenerate();

        $cliente->forceFill([
            'ultimo_acesso_em' => now(),
        ])->save();

        $empresa = Empresa::query()->find((int) $cliente->empresa_id);
        $token = $empresa?->portal_token;

        if (! $token) {
            return redirect('/')
                ->with('error', 'Não foi possível localizar o link do portal da empresa. Entre em contato com o suporte.');
        }

        return redirect()->intended(route('portal.cliente.show', ['token' => $token]) . '#chat');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('portal_cliente')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Você saiu do portal com segurança.');
    }

    private function empresaCadastroPorToken(string $token): ?Empresa
    {
        return Empresa::query()
            ->where('portal_token', $token)
            ->where(function ($query): void {
                $query->whereNull('portal_ativo')->orWhere('portal_ativo', true);
            })
            ->where(function ($query): void {
                $query->whereNull('portal_expira_em')->orWhere('portal_expira_em', '>=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ativo')->orWhere('ativo', true);
            })
            ->first();
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => 'Muitas tentativas de acesso. Tente novamente em ' . $seconds . ' segundos.',
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower((string) $request->input('email')) . '|' . $request->ip());
    }
}
