<?php

namespace App\Services;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\WalletTransactionType;
use App\Exceptions\CustomerAccountLockedException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class OrderService
{
    public function __construct(
        public readonly OrderPricingService $pricingService,
        public readonly OrderFulfillmentService $fulfillmentService,
        public readonly WalletService $walletService,
        public readonly PromoCodeService $promoCodeService
    ) {}

    /**
     * Create a new purchase order with calculated pricing.
     *
     * @param  array<string, mixed>  $extra
     *
     * @throws CustomerAccountLockedException
     * @throws InvalidArgumentException
     */
    public function createOrder(
        Customer $customer,
        ProductVariant $variant,
        int $quantity,
        PaymentMethod $paymentMethod,
        array $extra = []
    ): Order {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Order quantity must be at least 1.');
        }

        if ($customer->isBanned()) {
            throw new CustomerAccountLockedException($customer->status, 'Banned customer accounts cannot create orders.');
        }

        $promoCode = $extra['promo_code'] ?? null;
        $pricing = $this->pricingService->calculate($customer, $variant, $quantity, $promoCode);
        $orderNumber = Order::generateOrderNumber($customer->isReseller());

        return Order::create([
            'order_number' => $orderNumber,
            'customer_id' => $customer->id,
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'promo_code_id' => $pricing['promo_code_id'],
            'quantity' => $quantity,
            'unit_price' => $pricing['unit_price'],
            'subtotal' => $pricing['subtotal'],
            'discount_amount' => $pricing['discount_amount'],
            'total_amount' => $pricing['total_amount'],
            'cost_price' => $pricing['cost_price'],
            'payment_method' => $paymentMethod,
            'payment_status' => PaymentStatus::Pending,
            'fulfillment_status' => FulfillmentStatus::Unfulfilled,
            'status' => OrderStatus::Pending,
            'wallet_amount_paid' => 0.00,
            'gateway_amount_paid' => 0.00,
            'customer_notes' => $extra['customer_notes'] ?? null,
            'ip_address' => $extra['ip_address'] ?? null,
            'user_agent' => $extra['user_agent'] ?? null,
        ]);
    }

    /**
     * Complete order checkout using customer wallet with fail-safe auto-refund on stock-out.
     *
     * @throws RuntimeException
     */
    public function checkoutWithWallet(Order $order): Order
    {
        if ($order->isPaid()) {
            throw new RuntimeException("Order #{$order->order_number} is already marked as paid.");
        }

        return DB::transaction(function () use ($order): Order {
            /** @var Customer $customer */
            $customer = $order->customer;

            // 1. Atomically debit customer wallet
            $this->walletService->debit(
                customer: $customer,
                amount: (float) $order->total_amount,
                type: WalletTransactionType::Purchase,
                description: "Payment for Order #{$order->order_number}",
                referenceId: $order->order_number
            );

            // 2. Ledger promo code usage if applied
            if ($order->promo_code_id && $order->promoCode) {
                $this->promoCodeService->applyToOrder(
                    promoCode: $order->promoCode,
                    order: $order,
                    discountAmount: (float) $order->discount_amount
                );
            }

            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'wallet_amount_paid' => $order->total_amount,
                'status' => OrderStatus::Processing,
            ]);

            // 3. Atomically fulfill license keys from stock
            $fulfillment = $this->fulfillmentService->fulfill($order);

            // 4. Fail-Safe Auto-Refund if stock allocation failed
            if (! $fulfillment['success']) {
                $this->walletService->refund(
                    customer: $customer,
                    amount: (float) $order->total_amount,
                    description: "Auto-refund for Order #{$order->order_number}: Stock out",
                    referenceId: $order->order_number,
                    metadata: ['reason' => 'auto_refund_stock_unavailable', 'error' => $fulfillment['error']]
                );

                // Release promo code quota so buyer can reuse it
                $this->promoCodeService->releaseOrderUsage($order);

                $order->update([
                    'fulfillment_status' => FulfillmentStatus::RefundedToWallet,
                    'status' => OrderStatus::Refunded,
                    'admin_notes' => trim(($order->admin_notes ? $order->admin_notes."\n" : '').'Auto-refunded to customer wallet due to stock exhaustion.'),
                ]);
            }

            return $order->fresh(['licenseKeys', 'customer', 'promoCode']);
        });
    }

    /**
     * Admin manual refund back to customer wallet with license key revocation and promo quota restoration.
     *
     * @throws RuntimeException
     */
    public function refundOrderToWallet(Order $order, string $reason, User $admin): Order
    {
        if (! $order->canBeRefunded()) {
            throw new RuntimeException("Order #{$order->order_number} cannot be refunded in its current status.");
        }

        return DB::transaction(function () use ($order, $reason, $admin): Order {
            // 1. Credit wallet
            $this->walletService->refund(
                customer: $order->customer,
                amount: (float) $order->total_amount,
                description: "Admin Refund for Order #{$order->order_number}: {$reason}",
                referenceId: $order->order_number,
                metadata: ['admin_id' => $admin->id, 'reason' => $reason]
            );

            // 2. Revoke any allocated license keys
            foreach ($order->licenseKeys as $key) {
                $this->fulfillmentService->stockService->revokeKey(
                    $key,
                    "Order #{$order->order_number} refunded: {$reason}"
                );
            }

            // 3. Release promo quota
            $this->promoCodeService->releaseOrderUsage($order);

            // 4. Mark order as refunded
            $order->update([
                'status' => OrderStatus::Refunded,
                'payment_status' => PaymentStatus::Refunded,
                'fulfillment_status' => FulfillmentStatus::RefundedToWallet,
                'admin_notes' => trim(($order->admin_notes ? $order->admin_notes."\n" : '')."Refunded by {$admin->name}: {$reason}"),
            ]);

            return $order->fresh(['licenseKeys', 'customer', 'promoCode']);
        });
    }
}
