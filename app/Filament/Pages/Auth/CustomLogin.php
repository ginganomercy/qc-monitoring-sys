<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class CustomLogin extends BaseLogin
{
    /**
     * Override the default view for the login page
     */
    protected static string $view = 'filament.pages.auth.custom-login';

    public function quickLogin(string $role): void
    {
        if ($role === 'admin') {
            $this->form->fill([
                'email' => 'admin@qc.com',
                'password' => 'Tegal*2026',
                'remember' => true,
            ]);
        } else {
            $this->form->fill([
                'email' => 'alisa2891@qc.com',
                'password' => 'Tegal*2026',
                'remember' => true,
            ]);
        }

        $this->authenticate();
    }
}
