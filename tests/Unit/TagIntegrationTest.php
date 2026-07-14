<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Tests\Unit;

use LogicException;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraSupport\Contracts\TagResolver;
use Misaf\VendraSupport\Support\EloquentTagResolver;
use Misaf\VendraSupport\Support\NullTagResolver;
use Misaf\VendraSupport\Support\TagRelationship;

it('keeps product tags unavailable without a tag provider', function (): void {
    app()->instance(TagResolver::class, new NullTagResolver());

    expect(fn() => (new Product())->tags())
        ->toThrow(LogicException::class, 'Install a tag provider to use tags.');
});

it('builds a typed polymorphic tag relation through the support contract', function (): void {
    app()->instance(
        TagResolver::class,
        new EloquentTagResolver(new TagRelationship(ProductTestTag::class)),
    );

    $relation = (new Product())->tags();

    expect($relation->getRelated())->toBeInstanceOf(ProductTestTag::class)
        ->and($relation->getTable())->toBe('taggables')
        ->and($relation->getForeignPivotKeyName())->toBe('taggable_id')
        ->and($relation->getRelatedPivotKeyName())->toBe('tag_id')
        ->and($relation->toBase()->wheres)->toContainEqual([
            'type'     => 'Basic',
            'column'   => 'tags.type',
            'operator' => '=',
            'value'    => Product::TAG_TYPE,
            'boolean'  => 'and',
        ]);
});
