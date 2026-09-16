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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Livewire\Component as Livewire;
use Misaf\VendraMultimedia\Filament\Tables\Columns\ModelImageColumn;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;
use Misaf\VendraSupport\Filament\Concerns\HasDefaultAvatarImageUrl;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedTableRecords;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\DescriptionColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsActiveToggleColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\SlugColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\UpdatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\IsActiveConstraint;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\PositionConstraint;

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
            RowIndexColumn::make(),

            ModelImageColumn::make()
                ->collection(ProductCategory::MEDIA_COLLECTION)
                ->defaultImageUrl(fn (ProductCategory $record, Livewire $livewire): string => self::defaultAvatarImageUrl(self::translatedAttribute($record, 'name', $livewire))),

            BadgeableColumn::make('name')
                ->alignStart()
                ->label(__('vendra-product::attributes.name'))
                ->icon(Heroicon::Tag)
                ->suffixBadges([
                    Badge::make('count')
                        ->label(fn (ProductCategory $record): string => (string) Number::format(self::integerAttribute($record, 'products_count')))
                        ->size(Size::Small),
                ])
                ->suffix(''),

            DescriptionColumn::make()
                ->state(fn (ProductCategory $record, Livewire $livewire): string => self::translatedAttribute($record, 'description', $livewire)),

            SlugColumn::make(),

            IsActiveToggleColumn::make(),

            CreatedAtColumn::make(),

            UpdatedAtColumn::make(),
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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('products'))
            ->columns($columns)
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            IsActiveConstraint::make(),

                            PositionConstraint::make(),
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
