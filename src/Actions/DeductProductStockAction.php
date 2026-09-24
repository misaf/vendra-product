<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Misaf\VendraProduct\Exceptions\InsufficientProductStockException;
use Misaf\VendraProduct\Models\Product;

/**
 * Products are locked in id order, so two checkouts sharing products cannot
 * deadlock, and every quantity is checked before any is taken.
 *
 * Taking the last unit switches `in_stock` off. Restocking never switches it
 * back on, so a product the merchant took off sale stays off.
 */
final class DeductProductStockAction
{
    /**
     * @param  array<int, int>  $quantities  Quantity to take, keyed by product id.
     *
     * @throws InsufficientProductStockException
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
                ->whereKey(array_keys($quantities))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);

                throw_if(! $product instanceof Product || ! $product->in_stock || $product->quantity < $quantity, InsufficientProductStockException::class, $productId);
            }

            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);

                $product?->decrement('quantity', $quantity, $product->quantity === $quantity ? ['in_stock' => false] : []);
            }
        });
    }
}
