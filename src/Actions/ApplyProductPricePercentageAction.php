<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Actions;

use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;

final readonly class ApplyProductPricePercentageAction
{
    public function __construct(private SetProductPriceAction $setProductPrice) {}

    /**
     * The caller keeps the percentage at or above -100.
     */
    public function execute(Product $product, float $percent): ?ProductPrice
    {
        $latestProductPrice = $product->latestProductPrice;

        if (! $latestProductPrice instanceof ProductPrice) {
            return null;
        }

        $latestPriceAmount = (int) $latestProductPrice->price->getAmount();

        if ($percent < 0) {
            $newPrice = $latestPriceAmount * (1 - abs((int) $percent) / 100);
        } else {
            $newPrice = $latestPriceAmount * (1 + $percent / 100);
        }

        return $this->setProductPrice->execute(
            $product,
            $latestProductPrice->currency_code,
            (int) round($newPrice),
        );
    }
}
