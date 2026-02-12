<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Misaf\VendraProduct\Enums\ProductCategoryPolicyEnum;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraUser\Models\User;

final class ProductCategoryPolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return $user->can(ProductCategoryPolicyEnum::CREATE);
    }

    public function delete(User $user, ProductCategory $productCategory): bool
    {
        return $user->can(ProductCategoryPolicyEnum::DELETE);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(ProductCategoryPolicyEnum::DELETE_ANY);
    }

    public function forceDelete(User $user, ProductCategory $productCategory): bool
    {
        return $user->can(ProductCategoryPolicyEnum::FORCE_DELETE);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can(ProductCategoryPolicyEnum::FORCE_DELETE_ANY);
    }

    public function reorder(User $user): bool
    {
        return $user->can(ProductCategoryPolicyEnum::REORDER);
    }

    public function replicate(User $user, ProductCategory $productCategory): bool
    {
        return $user->can(ProductCategoryPolicyEnum::REPLICATE);
    }

    public function restore(User $user, ProductCategory $productCategory): bool
    {
        return $user->can(ProductCategoryPolicyEnum::RESTORE);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can(ProductCategoryPolicyEnum::RESTORE_ANY);
    }

    public function update(User $user, ProductCategory $productCategory): bool
    {
        return $user->can(ProductCategoryPolicyEnum::UPDATE);
    }

    public function view(User $user, ProductCategory $productCategory): bool
    {
        return $user->can(ProductCategoryPolicyEnum::VIEW);
    }

    public function viewAny(User $user): bool
    {
        return $user->can(ProductCategoryPolicyEnum::VIEW_ANY);
    }
}
