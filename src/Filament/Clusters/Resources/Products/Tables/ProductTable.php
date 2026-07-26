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
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\SpatieTagsColumn;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
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
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\DuplicateProductAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\InStockAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\OutOfStockAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\SetColumnPriceAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\SetPriceAction;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Actions\SetPriceByPercentageAction;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Filament\Concerns\HasDefaultAvatarImageUrl;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedTableRecords;
use Misaf\VendraSupport\Support\AttributeIntegration;
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
                ->rowIndex()
                ->sortable(['id']),

            SpatieMediaLibraryImageColumn::make('image')
                ->alignCenter()
                ->collection(Product::MEDIA_COLLECTION)
                ->conversion('thumb-table')
                ->defaultImageUrl(function (Product $record, Livewire $livewire): string {
                    return static::defaultAvatarImageUrl(static::translatedAttribute($record, 'name', $livewire));
                })
                ->extraImgAttributes(['class' => 'saturate-50', 'loading' => 'lazy'])
                ->label(__('vendra-product::attributes.image'))
                ->stacked(),

            TextColumn::make('name')
                ->alignStart()
                ->label(__('vendra-product::attributes.name'))
                ->icon(Heroicon::Tag)
                ->description(function (Product $record, Livewire $livewire): View {
                    $productCategory = $record->productCategory;
                    $stockThreshold = $record->getAttribute('stock_threshold');
                    $badges = [
                        __('vendra-product::attributes.quantity') . ': ' . (
                            is_numeric($record->quantity) ? Number::format((int) $record->quantity) : '—'
                        ),
                        __('vendra-product::attributes.stock_threshold') . ': ' . (
                            is_numeric($stockThreshold) ? Number::format((int) $stockThreshold) : '—'
                        ),
                    ];

                    if (null !== $productCategory) {
                        array_unshift(
                            $badges,
                            static::translatedAttribute($productCategory, 'name', $livewire),
                        );
                    }

                    return view('vendra-product::filament.tables.columns.product-badges', [
                        'badges' => $badges,
                    ]);
                }),

            TextColumn::make('description')
                ->label(__('vendra-product::attributes.description'))
                ->icon(Heroicon::DocumentText)
                ->state(fn(Product $record, Livewire $livewire): string => self::translatedAttribute($record, 'description', $livewire))
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('slug')
                ->alignStart()
                ->label(__('vendra-product::attributes.slug'))
                ->icon(Heroicon::Link)
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
                ->icon(Heroicon::Key)
                ->searchable(isGlobal: true),

            TextColumn::make('latestProductPrice.price')
                ->label(__('vendra-product::attributes.price'))
                ->state(fn(Product $record): string => $record->latestProductPrice?->formattedPrice() ?? '')
                ->action(SetColumnPriceAction::make())
                ->summarize([Sum::make(), Average::make(), Range::make()]),

            ToggleColumn::make('in_stock')
                ->label(__('vendra-product::attributes.in_stock'))
                ->onIcon(Heroicon::Bolt),

            ToggleColumn::make('available_soon')
                ->label(__('vendra-product::attributes.available_soon'))
                ->onIcon(Heroicon::Bolt),

            TextColumn::make('availability_date')
                ->alignCenter()
                ->badge()
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-product::attributes.availability_date'))
                ->sinceTooltip()
                ->when(
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
                ->when(
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
                ->when(
                    app()->isLocale('fa'),
                    fn(TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn(TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),
        ];

        if (AttributeIntegration::isAvailable()) {
            $columns[] = TextColumn::make('selected_attribute_values_count')
                ->badge()
                ->counts('selectedAttributeValues')
                ->label(__('vendra-product::attributes.attributes'))
                ->toggleable(isToggledHiddenByDefault: true);
        }

        if (TagIntegration::isAvailable()) {
            $columns[] = SpatieTagsColumn::make('tags')
                ->label(__('vendra-support::attributes.tags'))
                ->type(Product::TAG_TYPE)
                ->toggleable();
        }

        return $table
            ->description(__('vendra-product::tables.description.products'))
            ->emptyStateHeading(__('vendra-product::tables.empty_state.heading.products'))
            ->emptyStateDescription(__('vendra-product::tables.empty_state.description.products'))
            ->emptyStateIcon(Heroicon::OutlinedCube)
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->with('productCategory'))
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

                            NumberConstraint::make('position'),
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
            ->defaultSort(column: 'id', direction: 'desc')
            ->reorderable(column: 'position', direction: 'desc');
    }
}
