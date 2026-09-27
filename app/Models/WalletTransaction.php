<?php

namespace App\Models;

use App\Enums\TransactionDirection;
use App\Enums\WalletTransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => WalletTransactionType::class,
            'direction' => TransactionDirection::class,
            'amount' => 'decimal:2',
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    // ── Relationships ──

    /**
     * The customer who owns this ledger transaction.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The admin user who initiated this adjustment, if any.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // ── Helpers & Accessors ──

    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->direction === TransactionDirection::Credit ? '+৳ ' : '-৳ ';

        return $prefix.number_format((float) $this->amount, 2);
    }

    // ── Scopes ──

    public function scopeDeposits(Builder $query): Builder
    {
        return $query->where('type', WalletTransactionType::Deposit);
    }

    public function scopePurchases(Builder $query): Builder
    {
        return $query->where('type', WalletTransactionType::Purchase);
    }

    public function scopeRefunds(Builder $query): Builder
    {
        return $query->where('type', WalletTransactionType::Refund);
    }

    public function scopeAdjustments(Builder $query): Builder
    {
        return $query->where('type', WalletTransactionType::AdminAdjust);
    }

    public function scopeCredits(Builder $query): Builder
    {
        return $query->where('direction', TransactionDirection::Credit);
    }

    public function scopeDebits(Builder $query): Builder
    {
        return $query->where('direction', TransactionDirection::Debit);
    }
}
