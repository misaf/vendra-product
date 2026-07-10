<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraProduct\ProductPlugin;

final class ProductsCluster extends Cluster
{
    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'products';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    public static function getNavigationGroup(): string
    {
        return ProductPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-product::navigation.product');
    }

    public static function getClusterBreadcrumb(): string
    {
        return ProductPlugin::get()->getNavigationGroup();
    }
}
