<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;

final class SetColumnPriceAction
{
    public static function make(): Action
    {
        return Action::make('setPrice')
            ->requiresConfirmation()
            ->schema([
                Select::make('currency_code')
                    ->columnSpanFull()
                    ->default(fn(): string => ProductPrice::defaultCurrencyCode())
                    ->label(__('vendra-product::attributes.currency'))
                    ->native(false)
                    ->options(fn(): array => ProductPrice::currencyOptions())
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
            ])
            ->action(function (Product $record, array $data): void {
                $currencyCode = (string) $data['currency_code'];

                $record->productPrices()->create([
                    'currency_code' => $currencyCode,
                    'price'         => ProductPrice::toMinorUnits($currencyCode, (float) $data['price']),
                ]);
            });
    }
}
