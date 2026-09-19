<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\DuplicateProductTableAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\ProductResource;

final class ViewProduct extends ViewRecord
{
    use Translatable;

    protected static string $resource = ProductResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/view-record.breadcrumb').' '.__('vendra-product::navigation.product');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            DuplicateProductTableAction::make(),

            LocaleSwitcher::make(),
        ];
    }
}
