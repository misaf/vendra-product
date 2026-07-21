<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Misaf\VendraProduct\Database\Factories\ProductFactory;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('assigns a token, slug, and position when creating a product', function (): void {
    $first = ProductFactory::new()->create();
    $second = ProductFactory::new()->create();

    expect(mb_strlen($first->token))->toBeGreaterThanOrEqual(9)
        ->and($first->token)->not->toBe($second->token)
        ->and($first->position)->not->toBeNull()
        ->and($first->slug)->not->toBe('');
});

it('rejects duplicate tokens at the database level', function (): void {
    $first = ProductFactory::new()->create();
    $second = ProductFactory::new()->create();

    expect(
        fn(): int => DB::table('products')
            ->where('id', $second->getKey())
            ->update(['token' => $first->token]),
    )->toThrow(QueryException::class);
});

it('falls back to the unique constraint when the token space is exhausted', function (): void {
    Config::set('vendra-product.token_generator_characters', '1');

    ProductFactory::new()->create();

    expect(fn() => ProductFactory::new()->create())->toThrow(QueryException::class);
});
