<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Observers;

use Misaf\VendraProduct\Models\Product;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;

/**
 * The synchronous half of Product's lifecycle, kept separate from the queued
 * ProductObserver because neither hook can run on the queue: `updated` reads
 * `wasChanged()` state that does not survive serialization, and `forceDeleting`
 * needs the pivot rows that the delete is about to remove.
 *
 * Token generation stays in the model's `booted()` — it is a self-attribute
 * default with no collaborator, like Cart's expiry and Transaction's token.
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
        if (null !== AttributeIntegration::valueModel()) {
            $product->selectedAttributeValues()->detach();
        }
    }
}
