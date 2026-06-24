<?php

namespace App\Http\Controllers;

use App\Models\ClientePortalUser;
use App\Models\PortalClienteToken;
use App\Services\AuditoriaManualService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PortalClientePasswordController extends Controller
{
    public function forgotForm(): View
    {
        return view('portal.cliente.auth.forgot');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'email' => ['bail', 'required', 'email', 'max:255'],
            'website' => ['nullable', 'prohibited'],
        ], [
            'email.required' => 'Informe o e-mail cadastrado no portal.',
            'email.email' => 'Informe um e-mail válido.',
            'website.prohibited' => 'Requisição inválida. Atualize a página e tente novamente.',
        ]);

        $email = strtolower(trim((string) $dados['email']));
        $rateKey = 'portal-cliente-reset|' . $email . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            return back()
                ->withInput(['email' => $email])
                ->withErrors(['email' => 'Muitas solicitações. Aguarde alguns minutos e tente novamente.']);
        }

        RateLimiter::hit($rateKey, 300);

        $cliente = ClientePortalUser::query()
            ->where('email', $email)
            ->where('ativo', true)
            ->first();

        if ($cliente) {
            PortalClienteToken::query()
                ->where('email', $email)
                ->where('tipo', 'reset')
                ->whereNull('used_at')
                ->update(['used_at' => now(), 'updated_at' => now()]);

            $token = Str::random(72);

            PortalClienteToken::query()->create([
                'cliente_portal_user_id' => $cliente->id,
                'email' => $email,
                'tipo' => 'reset',
                'token_hash' => Hash::make($token),
                'expires_at' => now()->addMinutes(60),
            ]);

            $link = route('portal.cliente.password.reset', ['token' => $token, 'email' => $email]);

            AuditoriaManualService::registrarEvento('portal_cliente.password.reset_requested', [
                'email' => $email,
                'empresa_id' => $cliente->empresa_id,
                'expires_at' => now()->addMinutes(60)->toDateTimeString(),
            ], $cliente, empresaId: $cliente->empresa_id, userId: null, nivel: 'warning');

            $this->enviarEmailToken(
                $email,
                'Redefinição de senha - Portal do Cliente',
                "Olá, {$cliente->nome}.\n\nRecebemos uma solicitação para redefinir sua senha no Portal do Cliente.\n\nAcesse o link abaixo para cadastrar uma nova senha. O link expira em 60 minutos:\n{$link}\n\nSe você não solicitou essa alteração, ignore esta mensagem."
            );
        }

        return redirect()
            ->route('portal.cliente.login')
            ->with('success', 'Se o e-mail informado estiver cadastrado, enviaremos um link para redefinir a senha.');
    }

    public function resetForm(Request $request, string $token): View|RedirectResponse
    {
        $email = strtolower(trim((string) $request->query('email')));
        $registro = $this->localizarTokenValido($email, $token, 'reset');

        if (! $registro) {
            return redirect()
                ->route('portal.cliente.forgot')
                ->withErrors(['email' => 'Link de redefinição inválido, expirado ou já utilizado. Solicite um novo link.']);
        }

        return view('portal.cliente.auth.reset', [
            'email' => $email,
            'token' => $token,
        ]);
    }

    public function resetPassword(Request $request, string $token): RedirectResponse
    {
        $dados = $request->validate([
            'email' => ['bail', 'required', 'email', 'max:255'],
            'password' => ['bail', 'required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'website' => ['nullable', 'prohibited'],
        ], [
            'email.required' => 'Informe o e-mail cadastrado no portal.',
            'email.email' => 'Informe um e-mail válido.',
            'password.required' => 'Informe a nova senha.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'website.prohibited' => 'Requisição inválida. Atualize a página e tente novamente.',
        ]);

        $email = strtolower(trim((string) $dados['email']));
        $registro = $this->localizarTokenValido($email, $token, 'reset');

        if (! $registro || ! $registro->cliente) {
            return back()
                ->withInput(['email' => $email])
                ->withErrors(['email' => 'Link de redefinição inválido, expirado ou já utilizado. Solicite um novo link.']);
        }

        $registro->cliente->forceFill([
            'password' => Hash::make((string) $dados['password']),
            'email_verified_at' => $registro->cliente->email_verified_at ?: now(),
        ])->save();

        $registro->forceFill(['used_at' => now()])->save();

        AuditoriaManualService::registrarEvento('portal_cliente.password.reset_success', [
            'email' => $email,
            'empresa_id' => $registro->cliente->empresa_id,
        ], $registro->cliente, empresaId: $registro->cliente->empresa_id, userId: null, nivel: 'warning');

        return redirect()
            ->route('portal.cliente.login')
            ->with('success', 'Senha alterada com sucesso. Faça login com sua nova senha.');
    }

    public function conviteForm(Request $request, string $token): View|RedirectResponse
    {
        $email = strtolower(trim((string) $request->query('email')));
        $registro = $this->localizarTokenValido($email, $token, 'convite');

        if (! $registro) {
            return redirect()
                ->route('portal.cliente.login')
                ->withErrors(['email' => 'Convite inválido, expirado ou já utilizado. Solicite um novo convite ao suporte.']);
        }

        return view('portal.cliente.auth.convite', [
            'email' => $email,
            'token' => $token,
            'cliente' => $registro->cliente,
        ]);
    }

    public function aceitarConvite(Request $request, string $token): RedirectResponse
    {
        $dados = $request->validate([
            'email' => ['bail', 'required', 'email', 'max:255'],
            'password' => ['bail', 'required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'website' => ['nullable', 'prohibited'],
        ], [
            'email.required' => 'Informe o e-mail do convite.',
            'email.email' => 'Informe um e-mail válido.',
            'password.required' => 'Cadastre uma senha para ativar o acesso.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'website.prohibited' => 'Requisição inválida. Atualize a página e tente novamente.',
        ]);

        $email = strtolower(trim((string) $dados['email']));
        $registro = $this->localizarTokenValido($email, $token, 'convite');

        if (! $registro || ! $registro->cliente) {
            return back()
                ->withInput(['email' => $email])
                ->withErrors(['email' => 'Convite inválido, expirado ou já utilizado. Solicite um novo convite ao suporte.']);
        }

        $registro->cliente->forceFill([
            'password' => Hash::make((string) $dados['password']),
            'ativo' => true,
            'email_verified_at' => $registro->cliente->email_verified_at ?: now(),
        ])->save();

        $registro->forceFill(['used_at' => now()])->save();

        AuditoriaManualService::registrarEvento('portal_cliente.invite.accepted', [
            'email' => $email,
            'empresa_id' => $registro->cliente->empresa_id,
        ], $registro->cliente, empresaId: $registro->cliente->empresa_id, userId: null, nivel: 'info');

        return redirect()
            ->route('portal.cliente.login')
            ->with('success', 'Acesso ativado com sucesso. Entre com seu e-mail e a senha cadastrada.');
    }

    private function localizarTokenValido(string $email, string $token, string $tipo): ?PortalClienteToken
    {
        if ($email === '' || $token === '') {
            return null;
        }

        return PortalClienteToken::query()
            ->with('cliente')
            ->where('email', $email)
            ->where('tipo', $tipo)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->get()
            ->first(fn (PortalClienteToken $registro): bool => $registro->confereToken($token));
    }

    private function enviarEmailToken(string $email, string $assunto, string $mensagem): void
    {
        try {
            Mail::raw($mensagem, function ($mail) use ($email, $assunto): void {
                $mail->to($email)->subject($assunto);
            });
        } catch (\Throwable $e) {
            Log::warning('[PORTAL_CLIENTE] Falha ao enviar e-mail de token.', [
                'email' => $email,
                'erro' => $e->getMessage(),
            ]);
        }
    }
}
