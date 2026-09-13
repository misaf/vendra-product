<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Actions;

use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;

final class SetProductPriceAction
{
    /**
     * The caller validates a supported currency and a non-negative price; the
     * model stores the currency code uppercased.
     */
    public function execute(Product $product, string $currencyCode, int $priceMinorUnits): ProductPrice
    {
        return $product->productPrices()->create([
            'currency_code' => $currencyCode,
            'price' => $priceMinorUnits,
        ]);
    }
}
