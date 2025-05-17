<?php

declare(strict_types = 1);

namespace FilamentWebpush;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Config;

class FilamentWebpushPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-webpush';
    }

    public function register(Panel $panel): void
    {
    }

    public function boot(Panel $panel): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            function () {
                if (! auth()->check()) {
                    return '';
                }

                $vapidPublicKey = Config::get('webpush.vapid.public_key');

                return view('filament-webpush::meta', ['vapidPublicKey' => $vapidPublicKey]);
            }
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            function () {
                if (! auth()->check()) {
                    return '';
                }

                return view('filament-webpush::script');
            }
        );
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
