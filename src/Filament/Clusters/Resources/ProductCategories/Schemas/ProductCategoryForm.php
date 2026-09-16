<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Schemas;

use Closure;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Misaf\VendraMultimedia\Filament\Forms\Components\ModelImageUpload;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Capabilities\AttributeIntegration;
use Misaf\VendraSupport\Filament\Forms\Components\IsActiveToggle;
use Misaf\VendraSupport\Filament\Forms\Components\SluggableNameInput;
use Misaf\VendraSupport\Filament\Forms\Components\SlugInput;

final class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SluggableNameInput::make()
                    ->uniqueWithinTenant(perLocale: true),

                SlugInput::make()
                    ->uniqueWithinTenant(perLocale: true),

                RichEditor::make('description')
                    ->columnSpanFull()
                    ->json()
                    ->label(__('vendra-product::attributes.description'))
                    ->required(),

                ModelImageUpload::make()
                    ->collection(ProductCategory::MEDIA_COLLECTION),

                IsActiveToggle::make()
                    ->default(false),

                ...self::attributeComponents(),
            ]);
    }

    /**
     * Attribute values assigned here are inherited by every product in this
     * category.
     *
     * @return list<Section>
     */
    private static function attributeComponents(): array
    {
        if (! AttributeIntegration::isAvailable()) {
            return [];
        }

        return [
            Section::make(__('vendra-product::attributes.attributes'))
                ->columnSpanFull()
                ->icon(Heroicon::OutlinedListBullet)
                ->schema([
                    Repeater::make('attributeValues')
                        ->relationship()
                        ->addActionLabel(__('vendra-product::attributes.add_attribute_value'))
                        ->columnSpanFull()
                        ->columns(3)
                        ->defaultItems(0)
                        ->hiddenLabel()
                        ->orderColumn('position')
                        ->reorderable()
                        ->rule(fn (): Closure => self::distinctAttributeValuePairsRule())
                        ->schema([
                            Select::make('attribute_id')
                                ->label(__('vendra-product::attributes.attribute'))
                                ->native(false)
                                ->options(fn (): array => AttributeIntegration::options())
                                ->preload()
                                ->required()
                                ->searchable(),

                            TextInput::make('value')
                                ->columnSpan(2)
                                ->label(__('vendra-product::attributes.attribute_value'))
                                ->maxLength(255)
                                ->required(),
                        ]),
                ]),
        ];
    }

    /**
     * The repeater must not contain the same attribute/value pair twice; the
     * tenant-scoped unique guard on `attribute_values` enforces the same rule
     * at the database level.
     */
    private static function distinctAttributeValuePairsRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_array($value)) {
                return;
            }

            $pairs = [];

            foreach ($value as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $pairs[] = (Arr::get($item, 'attribute_id', '')).'|'.mb_trim((string) (Arr::get($item, 'value', '')));
            }

            if (count($pairs) !== count(array_unique($pairs))) {
                $fail(__('vendra-product::attributes.duplicate_attribute_values'));
            }
        };
    }
}
