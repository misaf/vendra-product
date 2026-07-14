<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Layout\Component as LayoutComponent;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Component as Livewire;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\DuplicateProductAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\InStockAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\OutOfStockAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\SetPriceAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\SetPriceByPercentageAction;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Filament\Concerns\HasDefaultAvatarImageUrl;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedTableRecords;
use Misaf\VendraSupport\Support\AttributeIntegration;
use Misaf\VendraSupport\Support\CurrencyIntegration;
use Misaf\VendraSupport\Support\TagIntegration;

final class ProductTable
{
    use HasDefaultAvatarImageUrl;
    use InteractsWithTranslatedTableRecords;

    public static function configure(Table $table): Table
    {
        /**
         * @var array<int, Column|ColumnGroup|LayoutComponent> $columns
         */
        $columns = [
            TextColumn::make('row')
                ->label('#')
                ->rowIndex(),

            SpatieMediaLibraryImageColumn::make('image')
                ->alignCenter()
                ->collection('products')
                ->conversion('thumb-table')
                ->defaultImageUrl(function (Product $record, Livewire $livewire): string {
                    return static::defaultAvatarImageUrl(static::translatedAttribute($record, 'name', $livewire));
                })
                ->extraImgAttributes(['class' => 'saturate-50', 'loading' => 'lazy'])
                ->label(__('vendra-product::attributes.image'))
                ->stacked(),

            TextColumn::make('name')
                ->alignStart()
                ->label(__('vendra-product::attributes.name')),

            TextColumn::make('slug')
                ->alignStart()
                ->label(__('vendra-product::attributes.slug'))
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('token')
                ->alignCenter()
                ->badge()
                ->copyable()
                ->copyMessage(__('vendra-product::messages.token_copied'))
                ->copyMessageDuration(1500)
                ->extraCellAttributes(['dir' => 'ltr'])
                ->formatStateUsing(function (string $state): string {
                    return Str::of($state)->split(3)->implode(' ');
                })
                ->label(__('vendra-product::attributes.token'))
                ->searchable(isGlobal: true),

            TextColumn::make('latestProductPrice.price')
                ->label(__('vendra-product::attributes.price'))
                ->action(
                    Action::make('setPrice')
                        ->requiresConfirmation()
                        ->schema([
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
                        ])
                        ->action(function (Product $record, array $data): void {
                            $record->productPrices()->create([
                                'currency_code' => $data['currency_code'],
                                'price'         => $data['price'],
                            ]);
                        })
                ),

            TextColumn::make('quantity')
                ->label(__('vendra-product::attributes.quantity'))
                ->numeric(),

            TextColumn::make('stock_threshold')
                ->label(__('vendra-product::attributes.stock_threshold'))
                ->numeric(),

            ToggleColumn::make('in_stock')
                ->label(__('vendra-product::attributes.in_stock'))
                ->onIcon('heroicon-m-bolt'),

            ToggleColumn::make('available_soon')
                ->label(__('vendra-product::attributes.available_soon'))
                ->onIcon('heroicon-m-bolt'),

            TextColumn::make('availability_date')
                ->alignCenter()
                ->badge()
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-product::attributes.availability_date'))
                ->sinceTooltip()
                ->unless(
                    app()->isLocale('fa'),
                    fn(TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn(TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),

            TextColumn::make('created_at')
                ->alignCenter()
                ->badge()
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-product::attributes.created_at'))
                ->sinceTooltip()
                ->toggleable(isToggledHiddenByDefault: true)
                ->unless(
                    app()->isLocale('fa'),
                    fn(TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn(TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),

            TextColumn::make('updated_at')
                ->alignCenter()
                ->badge()
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-product::attributes.updated_at'))
                ->sinceTooltip()
                ->toggleable(isToggledHiddenByDefault: true)
                ->unless(
                    app()->isLocale('fa'),
                    fn(TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn(TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),
        ];

        if (AttributeIntegration::isAvailable()) {
            $columns[] = TextColumn::make('attribute_values_count')
                ->badge()
                ->counts('attributeValues')
                ->label(__('vendra-product::attributes.attributes'))
                ->toggleable(isToggledHiddenByDefault: true);
        }

        if (TagIntegration::isAvailable()) {
            $columns[] = TextColumn::make('tags.name')
                ->badge()
                ->label(__('vendra-product::attributes.tags'))
                ->toggleable();
        }

        return $table
            ->columns($columns)
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            RelationshipConstraint::make('productCategory')
                                ->label(__('vendra-product::navigation.product_category'))
                                ->selectable(
                                    IsRelatedToOperator::make()
                                        ->getOptionLabelFromRecordUsing(function (ProductCategory $record, Livewire $livewire): string {
                                            return static::translatedAttribute($record, 'name', $livewire);
                                        })
                                        ->preload()
                                        ->searchable()
                                        ->titleAttribute('name'),
                                ),

                            TextConstraint::make('token')
                                ->label(__('vendra-product::attributes.token')),

                            NumberConstraint::make('price')
                                ->relationship('productPrices', 'price'),

                            NumberConstraint::make('quantity')
                                ->label(__('vendra-product::attributes.quantity')),

                            NumberConstraint::make('stock_threshold')
                                ->label(__('vendra-product::attributes.stock_threshold')),

                            BooleanConstraint::make('in_stock')
                                ->label(__('vendra-product::attributes.in_stock')),

                            BooleanConstraint::make('available_soon')
                                ->label(__('vendra-product::attributes.available_soon')),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    EditAction::make(),

                    DuplicateProductAction::make(),

                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    InStockAction::make(),

                    OutOfStockAction::make(),

                    SetPriceAction::make(),

                    SetPriceByPercentageAction::make(),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort(column: 'position', direction: 'desc')
            ->reorderable(column: 'position', direction: 'desc')
            ->defaultGroup(
                Group::make('productCategory.name')
                    ->label(__('vendra-product::navigation.product_category'))
                    ->getTitleFromRecordUsing(function (Product $record, Livewire $livewire): string {
                        $productCategory = $record->productCategory;

                        if (null === $productCategory) {
                            return '';
                        }

                        return static::translatedAttribute($productCategory, 'name', $livewire);
                    })
            );
    }
}
