<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Misaf\VendraProduct\Models\Product;

/**
 * Deleted products are restocked too, so restoring one brings back its stock.
 */
final class RestockProductsAction
{
    /**
     * @param  array<int, int>  $quantities  Quantity to return, keyed by product id.
     */
    public function execute(array $quantities): void
    {
        if ($quantities === []) {
            return;
        }

        foreach ($quantities as $quantity) {
            throw_if($quantity < 1, InvalidArgumentException::class, 'Quantity must be positive.');
        }

        DB::transaction(function () use ($quantities): void {
            $products = Product::query()
                ->withTrashed()
                ->whereKey(array_keys($quantities))
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($products as $product) {
                $product->increment('quantity', $quantities[$product->id]);
            }
        });
    }
}
