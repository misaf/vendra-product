<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductPrices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Misaf\VendraSupport\Support\CurrencyIntegration;

final class ProductPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('currency_code')
                    ->columnSpanFull()
                    ->default(fn(): string => CurrencyIntegration::defaultCode())
                    ->label(__('vendra-product::attributes.currency'))
                    ->native(false)
                    ->options(fn(): array => CurrencyIntegration::options())
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
