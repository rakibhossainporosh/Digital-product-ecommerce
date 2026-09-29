<?php

namespace App\Services;

use App\Enums\FulfillmentStatus;
use App\Enums\LicenseKeyStatus;
use App\Enums\OrderStatus;
use App\Models\LicenseKey;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderFulfillmentService
{
    public function __construct(
        public readonly LicenseKeyStockService $stockService,
        public readonly ExternalPanelApiService $apiService
    ) {}

    /**
     * Fulfill license keys for an order using "Local First, Then Supplier API" strategy.
     *
     * For service-type products, fulfillment is marked immediately without key allocation.
     *
     * @return array{
     *     success: bool,
     *     keys: Collection<int, LicenseKey>|array,
     *     source: string,
     *     error: ?string
     * }
     */
    public function fulfill(Order $order): array
    {
        if ($order->isFulfilled()) {
            return [
                'success' => true,
                'keys' => $order->licenseKeys,
                'source' => 'already_fulfilled',
                'error' => null,
            ];
        }

        if (in_array($order->status, [OrderStatus::Cancelled, OrderStatus::Refunded])) {
            return [
                'success' => false,
                'keys' => [],
                'source' => 'none',
                'error' => "Cannot fulfill an order in '{$order->status->getLabel()}' status.",
            ];
        }

        // Service-type products don't need license key allocation
        if ($order->isService()) {
            return $this->fulfillServiceOrder($order);
        }

        return $this->fulfillDigitalOrder($order);
    }

    /**
     * Fulfill a service-type order (no license keys needed).
     *
     * @return array{success: bool, keys: array, source: string, error: ?string}
     */
    protected function fulfillServiceOrder(Order $order): array
    {
        $order->update([
            'fulfillment_status' => FulfillmentStatus::Fulfilled,
            'status' => OrderStatus::Completed,
            'fulfilled_at' => now(),
            'admin_notes' => trim(($order->admin_notes ? $order->admin_notes."\n" : '').'Service order fulfilled automatically (no license key required).'),
        ]);

        Log::info("Order #{$order->order_number}: Service-type order fulfilled without key allocation.");

        return [
            'success' => true,
            'keys' => [],
            'source' => 'service',
            'error' => null,
        ];
    }

    /**
     * Fulfill a digital order using dual-source strategy:
     * 1. Allocate available keys from local database inventory
     * 2. If insufficient, fallback to external supplier API for remaining keys
     *
     * @return array{success: bool, keys: Collection<int, LicenseKey>|array, source: string, error: ?string}
     */
    protected function fulfillDigitalOrder(Order $order): array
    {
        $neededQty = (int) $order->quantity;
        $variant = $order->productVariant;

        if (! $variant) {
            Log::error("OrderFulfillmentService: Variant #{$order->product_variant_id} not found for Order #{$order->order_number}");

            $order->update([
                'fulfillment_status' => FulfillmentStatus::Failed,
                'admin_notes' => trim(($order->admin_notes ? $order->admin_notes."\n" : '').'Fulfillment failed: Product variant not found.'),
            ]);

            return [
                'success' => false,
                'keys' => [],
                'source' => 'none',
                'error' => 'Product variant not found.',
            ];
        }

        $allKeys = new Collection;
        $sources = [];

        try {
            // ── Step 1: Local Database Stock (Priority) ──
            $localKeys = $this->stockService->allocateAvailableKeys(
                variantId: $variant->id,
                maxQuantity: $neededQty,
                orderId: $order->id
            );

            if ($localKeys->isNotEmpty()) {
                $allKeys = $allKeys->merge($localKeys);
                $sources[] = 'Local ('.$localKeys->count().')';
                $neededQty -= $localKeys->count();
                Log::info("Order #{$order->order_number}: Assigned {$localKeys->count()} key(s) from local stock.");
            }

            // ── Step 2: Supplier API Fallback (if local stock insufficient) ──
            if ($neededQty > 0 && filled($variant->api_provider_id) && $this->apiService->isEnabled()) {
                Log::info("Order #{$order->order_number}: Local stock exhausted. Requesting {$neededQty} key(s) from supplier API (Provider ID: {$variant->api_provider_id}).");

                $apiKeys = $this->fulfillFromSupplierApi($order, $variant, $neededQty);

                if ($apiKeys->isNotEmpty()) {
                    $allKeys = $allKeys->merge($apiKeys);
                    $sources[] = 'Supplier API ('.$apiKeys->count().')';
                    $neededQty -= $apiKeys->count();
                    Log::info("Order #{$order->order_number}: Successfully generated {$apiKeys->count()} key(s) from supplier API.");
                }
            }

            // ── Step 3: Evaluate fulfillment result ──
            $isFullyDelivered = ($allKeys->count() >= (int) $order->quantity);
            $sourceLabel = ! empty($sources) ? implode(' + ', $sources) : 'none';

            if ($isFullyDelivered) {
                $order->update([
                    'fulfillment_status' => FulfillmentStatus::Fulfilled,
                    'status' => OrderStatus::Completed,
                    'fulfilled_at' => now(),
                    'admin_notes' => trim(($order->admin_notes ? $order->admin_notes."\n" : '')."Keys delivered via: {$sourceLabel}"),
                ]);

                return [
                    'success' => true,
                    'keys' => $allKeys,
                    'source' => $sourceLabel,
                    'error' => null,
                ];
            }

            // Partial or no delivery — rollback allocated local keys
            if ($allKeys->isNotEmpty()) {
                $this->stockService->releaseAllocatedKeys($allKeys);
                Log::warning("Order #{$order->order_number}: Partial fulfillment ({$allKeys->count()}/{$order->quantity}). Released allocated keys back to inventory.");
            }

            $order->update([
                'fulfillment_status' => FulfillmentStatus::Failed,
                'admin_notes' => trim(($order->admin_notes ? $order->admin_notes."\n" : '')."Fulfillment failed: Only {$allKeys->count()}/{$order->quantity} keys could be sourced ({$sourceLabel})."),
            ]);

            return [
                'success' => false,
                'keys' => [],
                'source' => $sourceLabel,
                'error' => "Insufficient stock: needed {$order->quantity} key(s) but only {$allKeys->count()} available.",
            ];
        } catch (Throwable $e) {
            // Rollback any keys allocated before the exception
            if ($allKeys->isNotEmpty()) {
                $this->stockService->releaseAllocatedKeys($allKeys);
            }

            Log::error("OrderFulfillmentService: Exception fulfilling Order #{$order->order_number}: {$e->getMessage()}");

            $order->update([
                'fulfillment_status' => FulfillmentStatus::Failed,
                'admin_notes' => trim(($order->admin_notes ? $order->admin_notes."\n" : '').'Fulfillment attempt failed: '.$e->getMessage()),
            ]);

            return [
                'success' => false,
                'keys' => [],
                'source' => 'none',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Request keys from external supplier API and persist them to local license_keys table.
     *
     * @return Collection<int, LicenseKey>
     */
    protected function fulfillFromSupplierApi(Order $order, ProductVariant $variant, int $neededQty): Collection
    {
        $apiResult = $this->apiService->generateKey(
            $variant->api_provider_id,
            $neededQty,
            $order->order_number
        );

        $persistedKeys = new Collection;

        if (! $apiResult['ok'] || empty($apiResult['keys'])) {
            $apiError = $apiResult['error'] ?? 'API generation failed';
            Log::warning("Order #{$order->order_number}: Supplier API failed to generate keys: {$apiError}");

            return $persistedKeys;
        }

        foreach ($apiResult['keys'] as $keyString) {
            try {
                $license = LicenseKey::create([
                    'product_variant_id' => $variant->id,
                    'key' => $keyString,
                    'status' => LicenseKeyStatus::Sold,
                    'order_id' => $order->id,
                    'sold_at' => now(),
                    'notes' => 'Generated via supplier API',
                ]);
                $persistedKeys->push($license);
            } catch (Throwable $keyEx) {
                // Handle duplicate key scenario gracefully
                $existing = LicenseKey::where('key', $keyString)->first();
                if ($existing && $existing->status === LicenseKeyStatus::Available) {
                    $existing->update([
                        'product_variant_id' => $variant->id,
                        'status' => LicenseKeyStatus::Sold,
                        'order_id' => $order->id,
                        'sold_at' => now(),
                        'notes' => 'Re-assigned from supplier API (duplicate key handled)',
                    ]);
                    $persistedKeys->push($existing);
                    Log::warning("Order #{$order->order_number}: Supplier key already existed (ID: {$existing->id}). Re-assigned safely.");
                } else {
                    Log::error("Order #{$order->order_number}: Could not insert supplier API key: {$keyEx->getMessage()}");
                }
            }
        }

        return $persistedKeys;
    }
}
