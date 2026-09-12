<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Actions;

use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;

final class SetProductPriceAction
{
    public function execute(Product $product, string $currencyCode, int $priceMinorUnits): ProductPrice
    {
        $currencyCode = Str::upper($currencyCode);

        Validator::make(
            ['currency_code' => $currencyCode, 'price' => $priceMinorUnits],
            [
                'currency_code' => ['required', 'string', function (string $attribute, mixed $value, Closure $fail): void {
                    if (! ProductPrice::supportsCurrencyCode((string) $value)) {
                        $fail('The selected currency is not supported.');
                    }
                }],
                'price' => ['required', 'integer', 'min:0'],
            ],
        )->validate();

        return $product->productPrices()->create([
            'currency_code' => $currencyCode,
            'price' => $priceMinorUnits,
        ]);
    }
}
