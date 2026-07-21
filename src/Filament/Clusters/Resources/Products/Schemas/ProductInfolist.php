<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\SpatieTagsEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraSupport\Filament\Concerns\RendersRichContent;
use Misaf\VendraSupport\Support\AttributeIntegration;
use Misaf\VendraSupport\Support\TagIntegration;

final class ProductInfolist
{
    use RendersRichContent;

    public static function configure(Schema $schema): Schema
    {
        /** @var list<Component> $components */
        $components = [
            TextEntry::make('productCategory.name')
                ->label(__('vendra-product::navigation.product_category')),
            TextEntry::make('name')->label(__('vendra-product::attributes.name')),
            TextEntry::make('slug')->label(__('vendra-product::attributes.slug')),
            TextEntry::make('token')
                ->copyable()
                ->label(__('vendra-product::attributes.token')),
            TextEntry::make('latestProductPrice')
                ->label(__('vendra-product::attributes.price'))
                ->state(fn(Product $record): ?string => $record->latestProductPrice?->formattedPrice()),
            TextEntry::make('quantity')->label(__('vendra-product::attributes.quantity')),
            TextEntry::make('stock_threshold')->label(__('vendra-product::attributes.stock_threshold')),
            IconEntry::make('available_soon')
                ->boolean()
                ->label(__('vendra-product::attributes.available_soon')),
            self::dateEntry('availability_date'),
            IconEntry::make('in_stock')
                ->boolean()
                ->label(__('vendra-product::attributes.in_stock')),
            TextEntry::make('description')
                ->columnSpanFull()
                ->formatStateUsing(fn(array|string|null $state): string => self::renderRichContent($state))
                ->html()
                ->label(__('vendra-product::attributes.description')),
            SpatieMediaLibraryImageEntry::make('image')
                ->collection(Product::MEDIA_COLLECTION)
                ->columnSpanFull()
                ->label(__('vendra-product::attributes.image')),
            self::dateEntry('created_at'),
            self::dateEntry('updated_at'),
        ];

        if (AttributeIntegration::isAvailable()) {
            $components[] = RepeatableEntry::make('selectedAttributeValues')
                ->state(fn(Product $record): Collection => $record->selectedAttributeValues()->with('attribute')->get())
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

        if (TagIntegration::isAvailable()) {
            $components[] = SpatieTagsEntry::make('tags')
                ->columnSpanFull()
                ->label(__('vendra-support::attributes.tags'))
                ->type(Product::TAG_TYPE);
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
