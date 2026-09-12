<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Actions;

use Misaf\VendraProduct\Models\Product;

final class SetProductStockAction
{
    public function execute(Product $product, bool $inStock): void
    {
        if ($inStock) {
            $product->update([
                'in_stock' => true,
                'available_soon' => false,
                'availability_date' => null,
            ]);

            return;
        }

        $product->update(['in_stock' => false]);
    }
}
