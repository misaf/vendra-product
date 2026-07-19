<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Schemas;

use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraProduct\Models\ProductCategory;

final class ProductCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label(__('vendra-product::attributes.name')),
                TextEntry::make('slug')->label(__('vendra-product::attributes.slug')),
                IconEntry::make('status')
                    ->boolean()
                    ->label(__('vendra-product::attributes.status')),
                TextEntry::make('description')
                    ->columnSpanFull()
                    ->formatStateUsing(fn(array|string|null $state): RichContentRenderer => self::renderRichContent($state))
                    ->html()
                    ->label(__('vendra-product::attributes.description')),
                SpatieMediaLibraryImageEntry::make('image')
                    ->collection(ProductCategory::MEDIA_COLLECTION)
                    ->columnSpanFull()
                    ->label(__('vendra-product::attributes.image')),
                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
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

    /** @param array<array-key, mixed>|string|null $state */
    private static function renderRichContent(array|string|null $state): RichContentRenderer
    {
        if ( ! is_array($state)) {
            return RichContentRenderer::make($state);
        }

        $content = [];

        foreach ($state as $key => $value) {
            $content[(string) $key] = $value;
        }

        return RichContentRenderer::make($content);
    }
}
