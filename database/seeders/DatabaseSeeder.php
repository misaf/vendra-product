<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraTenant\Models\Tenant;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->first();

        if ( ! $tenant) {
            $this->command?->error('Tenants not found. Please run TenantSeeder first.');
            return;
        }

        $tenant->makeCurrent();

        $this->seedProducts($tenant);
    }

    private function seedProducts(Tenant $tenant): void
    {
        $locales = config('app.supported_locales', ['en', 'fa']);

        $categories = [
            [
                'base_name' => [
                    'en' => 'Laptops',
                    'fa' => 'لپ‌تاپ‌ها',
                ],
                'base_description' => [
                    'en' => 'Portable computers for personal and professional use',
                    'fa' => 'کامپیوترهای قابل حمل برای استفاده شخصی و حرفه‌ای',
                ],
                'status'   => true,
                'products' => [
                    [
                        'base_name' => [
                            'en' => 'Dell XPS 13',
                            'fa' => 'دل XPS 13',
                        ],
                        'base_description' => [
                            'en' => 'Compact and powerful ultrabook with Intel i7 processor and 16GB RAM',
                            'fa' => 'اولترابوک جمع‌وجور و قدرتمند با پردازنده Intel i7 و رم 16 گیگابایت',
                        ],
                        'in_stock'       => true,
                        'available_soon' => false,
                        'productPrices'  => [
                            'currency_code' => 'IRR',
                            'price'         => 120000,
                        ],
                    ],
                    [
                        'base_name' => [
                            'en' => 'MacBook Pro 16"',
                            'fa' => 'مک‌بوک پرو 16 اینچ',
                        ],
                        'base_description' => [
                            'en' => 'High-performance laptop with Apple M2 Max chip, ideal for creative professionals',
                            'fa' => 'لپ‌تاپ با عملکرد بالا و تراشه Apple M2 Max، مناسب برای حرفه‌ای‌های خلاق',
                        ],
                        'in_stock'       => true,
                        'available_soon' => true,
                        'productPrices'  => [
                            'currency_code' => 'IRR',
                            'price'         => 145000,
                        ],
                    ],
                ],
            ],
            [
                'base_name' => [
                    'en' => 'Desktops',
                    'fa' => 'کامپیوتر رومیزی',
                ],
                'base_description' => [
                    'en' => 'Stationary computers suitable for home and office use',
                    'fa' => 'کامپیوترهای ثابت مناسب استفاده خانگی و اداری',
                ],
                'status'   => true,
                'products' => [
                    [
                        'base_name' => [
                            'en' => 'HP Pavilion Gaming Desktop',
                            'fa' => 'اچ‌پی پاویلیون گیمینگ دسکتاپ',
                        ],
                        'base_description' => [
                            'en' => 'Gaming desktop with NVIDIA RTX 3060 and Intel i5 processor',
                            'fa' => 'کامپیوتر رومیزی مخصوص بازی با کارت گرافیک NVIDIA RTX 3060 و پردازنده Intel i5',
                        ],
                        'in_stock'       => true,
                        'available_soon' => true,
                        'productPrices'  => [
                            'currency_code' => 'IRR',
                            'price'         => 98000,
                        ],
                    ],
                    [
                        'base_name' => [
                            'en' => 'Apple iMac 24"',
                            'fa' => 'اپل آی‌مک 24 اینچ',
                        ],
                        'base_description' => [
                            'en' => 'All-in-one desktop with M1 chip and 4.5K Retina display',
                            'fa' => 'کامپیوتر رومیزی همه‌کاره با تراشه M1 و نمایشگر Retina با رزولوشن 4.5K',
                        ],
                        'in_stock'       => false,
                        'available_soon' => true,
                        'productPrices'  => [
                            'currency_code' => 'IRR',
                            'price'         => 132000,
                        ],
                    ],
                ],
            ],
            [
                'base_name' => [
                    'en' => 'Computer Components',
                    'fa' => 'قطعات کامپیوتر',
                ],
                'base_description' => [
                    'en' => 'Individual components for building or upgrading PCs',
                    'fa' => 'قطعات جداگانه برای ساخت یا ارتقای کامپیوتر',
                ],
                'status'   => true,
                'products' => [
                    [
                        'base_name' => [
                            'en' => 'NVIDIA GeForce RTX 4070',
                            'fa' => 'ان‌ویدیا GeForce RTX 4070',
                        ],
                        'base_description' => [
                            'en' => 'High-end graphics card for gaming and rendering',
                            'fa' => 'کارت گرافیک رده بالا برای بازی و رندرینگ',
                        ],
                        'in_stock'       => true,
                        'available_soon' => true,
                        'productPrices'  => [
                            'currency_code' => 'IRR',
                            'price'         => 87000,
                        ],
                    ],
                    [
                        'base_name' => [
                            'en' => 'Corsair Vengeance 32GB RAM',
                            'fa' => 'کورسیر Vengeance 32 گیگابایت رم',
                        ],
                        'base_description' => [
                            'en' => 'High-speed memory module for desktops and gaming PCs',
                            'fa' => 'ماژول حافظه پرسرعت برای کامپیوترهای رومیزی و گیمینگ',
                        ],
                        'in_stock'       => true,
                        'available_soon' => false,
                        'productPrices'  => [
                            'currency_code' => 'IRR',
                            'price'         => 36000,
                        ],
                    ],
                ],
            ],
            [
                'base_name' => [
                    'en' => 'Peripherals',
                    'fa' => 'لوازم جانبی',
                ],
                'base_description' => [
                    'en' => 'External devices such as keyboards, mice, and monitors',
                    'fa' => 'دستگاه‌های جانبی مانند کیبورد، ماوس و مانیتورها',
                ],
                'status'   => true,
                'products' => [
                    [
                        'base_name' => [
                            'en' => 'Logitech MX Master 3 Mouse',
                            'fa' => 'لوجیتک MX Master 3 ماوس',
                        ],
                        'base_description' => [
                            'en' => 'Ergonomic wireless mouse with customizable buttons',
                            'fa' => 'ماوس بی‌سیم ارگونومیک با دکمه‌های قابل تنظیم',
                        ],
                        'in_stock'       => true,
                        'available_soon' => true,
                        'productPrices'  => [
                            'currency_code' => 'IRR',
                            'price'         => 15000,
                        ],
                    ],
                    [
                        'base_name' => [
                            'en' => 'Dell UltraSharp 27" Monitor',
                            'fa' => 'دل UltraSharp مانیتور 27 اینچ',
                        ],
                        'base_description' => [
                            'en' => 'High-resolution monitor with vibrant colors and wide viewing angles',
                            'fa' => 'مانیتور با رزولوشن بالا، رنگ‌های زنده و زاویه دید گسترده',
                        ],
                        'in_stock'       => true,
                        'available_soon' => false,
                        'productPrices'  => [
                            'currency_code' => 'IRR',
                            'price'         => 47000,
                        ],
                    ],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $categoryName = $this->buildTranslations($categoryData['base_name'], $locales);
            $categoryDescription = $this->buildTranslations($categoryData['base_description'], $locales);

            $category = ProductCategory::query()->updateOrCreate(
                ['slug' => Str::slug($categoryName['en'])],
                [
                    'name'        => $categoryName,
                    'description' => $categoryDescription,
                    'status'      => $categoryData['status'],
                ],
            );

            foreach ($categoryData['products'] as $productData) {
                $productName = $this->buildTranslations($productData['base_name'], $locales);
                $productDescription = $this->buildTranslations($productData['base_description'], $locales);

                $product = $category->products()->updateOrCreate(
                    ['slug' => Str::slug($productName['en'])],
                    [
                        'name'           => $productName,
                        'description'    => $productDescription,
                        'in_stock'       => $productData['in_stock'],
                        'available_soon' => $productData['available_soon'],
                    ],
                );

                $product->productPrices()->updateOrCreate(
                    ['currency_code' => $productData['productPrices']['currency_code']],
                    ['price' => $productData['productPrices']['price']],
                );
            }
        }

        $this->command?->info("Products seeded successfully for {$tenant->slug} tenant.");
    }

    /**
     * @param  array<string, string>  $baseTranslations
     * @param  array<int, string>  $locales
     * @return array<string, string>
     */
    private function buildTranslations(array $baseTranslations, array $locales, string $fallback = 'en'): array
    {
        $translations = [];

        foreach ($locales as $locale) {
            $translations[$locale] = $baseTranslations[$locale] ?? $baseTranslations[$fallback] ?? '';
        }

        return $translations;
    }
}
