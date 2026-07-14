<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use LogicException;
use Misaf\VendraMultimedia\Concerns\HasDefaultMediaConversions;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Observers\ProductObserver;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Support\AttributeIntegration;
use Misaf\VendraSupport\Traits\BelongsToTenant;
use Misaf\VendraSupport\Traits\HasOptionalTags;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $product_category_id
 * @property array<string, string> $name
 * @property array<string, string> $description
 * @property array<string, string> $slug
 * @property string $token
 * @property int $quantity
 * @property int $stock_threshold
 * @property bool $in_stock
 * @property int $position
 * @property bool $available_soon
 * @property Carbon $availability_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['product_category_id', 'name', 'description', 'slug', 'quantity', 'stock_threshold', 'in_stock', 'position', 'available_soon', 'availability_date'])]
#[Hidden(['tenant_id'])]
#[ObservedBy([ProductObserver::class])]
#[UseFactory(ProductFactory::class)]
final class Product extends Model implements HasMedia, Sortable, ShouldLogActivity
{
    use BelongsToTenant;

    use HasDefaultMediaConversions, InteractsWithMedia {
        HasDefaultMediaConversions::registerMediaConversions insteadof InteractsWithMedia;
    }

    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use HasOptionalTags;
    use HasTranslatableSlug;
    use HasTranslations;
    use SoftDeletes;
    use SortableTrait;
    public const string TAG_TYPE = 'product';

    /**
     * @var list<string>
     */
    public array $translatable = ['name', 'description', 'slug'];

    /**
     * Pin sortable behavior regardless of the global `eloquent-sortable`
     * configuration values: order on the `position` column and always assign
     * the next position when creating.
     *
     * Note: `ignore_timestamps` cannot be pinned here because the package reads
     * it directly from config (no per-model override), and it already defaults
     * to `false` both in config and in the package.
     *
     * @var array{order_column_name: string, sort_when_creating: bool}
     */
    public array $sortable = [
        'order_column_name'  => 'position',
        'sort_when_creating' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id'                  => 'integer',
            'tenant_id'           => 'integer',
            'product_category_id' => 'integer',
            'name'                => 'array',
            'description'         => 'array',
            'slug'                => 'array',
            'token'               => 'string',
            'quantity'            => 'integer',
            'stock_threshold'     => 'integer',
            'in_stock'            => 'boolean',
            'position'            => 'integer',
            'available_soon'      => 'boolean',
            'availability_date'   => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        self::creating(function (self $product): string {
            return $product->token = self::generateToken();
        });
    }

    private static function generateToken(): string
    {
        $tokenCharacters = self::tokenCharacters();
        $tokenLength = self::tokenLength();
        $maxIndex = mb_strlen($tokenCharacters) - 1;

        $token = '';

        for ($i = 0; $i < $tokenLength; $i++) {
            $token .= $tokenCharacters[random_int(0, $maxIndex)];
        }

        return $token;
    }

    private static function tokenCharacters(): string
    {
        $tokenCharacters = Config::string('vendra-product.token_generator_characters', '123456789');

        return '' === $tokenCharacters ? '123456789' : $tokenCharacters;
    }

    private static function tokenLength(): int
    {
        return max(Config::integer('vendra-product.token_generator_length', 9), 9);
    }

    /**
     * @return BelongsTo<ProductCategory, $this>
     */
    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * @return HasMany<ProductPrice, $this>
     */
    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    /**
     * @return HasOne<ProductPrice, $this>
     */
    public function latestProductPrice(): HasOne
    {
        return $this->hasOne(ProductPrice::class)->latestOfMany();
    }

    /**
     * @return HasOne<ProductPrice, $this>
     */
    public function oldestProductPrice(): HasOne
    {
        return $this->hasOne(ProductPrice::class)->oldestOfMany();
    }

    /**
     * @return MorphMany<Media, $this>
     */
    public function multimedia(): MorphMany
    {
        return $this->media();
    }

    /** @return MorphMany<Model, $this> */
    public function attributeValues(): MorphMany
    {
        $attributeValueModel = AttributeIntegration::valueModel();

        if (null === $attributeValueModel) {
            throw new LogicException('Install misaf/vendra-attribute to use product attribute values.');
        }

        return $this->morphMany(
            $attributeValueModel,
            'attributable',
            'attributable_type',
            'attributable_id',
            $this->getKeyName(),
        );
    }

    protected function tagType(): string
    {
        return self::TAG_TYPE;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->preventOverwrite();
    }
}
