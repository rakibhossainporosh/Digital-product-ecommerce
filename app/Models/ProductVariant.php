<?php

namespace App\Models;

use App\Enums\LicenseKeyStatus;
use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'product_id',
    'duration_name',
    'duration_days',
    'regular_price',
    'offer_price',
    'cost_price',
    'api_provider_id',
    'is_popular',
])]
class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'regular_price' => 'decimal:2',
            'offer_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_popular' => 'boolean',
        ];
    }

    /**
     * Parent product relationship.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * All license keys associated with this duration tier.
     *
     * @return HasMany<LicenseKey, $this>
     */
    public function licenseKeys(): HasMany
    {
        return $this->hasMany(LicenseKey::class);
    }

    /**
     * Available (unsold) license keys.
     *
     * @return HasMany<LicenseKey, $this>
     */
    public function availableLicenseKeys(): HasMany
    {
        return $this->hasMany(LicenseKey::class)->where('status', LicenseKeyStatus::Available);
    }

    /**
     * Reseller custom pricing rules for this variant.
     *
     * @return HasMany<ResellerPrice, $this>
     */
    public function resellerPrices(): HasMany
    {
        return $this->hasMany(ResellerPrice::class);
    }

    /**
     * Orders placed for this specific variant tier.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    /**
     * Get live count of available keys.
     */
    public function getAvailableKeysCountAttribute(): int
    {
        return (int) $this->availableLicenseKeys()->count();
    }

    /**
     * Check if variant has available stock or external API provider available.
     */
    public function getIsInStockAttribute(): bool
    {
        return $this->available_keys_count > 0 || filled($this->api_provider_id);
    }

    /**
     * Get visual stock status badge definition.
     *
     * @return array{label: string, color: string}
     */
    public function getStockBadgeAttribute(): array
    {
        $count = $this->available_keys_count;

        if ($count > 10) {
            return ['label' => "In Stock ({$count})", 'color' => 'success'];
        }

        if ($count > 0) {
            return ['label' => "Low Stock ({$count})", 'color' => 'warning'];
        }

        if (filled($this->api_provider_id)) {
            return ['label' => 'Auto API Stock', 'color' => 'info'];
        }

        return ['label' => 'Out of Stock (0)', 'color' => 'danger'];
    }

    /**
     * Scope a query to only include popular variants.
     */
    public function scopePopular(Builder $query): Builder
    {
        return $query->where('is_popular', true);
    }

    /**
     * Scope a query to order variants by duration in days.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('duration_days', 'asc');
    }

    /**
     * Get effective purchase price (offer price if present, else regular price).
     */
    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->offer_price ?? $this->regular_price);
    }

    /**
     * Check if variant has an active discount.
     */
    public function getHasDiscountAttribute(): bool
    {
        return $this->offer_price !== null && (float) $this->regular_price > (float) $this->offer_price && (float) $this->offer_price > 0;
    }

    /**
     * Calculate discount percentage string (e.g. "25%").
     */
    public function getDiscountPercentageAttribute(): ?string
    {
        if ($this->has_discount) {
            $discount = (((float) $this->regular_price - (float) $this->offer_price) / (float) $this->regular_price) * 100;

            return round($discount).'%';
        }

        return null;
    }

    /**
     * Get formatted regular price in BDT.
     */
    public function getFormattedRegularPriceAttribute(): string
    {
        return '৳'.number_format((float) $this->regular_price, 2);
    }

    /**
     * Get formatted offer price in BDT.
     */
    public function getFormattedOfferPriceAttribute(): ?string
    {
        return $this->offer_price !== null ? '৳'.number_format((float) $this->offer_price, 2) : null;
    }

    /**
     * Get formatted cost price in BDT.
     */
    public function getFormattedCostPriceAttribute(): ?string
    {
        return $this->cost_price !== null ? '৳'.number_format((float) $this->cost_price, 2) : null;
    }

    /**
     * Calculate profit in BDT (effective price - cost price).
     */
    public function getProfitAttribute(): ?float
    {
        if ($this->cost_price === null) {
            return null;
        }

        return round($this->effective_price - (float) $this->cost_price, 2);
    }

    /**
     * Calculate profit margin percentage (profit / cost_price).
     */
    public function getProfitMarginPercentageAttribute(): ?string
    {
        if ($this->cost_price !== null && (float) $this->cost_price > 0) {
            $margin = (($this->effective_price - (float) $this->cost_price) / (float) $this->cost_price) * 100;

            return round($margin).'%';
        }

        return null;
    }
}
