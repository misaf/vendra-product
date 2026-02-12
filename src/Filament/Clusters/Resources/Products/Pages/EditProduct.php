<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use InvalidArgumentException;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\ProductResource;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;
use RuntimeException;

final class EditProduct extends EditRecord
{
    use Translatable;

    protected static string $resource = ProductResource::class;

    /**
     * @var array{currency_code: string, price: int}|null
     */
    protected ?array $pricingData = null;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/edit-record.breadcrumb') . ' ' . __('vendra-product::navigation.product');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            DeleteAction::make(),

            LocaleSwitcher::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Product $record */
        $record = $this->getRecord();

        /** @var ProductPrice|null $latestProductPrice */
        $latestProductPrice = $record->latestProductPrice()->first();

        if ( ! $latestProductPrice) {
            throw new RuntimeException('Product price is required before editing this product.');
        }

        $data['currency_code'] = $latestProductPrice->currency_code;
        $data['price'] = $latestProductPrice->price->getAmount();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $currencyCode = $data['currency_code'] ?? null;
        $price = $data['price'] ?? null;

        if ( ! is_string($currencyCode) || '' === $currencyCode) {
            throw new InvalidArgumentException('Invalid currency code provided.');
        }

        if ( ! is_numeric($price)) {
            throw new InvalidArgumentException('Invalid price provided.');
        }

        $this->pricingData = [
            'currency_code' => $currencyCode,
            'price'         => (int) $price,
        ];

        unset($data['currency_code'], $data['price']);

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var Product $record */
        $record = $this->getRecord();

        if (null === $this->pricingData) {
            throw new RuntimeException('Pricing data is missing after save operation.');
        }

        $record->productPrices()->firstOrCreate(
            [
                'currency_code' => $this->pricingData['currency_code'],
                'price'         => $this->pricingData['price'],
            ]
        );
    }
}
