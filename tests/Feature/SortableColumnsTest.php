<?php

declare(strict_types=1);

use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Misaf\VendraPermission\Tests\Support\PermissionModuleTestContext;
use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Pages\ListProductCategories;
use Misaf\VendraProduct\Filament\Clusters\Resources\Products\Pages\ListProducts;
use Misaf\VendraProduct\Models\Product;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    PermissionModuleTestContext::setUpFilamentAdminContext();

    Filament::getPanel('admin')->plugin(
        SpatieTranslatablePlugin::make()->defaultLocales(['en', 'de']),
    );
});

it('sorts the products table by every sortable column following the stored values', function (): void {
    $productCategory = ProductCategoryFactory::new()->createOne();

    $first = ProductFactory::new()->forCategory($productCategory)->createOne();
    $second = ProductFactory::new()->forCategory($productCategory)->createOne();

    expect(livewire(ListProducts::class)->call('loadTable'))
        ->toSortByEverySortableColumn([$first, $second]);
});

it('renders the product badges below its name without lazy loading', function (?int $stockThreshold, string $formattedStockThreshold): void {
    $productCategory = ProductCategoryFactory::new()->createOne([
        'name' => [
            'en' => 'English category',
            'de' => 'German category',
        ],
    ]);
    $product = ProductFactory::new()->forCategory($productCategory)->createOne([
        'quantity'        => 7,
        'stock_threshold' => $stockThreshold,
    ]);

    livewire(ListProducts::class)
        ->set('activeLocale', 'de')
        ->call('loadTable')
        ->assertTableColumnExists(
            'name',
            function (TextColumn $column) use ($formattedStockThreshold): bool {
                $record = $column->getRecord();
                $description = (string) $column->getDescriptionBelow();

                return $record instanceof Product
                    && $record->relationLoaded('productCategory')
                    && 3 === mb_substr_count($description, 'fi-badge-label-ctn')
                    && str_contains($description, 'German category')
                    && str_contains($description, __('vendra-product::attributes.quantity') . ': 7')
                    && str_contains($description, __('vendra-product::attributes.stock_threshold') . ': ' . $formattedStockThreshold);
            },
            $product,
        )
        ->assertTableColumnDoesNotExist('quantity')
        ->assertTableColumnDoesNotExist('stock_threshold');
})->with([
    'numeric stock threshold' => [3, '3'],
    'no stock threshold'      => [null, '—'],
]);

it('sorts the product categories table by every sortable column following the stored values', function (): void {
    $first = ProductCategoryFactory::new()->createOne();
    $second = ProductCategoryFactory::new()->createOne();

    expect(livewire(ListProductCategories::class)->call('loadTable'))
        ->toSortByEverySortableColumn([$first, $second]);
});

it('preloads product counts for category badges', function (): void {
    $category = ProductCategoryFactory::new()->createOne();
    ProductFactory::new()->count(2)->forCategory($category)->create();

    $records = livewire(ListProductCategories::class)
        ->call('loadTable')
        ->instance()
        ->getTableRecords()
        ->getCollection();

    expect($records->firstWhere('id', $category->id)?->getAttribute('products_count'))->toBe(2);

    livewire(ListProductCategories::class)
        ->call('loadTable')
        ->assertTableColumnExists(
            'name',
            function (BadgeableColumn $column): bool {
                $formattedState = (string) $column->formatState('Category');

                return str_contains($formattedState, 'badgeable-column-badge')
                    && str_contains($formattedState, '2');
            },
            $category,
        );
});
