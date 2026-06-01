<?php

namespace App\Http\Controllers;

use App\Models\Assinatura;
use App\Models\Empresa;
use App\Services\AsaasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class BillingController extends Controller
{
    public function sucesso(Request $request): View
    {
        return view('billing.sucesso');
    }

    public function bloqueado(Request $request): View
    {
        $empresa = $request->user()?->empresa?->load('assinaturaAtual.pagamentos');
        $assinatura = $empresa?->assinaturaAtual;
        $pagamento = $assinatura?->pagamentos()->latest('id')->first();

        return view('billing.bloqueado', compact('empresa', 'assinatura', 'pagamento'));
    }

    public function pagar(Empresa $empresa): RedirectResponse
    {
        $user = request()->user();

        if (! $user || (! $user->isSuperAdmin() && (int) $user->empresa_id !== (int) $empresa->id)) {
            abort(403);
        }

        $assinatura = $empresa->assinaturaAtual()->with('pagamentos')->first();
        $pagamento = $assinatura?->pagamentos()->latest('id')->first();

        if ($pagamento?->invoice_url) {
            return redirect()->away($pagamento->invoice_url);
        }

        return redirect()
            ->route('planos')
            ->with('error', 'Não foi possível localizar uma cobrança aberta para esta empresa.');
    }

    public function cancelar(Assinatura $assinatura, AsaasService $asaas): RedirectResponse
    {
        $user = request()->user();
        $assinatura->loadMissing('empresa');

        if (! $user || (! $user->isSuperAdmin() && (int) $user->empresa_id !== (int) $assinatura->empresa_id)) {
            abort(403);
        }

        try {
            $asaas->cancelarAssinatura($assinatura);
        } catch (RuntimeException $exception) {
            Log::warning('Cancelamento de assinatura recusado.', [
                'assinatura_id' => $assinatura->id,
                'empresa_id' => $assinatura->empresa_id,
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Falha inesperada ao cancelar assinatura.', [
                'assinatura_id' => $assinatura->id,
                'empresa_id' => $assinatura->empresa_id,
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return back()->with('error', 'Não foi possível cancelar a assinatura agora. Tente novamente em instantes.');
        }

        return back()->with('success', 'Assinatura cancelada com sucesso.');
    }
}
