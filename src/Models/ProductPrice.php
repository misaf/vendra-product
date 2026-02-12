<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Misaf\VendraProduct\Database\Factories\ProductPriceFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Znck\Eloquent\Relations;
use Znck\Eloquent\Traits;

/**
 * @property int $id
 * @property int $product_id
 * @property string $currency_code
 * @property Money $price
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
final class ProductPrice extends Model
{
    /** @use HasFactory<ProductPriceFactory> */
    use HasFactory;

    use LogsActivity;
    use SoftDeletes;
    use Traits\BelongsToThrough;

    protected $casts = [
        'id'            => 'integer',
        'product_id'    => 'integer',
        'currency_code' => 'string',
        'price'         => MoneyIntegerCast::class . ':currency_code',
    ];

    protected $fillable = [
        'product_id',
        'currency_code',
        'price',
    ];

    protected function currencyCode(): Attribute
    {
        return Attribute::make(
            set: fn(string $value): string => Str::upper($value),
        );
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return Relations\BelongsToThrough<ProductCategory, $this>
     */
    public function productCategory(): Relations\BelongsToThrough
    {
        return $this->belongsToThrough(ProductCategory::class, Product::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logExcept(['id']);
    }
}
