<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Enums;

enum ProductPricePolicyEnum: string
{
    case CREATE = 'create-product-price';
    case DELETE = 'delete-product-price';
    case DELETE_ANY = 'delete-any-product-price';
    case FORCE_DELETE = 'force-delete-product-price';
    case FORCE_DELETE_ANY = 'force-delete-any-product-price';
    case REPLICATE = 'replicate-product-price';
    case RESTORE = 'restore-product-price';
    case RESTORE_ANY = 'restore-any-product-price';
    case UPDATE = 'update-product-price';
    case VIEW = 'view-product-price';
    case VIEW_ANY = 'view-any-product-price';
}
