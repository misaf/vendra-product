<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductPrices\RelationManagers\ProductPriceRelationManager;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\CreateProduct;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\EditProduct;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\ListProducts;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\ViewProduct;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Schemas\ProductForm;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Tables\ProductTable;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraSupport\Filament\Clusters\CatalogCluster;

use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class ProductResource extends Resource
{
    use Translatable;

    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?int $navigationSort = NavigationPriority::Products->value;

    protected static ?string $slug = 'products';

    protected static ?string $cluster = CatalogCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-product::navigation.product');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-product::navigation.product');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-product::navigation.products');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-product::navigation.products');
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
        $locale = static::getTranslatableLocales()[0] ?? app()->getLocale();

        return is_string($locale) && '' !== $locale ? $locale : 'en';
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
