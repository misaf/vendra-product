<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Misaf\VendraProduct\Database\Factories\ProductPriceFactory;
use Misaf\VendraSupport\Capabilities\CurrencyIntegration;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Money\Currency;
use Money\Exception\UnknownCurrencyException;
use Throwable;
use Znck\Eloquent\Relations;
use Znck\Eloquent\Traits;

/**
 * @property int $id
 * @property int $product_id
 * @property string $currency_code
 * @property Money $price
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['product_id', 'currency_code', 'price'])]
#[UseFactory(ProductPriceFactory::class)]
final class ProductPrice extends Model implements ShouldLogActivity
{
    /** @use HasFactory<ProductPriceFactory> */
    use HasFactory;

    use SoftDeletes;
    use Traits\BelongsToThrough;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id'            => 'integer',
            'product_id'    => 'integer',
            'currency_code' => 'string',
            'price'         => MoneyIntegerCast::class . ':currency_code',
        ];
    }

    public static function defaultCurrencyCode(): string
    {
        $defaultCurrencyCode = Str::upper(CurrencyIntegration::defaultCode());

        if (self::supportsCurrencyCode($defaultCurrencyCode)) {
            return $defaultCurrencyCode;
        }

        $configuredCurrencyCode = Str::upper(Config::string('app.currency', 'USD'));

        return self::supportsCurrencyCode($configuredCurrencyCode) ? $configuredCurrencyCode : 'USD';
    }

    /**
     * @return array<string, string>
     */
    public static function currencyOptions(): array
    {
        $options = [];

        foreach (CurrencyIntegration::options() as $currencyCode => $label) {
            $currencyCode = Str::upper($currencyCode);

            if (self::supportsCurrencyCode($currencyCode)) {
                $options[$currencyCode] = $label;
            }
        }

        if ([] !== $options) {
            return $options;
        }

        $defaultCurrencyCode = self::defaultCurrencyCode();

        return [$defaultCurrencyCode => $defaultCurrencyCode];
    }

    /**
     * The number of minor units in one major unit of the currency (100 for
     * USD); currencies without a known ISO subunit are treated as having none.
     */
    public static function minorUnitsPerMajorUnit(string $currencyCode): int
    {
        try {
            return 10 ** Money::getCurrencies()->subunitFor(new Currency(Str::upper($currencyCode)));
        } catch (Throwable) {
            return 1;
        }
    }

    public static function toMinorUnits(string $currencyCode, float|int|string $amount): int
    {
        return (int) round((float) $amount * self::minorUnitsPerMajorUnit($currencyCode));
    }

    public static function toMajorUnits(string $currencyCode, int $minorUnits): float|int
    {
        $factor = self::minorUnitsPerMajorUnit($currencyCode);

        return 1 === $factor ? $minorUnits : $minorUnits / $factor;
    }

    public static function supportsCurrencyCode(string $currencyCode): bool
    {
        try {
            return Money::isValidCurrency(Str::upper($currencyCode));
        } catch (Throwable) {
            return false;
        }
    }

    public function formattedPrice(): string
    {
        try {
            Money::getCurrencies()->subunitFor($this->price->getCurrency());

            return $this->price->format();
        } catch (UnknownCurrencyException) {
            return Number::format((int) $this->price->getAmount(), locale: 'en') . ' ' . $this->currency_code;
        }
    }

    /**
     * @return Attribute<string, string>
     */
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
}
