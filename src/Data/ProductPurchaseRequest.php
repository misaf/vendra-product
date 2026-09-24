<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Data;

use InvalidArgumentException;

final readonly class ProductPurchaseRequest
{
    public function __construct(
        public int $productId,
        public int $quantity,
    ) {
        throw_if($quantity < 1, InvalidArgumentException::class, 'Quantity must be positive.');
    }
}
