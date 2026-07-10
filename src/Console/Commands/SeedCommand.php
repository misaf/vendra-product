<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Console\Commands;

use Misaf\VendraProduct\Database\Seeders\DemoContentSeeder;
use Misaf\VendraProduct\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraProduct\ProductPlugin;
use Misaf\VendraSupport\Console\Commands\TenantSeedCommand;

final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = ProductPlugin::ID;

    protected $signature = self::MODULE_NAME . ':seed
        {tenant? : Tenant ID or slug to seed product data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies, demo-contents}';

    protected $description = 'Seed product module data for a tenant';

    /**
     * @return array<string, class-string>
     */
    protected function seeders(): array
    {
        return [
            'permission-policies' => PermissionPolicySeeder::class,
            'demo-contents'       => DemoContentSeeder::class,
        ];
    }
}
