<?php

namespace App\Models;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'customer_id',
        'product_id',
        'product_variant_id',
        'promo_code_id',
        'quantity',
        'unit_price',
        'subtotal',
        'discount_amount',
        'total_amount',
        'cost_price',
        'payment_method',
        'payment_status',
        'fulfillment_status',
        'status',
        'wallet_amount_paid',
        'gateway_amount_paid',
        'gateway_transaction_id',
        'ip_address',
        'user_agent',
        'customer_notes',
        'service_data',
        'admin_notes',
        'fulfilled_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service_data' => 'array',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'wallet_amount_paid' => 'decimal:2',
            'gateway_amount_paid' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'fulfillment_status' => FulfillmentStatus::class,
            'status' => OrderStatus::class,
            'fulfilled_at' => 'datetime',
        ];
    }

    /**
     * Generate unique prefix-based order number (e.g. RES-2026-98124 or ORD-2026-98124).
     */
    public static function generateOrderNumber(bool $isReseller = false): string
    {
        $prefix = $isReseller ? 'RES-' : 'ORD-';
        $year = now()->format('Y');

        do {
            $random = strtoupper(bin2hex(random_bytes(3))); // 6 hex chars
            $number = "{$prefix}{$year}-{$random}";
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    // ── Relationships ──

    /**
     * The customer who placed this order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    /**
     * The purchased product catalog item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * The specific duration-based variant.
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id')->withTrashed();
    }

    /**
     * Alias for productVariant relationship.
     */
    public function variant(): BelongsTo
    {
        return $this->productVariant();
    }

    /**
     * All license keys assigned and delivered for this order.
     */
    public function licenseKeys(): HasMany
    {
        return $this->hasMany(LicenseKey::class);
    }

    /**
     * The promotional discount coupon applied to this order, if any.
     */
    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class)->withTrashed();
    }

    /**
     * The redemption audit record for this order.
     */
    public function promoCodeUsage(): HasOne
    {
        return $this->hasOne(PromoCodeUsage::class);
    }

    // ── Financial & State Helpers ──

    /**
     * Calculate net financial profit for this order (Total Revenue - Total Cost).
     */
    public function getNetProfitAttribute(): ?float
    {
        if ($this->cost_price === null) {
            return null;
        }

        $totalCost = (float) $this->cost_price * $this->quantity;

        return round((float) $this->total_amount - $totalCost, 2);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }

    public function isFulfilled(): bool
    {
        return $this->fulfillment_status === FulfillmentStatus::Fulfilled;
    }

    public function isService(): bool
    {
        return $this->product?->type === 'service';
    }

    public function isDigital(): bool
    {
        return ! $this->isService();
    }

    public function canBeFulfilled(): bool
    {
        return $this->isPaid() && ! $this->isFulfilled() && $this->status !== OrderStatus::Cancelled && $this->status !== OrderStatus::Refunded;
    }

    public function canBeRefunded(): bool
    {
        return $this->isPaid() && $this->status !== OrderStatus::Refunded && $this->fulfillment_status !== FulfillmentStatus::RefundedToWallet;
    }

    public function getFormattedTotalAttribute(): string
    {
        return '৳ '.number_format((float) $this->total_amount, 2);
    }

    // ── Scopes ──

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatus::Paid);
    }

    public function scopeFulfilled(Builder $query): Builder
    {
        return $query->where('fulfillment_status', FulfillmentStatus::Fulfilled);
    }

    public function scopePendingFulfillment(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatus::Paid)
            ->where('fulfillment_status', '!=', FulfillmentStatus::Fulfilled);
    }

    public function scopeResellerOrders(Builder $query): Builder
    {
        return $query->where('order_number', 'like', 'RES-%');
    }
}
