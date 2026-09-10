<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\ProductResource;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;
use RuntimeException;

final class CreateProduct extends CreateRecord
{
    use Translatable;

    protected static string $resource = ProductResource::class;

    /**
     * @var array{currency_code: string, price: int}|null
     */
    protected ?array $pricingData = null;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/create-record.breadcrumb').' '.__('vendra-product::navigation.product');
    }

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
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

    protected function afterCreate(): void
    {
        /** @var Product|null $record */
        $record = $this->getRecord();

        throw_if($record === null || $this->pricingData === null, RuntimeException::class, 'Product or pricing data is missing after create operation.');

        $record->productPrices()->create($this->pricingData);
    }
}
