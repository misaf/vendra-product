<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Enums;

enum ProductPurchaseRefusalEnum
{
    /**
     * The product is missing, deleted, or in an inactive category.
     */
    case Unavailable;
    case OutOfStock;
    case PriceMissing;
}
