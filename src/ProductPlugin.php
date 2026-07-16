<?php

declare(strict_types=1);

namespace Misaf\VendraProduct;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\Facades\Config;
use Misaf\VendraProduct\Filament\Widgets\ProductOverviewWidget;

final class ProductPlugin implements Plugin
{
    public const string ID = 'vendra-product';

    protected string|Closure|null $navigationGroup = null;

    public function getId(): string
    {
        return self::ID;
    }

    public static function make(): static
    {
        /** @var static $plugin */
        $plugin = app(self::class);

        return $plugin;
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(self::ID);

        return $plugin;
    }

    public function navigationGroup(string|Closure|null $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): string
    {
        $group = $this->navigationGroup ?? Config::get('vendra-product.navigation_group');

        if ($group instanceof Closure) {
            $group = $group();
        }

        if ( ! is_string($group) || '' === $group) {
            $group = 'vendra-support::navigation.groups.Catalog';
        }

        return (string) __($group);
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
