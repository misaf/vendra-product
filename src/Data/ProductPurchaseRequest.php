<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Data;

final readonly class ProductPurchaseRequest
{
    public function __construct(
        public int $productId,
        public int $quantity,
    ) {}
}
