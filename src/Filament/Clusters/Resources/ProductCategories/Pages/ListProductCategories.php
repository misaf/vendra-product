<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\ProductCategoryResource;

final class ListProductCategories extends ListRecords
{
    use Translatable;

    protected static string $resource = ProductCategoryResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/list-records.breadcrumb') . ' ' . __('vendra-product::navigation.product_category');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            LocaleSwitcher::make(),
        ];
    }
}
