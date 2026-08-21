<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Observers;

use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;

/**
 * The synchronous half of ProductCategory's lifecycle. `forceDeleting` has to
 * reach the attribute values while the category row is still present, so it
 * cannot live in the queued ProductCategoryObserver alongside the soft-delete
 * cascade.
 */
final class ProductCategoryLifecycleObserver
{
    public function forceDeleting(ProductCategory $productCategory): void
    {
        if (null !== AttributeIntegration::valueModel()) {
            $productCategory->attributeValues()->forceDelete();
        }
    }
}
