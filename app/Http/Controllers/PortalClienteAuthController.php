<?php

namespace App\Http\Controllers;

use App\Models\ClientePortalUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        return redirect()->intended(route('portal.cliente.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('portal_cliente')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.cliente.login')->with('success', 'Você saiu do portal com segurança.');
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
