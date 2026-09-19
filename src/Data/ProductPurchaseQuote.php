<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Data;

use Misaf\VendraProduct\Models\Product;

final readonly class ProductPurchaseQuote
{
    /**
     * The unit amount is in minor units of the quoted currency.
     */
    public function __construct(
        public Product $product,
        public int $unitAmount,
    ) {}
}
