<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters;

use Filament\Clusters\Cluster;

final class ProductsCluster extends Cluster
{
    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'products';

    public static function getNavigationGroup(): string
    {
        return __('navigation.content_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-product::navigation.product');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('navigation.content_management');
    }
}
