<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Misaf\VendraProduct\Enums\ProductCategoryPolicyEnum;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Support\SandboxMode;

final class ProductCategoryPolicy
{
    use HandlesAuthorization;

    public function create(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductCategoryPolicyEnum::CREATE->value);
    }

    public function delete(Authorizable $user, ProductCategory $productCategory): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductCategoryPolicyEnum::DELETE->value);
    }

    public function deleteAny(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductCategoryPolicyEnum::DELETE_ANY->value);
    }

    public function forceDelete(Authorizable $user, ProductCategory $productCategory): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductCategoryPolicyEnum::FORCE_DELETE->value);
    }

    public function forceDeleteAny(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductCategoryPolicyEnum::FORCE_DELETE_ANY->value);
    }

    public function reorder(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductCategoryPolicyEnum::REORDER->value);
    }

    public function replicate(Authorizable $user, ProductCategory $productCategory): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductCategoryPolicyEnum::REPLICATE->value);
    }

    public function restore(Authorizable $user, ProductCategory $productCategory): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductCategoryPolicyEnum::RESTORE->value);
    }

    public function restoreAny(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductCategoryPolicyEnum::RESTORE_ANY->value);
    }

    public function update(Authorizable $user, ProductCategory $productCategory): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductCategoryPolicyEnum::UPDATE->value);
    }

    public function view(Authorizable $user, ProductCategory $productCategory): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductCategoryPolicyEnum::VIEW->value);
    }

    public function viewAny(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductCategoryPolicyEnum::VIEW_ANY->value);
    }
}
