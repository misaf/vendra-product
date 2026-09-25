<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Observers;

use Misaf\VendraProduct\Models\Product;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;
use Misaf\VendraSupport\Contracts\TenantEntitlements;
use Misaf\VendraSupport\Enums\PlanLimit;
use Misaf\VendraSupport\Exceptions\EntitlementExceededException;

/**
 * The synchronous product hooks that cannot run on the queue.
 *
 * `creating` aborts by throwing, `created` counts the new row, `updated`
 * reads `wasChanged()`, and `forceDeleting` needs the pivot rows.
 */
final readonly class ProductLifecycleObserver
{
    public function __construct(private TenantEntitlements $entitlements) {}

    /**
     * Refuse a product past the store's plan limit, whichever path creates it.
     *
     * The panels and the duplicate action create inside a transaction, which keeps
     * the store locked until commit so concurrent creates cannot pass the limit.
     *
     * @throws EntitlementExceededException
     */
    public function creating(Product $product): void
    {
        $this->entitlements->assertCanAdd(PlanLimit::ProductsPerStore);
    }

    public function created(Product $product): void
    {
        $this->entitlements->recordAdded(PlanLimit::ProductsPerStore);
    }

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
