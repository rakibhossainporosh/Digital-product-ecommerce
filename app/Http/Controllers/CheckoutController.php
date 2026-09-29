<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\ProductVariant;
use App\Models\PromoCode;
use App\Services\OrderService;
use App\Services\PaymentGatewayService;
use App\Services\PromoCodeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends Controller
{
    /**
     * Validate a promo code for the checkout form.
     */
    public function validatePromo(Request $request, PromoCodeService $promoService): JsonResponse
    {
        $validated = $request->validate([
            'promo_code' => ['required', 'string'],
            'variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        /** @var Customer|null $customer */
        $customer = auth('customer')->user();
        if (! $customer) {
            return response()->json([
                'valid' => false,
                'error' => 'Please login to apply promo codes.',
            ], 401);
        }

        $variant = ProductVariant::findOrFail($validated['variant_id']);

        $result = $promoService->validate(
            code: $validated['promo_code'],
            customer: $customer,
            variant: $variant,
            quantity: (int) $validated['quantity']
        );

        if (! $result['valid']) {
            return response()->json([
                'valid' => false,
                'error' => $result['error'] ?? 'Invalid promo code.',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'discount_amount' => $result['discount_amount'],
            'final_total' => $result['final_total'],
            'subtotal' => $result['subtotal'],
            'code' => strtoupper(trim($validated['promo_code'])),
        ]);
    }

    /**
     * Instant 1-Click purchase using Customer Wallet.
     */
    public function walletCheckout(Request $request, OrderService $orderService): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        $validated = $request->validate([
            'variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
            'promo_code' => ['nullable', 'string'],
            'service_data' => ['nullable', 'array'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $variant = ProductVariant::findOrFail($validated['variant_id']);

        // Find promo code model if supplied
        $promo = null;
        if (! empty($validated['promo_code'])) {
            $promo = PromoCode::where('code', strtoupper(trim($validated['promo_code'])))->first();
        }

        try {
            $order = $orderService->createOrder(
                customer: $customer,
                variant: $variant,
                quantity: (int) $validated['quantity'],
                paymentMethod: PaymentMethod::Wallet,
                extra: [
                    'promo_code' => $promo,
                    'service_data' => $validated['service_data'] ?? null,
                    'customer_notes' => $validated['customer_notes'] ?? null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            $orderService->checkoutWithWallet($order);

            $order->refresh();

            if ($order->isService()) {
                return redirect()->route('customer.orders')->with('success', "Order #{$order->order_number} placed successfully! Our support team will process your service shortly.");
            }

            return redirect()->route('customer.keys')->with('success', "Order #{$order->order_number} completed! Your license keys have been delivered.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Checkout via automated payment gateway (bKash / Nagad / Rocket / Cards).
     */
    public function gatewayCheckout(
        Request $request,
        OrderService $orderService,
        PaymentGatewayService $gatewayService
    ): Response|RedirectResponse {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        $validated = $request->validate([
            'variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
            'promo_code' => ['nullable', 'string'],
            'service_data' => ['nullable', 'array'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $variant = ProductVariant::findOrFail($validated['variant_id']);

        $promo = null;
        if (! empty($validated['promo_code'])) {
            $promo = PromoCode::where('code', strtoupper(trim($validated['promo_code'])))->first();
        }

        try {
            $order = $orderService->createOrder(
                customer: $customer,
                variant: $variant,
                quantity: (int) $validated['quantity'],
                paymentMethod: PaymentMethod::Gateway,
                extra: [
                    'promo_code' => $promo,
                    'service_data' => $validated['service_data'] ?? null,
                    'customer_notes' => $validated['customer_notes'] ?? null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            $transaction = $gatewayService->initiateOrderPayment($order);

            $paymentUrl = $gatewayService->createGatewaySession(
                transaction: $transaction,
                customer: $customer,
                redirectUrl: route('payment.verify'),
                cancelUrl: route('payment.cancel'),
                webhookUrl: route('payment.webhook')
            );

            return Inertia::location($paymentUrl);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
