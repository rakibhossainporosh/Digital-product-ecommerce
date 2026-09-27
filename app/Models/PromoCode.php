<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoCode extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_spend',
        'max_discount',
        'product_id',
        'category_id',
        'max_uses',
        'max_uses_per_customer',
        'used_count',
        'exclude_resellers',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => DiscountType::class,
            'value' => 'decimal:2',
            'min_spend' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'max_uses' => 'integer',
            'max_uses_per_customer' => 'integer',
            'used_count' => 'integer',
            'exclude_resellers' => 'boolean',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Normalize code to uppercase on saving.
     */
    protected static function booted(): void
    {
        static::saving(function (PromoCode $promoCode): void {
            $promoCode->code = strtoupper(trim($promoCode->code));
        });
    }

    // ── Relationships ──

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PromoCodeUsage::class)->latest();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    // ── Status & Validity Helpers ──

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->isAfter($this->expires_at);
    }

    public function isScheduled(): bool
    {
        return $this->starts_at !== null && now()->isBefore($this->starts_at);
    }

    public function hasUsesRemaining(): bool
    {
        return $this->max_uses === null || $this->used_count < $this->max_uses;
    }

    public function isUsable(): bool
    {
        return $this->is_active && ! $this->isExpired() && ! $this->isScheduled() && $this->hasUsesRemaining();
    }

    public function getFormattedDiscountAttribute(): string
    {
        return $this->type->formatDiscount((float) $this->value);
    }

    public function getScopeDescriptionAttribute(): string
    {
        if ($this->product_id) {
            return 'Product: '.($this->product?->name ?? '#'.$this->product_id);
        }

        if ($this->category_id) {
            return 'Category: '.($this->category?->name ?? '#'.$this->category_id);
        }

        return 'All Store Products';
    }

    public function getUsageProgressAttribute(): string
    {
        if ($this->max_uses === null) {
            return "{$this->used_count} uses (Unlimited)";
        }

        return "{$this->used_count} / {$this->max_uses} used";
    }

    // ── Scopes ──

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeValidNow(Builder $query): Builder
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now))
            ->where(fn (Builder $q) => $q->whereNull('max_uses')->orWhereColumn('used_count', '<', 'max_uses'));
    }
}
