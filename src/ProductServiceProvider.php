<?php

declare(strict_types=1);

namespace Misaf\VendraProduct;

use Filament\Panel;
use Illuminate\Foundation\Console\AboutCommand;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class ProductServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-product')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations([
                'create_products_table'
            ])
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-product');
            });
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if ('admin' !== $panel->getId()) {
                return;
            }

            $panel->plugin(ProductPlugin::make());
        });
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Vendra Product', fn() => ['Version' => 'dev-master']);
    }
}
