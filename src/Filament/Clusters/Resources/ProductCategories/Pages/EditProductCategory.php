<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\ProductCategoryResource;

final class EditProductCategory extends EditRecord
{
    use Translatable;

    protected static string $resource = ProductCategoryResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/edit-record.breadcrumb') . ' ' . __('vendra-product::navigation.product_category');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            DeleteAction::make(),

            LocaleSwitcher::make(),
        ];
    }
}
