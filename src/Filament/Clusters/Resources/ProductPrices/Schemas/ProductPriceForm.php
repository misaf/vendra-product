<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductPrices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Livewire\Component as Livewire;
use Misaf\VendraProduct\Models\ProductPrice;

final class ProductPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('currency_code')
                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.currency_code'))
                    ->columnSpanFull()
                    ->default(fn(): string => ProductPrice::defaultCurrencyCode())
                    ->label(__('vendra-product::attributes.currency'))
                    ->live()
                    ->native(false)
                    ->options(fn(): array => ProductPrice::currencyOptions())
                    ->preload()
                    ->required()
                    ->searchable(),

                TextInput::make('price')
                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.price'))
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
