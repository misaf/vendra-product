<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages\CreateProductCategory;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages\EditProductCategory;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages\ListProductCategories;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages\ViewProductCategory;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Schemas\ProductCategoryForm;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Schemas\ProductCategoryInfolist;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Tables\ProductCategoryTable;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\RelationManagers\ProductRelationManager;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Filament\Clusters\CatalogCluster;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedGlobalSearch;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class ProductCategoryResource extends Resource
{
    use InteractsWithTranslatedGlobalSearch;
    use Translatable;

    protected static ?string $model = ProductCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?int $navigationSort = NavigationPriority::ProductCategories->value;

    protected static ?string $slug = 'product-categories';

    protected static ?string $cluster = CatalogCluster::class;

    /**
     * @return array<int, string>
     */
    protected static function translatableGlobalSearchAttributes(): array
    {
        return ['name', 'slug'];
    }

    public static function getBreadcrumb(): string
    {
        return __('vendra-product::navigation.product_category');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-product::navigation.product_category');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-product::navigation.product_categories');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-product::navigation.product_categories');
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
        $locale = self::getTranslatableLocales()[0] ?? app()->getLocale();

        return is_string($locale) && '' !== $locale ? $locale : 'en';
    }

    public static function form(Schema $schema): Schema
    {
        return ProductCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductCategoryTable::configure($table);
    }
}
