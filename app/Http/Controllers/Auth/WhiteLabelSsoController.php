<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\WhiteLabelSettings;
use Illuminate\Http\RedirectResponse;

class WhiteLabelSsoController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $whiteLabel = WhiteLabelSettings::make();

        if (! $whiteLabel->ssoReady()) {
            return redirect()
                ->route('filament.admin.auth.login')
                ->with('status', 'SSO ainda não está configurado para este workspace.');
        }

        return redirect()->away($whiteLabel->ssoExternalUrl());
    }
}
