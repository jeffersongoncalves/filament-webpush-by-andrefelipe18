<?php

declare(strict_types = 1);

namespace FilamentWebpush;

use FilamentWebpush\Commands\PrepareWebpushCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentWebpushServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-webpush')
            ->hasCommand(PrepareWebpushCommand::class)
            ->hasViews();
    }

    public function bootingPackage(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }
}
