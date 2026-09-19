<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Observers;

use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;

/**
 * The synchronous product category hooks that cannot run on the queue.
 *
 * `forceDeleting` removes the attribute values while the category still exists.
 */
final class ProductCategoryLifecycleObserver
{
    public function forceDeleting(ProductCategory $productCategory): void
    {
        if (AttributeIntegration::valueModel() !== null) {
            $productCategory->attributeValues()->forceDelete();
        }
    }
}
