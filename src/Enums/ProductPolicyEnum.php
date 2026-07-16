<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Enums;

enum ProductPolicyEnum: string
{
    case Create = 'create-product';
    case Delete = 'delete-product';
    case DeleteAny = 'delete-any-product';
    case ForceDelete = 'force-delete-product';
    case ForceDeleteAny = 'force-delete-any-product';
    case Reorder = 'reorder-product';
    case Replicate = 'replicate-product';
    case Restore = 'restore-product';
    case RestoreAny = 'restore-any-product';
    case Update = 'update-product';
    case View = 'view-product';
    case ViewAny = 'view-any-product';
}
