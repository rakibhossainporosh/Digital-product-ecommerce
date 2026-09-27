<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\ProductVariant;
use App\Models\PromoCode;
use App\Models\ResellerPrice;

class OrderPricingService
{
    public function __construct(
        public readonly ?PromoCodeService $promoCodeService = null
    ) {}

    /**
     * Calculate comprehensive price breakdown for an order line item.
     * Evaluates custom reseller variant price -> global reseller discount -> promo code -> standard effective price.
     *
     * @return array{
     *     unit_price: float,
     *     final_unit_price: float,
     *     subtotal: float,
     *     discount_amount: float,
     *     total_amount: float,
     *     cost_price: ?float,
     *     discount_type: string,
     *     promo_code: ?PromoCode,
     *     promo_code_id: ?int
     * }
     */
    public function calculate(
        Customer $customer,
        ProductVariant $variant,
        int $quantity = 1,
        ?string $promoCode = null
    ): array {
        $baseUnitPrice = (float) $variant->effective_price;
        $finalUnitPrice = $baseUnitPrice;
        $discountType = 'none';
        $appliedPromo = null;

        // 1. Reseller Custom / Global Tier Discount
        if ($customer->isReseller()) {
            /** @var ResellerPrice|null $customPriceRule */
            $customPriceRule = ResellerPrice::where('customer_id', $customer->id)
                ->where('product_variant_id', $variant->id)
                ->first();

            if ($customPriceRule) {
                if ($customPriceRule->custom_price !== null) {
                    $finalUnitPrice = (float) $customPriceRule->custom_price;
                    $discountType = 'reseller_fixed_price';
                } elseif ($customPriceRule->discount_percentage !== null) {
                    $discountPct = (float) $customPriceRule->discount_percentage;
                    $finalUnitPrice = round($baseUnitPrice * (1 - ($discountPct / 100)), 2);
                    $discountType = 'reseller_custom_discount';
                }
            } elseif ($customer->reseller_discount !== null && (float) $customer->reseller_discount > 0) {
                $discountPct = (float) $customer->reseller_discount;
                $finalUnitPrice = round($baseUnitPrice * (1 - ($discountPct / 100)), 2);
                $discountType = 'reseller_global_discount';
            }
        }

        $subtotal = round($baseUnitPrice * $quantity, 2);
        $totalAmount = round($finalUnitPrice * $quantity, 2);
        $discountAmount = round(max(0, $subtotal - $totalAmount), 2);

        // 2. Promotional Discount Coupon (if provided and valid)
        if (filled($promoCode)) {
            $service = $this->promoCodeService ?? app(PromoCodeService::class);
            $validation = $service->validate($promoCode, $customer, $variant, $quantity);

            if ($validation['valid'] && $validation['promo_code']) {
                $appliedPromo = $validation['promo_code'];
                $discountAmount = $validation['discount_amount'];
                $totalAmount = $validation['final_total'];
                $discountType = 'promo_code:'.$appliedPromo->code;
            }
        }

        $costPrice = $variant->cost_price !== null ? (float) $variant->cost_price : null;

        return [
            'unit_price' => $baseUnitPrice,
            'final_unit_price' => round($totalAmount / max(1, $quantity), 2),
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'cost_price' => $costPrice,
            'discount_type' => $discountType,
            'promo_code' => $appliedPromo,
            'promo_code_id' => $appliedPromo?->id,
        ];
    }
}
