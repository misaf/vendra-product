<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductPrices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Misaf\VendraCurrency\Models\Currency;

final class ProductPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('currency_code')
                    ->columnSpanFull()
                    ->default(
                        Currency::query()
                            ->where('status', true)
                            ->where('is_default', true)
                            ->value('iso_code')
                    )
                    ->label(__('vendra-currency::navigation.currency'))
                    ->native(false)
                    ->options(
                        Currency::query()
                            ->where('status', true)
                            ->orderBy('position', 'desc')
                            ->pluck('name', 'iso_code')
                    )
                    ->preload()
                    ->required()
                    ->searchable(),

                TextInput::make('price')
                    ->autofocus()
                    ->columnSpanFull()
                    ->label(__('vendra-product::attributes.price'))
                    ->live(onBlur: true)
                    ->mask(RawJs::make('$money($input)'))
                    ->numeric()
                    ->required()
                    ->stripCharacters(','),
            ]);
    }
}
