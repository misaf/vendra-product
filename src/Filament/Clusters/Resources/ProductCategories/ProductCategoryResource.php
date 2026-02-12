<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Misaf\VendraProduct\Filament\Clusters\ProductsCluster;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages\CreateProductCategory;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages\EditProductCategory;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages\ListProductCategories;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages\ViewProductCategory;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Schemas\ProductCategoryForm;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Tables\ProductCategoryTable;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\RelationManagers\ProductRelationManager;
use Misaf\VendraProduct\Models\ProductCategory;

final class ProductCategoryResource extends Resource
{
    use Translatable;

    protected static ?string $model = ProductCategory::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'categories';

    protected static ?string $cluster = ProductsCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-product::navigation.product_category');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-product::navigation.product_category');
    }

    public static function getNavigationGroup(): string
    {
        return __('vendra-product::navigation.product_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-product::navigation.product_category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-product::navigation.product_category');
    }

    public static function getRelations(): array
    {
        return [
            ProductRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProductCategories::route('/'),
            'create' => CreateProductCategory::route('/create'),
            'view'   => ViewProductCategory::route('/{record}'),
            'edit'   => EditProductCategory::route('/{record}/edit'),
        ];
    }

    public static function getDefaultTranslatableLocale(): string
    {
        return app()->getLocale();
    }

    public static function form(Schema $schema): Schema
    {
        return ProductCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductCategoryTable::configure($table);
    }
}
