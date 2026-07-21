<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductPrices;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductPrices\Schemas\ProductPriceForm;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductPrices\Tables\ProductPriceTable;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraSupport\Filament\Clusters\CatalogCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class ProductPriceResource extends Resource
{
    protected static ?string $model = ProductPrice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?int $navigationSort = NavigationPriority::ProductPrices->value;

    protected static ?string $slug = 'product-prices';

    protected static ?string $cluster = CatalogCluster::class;

    protected static bool $isScopedToTenant = false;

    public static function getBreadcrumb(): string
    {
        return __('vendra-product::navigation.product_price');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-product::navigation.product_price');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-product::navigation.product_prices');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-product::navigation.product_prices');
    }

    public static function form(Schema $schema): Schema
    {
        return ProductPriceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductPriceTable::configure($table);
    }
}
