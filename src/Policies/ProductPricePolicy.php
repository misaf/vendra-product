<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Misaf\VendraProduct\Enums\ProductPricePolicyEnum;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraUser\Models\User;

final class ProductPricePolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return $user->can(ProductPricePolicyEnum::CREATE);
    }

    public function delete(User $user, ProductPrice $productPrice): bool
    {
        return $user->can(ProductPricePolicyEnum::DELETE);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(ProductPricePolicyEnum::DELETE_ANY);
    }

    public function forceDelete(User $user, ProductPrice $productPrice): bool
    {
        return $user->can(ProductPricePolicyEnum::FORCE_DELETE);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can(ProductPricePolicyEnum::FORCE_DELETE_ANY);
    }

    public function replicate(User $user, ProductPrice $productPrice): bool
    {
        return $user->can(ProductPricePolicyEnum::REPLICATE);
    }

    public function restore(User $user, ProductPrice $productPrice): bool
    {
        return $user->can(ProductPricePolicyEnum::RESTORE);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can(ProductPricePolicyEnum::RESTORE_ANY);
    }

    public function update(User $user, ProductPrice $productPrice): bool
    {
        return $user->can(ProductPricePolicyEnum::UPDATE);
    }

    public function view(User $user, ProductPrice $productPrice): bool
    {
        return $user->can(ProductPricePolicyEnum::VIEW);
    }

    public function viewAny(User $user): bool
    {
        return $user->can(ProductPricePolicyEnum::VIEW_ANY);
    }
}
