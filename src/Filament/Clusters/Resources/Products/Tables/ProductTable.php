<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\Products\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Layout\Component as LayoutComponent;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Component as Livewire;
use Misaf\VendraMultimedia\Filament\Tables\Columns\ModelImageColumn;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\DuplicateProductTableAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\InStockBulkAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\OutOfStockBulkAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\SetPriceBulkAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\SetPriceByPercentageBulkAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\SetPriceTableAction;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Filament\Concerns\HasDefaultAvatarImageUrl;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedTableRecords;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\DescriptionColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsActiveToggleColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\NameColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\SlugColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\UpdatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\PositionConstraint;
use Misaf\VendraTagger\Filament\Tables\Columns\ModelTagsColumn;

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
            RowIndexColumn::make(),

            ModelImageColumn::make()
                ->collection(Product::MEDIA_COLLECTION)
                ->defaultImageUrl(fn (Product $record, Livewire $livewire): string => self::defaultAvatarImageUrl(self::translatedAttribute($record, 'name', $livewire))),

            NameColumn::make()
                ->description(function (Product $record, Livewire $livewire): View {
                    $productCategory = $record->productCategory;
                    $stockThreshold = $record->getAttribute('stock_threshold');
                    $badges = [
                        __('vendra-product::attributes.quantity').': '.Number::format($record->quantity),
                        __('vendra-product::attributes.stock_threshold').': '.(
                            is_numeric($stockThreshold) ? Number::format((int) $stockThreshold) : '—'
                        ),
                    ];

                    if ($productCategory !== null) {
                        array_unshift(
                            $badges,
                            static::translatedAttribute($productCategory, 'name', $livewire),
                        );
                    }

                    return view('vendra-product::filament.tables.columns.product-badges', [
                        'badges' => $badges,
                    ]);
                }),

            DescriptionColumn::make()
                ->state(fn (Product $record, Livewire $livewire): string => self::translatedAttribute($record, 'description', $livewire)),

            SlugColumn::make(),

            TextColumn::make('token')
                ->alignCenter()
                ->badge()
                ->copyable()
                ->copyMessage(__('vendra-product::messages.token_copied'))
                ->copyMessageDuration(1500)
                ->extraCellAttributes(['dir' => 'ltr'])
                ->formatStateUsing(fn (string $state): string => Str::of($state)->split(3)->implode(' '))
                ->label(__('vendra-product::attributes.token'))
                ->icon(Heroicon::Key)
                ->searchable(isGlobal: true),

            TextColumn::make('latestProductPrice.price')
                ->label(__('vendra-product::attributes.price'))
                ->state(fn (Product $record): string => $record->latestProductPrice?->formattedPrice() ?? '')
                ->action(SetPriceTableAction::make())
                ->summarize([Sum::make(), Average::make(), Range::make()]),

            IsActiveToggleColumn::make('in_stock')
                ->label(__('vendra-product::attributes.in_stock')),

            IsActiveToggleColumn::make('available_soon')
                ->label(__('vendra-product::attributes.available_soon')),

            TextColumn::make('availability_date')
                ->alignCenter()
                ->badge()
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-product::attributes.availability_date'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn (TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn (TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),

            CreatedAtColumn::make(),

            UpdatedAtColumn::make(),
        ];

        if (AttributeIntegration::isAvailable()) {
            $columns[] = TextColumn::make('selected_attribute_values_count')
                ->badge()
                ->counts('selectedAttributeValues')
                ->label(__('vendra-product::attributes.attributes'))
                ->toggleable(isToggledHiddenByDefault: true);
        }

        if (TagIntegration::isAvailable()) {
            $columns[] = ModelTagsColumn::make()
                ->type(Product::TAG_TYPE);
        }

        return $table
            ->description(__('vendra-product::tables.description.products'))
            ->emptyStateHeading(__('vendra-product::tables.empty_state.heading.products'))
            ->emptyStateDescription(__('vendra-product::tables.empty_state.description.products'))
            ->emptyStateIcon(Heroicon::OutlinedCube)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('productCategory'))
            ->columns($columns)
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            RelationshipConstraint::make('productCategory')
                                ->label(__('vendra-product::navigation.product_category'))
                                ->selectable(
                                    IsRelatedToOperator::make()
                                        ->getOptionLabelFromRecordUsing(fn (ProductCategory $record, Livewire $livewire): string => self::translatedAttribute($record, 'name', $livewire))
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

                            PositionConstraint::make(),
                        ]),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    EditAction::make(),

                    DuplicateProductTableAction::make(),

                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    InStockBulkAction::make(),

                    OutOfStockBulkAction::make(),

                    SetPriceBulkAction::make(),

                    SetPriceByPercentageBulkAction::make(),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort(column: 'id', direction: 'desc')
            ->reorderable(column: 'position', direction: 'desc');
    }
}
