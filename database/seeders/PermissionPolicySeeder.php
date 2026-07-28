<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Database\Seeders;

use Misaf\VendraProduct\Enums\ProductCategoryPolicyEnum;
use Misaf\VendraProduct\Enums\ProductPolicyEnum;
use Misaf\VendraProduct\Enums\ProductPricePolicyEnum;
use Misaf\VendraProduct\ProductPlugin;
use Misaf\VendraSupport\Tenancy\Database\Seeders\PermissionPolicySeeder as BasePermissionPolicySeeder;

final class PermissionPolicySeeder extends BasePermissionPolicySeeder
{
    protected const string MODULE_NAME = ProductPlugin::ID;

    /**
     * @return list<string>
     */
    protected function policies(): array
    {
        return [
            ...array_column(ProductCategoryPolicyEnum::cases(), 'value'),
            ...array_column(ProductPolicyEnum::cases(), 'value'),
            ...array_column(ProductPricePolicyEnum::cases(), 'value'),
        ];
    }
}
