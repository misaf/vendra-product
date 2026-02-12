<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Misaf\VendraProduct\Filament\Clusters\ProductsCluster;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductPrices\RelationManagers\ProductPriceRelationManager;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\CreateProduct;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\EditProduct;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\ListProducts;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\ViewProduct;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Schemas\ProductForm;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Tables\ProductTable;
use Misaf\VendraProduct\Models\Product;

final class ProductResource extends Resource
{
    use Translatable;

    protected static ?string $model = Product::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'products';

    protected static ?string $cluster = ProductsCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-product::navigation.product');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-product::navigation.product');
    }

    public static function getNavigationGroup(): string
    {
        return __('vendra-product::navigation.product_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-product::navigation.product');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-product::navigation.product');
    }

    public static function getRelations(): array
    {
        return [
            // ProductPriceRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view'   => ViewProduct::route('/{record}'),
            'edit'   => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getDefaultTranslatableLocale(): string
    {
        return app()->getLocale();
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductTable::configure($table);
    }
}
