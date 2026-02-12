<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Misaf\VendraProduct\Models\Product;

final class ProductObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function deleted(Product $product): void
    {
        $product->productPrices()->delete();
    }
}
