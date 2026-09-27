<?php

namespace App\Http\Controllers;

use App\Enums\PaymentTransactionStatus;
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
            return redirect('/')->with('error', 'Invalid payment verification request.');
        }

        $transaction = $this->paymentService->verifyPayment((string) $invoiceId);

        if (! $transaction) {
            return redirect('/')->with('error', 'Payment transaction not found.');
        }

        if ($transaction->status === PaymentTransactionStatus::Completed) {
            return redirect('/dashboard')->with('success', 'Payment successful!');
        }

        return redirect('/dashboard')->with('error', 'Payment failed or is still pending.');
    }

    /**
     * Handle payment cancellation redirect.
     */
    public function cancel(): RedirectResponse
    {
        return redirect('/dashboard')->with('error', 'Payment was cancelled.');
    }
}
