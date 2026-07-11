<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Providers;

use Filament\Panel;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraProduct\Console\Commands\SeedCommand;
use Misaf\VendraProduct\ProductPlugin;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Support\TenantSeeders;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class ProductServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-product')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations([
                'create_products_table',
            ])
            ->hasCommands(SeedCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-product');
            });
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if ( ! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-product')) {
                return;
            }

            $panel->plugin(ProductPlugin::make());
        });
    }

    public function packageBooted(): void
    {
        $this->app->make(TenantSeeders::class)->register('vendra-product:seed', priority: 40);

        AboutCommand::add('Vendra Product', fn() => ['Version' => 'dev-master']);
    }


}
