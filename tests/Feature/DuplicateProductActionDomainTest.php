<?php

declare(strict_types=1);

use Misaf\VendraProduct\Actions\DuplicateProductAction;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('duplicates a product with a copy suffix through the domain action', function (): void {
    $product = ProductFactory::new()->create([
        'name' => ['en' => 'T-Shirt', 'de' => 'T-Shirt'],
        'slug' => ['en' => 't-shirt', 'de' => 't-shirt'],
    ]);

    $product->productPrices()->create([
        'currency_code' => ProductPrice::defaultCurrencyCode(),
        'price' => 1500,
    ]);

    $duplicate = resolve(DuplicateProductAction::class)->execute($product);

    expect($duplicate)->toBeInstanceOf(Product::class)
        ->and($duplicate->getKey())->not->toBe($product->getKey())
        ->and($duplicate->getTranslations('name'))->toBe([
            'en' => 'T-Shirt Copy',
            'de' => 'T-Shirt Copy',
        ])
        ->and($duplicate->getTranslations('slug'))->toBe([
            'en' => 't-shirt-copy',
            'de' => 't-shirt-copy',
        ])
        ->and($duplicate->token)->not->toBe($product->token)
        ->and($duplicate->productPrices()->count())->toBe(1)
        ->and((int) $duplicate->productPrices()->first()->price->getAmount())->toBe(1500);
});

it('increments the suffix when a previous duplicate exists', function (): void {
    $product = ProductFactory::new()->create([
        'name' => ['en' => 'T-Shirt', 'de' => 'T-Shirt'],
        'slug' => ['en' => 't-shirt', 'de' => 't-shirt'],
    ]);

    resolve(DuplicateProductAction::class)->execute($product);
    $second = resolve(DuplicateProductAction::class)->execute($product);

    expect($second->getTranslations('name'))->toBe([
        'en' => 'T-Shirt Copy 2',
        'de' => 'T-Shirt Copy 2',
    ])
        ->and($second->getTranslations('slug'))->toBe([
            'en' => 't-shirt-copy-2',
            'de' => 't-shirt-copy-2',
        ]);
});

it('leaves the original prices untouched', function (): void {
    $product = ProductFactory::new()->create();

    $product->productPrices()->create([
        'currency_code' => ProductPrice::defaultCurrencyCode(),
        'price' => 1000,
    ]);

    resolve(DuplicateProductAction::class)->execute($product);

    expect($product->refresh()->productPrices()->count())->toBe(1);
});
