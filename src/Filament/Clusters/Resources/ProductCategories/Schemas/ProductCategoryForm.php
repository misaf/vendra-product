<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Filament\Clusters\Resources\ProductCategories\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraTenant\Models\Tenant;

final class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                        if (($get->string('slug', isNullable: true) ?? '') === Str::slug($old ?? '')) {
                            $set('slug', Str::slug($state ?? ''));
                        }
                    })
                    ->autofocus()
                    ->columnSpan(['lg' => 1])
                    ->label(__('vendra-product::attributes.name'))
                    ->live(onBlur: true)
                    ->required()
                    ->unique(
                        modifyRuleUsing: function (Unique $rule): void {
                            $rule->where('tenant_id', Tenant::current()?->id)
                                ->withoutTrashed();
                        },
                    ),

                TextInput::make('slug')
                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly("data.slug"))
                    ->columnSpan(['lg' => 1])
                    ->helperText(__('vendra-product::attributes.slug_helper_text'))
                    ->label(__('vendra-product::attributes.slug'))
                    ->label(__('vendra-product::attributes.slug'))
                    ->required()
                    ->unique(modifyRuleUsing: fn(Unique $rule) => $rule->withoutTrashed()),

                Textarea::make('description')
                    ->columnSpanFull()
                    ->label(__('vendra-product::attributes.description'))
                    ->rows(5),

                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('products/categories')
                    ->columnSpanFull()
                    ->image()
                    ->label(__('vendra-product::attributes.image'))
                    ->panelLayout('grid'),

                Toggle::make('status')
                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly("data.status"))
                    ->columnSpanFull()
                    ->default(false)
                    ->label(__('vendra-product::attributes.status'))
                    ->onIcon('heroicon-m-bolt')
                    ->required()
                    ->rules([
                        'boolean',
                    ]),
            ]);
    }
}
