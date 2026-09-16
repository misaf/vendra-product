<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection;
use Misaf\VendraMultimedia\Filament\Infolists\Components\ModelImageEntry;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;
use Misaf\VendraSupport\Filament\Infolists\Components\DescriptionEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\NameEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\SlugEntry;

final class ProductCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        /** @var list<Component> $components */
        $components = [
            NameEntry::make(),
            SlugEntry::make(),
            IconEntry::make('active')
                ->boolean()
                ->label(__('vendra-product::attributes.active')),
            DescriptionEntry::make()
                ->richContent(),
            ModelImageEntry::make()
                ->collection(ProductCategory::MEDIA_COLLECTION),
            self::dateEntry('created_at'),
            self::dateEntry('updated_at'),
        ];

        if (AttributeIntegration::isAvailable()) {
            $components[] = RepeatableEntry::make('attributeValues')
                ->state(fn (ProductCategory $record): Collection => $record->attributeValues()->with('attribute')->get())
                ->columnSpanFull()
                ->columns(2)
                ->label(__('vendra-product::attributes.attributes'))
                ->schema([
                    TextEntry::make('attribute.name')
                        ->label(__('vendra-product::attributes.attribute')),
                    TextEntry::make('value')
                        ->label(__('vendra-product::attributes.attribute_value')),
                ]);
        }

        return $schema
            ->components($components)
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-product::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
