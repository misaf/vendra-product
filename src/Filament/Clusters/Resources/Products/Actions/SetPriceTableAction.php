<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Misaf\VendraProduct\Actions\SetProductPriceAction;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;

final class SetPriceTableAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'setPrice';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->requiresConfirmation()
            ->schema([
                Select::make('currency_code')
                    ->columnSpanFull()
                    ->default(fn (): string => ProductPrice::defaultCurrencyCode())
                    ->label(__('vendra-product::attributes.currency'))
                    ->native(false)
                    ->options(fn (): array => ProductPrice::currencyOptions())
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
                    ->minValue(0)
                    ->required()
                    ->stripCharacters(','),
            ])
            ->action(function (Product $record, array $data): void {
                $currencyCode = Arr::string($data, 'currency_code');
                $price = Arr::get($data, 'price');

                throw_unless(is_numeric($price), InvalidArgumentException::class, 'Invalid price provided.');

                resolve(SetProductPriceAction::class)->execute(
                    $record,
                    $currencyCode,
                    ProductPrice::toMinorUnits($currencyCode, (float) $price),
                );
            });
    }
}
