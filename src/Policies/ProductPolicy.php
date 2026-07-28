<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Policies;

use Misaf\VendraProduct\Enums\ProductPolicyEnum;
use Misaf\VendraSupport\Authorization\AuthorizesCreateAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesDeleteAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesForceDeleteAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesReorderAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesReplicateAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesRestoreAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesSandboxMode;
use Misaf\VendraSupport\Authorization\AuthorizesUpdateAbilities;
use Misaf\VendraSupport\Authorization\AuthorizesViewAbilities;
use Misaf\VendraSupport\Authorization\ResolvesPolicyPermissions;

final class ProductPolicy
{
    use AuthorizesCreateAbilities;
    use AuthorizesDeleteAbilities;
    use AuthorizesForceDeleteAbilities;
    use AuthorizesReorderAbilities;
    use AuthorizesReplicateAbilities;
    use AuthorizesRestoreAbilities;
    use AuthorizesSandboxMode;
    use AuthorizesUpdateAbilities;
    use AuthorizesViewAbilities;
    use ResolvesPolicyPermissions;

    protected static function permissionEnum(): string
    {
        return ProductPolicyEnum::class;
    }
}
