<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component as Livewire;
use Misaf\VendraMultimedia\Filament\Forms\Components\ModelImageUpload;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Schemas\ProductCategoryForm;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Filament\Forms\Components\DescriptionRichEditor;
use Misaf\VendraSupport\Filament\Forms\Components\SluggableNameInput;
use Misaf\VendraSupport\Filament\Forms\Components\SlugInput;
use Misaf\VendraTagger\Filament\Forms\Components\ModelTagsInput;

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
                            ->label(__('vendra-product::attributes.general'))
                            ->schema([
                                Select::make('product_category_id')
                                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.product_category_id'))
                                    ->columnSpanFull()
                                    ->label(__('vendra-product::navigation.product_category'))
                                    ->live()
                                    ->native(false)
                                    ->preload()
                                    ->relationship('productCategory', 'name')
                                    ->required()
                                    ->searchable()
                                    ->createOptionForm(fn (Schema $schema): Schema => ProductCategoryForm::configure($schema)),

                                SluggableNameInput::make()
                                    ->uniqueWithinTenant(perLocale: true),

                                SlugInput::make()
                                    ->uniqueWithinTenant(perLocale: true),

                                DescriptionRichEditor::make(),
                            ]),
                        Tab::make('pricing')
                            ->columns(2)
                            ->icon(Heroicon::OutlinedCurrencyDollar)
                            ->label(__('vendra-product::attributes.pricing'))
                            ->schema([
                                Select::make('currency_code')
                                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.currency_code'))
                                    ->columnSpan(['lg' => 1])
                                    ->default(fn (): string => ProductPrice::defaultCurrencyCode())
                                    ->label(__('vendra-product::attributes.currency'))
                                    ->live()
                                    ->native(false)
                                    ->options(fn (): array => ProductPrice::currencyOptions())
                                    ->preload()
                                    ->required()
                                    ->searchable(),

                                TextInput::make('price')
                                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.price'))
                                    ->autofocus()
                                    ->columnSpan(['lg' => 1])
                                    ->label(__('vendra-product::attributes.price'))
                                    ->live(onBlur: true)
                                    ->mask(RawJs::make('$money($input)'))
                                    ->numeric()
                                    ->required()
                                    ->stripCharacters(','),

                                TextInput::make('quantity')
                                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.quantity'))
                                    ->columnSpan(['lg' => 1])
                                    ->label(__('vendra-product::attributes.quantity'))
                                    ->live(onBlur: true)
                                    ->numeric()
                                    ->required(),

                                TextInput::make('stock_threshold')
                                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.stock_threshold'))
                                    ->columnSpan(['lg' => 1])
                                    ->helperText(__('vendra-product::attributes.stock_threshold_helper_text'))
                                    ->label(__('vendra-product::attributes.stock_threshold'))
                                    ->live(onBlur: true)
                                    ->numeric(),

                                Toggle::make('available_soon')
                                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.available_soon'))
                                    ->columnSpan(['lg' => 1])
                                    ->default(false)
                                    ->label(__('vendra-product::attributes.available_soon'))
                                    ->live()
                                    ->onIcon(Heroicon::Bolt)
                                    ->required()
                                    ->rules([
                                        'boolean',
                                    ]),

                                DateTimePicker::make('availability_date')
                                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.availability_date'))
                                    ->closeOnDateSelection()
                                    ->columnSpan(['lg' => 1])
                                    ->displayFormat('Y-m-d H:i')
                                    ->firstDayOfWeek(6)
                                    ->label(__('vendra-product::attributes.availability_date'))
                                    ->live()
                                    ->minDate(now())
                                    ->native(false)
                                    ->seconds(false)
                                    ->visible(fn (Get $get): bool => $get->boolean('available_soon') === true),

                                Toggle::make('in_stock')
                                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.in_stock'))
                                    ->columnSpanFull()
                                    ->default(false)
                                    ->label(__('vendra-product::attributes.in_stock'))
                                    ->live()
                                    ->onIcon(Heroicon::Bolt)
                                    ->required()
                                    ->rules([
                                        'boolean',
                                    ]),
                            ]),
                        ...self::attributeTabs(),
                        ...self::tagTab(),
                        Tab::make('photos')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->label(__('vendra-product::attributes.photos'))
                            ->schema([
                                ModelImageUpload::make()
                                    ->collection(Product::MEDIA_COLLECTION)
                                    ->imageEditor()
                                    ->multiple(),
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
        if (! AttributeIntegration::isAvailable()) {
            return [];
        }

        return [
            Tab::make('attributes')
                ->columns(1)
                ->icon(Heroicon::OutlinedListBullet)
                ->label(__('vendra-product::attributes.attributes'))
                ->schema([
                    CheckboxList::make('selectedAttributeValues')
                        ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.selectedAttributeValues'))
                        ->relationship(
                            titleAttribute: 'value',
                            modifyQueryUsing: fn (Builder $query, Get $get): Builder => $query
                                ->with('attribute')
                                ->where('attributable_type', (new ProductCategory)->getMorphClass())
                                ->where('attributable_id', $get->integer('product_category_id'))
                                ->orderBy('position'),
                        )
                        ->bulkToggleable()
                        ->columns(2)
                        ->getOptionLabelFromRecordUsing(function (Model $record): string {
                            $attributeName = (string) $record->getAttribute('attribute')?->getAttribute('name');
                            $value = (string) $record->getAttribute('value');

                            return $attributeName === '' ? $value : "{$attributeName}: {$value}";
                        })
                        ->helperText(__('vendra-product::attributes.attribute_values_from_category'))
                        ->label(__('vendra-product::attributes.attributes'))
                        ->live(),
                ]),
        ];
    }

    /** @return list<Tab> */
    private static function tagTab(): array
    {
        if (! TagIntegration::isAvailable()) {
            return [];
        }

        return [
            Tab::make('tags')
                ->icon(Heroicon::OutlinedTag)
                ->label(__('vendra-tagger::attributes.tags'))
                ->schema([
                    ModelTagsInput::make()
                        ->type(Product::TAG_TYPE)
                        ->columnSpan(1)
                        ->reorderable(),
                ]),
        ];
    }
}
