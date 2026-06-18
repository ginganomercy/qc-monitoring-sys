<x-filament-panels::page.simple>
    <style>
        /* Base page background hiding filament defaults */
        html, body {
            background-color: transparent !important;
        }
        
        .fi-body {
            background-color: transparent !important;
        }

        /* Dynamic Animated Background */
        body {
            margin: 0;
            padding: 0;
            background-size: 400% 400% !important;
            animation: gradientBG 15s ease infinite !important;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Set theme colors */
        @if(filament()->getCurrentPanel()->getId() === 'admin')
            body {
                background: linear-gradient(-45deg, #d97706, #fbbf24, #f59e0b, #b45309) !important;
            }
            .fi-btn-primary {
                background: linear-gradient(135deg, #f59e0b, #b45309) !important;
                border-color: #fcd34d !important;
            }
        @else
            body {
                background: linear-gradient(-45deg, #4338ca, #6366f1, #4f46e5, #3730a3) !important;
            }
            .fi-btn-primary {
                background: linear-gradient(135deg, #6366f1, #3730a3) !important;
                border-color: #a5b4fc !important;
            }
        @endif

        /* Glassmorphism for the login card */
        /* Target the main container that has bg-white in Filament */
        main > div > section, 
        .fi-simple-main-ctn > section,
        .bg-white, .dark\:bg-gray-900 {
            background: rgba(255, 255, 255, 0.15) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.4) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
            border-radius: 1.5rem !important;
        }

        /* Typography enhancements for contrast over glass */
        h1, h2, span, p, .fi-logo, a {
            color: #ffffff !important;
            text-shadow: 0 1px 3px rgba(0,0,0,0.5) !important;
        }

        /* Make labels visible */
        label {
            color: #ffffff !important;
            font-weight: 600 !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.4) !important;
        }
        
        /* Make inputs readable */
        input[type="email"], 
        input[type="password"],
        input[type="text"] {
            background: rgba(255, 255, 255, 0.9) !important;
            color: #111827 !important;
            border: none !important;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1) !important;
            text-shadow: none !important;
        }

        input[type="email"]:focus, 
        input[type="password"]:focus {
            box-shadow: 0 0 0 3px rgba(255,255,255,0.5) !important;
        }

        /* Button styling */
        button[type="submit"] {
            color: white !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5) !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3) !important;
            transition: all 0.3s ease !important;
        }
        
        button[type="submit"]:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4) !important;
        }
        
        /* Override checkbox bg */
        input[type="checkbox"] {
            background-color: rgba(255, 255, 255, 0.8) !important;
        }
    </style>

    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}

            {{ $this->registerAction }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>
