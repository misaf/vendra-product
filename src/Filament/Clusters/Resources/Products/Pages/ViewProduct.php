<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\ProductResource;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;
use RuntimeException;

final class ViewProduct extends ViewRecord
{
    use Translatable;

    protected static string $resource = ProductResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/view-record.breadcrumb') . ' ' . __('vendra-product::navigation.product');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            LocaleSwitcher::make()
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Product $record */
        $record = $this->getRecord();

        /** @var ProductPrice|null $latestProductPrice */
        $latestProductPrice = $record->latestProductPrice()->first();

        if ( ! $latestProductPrice) {
            throw new RuntimeException('Product price is required before viewing this product.');
        }

        $data['currency_code'] = $latestProductPrice->currency_code;
        $data['price'] = $latestProductPrice->price->getAmount();

        return $data;
    }
}
