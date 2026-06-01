<?php

namespace App\Filament\Pages\Auth;

use App\Support\WhiteLabelSettings;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getTitle(): string | Htmlable
    {
        $whiteLabel = WhiteLabelSettings::make();

        if ($whiteLabel->isActive()) {
            $title = trim((string) $whiteLabel->get('login_titulo'));

            if ($title !== '') {
                return $title;
            }
        }

        return parent::getTitle();
    }

    public function getHeading(): string | Htmlable | null
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return parent::getHeading();
        }

        $whiteLabel = WhiteLabelSettings::make();

        if ($whiteLabel->isActive()) {
            $heading = trim((string) $whiteLabel->get('login_titulo'));

            if ($heading !== '') {
                return $heading;
            }
        }

        return parent::getHeading();
    }

    public function getSubheading(): string | Htmlable | null
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return parent::getSubheading();
        }

        $whiteLabel = WhiteLabelSettings::make();

        if ($whiteLabel->isActive()) {
            $subheading = trim((string) $whiteLabel->get('login_subtitulo'));

            if ($subheading !== '') {
                return $subheading;
            }
        }

        return parent::getSubheading();
    }
}
