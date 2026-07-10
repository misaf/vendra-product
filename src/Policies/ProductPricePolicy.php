<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Misaf\VendraProduct\Enums\ProductPricePolicyEnum;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraSupport\Support\SandboxMode;

final class ProductPricePolicy
{
    use HandlesAuthorization;

    public function create(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPricePolicyEnum::CREATE->value);
    }

    public function delete(Authorizable $user, ProductPrice $productPrice): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPricePolicyEnum::DELETE->value);
    }

    public function deleteAny(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPricePolicyEnum::DELETE_ANY->value);
    }

    public function forceDelete(Authorizable $user, ProductPrice $productPrice): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPricePolicyEnum::FORCE_DELETE->value);
    }

    public function forceDeleteAny(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPricePolicyEnum::FORCE_DELETE_ANY->value);
    }

    public function replicate(Authorizable $user, ProductPrice $productPrice): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPricePolicyEnum::REPLICATE->value);
    }

    public function restore(Authorizable $user, ProductPrice $productPrice): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPricePolicyEnum::RESTORE->value);
    }

    public function restoreAny(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPricePolicyEnum::RESTORE_ANY->value);
    }

    public function update(Authorizable $user, ProductPrice $productPrice): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPricePolicyEnum::UPDATE->value);
    }

    public function view(Authorizable $user, ProductPrice $productPrice): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPricePolicyEnum::VIEW->value);
    }

    public function viewAny(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPricePolicyEnum::VIEW_ANY->value);
    }
}
