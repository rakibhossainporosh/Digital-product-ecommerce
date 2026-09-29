<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'category_id',
    'type',
    'name',
    'slug',
    'icon',
    'image',
    'demo_video_url',
    'description',
    'features',
    'status',
    'sort_order',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'features' => 'array',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (blank($product->slug)) {
                $baseSlug = Str::slug($product->name);
                $slug = $baseSlug;
                $counter = 1;

                while (static::withTrashed()->where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$counter++;
                }

                $product->slug = $slug;
            }
        });
    }

    /**
     * Category relationship.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Product duration variants relationship.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('duration_days', 'asc');
    }

    /**
     * All customer orders for this product.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    /**
     * Promo codes scoped to this product.
     */
    public function promoCodes(): HasMany
    {
        return $this->hasMany(PromoCode::class);
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to order products by display sequence.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc')->latest('id');
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Get formatted price range of variants.
     */
    public function getPriceRangeAttribute(): string
    {
        $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->get();

        if ($variants->isEmpty()) {
            return '৳0.00';
        }

        $min = $variants->min(fn (ProductVariant $v): float => (float) ($v->offer_price ?? $v->regular_price));
        $max = $variants->max(fn (ProductVariant $v): float => (float) ($v->offer_price ?? $v->regular_price));

        if ($min === $max) {
            return '৳'.number_format($min, 2);
        }

        return '৳'.number_format($min, 2).' - ৳'.number_format($max, 2);
    }

    /**
     * Get normalized features list.
     *
     * @return array<int, string>
     */
    public function getFeaturesListAttribute(): array
    {
        return is_array($this->features) ? array_values(array_filter($this->features)) : [];
    }

    /**
     * Check if product is a non-inventory service (e.g. manual top-up, custom service).
     */
    public function isService(): bool
    {
        return $this->type === 'service';
    }

    /**
     * Check if product is a digital license key product.
     */
    public function isDigital(): bool
    {
        return $this->type === 'digital_key' || $this->type === 'digital' || blank($this->type);
    }
}
