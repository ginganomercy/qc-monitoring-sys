<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class BeautifiedLogin extends BaseLogin
{
    /**
     * Override the default view for the login page
     * @var view-string
     */
    protected static string $view = 'filament.pages.auth.beautified-login';
}
