<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;
use Misaf\VendraSupport\Filament\Concerns\RendersRichContent;

final class ProductCategoryInfolist
{
    use RendersRichContent;

    public static function configure(Schema $schema): Schema
    {
        /** @var list<Component> $components */
        $components = [
            TextEntry::make('name')->label(__('vendra-product::attributes.name')),
            TextEntry::make('slug')->label(__('vendra-product::attributes.slug')),
            IconEntry::make('active')
                ->boolean()
                ->label(__('vendra-product::attributes.active')),
            TextEntry::make('description')
                ->columnSpanFull()
                ->formatStateUsing(fn(array|string|null $state): string => self::renderRichContent($state))
                ->html()
                ->label(__('vendra-product::attributes.description')),
            SpatieMediaLibraryImageEntry::make('image')
                ->collection(ProductCategory::MEDIA_COLLECTION)
                ->columnSpanFull()
                ->label(__('vendra-product::attributes.image')),
            self::dateEntry('created_at'),
            self::dateEntry('updated_at'),
        ];

        if (AttributeIntegration::isAvailable()) {
            $components[] = RepeatableEntry::make('attributeValues')
                ->state(fn(ProductCategory $record): Collection => $record->attributeValues()->with('attribute')->get())
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
                fn(TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn(TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
