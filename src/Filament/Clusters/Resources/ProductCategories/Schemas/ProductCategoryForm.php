<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Schemas;

use Closure;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedFormFields;
use Misaf\VendraSupport\Support\AttributeIntegration;
use Misaf\VendraSupport\Support\TenantAwareness;

final class ProductCategoryForm
{
    use InteractsWithTranslatedFormFields;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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

                SpatieMediaLibraryFileUpload::make('image')
                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.image'))
                    ->collection(ProductCategory::MEDIA_COLLECTION)
                    ->columnSpanFull()
                    ->image()
                    ->label(__('vendra-product::attributes.image'))
                    ->live()
                    ->panelLayout('grid')
                    ->responsiveImages(),

                Toggle::make('active')
                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly('data.active'))
                    ->columnSpanFull()
                    ->default(false)
                    ->label(__('vendra-product::attributes.active'))
                    ->live()
                    ->onIcon(Heroicon::Bolt)
                    ->required()
                    ->rules([
                        'boolean',
                    ]),

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
        if ( ! AttributeIntegration::isAvailable()) {
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
                        ->rule(fn(): Closure => self::distinctAttributeValuePairsRule())
                        ->schema([
                            Select::make('attribute_id')
                                ->label(__('vendra-product::attributes.attribute'))
                                ->native(false)
                                ->options(fn(): array => AttributeIntegration::options())
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
            if ( ! is_array($value)) {
                return;
            }

            $pairs = [];

            foreach ($value as $item) {
                if ( ! is_array($item)) {
                    continue;
                }

                $pairs[] = ($item['attribute_id'] ?? '') . '|' . mb_trim((string) ($item['value'] ?? ''));
            }

            if (count($pairs) !== count(array_unique($pairs))) {
                $fail(__('vendra-product::attributes.duplicate_attribute_values'));
            }
        };
    }
}
