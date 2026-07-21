<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Enums;

enum ProductCategoryPolicyEnum: string
{
    case Create = 'create-product-category';
    case Delete = 'delete-product-category';
    case DeleteAny = 'delete-any-product-category';
    case ForceDelete = 'force-delete-product-category';
    case ForceDeleteAny = 'force-delete-any-product-category';
    case Reorder = 'reorder-product-category';
    case Restore = 'restore-product-category';
    case RestoreAny = 'restore-any-product-category';
    case Update = 'update-product-category';
    case View = 'view-product-category';
    case ViewAny = 'view-any-product-category';
}
