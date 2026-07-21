<?php

declare(strict_types=1);

namespace Misaf\VendraProduct;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Misaf\VendraProduct\Filament\Widgets\ProductOverviewWidget;
use Misaf\VendraSupport\Filament\Concerns\HasPluginNavigationGroup;
use Misaf\VendraSupport\Filament\Concerns\ResolvesPluginInstances;

final class ProductPlugin implements Plugin
{
    use HasPluginNavigationGroup;
    use ResolvesPluginInstances;

    public const string ID = 'vendra-product';

    public function getId(): string
    {
        return self::ID;
    }

    protected function defaultNavigationGroup(): string
    {
        return 'vendra-support::navigation.groups.Catalog';
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: __DIR__ . '/Filament/Clusters/Resources',
            for: 'Misaf\\VendraProduct\\Filament\\Clusters\\Resources',
        );

        $panel->widgets([
            ProductOverviewWidget::class,
        ]);
    }

    public function boot(Panel $panel): void {}
}
