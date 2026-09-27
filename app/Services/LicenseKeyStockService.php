<?php

namespace App\Services;

use App\Enums\LicenseKeyStatus;
use App\Models\LicenseKey;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LicenseKeyStockService
{
    /**
     * Atomically allocate an available license key for an order using row-level locking.
     * Prevents race conditions and double-selling.
     *
     * @throws RuntimeException
     */
    public function allocateKey(int $variantId, ?int $orderId = null, ?int $orderItemId = null): LicenseKey
    {
        return DB::transaction(function () use ($variantId, $orderId, $orderItemId): LicenseKey {
            /** @var LicenseKey|null $key */
            $key = LicenseKey::query()
                ->where('product_variant_id', $variantId)
                ->where('status', LicenseKeyStatus::Available)
                ->lockForUpdate()
                ->first();

            if (! $key) {
                throw new RuntimeException("Stock unavailable: No available license keys found for variant #{$variantId}.");
            }

            $key->update([
                'status' => LicenseKeyStatus::Sold,
                'order_id' => $orderId,
                'order_item_id' => $orderItemId,
                'sold_at' => now(),
            ]);

            return $key;
        });
    }

    /**
     * Atomically allocate multiple available license keys for an order using row-level locking.
     * Prevents race conditions, partial allocations, and double-selling.
     *
     * @return Collection<int, LicenseKey>
     *
     * @throws RuntimeException
     */
    public function allocateKeys(int $variantId, int $quantity, ?int $orderId = null, ?int $orderItemId = null): Collection
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($variantId, $quantity, $orderId, $orderItemId) {
            $keys = LicenseKey::query()
                ->where('product_variant_id', $variantId)
                ->where('status', LicenseKeyStatus::Available)
                ->lockForUpdate()
                ->limit($quantity)
                ->get();

            if ($keys->count() < $quantity) {
                throw new RuntimeException("Stock unavailable: Required {$quantity} keys for variant #{$variantId}, but only {$keys->count()} available.");
            }

            foreach ($keys as $key) {
                $key->update([
                    'status' => LicenseKeyStatus::Sold,
                    'order_id' => $orderId,
                    'order_item_id' => $orderItemId,
                    'sold_at' => now(),
                ]);
            }

            return $keys;
        });
    }

    /**
     * Atomically reserve a key during checkout pending payment.
     *
     * @throws RuntimeException
     */
    public function reserveKey(int $variantId): LicenseKey
    {
        return DB::transaction(function () use ($variantId): LicenseKey {
            /** @var LicenseKey|null $key */
            $key = LicenseKey::query()
                ->where('product_variant_id', $variantId)
                ->where('status', LicenseKeyStatus::Available)
                ->lockForUpdate()
                ->first();

            if (! $key) {
                throw new RuntimeException("Stock unavailable: Cannot reserve license key for variant #{$variantId}.");
            }

            $key->update([
                'status' => LicenseKeyStatus::Reserved,
            ]);

            return $key;
        });
    }

    /**
     * Release a reserved key back to available status (e.g. upon payment cancellation or timeout).
     */
    public function releaseKey(int $keyId): bool
    {
        return DB::transaction(function () use ($keyId): bool {
            /** @var LicenseKey|null $key */
            $key = LicenseKey::query()
                ->where('id', $keyId)
                ->where('status', LicenseKeyStatus::Reserved)
                ->lockForUpdate()
                ->first();

            if (! $key) {
                return false;
            }

            return $key->update([
                'status' => LicenseKeyStatus::Available,
            ]);
        });
    }

    /**
     * Revoke an invalid or expired key.
     */
    public function revokeKey(int|LicenseKey $key, ?string $reason = null): bool
    {
        $licenseKey = $key instanceof LicenseKey ? $key : LicenseKey::findOrFail($key);

        return $licenseKey->update([
            'status' => LicenseKeyStatus::Revoked,
            'notes' => $reason ? ($licenseKey->notes ? $licenseKey->notes.' | '.$reason : $reason) : $licenseKey->notes,
        ]);
    }

    /**
     * Get real-time stock summary metrics for a variant.
     *
     * @return array{
     *     available: int,
     *     sold: int,
     *     reserved: int,
     *     revoked: int,
     *     total: int
     * }
     */
    public function getStockSummary(int $variantId): array
    {
        $counts = LicenseKey::query()
            ->where('product_variant_id', $variantId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $available = (int) ($counts[LicenseKeyStatus::Available->value] ?? 0);
        $sold = (int) ($counts[LicenseKeyStatus::Sold->value] ?? 0);
        $reserved = (int) ($counts[LicenseKeyStatus::Reserved->value] ?? 0);
        $revoked = (int) ($counts[LicenseKeyStatus::Revoked->value] ?? 0);

        return [
            'available' => $available,
            'sold' => $sold,
            'reserved' => $reserved,
            'revoked' => $revoked,
            'total' => $available + $sold + $reserved + $revoked,
        ];
    }
}
