<?php

declare(strict_types=1);

use Misaf\VendraProduct\Actions\SetProductStockAction;
use Misaf\VendraProduct\Database\Factories\ProductFactory;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('marks a product in stock and clears soon availability', function (): void {
    $product = ProductFactory::new()->create([
        'in_stock' => false,
        'available_soon' => true,
        'availability_date' => now()->addWeek(),
    ]);

    resolve(SetProductStockAction::class)->execute($product, true);

    expect($product->fresh()?->in_stock)->toBeTrue()
        ->and($product->fresh()?->available_soon)->toBeFalse()
        ->and($product->fresh()?->availability_date)->toBeNull();
});

it('marks a product out of stock without touching soon availability', function (): void {
    $product = ProductFactory::new()->create([
        'in_stock' => true,
        'available_soon' => true,
    ]);

    resolve(SetProductStockAction::class)->execute($product, false);

    expect($product->fresh()?->in_stock)->toBeFalse()
        ->and($product->fresh()?->available_soon)->toBeTrue();
});
