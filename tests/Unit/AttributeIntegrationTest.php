<?php

declare(strict_types=1);

use Misaf\VendraProduct\Models\Product;
use Misaf\VendraSupport\Contracts\AttributeResolver;
use Misaf\VendraSupport\Support\AttributeIntegration;

it('keeps attribute integration disabled without an attribute provider', function (): void {
    expect(AttributeIntegration::isAvailable())->toBeFalse()
        ->and(fn() => (new Product())->attributeValues())
        ->toThrow(LogicException::class);
});

it('resolves product attribute values through the shared contract', function (): void {
    app()->instance(AttributeResolver::class, new class () implements AttributeResolver {
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
        ->and((new Product())->attributeValues()->getRelated())->toBeInstanceOf(Product::class);
});
