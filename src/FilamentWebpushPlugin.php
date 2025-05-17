<?php

declare(strict_types = 1);

namespace FilamentWebpush;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use FilamentWebpush\Filament\Widgets\WebpushSubscriptionStats;
use Illuminate\Support\Facades\Config;

class FilamentWebpushPlugin implements Plugin
{
    use EvaluatesClosures;

    protected bool $registerSubscriptionStatsWidget = true;

    public function getId(): string
    {
        return 'filament-webpush';
    }

    public function register(Panel $panel): void
    {
        if ($this->shouldRegisterSubscriptionStatsWidget()) {
            $panel->widgets([
                WebpushSubscriptionStats::class,
            ]);
        }
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

    public function registerSubscriptionStatsWidget(\Closure | bool $register = true): static
    {
        if ($register instanceof \Closure) {
            $register = $this->evaluate($register);
        }

        $this->registerSubscriptionStatsWidget = $register;

        return $this;
    }

    protected function shouldRegisterSubscriptionStatsWidget(): bool
    {
        return $this->registerSubscriptionStatsWidget;
    }
}
