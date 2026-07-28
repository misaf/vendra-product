<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;
use Misaf\VendraSupport\Contracts\AttributeResolver;

it('persists morph types through stable aliases instead of class names', function (): void {
    expect((new Product())->getMorphClass())->toBe('product')
        ->and((new ProductCategory())->getMorphClass())->toBe('product_category');
});

it('keeps attribute integration disabled without an attribute provider', function (): void {
    app()->instance(AttributeResolver::class, new class implements AttributeResolver {
        public function available(): bool
        {
            return false;
        }

        public function valueModel(): ?string
        {
            return null;
        }

        public function options(): array
        {
            return [];
        }
    });

    expect(AttributeIntegration::isAvailable())->toBeFalse()
        ->and(fn() => (new Product())->attributeValues())
        ->toThrow(LogicException::class)
        ->and(fn() => (new ProductCategory())->attributeValues())
        ->toThrow(LogicException::class);
});

it('resolves category attribute values through the shared contract', function (): void {
    app()->instance(AttributeResolver::class, new class implements AttributeResolver {
        public function available(): bool
        {
            return true;
        }

        public function valueModel(): ?string
        {
            return Product::class;
        }

        public function options(): array
        {
            return [1 => 'Weight (kg)'];
        }
    });

    expect(AttributeIntegration::isAvailable())->toBeTrue()
        ->and(AttributeIntegration::options())->toBe([1 => 'Weight (kg)'])
        ->and((new ProductCategory())->attributeValues())->toBeInstanceOf(MorphMany::class)
        ->and((new ProductCategory())->attributeValues()->getRelated())->toBeInstanceOf(Product::class);
});

it('inherits product attribute values from the product category', function (): void {
    app()->instance(AttributeResolver::class, new class implements AttributeResolver {
        public function available(): bool
        {
            return true;
        }

        public function valueModel(): ?string
        {
            return Product::class;
        }

        public function options(): array
        {
            return [];
        }
    });

    $attributeValues = (new Product())->attributeValues();

    expect($attributeValues)->toBeInstanceOf(HasMany::class)
        ->and($attributeValues->getForeignKeyName())->toBe('attributable_id')
        ->and($attributeValues->getLocalKeyName())->toBe('product_category_id')
        ->and(collect($attributeValues->getQuery()->getQuery()->wheres)->contains(
            fn(array $where): bool => 'attributable_type' === ($where['column'] ?? null)
                && (new ProductCategory())->getMorphClass() === ($where['value'] ?? null),
        ))->toBeTrue();
});

it('selects attribute values through the attribute_value_selections pivot', function (): void {
    app()->instance(AttributeResolver::class, new class implements AttributeResolver {
        public function available(): bool
        {
            return true;
        }

        public function valueModel(): ?string
        {
            return Product::class;
        }

        public function options(): array
        {
            return [];
        }
    });

    $selectedAttributeValues = (new Product())->selectedAttributeValues();

    expect($selectedAttributeValues)->toBeInstanceOf(MorphToMany::class)
        ->and($selectedAttributeValues->getTable())->toBe('attribute_value_selections')
        ->and($selectedAttributeValues->getMorphType())->toBe('selectable_type')
        ->and($selectedAttributeValues->getRelatedPivotKeyName())->toBe('attribute_value_id');
});
