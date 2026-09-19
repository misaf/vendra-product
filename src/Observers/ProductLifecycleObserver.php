<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Observers;

use Misaf\VendraProduct\Models\Product;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;

/**
 * The synchronous product hooks that cannot run on the queue.
 *
 * `updated` reads `wasChanged()`, and `forceDeleting` needs the pivot rows.
 */
final class ProductLifecycleObserver
{
    public function updated(Product $product): void
    {
        if ($product->wasChanged('product_category_id')) {
            $product->detachStaleAttributeValueSelections();
        }
    }

    public function forceDeleting(Product $product): void
    {
        if (AttributeIntegration::valueModel() !== null) {
            $product->selectedAttributeValues()->detach();
        }
    }
}
