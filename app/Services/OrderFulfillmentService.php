<?php

namespace App\Services;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Models\LicenseKey;
use App\Models\Order;
use Illuminate\Support\Collection;
use Throwable;

class OrderFulfillmentService
{
    public function __construct(
        public readonly LicenseKeyStockService $stockService
    ) {}

    /**
     * Atomically fulfill license keys for an order using local inventory.
     *
     * @return array{
     *     success: bool,
     *     keys: Collection<int, LicenseKey>|array,
     *     error: ?string
     * }
     */
    public function fulfill(Order $order): array
    {
        if ($order->isFulfilled()) {
            return [
                'success' => true,
                'keys' => $order->licenseKeys,
                'error' => null,
            ];
        }

        if (in_array($order->status, [OrderStatus::Cancelled, OrderStatus::Refunded])) {
            return [
                'success' => false,
                'keys' => [],
                'error' => "Cannot fulfill an order in '{$order->status->getLabel()}' status.",
            ];
        }

        try {
            $allocatedKeys = $this->stockService->allocateKeys(
                variantId: $order->product_variant_id,
                quantity: $order->quantity,
                orderId: $order->id
            );

            $order->update([
                'fulfillment_status' => FulfillmentStatus::Fulfilled,
                'status' => OrderStatus::Completed,
                'fulfilled_at' => now(),
            ]);

            return [
                'success' => true,
                'keys' => $allocatedKeys,
                'error' => null,
            ];
        } catch (Throwable $e) {
            $order->update([
                'fulfillment_status' => FulfillmentStatus::Failed,
                'admin_notes' => trim(($order->admin_notes ? $order->admin_notes."\n" : '').'Fulfillment attempt failed: '.$e->getMessage()),
            ]);

            return [
                'success' => false,
                'keys' => [],
                'error' => $e->getMessage(),
            ];
        }
    }
}
