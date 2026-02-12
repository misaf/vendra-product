<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Misaf\VendraProduct\Models\ProductCategory;

final class ProductCategoryObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function deleted(ProductCategory $productCategory): void
    {
        $productCategory->productPrices()->delete();
        $productCategory->products()->delete();
    }
}
