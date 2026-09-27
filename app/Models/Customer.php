<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * Note: 'balance' is strictly protected from mass assignment and only modified through WalletService.
     *
     * @var list<string>
     */
    protected $fillable = [
        'referral_code',
        'referred_by',
        'name',
        'email',
        'whatsapp_number',
        'password',
        'google_id',
        'facebook_id',
        'status',
        'is_reseller',
        'reseller_discount',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => CustomerStatus::class,
            'balance' => 'decimal:2',
            'is_reseller' => 'boolean',
            'reseller_discount' => 'decimal:2',
        ];
    }

    /**
     * Auto-generate unique referral code if empty on creation.
     */
    protected static function booted(): void
    {
        static::creating(function (Customer $customer): void {
            if (empty($customer->referral_code)) {
                $customer->referral_code = static::generateUniqueReferralCode();
            }
        });
    }

    /**
     * Generate a cryptographically distinct, uppercase alphanumeric referral code.
     */
    public static function generateUniqueReferralCode(): string
    {
        do {
            $code = 'REF'.strtoupper(bin2hex(random_bytes(4)));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    // ── Relationships ──

    /**
     * The wallet ledger transactions for this customer.
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    /**
     * All orders placed by this customer.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    /**
     * All promo code usages by this customer.
     */
    public function promoCodeUsages(): HasMany
    {
        return $this->hasMany(PromoCodeUsage::class)->latest();
    }

    /**
     * The customer who referred this customer.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referred_by');
    }

    /**
     * Customers referred by this customer.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Customer::class, 'referred_by');
    }

    /**
     * Custom pricing overrides for reseller.
     */
    public function resellerPrices(): HasMany
    {
        return $this->hasMany(ResellerPrice::class);
    }

    // ── Status & Reseller Helpers ──

    public function isReseller(): bool
    {
        return (bool) $this->is_reseller;
    }

    public function isActive(): bool
    {
        return $this->status === CustomerStatus::Active;
    }

    public function isBanned(): bool
    {
        return $this->status === CustomerStatus::Banned;
    }

    public function isSuspended(): bool
    {
        return $this->status === CustomerStatus::Suspended;
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return (float) $this->balance >= $amount;
    }

    public function getFormattedBalanceAttribute(): string
    {
        return '৳ '.number_format((float) $this->balance, 2);
    }

    // ── Scopes ──

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CustomerStatus::Active);
    }

    public function scopeResellers(Builder $query): Builder
    {
        return $query->where('is_reseller', true);
    }

    public function scopeBanned(Builder $query): Builder
    {
        return $query->where('status', CustomerStatus::Banned);
    }
}
