<?php

namespace App\Http\Controllers;

use App\Enums\PaymentTransactionStatus;
use App\Enums\PaymentTransactionType;
use App\Services\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentController extends Controller
{
    public function __construct(
        public readonly PaymentGatewayService $paymentService
    ) {}

    /**
     * Handle incoming UddoktaPay IPN Webhook.
     * Note: Make sure to exclude this route from CSRF verification if used in web.php,
     * or put it in api.php.
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $apiKeyHeader = $request->header('RT-UDDOKTAPAY-API-KEY');

        $result = $this->paymentService->processWebhook($payload, $apiKeyHeader);

        return response()->json([
            'message' => $result['message'],
        ], $result['status']);
    }

    /**
     * Handle browser redirect verification.
     */
    public function verify(Request $request): RedirectResponse
    {
        $invoiceId = $request->query('invoice_id');

        if (! $invoiceId) {
            return redirect('/')->with('error', 'Invalid payment verification link. No invoice ID was provided.');
        }

        $transaction = $this->paymentService->verifyPayment((string) $invoiceId);

        if (! $transaction) {
            return redirect('/')->with('error', 'Payment transaction record was not found or has expired. Please try again.');
        }

        if ($transaction->status === PaymentTransactionStatus::Completed) {
            if ($transaction->type === PaymentTransactionType::Deposit) {
                $amount = '৳ '.number_format((float) $transaction->amount, 2);

                return redirect('/wallet')->with('success', "Wallet deposit successful! {$amount} has been added to your balance.");
            }

            if ($transaction->order) {
                return redirect()->route('customer.keys')->with('success', "Payment confirmed! Order #{$transaction->order->order_number} completed and your license key(s) are ready in 'My Keys'.");
            }

            return redirect('/dashboard')->with('success', 'Payment completed successfully! Thank you for your purchase.');
        }

        return redirect('/dashboard')->with('error', 'Payment verification failed or payment is still pending. If money was deducted, please message support.');
    }

    /**
     * Handle payment cancellation redirect.
     */
    public function cancel(): RedirectResponse
    {
        return redirect('/dashboard')->with('info', 'Payment checkout was cancelled. No funds were charged from your account.');
    }
}
