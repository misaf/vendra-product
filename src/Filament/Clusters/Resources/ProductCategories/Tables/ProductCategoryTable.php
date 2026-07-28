<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Tables;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Livewire\Component as Livewire;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;
use Misaf\VendraSupport\Filament\Concerns\HasDefaultAvatarImageUrl;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedTableRecords;

final class ProductCategoryTable
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
                ->collection(ProductCategory::MEDIA_COLLECTION)
                ->conversion('thumb-table')
                ->defaultImageUrl(function (ProductCategory $record, Livewire $livewire): string {
                    return static::defaultAvatarImageUrl(static::translatedAttribute($record, 'name', $livewire));
                })
                ->extraImgAttributes(['class' => 'saturate-50', 'loading' => 'lazy'])
                ->label(__('vendra-product::attributes.image'))
                ->stacked(),

            BadgeableColumn::make('name')
                ->alignStart()
                ->label(__('vendra-product::attributes.name'))
                ->icon(Heroicon::Tag)
                ->suffixBadges([
                    Badge::make('count')
                        ->label(fn(ProductCategory $record): string => (string) Number::format(self::integerAttribute($record, 'products_count')))
                        ->size(Size::Small),
                ])
                ->suffix(''),

            TextColumn::make('description')
                ->label(__('vendra-product::attributes.description'))
                ->icon(Heroicon::DocumentText)
                ->state(fn(ProductCategory $record, Livewire $livewire): string => self::translatedAttribute($record, 'description', $livewire))
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('slug')
                ->alignStart()
                ->label(__('vendra-product::attributes.slug'))
                ->icon(Heroicon::Link)
                ->toggleable(isToggledHiddenByDefault: true),

            ToggleColumn::make('active')
                ->label(__('vendra-product::attributes.active'))
                ->onIcon(Heroicon::Bolt),

            TextColumn::make('created_at')
                ->extraCellAttributes(['dir' => 'ltr'])
                ->label(__('vendra-product::attributes.created_at'))
                ->sinceTooltip()
                ->when(
                    app()->isLocale('fa'),
                    fn(TextColumn $column) => $column->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                    fn(TextColumn $column) => $column->dateTime('Y-m-d H:i')
                ),

            TextColumn::make('updated_at')
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
            $columns[] = TextColumn::make('attribute_values_count')
                ->badge()
                ->counts('attributeValues')
                ->label(__('vendra-product::attributes.attributes'))
                ->toggleable(isToggledHiddenByDefault: true);
        }

        return $table
            ->description(__('vendra-product::tables.description.product_categories'))
            ->emptyStateHeading(__('vendra-product::tables.empty_state.heading.product_categories'))
            ->emptyStateDescription(__('vendra-product::tables.empty_state.description.product_categories'))
            ->emptyStateIcon(Heroicon::OutlinedSquares2x2)
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->withCount('products'))
            ->columns($columns)
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            BooleanConstraint::make('active')
                                ->label(__('vendra-product::attributes.active')),

                            NumberConstraint::make('position'),
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
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort(column: 'id', direction: 'desc')
            ->reorderable(column: 'position', direction: 'desc');
    }
}
