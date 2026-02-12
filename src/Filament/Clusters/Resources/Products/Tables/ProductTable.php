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
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraProduct\Filament\Clusters\Resources\Concerns\HasDefaultAvatarImageUrl;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\InStockAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\OutOfStockAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\SetPriceAction;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;

final class ProductTable
{
    use HasDefaultAvatarImageUrl;

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
                    return static::defaultAvatarImageUrl($record->getTranslation('name', $livewire->activeLocale));
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
                        ])
                        ->action(function (Product $record, array $data): void {
                            $record->productPrices()->create([
                                'currency_code' => $data['currency_code'],
                                'price'         => $data['price']
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
                    fn(TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', toLatin: true),
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
                    fn(TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', toLatin: true),
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
                    fn(TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', toLatin: true),
                    fn(TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),
        ];

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
                                        ->getOptionLabelFromRecordUsing(function (ProductCategory $record, Livewire $livewire) {
                                            return $record->getTranslation('name', $livewire->activeLocale);
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

                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    InStockAction::make(),

                    OutOfStockAction::make(),

                    SetPriceAction::make(),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort(column: 'position', direction: 'desc')
            ->reorderable(column: 'position', direction: 'desc')
            ->defaultGroup(
                Group::make('productCategory.name')
                    ->label(__('vendra-product::navigation.product_category'))
                    ->getTitleFromRecordUsing(function (Product $record, Livewire $livewire) {
                        return $record->productCategory->getTranslation('name', $livewire->activeLocale);
                    })
            );
    }
}
