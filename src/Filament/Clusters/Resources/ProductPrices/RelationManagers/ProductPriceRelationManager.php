<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductPrices\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductPrices\ProductPriceResource;
use Misaf\VendraProduct\Models\ProductPrice;

final class ProductPriceRelationManager extends RelationManager
{
    protected static string $relationship = 'productPrices';

    protected static bool $isLazy = false;

    public static function getModelLabel(): string
    {
        return __('vendra-product::navigation.product_price');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('vendra-product::navigation.product_price');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): string
    {
        /** @var Collection<int, ProductPrice> $productPrices */
        $productPrices = $ownerRecord->getRelation('productPrices') ?? collect();

        return (string) Number::format($productPrices->count());
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
