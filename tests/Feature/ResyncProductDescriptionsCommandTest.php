<?php

declare(strict_types=1);

use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Models\Product;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('converts legacy localized HTML descriptions to Tiptap JSON', function (): void {
    $product = ProductFactory::new()->create([
        'description' => [
            'en' => '<p>Hello <strong>world</strong>.</p>',
            'fa' => '<h2>سلام</h2>',
        ],
    ]);

    $this->artisan('vendra-product:resync-descriptions')
        ->expectsOutputToContain('Converted 2 description translations across 1 products.')
        ->assertSuccessful();

    $description = $product->fresh()->getTranslations('description');

    expect($description['en'])
        ->toMatchArray(['type' => 'doc'])
        ->and($description['en']['content'][0])
        ->toMatchArray(['type' => 'paragraph'])
        ->and($description['en']['content'][0]['content'][1])
        ->toMatchArray([
            'type'  => 'text',
            'text'  => 'world',
            'marks' => [['type' => 'bold']],
        ])
        ->and($description['fa']['content'][0])
        ->toMatchArray(['type' => 'heading'])
        ->and($description['fa']['content'][0]['attrs']['level'])->toBe(2);
});

it('preserves Tiptap descriptions and converts soft-deleted products', function (): void {
    $tiptapDescription = [
        'type'    => 'doc',
        'content' => [[
            'type'    => 'paragraph',
            'content' => [['type' => 'text', 'text' => 'Already synced']],
        ]],
    ];

    $syncedProduct = ProductFactory::new()->create(['description' => ['en' => $tiptapDescription]]);
    $deletedProduct = ProductFactory::new()->create(['description' => ['en' => '<p>Archived</p>']]);
    $deletedProduct->delete();

    $this->artisan('vendra-product:resync-descriptions')->assertSuccessful();

    expect($syncedProduct->fresh()->getTranslations('description')['en'])->toBe($tiptapDescription)
        ->and(Product::query()->withoutGlobalScopes()->findOrFail($deletedProduct->getKey())->getTranslations('description')['en'])
        ->toMatchArray(['type' => 'doc']);
});

it('supports previewing the resync without changing descriptions', function (): void {
    $product = ProductFactory::new()->create(['description' => ['en' => '<p>Preview me</p>']]);

    $this->artisan('vendra-product:resync-descriptions', ['--dry-run' => true])
        ->expectsOutputToContain('Would convert 1 description translations across 1 products.')
        ->assertSuccessful();

    expect($product->fresh()->getTranslations('description'))->toBe(['en' => '<p>Preview me</p>']);
});
