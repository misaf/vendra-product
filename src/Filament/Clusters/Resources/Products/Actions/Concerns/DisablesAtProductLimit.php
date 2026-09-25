<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\Concerns;

use Misaf\VendraSupport\Contracts\TenantEntitlements;
use Misaf\VendraSupport\Enums\PlanLimit;
use Misaf\VendraSupport\Exceptions\EntitlementExceededException;

trait DisablesAtProductLimit
{
    /**
     * Disable the action once the store's plan has no room for another product.
     */
    protected function disableAtProductLimit(): void
    {
        $this
            ->disabled(fn (TenantEntitlements $entitlements): bool => ! $entitlements->canAdd(PlanLimit::ProductsPerStore))
            ->tooltip(fn (TenantEntitlements $entitlements): ?string => $entitlements->canAdd(PlanLimit::ProductsPerStore)
                ? null
                : EntitlementExceededException::limitReached(
                    PlanLimit::ProductsPerStore,
                    $entitlements->limit(PlanLimit::ProductsPerStore) ?? 0,
                )->getMessage());
    }
}
