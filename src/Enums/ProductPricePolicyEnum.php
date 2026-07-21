<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Enums;

enum ProductPricePolicyEnum: string
{
    case Create = 'create-product-price';
    case Delete = 'delete-product-price';
    case DeleteAny = 'delete-any-product-price';
    case ForceDelete = 'force-delete-product-price';
    case ForceDeleteAny = 'force-delete-any-product-price';
    case Restore = 'restore-product-price';
    case RestoreAny = 'restore-any-product-price';
    case Update = 'update-product-price';
    case View = 'view-product-price';
    case ViewAny = 'view-any-product-price';
}
