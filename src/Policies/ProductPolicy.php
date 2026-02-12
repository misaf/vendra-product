<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Misaf\VendraProduct\Enums\ProductPolicyEnum;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraUser\Models\User;

final class ProductPolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return $user->can(ProductPolicyEnum::CREATE);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can(ProductPolicyEnum::DELETE);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(ProductPolicyEnum::DELETE_ANY);
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->can(ProductPolicyEnum::FORCE_DELETE);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can(ProductPolicyEnum::FORCE_DELETE_ANY);
    }

    public function reorder(User $user): bool
    {
        return $user->can(ProductPolicyEnum::REORDER);
    }

    public function replicate(User $user, Product $product): bool
    {
        return $user->can(ProductPolicyEnum::REPLICATE);
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->can(ProductPolicyEnum::RESTORE);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can(ProductPolicyEnum::RESTORE_ANY);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can(ProductPolicyEnum::UPDATE);
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can(ProductPolicyEnum::VIEW);
    }

    public function viewAny(User $user): bool
    {
        return $user->can(ProductPolicyEnum::VIEW_ANY);
    }
}
