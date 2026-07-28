<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraProduct\Console\Commands\SeedCommand;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraProduct\ProductPlugin;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
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
            ->hasViews()
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
        /**
         * Stable aliases keep persisted morph columns (attribute value owners,
         * selections, media) decoupled from the model FQCNs, so relocating a
         * model class never orphans stored rows.
         */
        Relation::morphMap([
            'product'          => Product::class,
            'product_category' => ProductCategory::class,
        ]);

        $this->app->make(TenantTableRegistry::class)->register('product_categories', 'products');
        $this->app->make(TenantSeeders::class)->register('vendra-product:seed', priority: 40);

        AboutCommand::add('Vendra Product', fn(): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-product')]);
    }
}
