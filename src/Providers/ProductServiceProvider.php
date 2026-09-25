<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraProduct\Console\Commands\ResyncProductDescriptionsCommand;
use Misaf\VendraProduct\Console\Commands\SeedCommand;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraProduct\ProductPlugin;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Enums\PlanLimit;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Misaf\VendraSupport\Tenancy\TenantUsageRegistry;
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
            ->hasCommands([
                ResyncProductDescriptionsCommand::class,
                SeedCommand::class,
            ])
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-product');
            });
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-product')) {
                return;
            }

            $panel->plugin(ProductPlugin::make());
        });
    }

    public function packageBooted(): void
    {
        // Stable aliases, so moving a model class never orphans stored morph rows.
        Relation::morphMap([
            'product' => Product::class,
            'product_category' => ProductCategory::class,
        ]);

        $this->app->make(TenantTableRegistry::class)->register('product_categories', 'products');
        $this->app->make(TenantSeeders::class)->register('vendra-product:seed', priority: 40);
        $this->app->make(TenantUsageRegistry::class)->register(
            PlanLimit::ProductsPerStore,
            // Every tenant scope is dropped, since the tenant is given rather than current.
            fn (Model $tenant): int => Product::query()
                ->withoutGlobalScopes()
                ->where(resolve(TenantResolver::class)->foreignKey(), $tenant->getKey())
                ->whereNull((new Product)->getQualifiedDeletedAtColumn())
                ->count(),
        );

        AboutCommand::add('Vendra Product', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-product')]);
    }
}
