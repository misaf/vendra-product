<?php

declare(strict_types=1);

use Misaf\VendraProduct\Actions\DeductProductStockAction;
use Misaf\VendraProduct\Actions\RestockProductsAction;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Exceptions\InsufficientProductStockException;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('takes the ordered quantities off each product', function (): void {
    $first = ProductFactory::new()->createOne(['in_stock' => true, 'quantity' => 5]);
    $second = ProductFactory::new()->createOne(['in_stock' => true, 'quantity' => 3]);

    resolve(DeductProductStockAction::class)->execute([$first->id => 5, $second->id => 1]);

    expect($first->fresh()?->quantity)->toBe(0)
        ->and($second->fresh()?->quantity)->toBe(2);
});

it('takes the product off sale with its last unit and leaves it off when restocked', function (): void {
    $soldOut = ProductFactory::new()->createOne(['in_stock' => true, 'quantity' => 2]);
    $remaining = ProductFactory::new()->createOne(['in_stock' => true, 'quantity' => 3]);

    resolve(DeductProductStockAction::class)->execute([$soldOut->id => 2, $remaining->id => 1]);

    expect($soldOut->fresh()?->in_stock)->toBeFalse()
        ->and($remaining->fresh()?->in_stock)->toBeTrue();

    resolve(RestockProductsAction::class)->execute([$soldOut->id => 2]);

    expect($soldOut->fresh())
        ->quantity->toBe(2)
        ->in_stock->toBeFalse();
});

it('takes nothing when any product lacks the stock', function (int $available, bool $inStock): void {
    $plenty = ProductFactory::new()->createOne(['in_stock' => true, 'quantity' => 10]);
    $short = ProductFactory::new()->createOne(['in_stock' => $inStock, 'quantity' => $available]);

    expect(fn () => resolve(DeductProductStockAction::class)->execute([$plenty->id => 2, $short->id => 2]))
        ->toThrow(fn (InsufficientProductStockException $exception) => expect($exception->productId)->toBe($short->id))
        ->and($plenty->fresh()?->quantity)->toBe(10)
        ->and($short->fresh()?->quantity)->toBe($available);
})->with([
    'too few left' => [1, true],
    'marked out of stock' => [5, false],
]);

it('refuses a product that no longer exists', function (): void {
    $product = ProductFactory::new()->createOne(['in_stock' => true, 'quantity' => 5]);
    $product->delete();

    expect(fn () => resolve(DeductProductStockAction::class)->execute([$product->id => 1]))
        ->toThrow(InsufficientProductStockException::class);
});

it('returns stock, including to a deleted product', function (): void {
    $product = ProductFactory::new()->createOne(['quantity' => 1]);
    $deleted = ProductFactory::new()->createOne(['quantity' => 0]);
    $deleted->delete();

    resolve(RestockProductsAction::class)->execute([$product->id => 2, $deleted->id => 3]);

    expect($product->fresh()?->quantity)->toBe(3)
        ->and($deleted->fresh()?->quantity)->toBe(3);
});
