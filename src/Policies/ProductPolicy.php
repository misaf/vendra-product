<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Misaf\VendraProduct\Enums\ProductPolicyEnum;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraSupport\Support\SandboxMode;

final class ProductPolicy
{
    use HandlesAuthorization;

    public function create(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPolicyEnum::CREATE->value);
    }

    public function delete(Authorizable $user, Product $product): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPolicyEnum::DELETE->value);
    }

    public function deleteAny(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPolicyEnum::DELETE_ANY->value);
    }

    public function forceDelete(Authorizable $user, Product $product): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPolicyEnum::FORCE_DELETE->value);
    }

    public function forceDeleteAny(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPolicyEnum::FORCE_DELETE_ANY->value);
    }

    public function reorder(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPolicyEnum::REORDER->value);
    }

    public function replicate(Authorizable $user, Product $product): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPolicyEnum::REPLICATE->value);
    }

    public function restore(Authorizable $user, Product $product): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPolicyEnum::RESTORE->value);
    }

    public function restoreAny(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPolicyEnum::RESTORE_ANY->value);
    }

    public function update(Authorizable $user, Product $product): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPolicyEnum::UPDATE->value);
    }

    public function view(Authorizable $user, Product $product): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPolicyEnum::VIEW->value);
    }

    public function viewAny(Authorizable $user): bool
    {
        return SandboxMode::enabled()
            || $user->can(ProductPolicyEnum::VIEW_ANY->value);
    }
}
