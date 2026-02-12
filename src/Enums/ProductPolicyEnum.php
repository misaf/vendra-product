<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Enums;

enum ProductPolicyEnum: string
{
    case CREATE = 'create-product';
    case DELETE = 'delete-product';
    case DELETE_ANY = 'delete-any-product';
    case FORCE_DELETE = 'force-delete-product';
    case FORCE_DELETE_ANY = 'force-delete-any-product';
    case REORDER = 'reorder-product';
    case REPLICATE = 'replicate-product';
    case RESTORE = 'restore-product';
    case RESTORE_ANY = 'restore-any-product';
    case UPDATE = 'update-product';
    case VIEW = 'view-product';
    case VIEW_ANY = 'view-any-product';
}
