<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Support;

use Illuminate\Support\Facades\Config;

final class ProductSpecificationUnits
{
    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $configuredUnits = Config::array('vendra-product.specification_units');

        $units = [];

        foreach ($configuredUnits as $value => $label) {
            if ( ! is_string($value) || '' === $value) {
                continue;
            }

            $units[$value] = is_string($label) && '' !== $label ? $label : $value;
        }

        return [] !== $units ? $units : self::defaults();
    }

    /**
     * @return array<string, string>
     */
    private static function defaults(): array
    {
        return [
            'kg'    => 'Kilogram (kg)',
            'g'     => 'Gram (g)',
            'm'     => 'Meter (m)',
            'cm'    => 'Centimeter (cm)',
            'l'     => 'Liter (l)',
            'ml'    => 'Milliliter (ml)',
            'item'  => 'Item',
            'piece' => 'Piece',
            'pack'  => 'Pack',
            'month' => 'Month',
            'year'  => 'Year',
        ];
    }
}
