<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Schemas\ProductCategoryForm;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedFormFields;
use Misaf\VendraSupport\Support\AttributeIntegration;
use Misaf\VendraSupport\Support\TagIntegration;
use Misaf\VendraSupport\Support\TenantAwareness;

final class ProductForm
{
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
                                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.product_category_id'))
                                    ->columnSpanFull()
                                    ->label(__('vendra-product::navigation.product_category'))
                                    ->live()
                                    ->native(false)
                                    ->preload()
                                    ->relationship('productCategory', 'name')
                                    ->required()
                                    ->searchable()
                                    ->createOptionForm(fn(Schema $schema): Schema => ProductCategoryForm::configure($schema)),

                                TextInput::make('name')
                                    ->afterStateUpdated(function (Livewire $livewire, Get $get, Set $set, ?string $old, ?string $state): void {
                                        $livewire->validateOnly('data.name');

                                        if (($get->string('slug', isNullable: true) ?? '') === Str::slug($old ?? '')) {
                                            $set('slug', Str::slug($state ?? ''));
                                        }
                                    })
                                    ->autofocus()
                                    ->columnSpan(['lg' => 1])
                                    ->label(__('vendra-product::attributes.name'))
                                    ->live(onBlur: true)
                                    ->maxLength(255)
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
                                    ->live(onBlur: true)
                                    ->maxLength(255)
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
                                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.currency_code'))
                                    ->columnSpan(['lg' => 1])
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
                                    ->live(onBlur: true)
                                    ->numeric(),

                                TextInput::make('stock_threshold')
                                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.stock_threshold'))
                                    ->columnSpan(['lg' => 1])
                                    ->helperText(__('vendra-product::attributes.stock_threshold_helper_text'))
                                    ->label(__('vendra-product::attributes.stock_threshold'))
                                    ->live(onBlur: true)
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
                                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.availability_date'))
                                    ->closeOnDateSelection()
                                    ->columnSpan(['lg' => 1])
                                    ->displayFormat('Y-m-d H:i')
                                    ->firstDayOfWeek(6)
                                    ->label(__('vendra-product::attributes.availability_date'))
                                    ->live()
                                    ->minDate(now())
                                    ->native(false)
                                    ->seconds(false)
                                    ->visible(fn(Get $get): bool => true === $get->boolean('available_soon')),

                                Toggle::make('in_stock')
                                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.in_stock'))
                                    ->columnSpanFull()
                                    ->default(false)
                                    ->inline(false)
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
                                SpatieMediaLibraryFileUpload::make('image')
                                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.image'))
                                    ->collection(Product::MEDIA_COLLECTION)
                                    ->columnSpanFull()
                                    ->image()
                                    ->label(__('vendra-product::attributes.image'))
                                    ->live()
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
                    CheckboxList::make('selectedAttributeValues')
                        ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.selectedAttributeValues'))
                        ->relationship(
                            titleAttribute: 'value',
                            modifyQueryUsing: fn(Builder $query, Get $get): Builder => $query
                                ->with('attribute')
                                ->where('attributable_type', (new ProductCategory())->getMorphClass())
                                ->where('attributable_id', $get->integer('product_category_id'))
                                ->orderBy('position'),
                        )
                        ->bulkToggleable()
                        ->columns(2)
                        ->getOptionLabelFromRecordUsing(function (Model $record): string {
                            $attributeName = (string) $record->getAttribute('attribute')?->getAttribute('name');
                            $value = (string) $record->getAttribute('value');

                            return '' === $attributeName ? $value : "{$attributeName}: {$value}";
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
        if ( ! TagIntegration::isAvailable()) {
            return [];
        }

        return [
            Tab::make('tags')
                ->icon(Heroicon::OutlinedTag)
                ->label(__('vendra-support::attributes.tags'))
                ->schema([
                    SpatieTagsInput::make('tags')
                        ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.tags'))
                        ->label(__('vendra-support::attributes.tags'))
                        ->live()
                        ->type(Product::TAG_TYPE)
                        ->reorderable(),
                ]),
        ];
    }
}
