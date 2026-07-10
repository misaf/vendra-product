<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Providers;

use Filament\Panel;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\VendraProduct\Console\Commands\SeedCommand;
use Misaf\VendraProduct\ProductPlugin;
use Misaf\VendraSupport\Support\TenantSeeders;
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
            if ( ! in_array($panel->getId(), $this->configuredPanelIds(), true)) {
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

    /**
     * @return array<int, string>
     */
    private function configuredPanelIds(): array
    {
        $panels = Config::get('vendra-product.panels');

        if (is_string($panels)) {
            return [Config::string('vendra-product.panels')];
        }

        if (is_array($panels)) {
            return $this->filterPanelIds(Config::array('vendra-product.panels'));
        }

        $legacyPanel = Config::get('vendra-product.panel');

        if (is_string($legacyPanel)) {
            return [Config::string('vendra-product.panel')];
        }

        if (is_array($legacyPanel)) {
            return $this->filterPanelIds(Config::array('vendra-product.panel'));
        }

        return ['admin'];
    }

    /**
     * @param  array<mixed>  $panels
     * @return array<int, string>
     */
    private function filterPanelIds(array $panels): array
    {
        return array_values(array_filter($panels, is_string(...)));
    }
}
