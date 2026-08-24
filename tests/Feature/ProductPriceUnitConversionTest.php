<?php

declare(strict_types=1);

use Misaf\VendraProduct\Models\ProductPrice;

it('converts major units to minor units using the currency subunit', function (): void {
    expect(ProductPrice::toMinorUnits('USD', 15))->toBe(1500)
        ->and(ProductPrice::toMinorUnits('USD', '15.50'))->toBe(1550)
        ->and(ProductPrice::toMinorUnits('JPY', 1500))->toBe(1500)
        ->and(ProductPrice::toMinorUnits('IRR', 1500))->toBe(150000);
});

it('converts minor units back to major units symmetrically', function (): void {
    expect(ProductPrice::toMajorUnits('USD', 1550))->toBe(15.5)
        ->and(ProductPrice::toMajorUnits('JPY', 1500))->toBe(1500);
});

it('treats unknown currencies as having no subunit', function (): void {
    expect(ProductPrice::toMinorUnits('XXX-UNKNOWN', 1500))->toBe(1500)
        ->and(ProductPrice::toMajorUnits('XXX-UNKNOWN', 1500))->toBe(1500);
});
