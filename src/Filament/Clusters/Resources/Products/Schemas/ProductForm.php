<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Illuminate\Support\Str;
use Livewire\Component as Livewire;
use Misaf\VendraCurrency\Models\Currency;

final class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('product-tabs')
                    ->tabs([
                        Tab::make('general')
                            ->columns(2)
                            ->icon(Heroicon::OutlinedCube)
                            ->label(__('General'))
                            ->schema([
                                Select::make('product_category_id')
                                    ->columnSpanFull()
                                    ->label(__('vendra-product::navigation.product_category'))
                                    ->native(false)
                                    ->preload()
                                    ->relationship('productCategory', 'name')
                                    ->required()
                                    ->searchable(),

                                TextInput::make('name')
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                                        if (($get->string('slug', isNullable: true) ?? '') === Str::slug($old ?? '')) {
                                            $set('slug', Str::slug($state ?? ''));
                                        }
                                    })
                                    ->autofocus()
                                    ->columnSpan(['lg' => 1])
                                    ->label(__('vendra-product::attributes.name'))
                                    ->live(onBlur: true)
                                    ->required(),

                                TextInput::make('slug')
                                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly("data.slug"))
                                    ->columnSpan(['lg' => 1])
                                    ->helperText(__('vendra-product::attributes.slug_helper_text'))
                                    ->label(__('vendra-product::attributes.slug'))
                                    ->required(),

                                RichEditor::make('description')
                                    ->columnSpanFull()
                                    ->json()
                                    ->label(__('vendra-product::attributes.description'))
                                    ->required(),
                            ]),
                        Tab::make('pricing')
                            ->columns(2)
                            ->icon(Heroicon::OutlinedCurrencyDollar)
                            ->label(__('Pricing'))
                            ->schema([
                                Select::make('currency_code')
                                    ->columnSpan(['lg' => 1])
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
                                    ->columnSpan(['lg' => 1])
                                    ->label(__('vendra-product::attributes.price'))
                                    ->live(onBlur: true)
                                    ->mask(RawJs::make('$money($input)'))
                                    ->numeric()
                                    ->required()
                                    ->stripCharacters(','),

                                TextInput::make('quantity')
                                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly("data.quantity"))
                                    ->columnSpan(['lg' => 1])
                                    ->label(__('vendra-product::attributes.quantity'))
                                    ->numeric(),

                                TextInput::make('stock_threshold')
                                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly("data.stock_threshold"))
                                    ->columnSpan(['lg' => 1])
                                    ->label(__('vendra-product::attributes.stock_threshold'))
                                    ->numeric(),

                                Toggle::make('available_soon')
                                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly("data.available_soon"))
                                    ->columnSpan(['lg' => 1])
                                    ->default(false)
                                    ->inline(false)
                                    ->label(__('vendra-product::attributes.available_soon'))
                                    ->live()
                                    ->onIcon('heroicon-m-bolt')
                                    ->required()
                                    ->rules([
                                        'boolean',
                                    ]),

                                DateTimePicker::make('availability_date')
                                    ->closeOnDateSelection()
                                    ->columnSpan(['lg' => 1])
                                    ->displayFormat('Y-m-d H:i')
                                    ->firstDayOfWeek(6)
                                    ->label(__('vendra-product::attributes.availability_date'))
                                    ->maxDate(now())
                                    ->native(false)
                                    ->seconds(false)
                                    ->visible(fn(Get $get): bool => true === $get->boolean('available_soon'))
                                    ->unless(
                                        app()->isLocale('fa'),
                                        fn(DateTimePicker $column): DateTimePicker => $column->jalali(),
                                    ),

                                Toggle::make('in_stock')
                                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly("data.in_stock"))
                                    ->columnSpanFull()
                                    ->default(false)
                                    ->inline(false)
                                    ->label(__('vendra-product::attributes.in_stock'))
                                    ->onIcon('heroicon-m-bolt')
                                    ->required()
                                    ->rules([
                                        'boolean',
                                    ]),
                            ]),
                        Tab::make('photos')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->label(__('Photos'))
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('image')
                                    ->collection('products')
                                    ->columnSpanFull()
                                    ->image()
                                    ->label(__('vendra-product::attributes.image'))
                                    ->panelLayout('grid'),
                            ]),
                    ])
                    ->contained(false)
                    ->persistTabInQueryString('products-tab')
            ])
            ->columns(1);
    }
}
