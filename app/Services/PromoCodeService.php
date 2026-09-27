<?php

namespace App\Services;

use App\Enums\DiscountType;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PromoCodeService
{
    /**
     * Validate a promo code against customer, product variant, quantity, and limits.
     *
     * @return array{
     *     valid: bool,
     *     promo_code: ?PromoCode,
     *     discount_amount: float,
     *     final_total: float,
     *     subtotal: float,
     *     error: ?string
     * }
     */
    public function validate(
        string $code,
        Customer $customer,
        ProductVariant $variant,
        int $quantity = 1
    ): array {
        $normalizedCode = strtoupper(trim($code));

        if (blank($normalizedCode)) {
            return $this->failure('Promo code cannot be empty.');
        }

        /** @var PromoCode|null $promo */
        $promo = PromoCode::where('code', $normalizedCode)->first();

        if (! $promo) {
            return $this->failure('Invalid promo code.');
        }

        if (! $promo->is_active) {
            return $this->failure('This promo code is currently inactive.');
        }

        if ($promo->isScheduled()) {
            return $this->failure("This promo code promotion begins on {$promo->starts_at?->format('M d, Y')}.");
        }

        if ($promo->isExpired()) {
            return $this->failure('This promo code has expired.');
        }

        if (! $promo->hasUsesRemaining()) {
            return $this->failure('This promo code has reached its maximum global usage limit.');
        }

        if ($customer->isReseller() && $promo->exclude_resellers) {
            return $this->failure('Promo codes cannot be applied to reseller accounts with wholesale pricing.');
        }

        // Per-customer usage limit verification
        $customerUsageCount = PromoCodeUsage::where('promo_code_id', $promo->id)
            ->where('customer_id', $customer->id)
            ->count();

        if ($customerUsageCount >= $promo->max_uses_per_customer) {
            return $this->failure("You have reached the redemption limit ({$promo->max_uses_per_customer} use) for this promo code.");
        }

        // Scope verification: Product
        if ($promo->product_id !== null && $promo->product_id !== $variant->product_id) {
            $productName = $promo->product?->name ?? 'specified product';

            return $this->failure("This promo code is only valid for {$productName}.");
        }

        // Scope verification: Category
        $productCategory = $variant->product?->category_id;
        if ($promo->category_id !== null && $promo->category_id !== $productCategory) {
            $categoryName = $promo->category?->name ?? 'specified category';

            return $this->failure("This promo code is only valid for products in the {$categoryName} category.");
        }

        // Subtotal calculation
        $baseUnitPrice = (float) $variant->effective_price;
        $subtotal = round($baseUnitPrice * $quantity, 2);

        // Minimum spend validation
        if ($promo->min_spend !== null && $subtotal < (float) $promo->min_spend) {
            $minFormatted = '৳ '.number_format((float) $promo->min_spend, 2);

            return $this->failure("This promo code requires a minimum order amount of {$minFormatted}.");
        }

        // Calculate discount amount
        if ($promo->type === DiscountType::Percentage) {
            $discount = round(($subtotal * (float) $promo->value) / 100, 2);
            if ($promo->max_discount !== null && (float) $promo->max_discount > 0) {
                $discount = min($discount, (float) $promo->max_discount);
            }
        } else {
            $discount = min((float) $promo->value, $subtotal);
        }

        $discount = min($discount, $subtotal);
        $finalTotal = max(0.00, round($subtotal - $discount, 2));

        return [
            'valid' => true,
            'promo_code' => $promo,
            'discount_amount' => $discount,
            'final_total' => $finalTotal,
            'subtotal' => $subtotal,
            'error' => null,
        ];
    }

    /**
     * Atomically apply promo code to an order and record ledger usage.
     *
     * @throws RuntimeException
     */
    public function applyToOrder(PromoCode $promoCode, Order $order, float $discountAmount): PromoCodeUsage
    {
        return DB::transaction(function () use ($promoCode, $order, $discountAmount): PromoCodeUsage {
            /** @var PromoCode $lockedPromo */
            $lockedPromo = PromoCode::where('id', $promoCode->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedPromo->hasUsesRemaining()) {
                throw new RuntimeException('Promo code reached maximum usage capacity while processing.');
            }

            $lockedPromo->increment('used_count');

            $usage = PromoCodeUsage::create([
                'promo_code_id' => $lockedPromo->id,
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'discount_amount' => $discountAmount,
            ]);

            $order->update([
                'promo_code_id' => $lockedPromo->id,
            ]);

            return $usage;
        });
    }

    /**
     * Release promo code quota if an order is cancelled or refunded (Quota Restitution).
     */
    public function releaseOrderUsage(Order $order): void
    {
        if (! $order->promo_code_id) {
            return;
        }

        DB::transaction(function () use ($order): void {
            /** @var PromoCode|null $lockedPromo */
            $lockedPromo = PromoCode::where('id', $order->promo_code_id)
                ->lockForUpdate()
                ->first();

            if ($lockedPromo && $lockedPromo->used_count > 0) {
                $lockedPromo->decrement('used_count');
            }

            PromoCodeUsage::where('order_id', $order->id)->delete();

            $order->update([
                'promo_code_id' => null,
            ]);
        });
    }

    /**
     * Helper to return standard failure response.
     *
     * @return array{valid: false, promo_code: null, discount_amount: 0.0, final_total: 0.0, subtotal: 0.0, error: string}
     */
    private function failure(string $message): array
    {
        return [
            'valid' => false,
            'promo_code' => null,
            'discount_amount' => 0.0,
            'final_total' => 0.0,
            'subtotal' => 0.0,
            'error' => $message,
        ];
    }
}
