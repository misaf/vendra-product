<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Misaf\VendraProduct\Database\Seeders\DemoContentSeeder;
use Misaf\VendraProduct\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraProduct\ProductPlugin;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

#[Description('Seed product module data for a tenant')]
final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = ProductPlugin::ID;

    protected $signature = self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed product data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies, demo-contents}';

    /**
     * @return array<string, class-string>
     */
    protected function seeders(): array
    {
        return [
            'permission-policies' => PermissionPolicySeeder::class,
            'demo-contents' => DemoContentSeeder::class,
        ];
    }
}
