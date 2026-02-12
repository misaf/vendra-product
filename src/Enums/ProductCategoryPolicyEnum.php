<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Enums;

enum ProductCategoryPolicyEnum: string
{
    case CREATE = 'create-product-category';
    case DELETE = 'delete-product-category';
    case DELETE_ANY = 'delete-any-product-category';
    case FORCE_DELETE = 'force-delete-product-category';
    case FORCE_DELETE_ANY = 'force-delete-any-product-category';
    case REORDER = 'reorder-product-category';
    case REPLICATE = 'replicate-product-category';
    case RESTORE = 'restore-product-category';
    case RESTORE_ANY = 'restore-any-product-category';
    case UPDATE = 'update-product-category';
    case VIEW = 'view-product-category';
    case VIEW_ANY = 'view-any-product-category';
}
