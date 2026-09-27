<?php

namespace App\Models;

use App\Enums\LicenseKeyStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LicenseKey extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_variant_id',
        'key',
        'status',
        'order_id',
        'order_item_id',
        'sold_at',
        'batch_ref',
        'notes',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'key' => 'encrypted',
            'status' => LicenseKeyStatus::class,
            'sold_at' => 'datetime',
        ];
    }

    /**
     * Get masked representation of the license key for secure UI presentation.
     */
    public function getMaskedKeyAttribute(): string
    {
        $raw = (string) $this->key;
        $length = strlen($raw);

        if ($length <= 8) {
            return substr($raw, 0, 2).str_repeat('•', max(0, $length - 4)).substr($raw, -2);
        }

        $visibleStart = substr($raw, 0, 4);
        $visibleEnd = substr($raw, -4);
        $maskedLength = min(8, max(4, $length - 8));

        return $visibleStart.'-'.str_repeat('•', $maskedLength).'-'.$visibleEnd;
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The order this license key was sold and delivered to.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @param  Builder<LicenseKey>  $query
     * @return Builder<LicenseKey>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', LicenseKeyStatus::Available);
    }

    /**
     * @param  Builder<LicenseKey>  $query
     * @return Builder<LicenseKey>
     */
    public function scopeSold(Builder $query): Builder
    {
        return $query->where('status', LicenseKeyStatus::Sold);
    }

    /**
     * @param  Builder<LicenseKey>  $query
     * @return Builder<LicenseKey>
     */
    public function scopeReserved(Builder $query): Builder
    {
        return $query->where('status', LicenseKeyStatus::Reserved);
    }

    /**
     * @param  Builder<LicenseKey>  $query
     * @return Builder<LicenseKey>
     */
    public function scopeRevoked(Builder $query): Builder
    {
        return $query->where('status', LicenseKeyStatus::Revoked);
    }

    /**
     * @param  Builder<LicenseKey>  $query
     * @return Builder<LicenseKey>
     */
    public function scopeForVariant(Builder $query, int $variantId): Builder
    {
        return $query->where('product_variant_id', $variantId);
    }

    // ── Status Helper Methods ──

    public function isAvailable(): bool
    {
        return $this->status === LicenseKeyStatus::Available;
    }

    public function isSold(): bool
    {
        return $this->status === LicenseKeyStatus::Sold;
    }

    public function isReserved(): bool
    {
        return $this->status === LicenseKeyStatus::Reserved;
    }

    public function isRevoked(): bool
    {
        return $this->status === LicenseKeyStatus::Revoked;
    }
}
