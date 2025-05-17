<?php

declare(strict_types = 1);

namespace FilamentWebpush;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentWebpushServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-webpush')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoutes();
    }
}
