<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductPrices\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductPrices\ProductPriceResource;
use Misaf\VendraProduct\Models\Product;

final class ProductPriceRelationManager extends RelationManager
{
    protected static string $relationship = 'productPrices';

    protected static bool $isBadgeDeferred = true;

    protected static bool $isLazy = false;

    public static function getModelLabel(): string
    {
        return __('vendra-product::navigation.product_price');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-product::navigation.product_prices');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-product::navigation.product_prices');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): string
    {
        if ( ! $ownerRecord instanceof Product) {
            return (string) Number::format(0);
        }

        return (string) Number::format($ownerRecord->productPrices()->count());
    }

    public function form(Schema $schema): Schema
    {
        return ProductPriceResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return ProductPriceResource::table($table)
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
