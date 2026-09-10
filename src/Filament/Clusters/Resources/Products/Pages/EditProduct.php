<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\DuplicateProductAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\ProductResource;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;

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
        return self::$breadcrumb ?? __('filament-panels::resources/pages/edit-record.breadcrumb').' '.__('vendra-product::navigation.product');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            DuplicateProductAction::make(),

            DeleteAction::make(),

            LocaleSwitcher::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! array_key_exists('product_category_id', $data)) {
            return $data;
        }

        /** @var Product $record */
        $record = $this->getRecord();

        /** @var ProductPrice|null $latestProductPrice */
        $latestProductPrice = $record->latestProductPrice()->first();

        if (! $latestProductPrice) {
            return $data;
        }

        $data['currency_code'] = $latestProductPrice->currency_code;
        $data['price'] = ProductPrice::toMajorUnits(
            $latestProductPrice->currency_code,
            (int) $latestProductPrice->price->getAmount(),
        );

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! array_key_exists('currency_code', $data) && ! array_key_exists('price', $data)) {
            return $data;
        }

        $currencyCode = Arr::get($data, 'currency_code', null);
        $price = Arr::get($data, 'price', null);

        throw_if(! is_string($currencyCode) || $currencyCode === '', InvalidArgumentException::class, 'Invalid currency code provided.');

        throw_unless(is_numeric($price), InvalidArgumentException::class, 'Invalid price provided.');

        $this->pricingData = [
            'currency_code' => $currencyCode,
            'price' => ProductPrice::toMinorUnits($currencyCode, (float) $price),
        ];

        unset($data['currency_code'], $data['price']);

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->pricingData === null) {
            return;
        }

        /** @var Product $record */
        $record = $this->getRecord();

        $record->productPrices()->firstOrCreate(
            [
                'currency_code' => Arr::get($this->pricingData, 'currency_code'),
                'price' => Arr::get($this->pricingData, 'price'),
            ]
        );
    }
}
