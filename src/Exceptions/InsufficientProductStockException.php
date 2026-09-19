<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Exceptions;

use DomainException;

final class InsufficientProductStockException extends DomainException
{
    public function __construct(public readonly int $productId)
    {
        parent::__construct("Product [{$productId}] does not have enough stock left.");
    }
}
