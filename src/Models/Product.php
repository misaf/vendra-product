<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraProduct\Observers\ProductObserver;
use Misaf\VendraTenant\Traits\BelongsToTenant;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
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
 * @property Carbon $deleted_at
 */
#[ObservedBy([ProductObserver::class])]
final class Product extends Model implements HasMedia, Sortable
{
    use BelongsToTenant;

    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use HasTranslations;
    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;
    use SortableTrait;

    /**
     * @var list<string>
     */
    public array $translatable = ['name', 'description', 'slug'];

    protected $casts = [
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

    protected $fillable = [
        'product_category_id',
        'name',
        'description',
        'slug',
        'quantity',
        'stock_threshold',
        'in_stock',
        'position',
        'available_soon',
        'availability_date',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $product): string {
            return $product->token = self::generateToken();
        });
    }

    private static function generateToken(): string
    {
        $tokenCharacters = self::tokenCharacters();
        $tokenLength = self::tokenLength();
        $tokenRepeatCount = self::tokenRepeatCount($tokenCharacters, $tokenLength);

        return mb_substr(str_shuffle(str_repeat($tokenCharacters, $tokenRepeatCount)), 0, $tokenLength);
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

    private static function tokenRepeatCount(string $tokenCharacters, int $tokenLength): int
    {
        $configuredRepeatCount = max(Config::integer('vendra-product.token_generator_repeat_count', 9), 1);
        $minimumRepeatCount = (int) ceil($tokenLength / mb_strlen($tokenCharacters));

        return max($configuredRepeatCount, $minimumRepeatCount);
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

    public function registerMediaCollections(): void
    {
        $this->addMediaConversion('thumb-table')
            ->width(48)
            ->format('webp');

        $this->addMediaConversion('small')
            ->width(300)
            ->format('webp');

        $this->addMediaConversion('medium')
            ->width(500)
            ->format('webp');

        $this->addMediaConversion('large')
            ->width(800)
            ->format('webp');

        $this->addMediaConversion('extra-large')
            ->width(1200)
            ->format('webp');
    }

    public function registerMediaConversions(?Media $media = null): void {}

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->preventOverwrite();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logExcept(['id']);
    }
}
