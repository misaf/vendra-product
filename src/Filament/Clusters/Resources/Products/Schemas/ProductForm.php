<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
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
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTagFields;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedFormFields;
use Misaf\VendraSupport\Support\AttributeIntegration;
use Misaf\VendraSupport\Support\TenantAwareness;

final class ProductForm
{
    use InteractsWithTagFields;
    use InteractsWithTranslatedFormFields;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('product-tabs')
                    ->tabs([
                        Tab::make('general')
                            ->columns(2)
                            ->icon(Heroicon::OutlinedCube)
                            ->label(__('vendra-product::attributes.general'))
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
                                    ->required()
                                    ->unique(
                                        column: fn(Livewire $livewire): string => 'name->' . self::activeFormLocale($livewire),
                                        modifyRuleUsing: fn(Unique $rule): Unique => TenantAwareness::constrainUniqueRule($rule)
                                            ->withoutTrashed(),
                                    ),

                                TextInput::make('slug')
                                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.slug'))
                                    ->columnSpan(['lg' => 1])
                                    ->helperText(__('vendra-product::attributes.slug_helper_text'))
                                    ->label(__('vendra-product::attributes.slug'))
                                    ->required()
                                    ->unique(
                                        column: fn(Livewire $livewire): string => 'slug->' . self::activeFormLocale($livewire),
                                        modifyRuleUsing: fn(Unique $rule): Unique => TenantAwareness::constrainUniqueRule($rule)
                                            ->withoutTrashed(),
                                    ),

                                RichEditor::make('description')
                                    ->columnSpanFull()
                                    ->json()
                                    ->label(__('vendra-product::attributes.description'))
                                    ->required(),
                            ]),
                        Tab::make('pricing')
                            ->columns(2)
                            ->icon(Heroicon::OutlinedCurrencyDollar)
                            ->label(__('vendra-product::attributes.pricing'))
                            ->schema([
                                Select::make('currency_code')
                                    ->columnSpan(['lg' => 1])
                                    ->default(fn(): string => ProductPrice::defaultCurrencyCode())
                                    ->label(__('vendra-product::attributes.currency'))
                                    ->native(false)
                                    ->options(fn(): array => ProductPrice::currencyOptions())
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
                                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.quantity'))
                                    ->columnSpan(['lg' => 1])
                                    ->label(__('vendra-product::attributes.quantity'))
                                    ->numeric(),

                                TextInput::make('stock_threshold')
                                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.stock_threshold'))
                                    ->columnSpan(['lg' => 1])
                                    ->label(__('vendra-product::attributes.stock_threshold'))
                                    ->numeric(),

                                Toggle::make('available_soon')
                                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.available_soon'))
                                    ->columnSpan(['lg' => 1])
                                    ->default(false)
                                    ->inline(false)
                                    ->label(__('vendra-product::attributes.available_soon'))
                                    ->live()
                                    ->onIcon(Heroicon::Bolt)
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
                                    ->visible(fn(Get $get): bool => true === $get->boolean('available_soon')),

                                Toggle::make('in_stock')
                                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.in_stock'))
                                    ->columnSpanFull()
                                    ->default(false)
                                    ->inline(false)
                                    ->label(__('vendra-product::attributes.in_stock'))
                                    ->onIcon(Heroicon::Bolt)
                                    ->required()
                                    ->rules([
                                        'boolean',
                                    ]),
                            ]),
                        ...self::attributeTabs(),
                        ...self::tagTabs(),
                        Tab::make('photos')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->label(__('vendra-product::attributes.photos'))
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('image')
                                    ->collection(Product::MEDIA_COLLECTION)
                                    ->columnSpanFull()
                                    ->image()
                                    ->label(__('vendra-product::attributes.image'))
                                    ->multiple()
                                    ->panelLayout('grid')
                                    ->responsiveImages(),
                            ]),
                    ])
                    ->contained(false)
                    ->persistTabInQueryString('products-tab'),
            ])
            ->columns(1);
    }

    /** @return list<Tab> */
    private static function attributeTabs(): array
    {
        if ( ! AttributeIntegration::isAvailable()) {
            return [];
        }

        return [
            Tab::make('attributes')
                ->columns(1)
                ->icon(Heroicon::OutlinedListBullet)
                ->label(__('vendra-product::attributes.attributes'))
                ->schema([
                    Repeater::make('attributeValues')
                        ->relationship()
                        ->addActionLabel(__('vendra-product::attributes.add_attribute_value'))
                        ->columnSpanFull()
                        ->columns(3)
                        ->defaultItems(0)
                        ->label(__('vendra-product::attributes.attributes'))
                        ->orderColumn('position')
                        ->reorderable()
                        ->schema([
                            Select::make('attribute_id')
                                ->label(__('vendra-product::attributes.attribute'))
                                ->native(false)
                                ->options(fn(): array => AttributeIntegration::options())
                                ->preload()
                                ->required()
                                ->searchable(),

                            TextInput::make('value')
                                ->columnSpan(2)
                                ->label(__('vendra-product::attributes.attribute_value'))
                                ->maxLength(2048)
                                ->required(),
                        ]),
                ]),
        ];
    }

    /** @return list<Tab> */
    private static function tagTabs(): array
    {
        $tagFields = self::tagFields();

        if ([] === $tagFields) {
            return [];
        }

        return [
            Tab::make('tags')
                ->icon(Heroicon::OutlinedTag)
                ->label(__('vendra-support::attributes.tags'))
                ->schema($tagFields),
        ];
    }

}
