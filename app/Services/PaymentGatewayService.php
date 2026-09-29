<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentTransactionStatus;
use App\Enums\PaymentTransactionType;
use App\Enums\WalletTransactionType;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class PaymentGatewayService
{
    public function __construct(
        public readonly WalletService $walletService,
        public readonly OrderFulfillmentService $fulfillmentService
    ) {}

    /**
     * Initiate a wallet deposit checkout session.
     */
    public function initiateDeposit(Customer $customer, float $amount): PaymentTransaction
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Deposit amount must be greater than zero.');
        }

        return PaymentTransaction::create([
            'uuid' => 'TRX-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'customer_id' => $customer->id,
            'type' => PaymentTransactionType::Deposit,
            'amount' => $amount,
            'currency' => 'BDT',
            'gateway_name' => 'uddoktapay',
            'status' => PaymentTransactionStatus::Pending,
        ]);
    }

    /**
     * Initiate a direct order payment checkout session.
     */
    public function initiateOrderPayment(Order $order): PaymentTransaction
    {
        if ($order->status !== OrderStatus::Pending) {
            throw new InvalidArgumentException('Order is not in a pending state.');
        }

        return PaymentTransaction::create([
            'uuid' => 'TRX-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
            'type' => PaymentTransactionType::OrderPayment,
            'amount' => (float) $order->total_amount,
            'currency' => 'BDT',
            'gateway_name' => 'uddoktapay',
            'status' => PaymentTransactionStatus::Pending,
        ]);
    }

    /**
     * Create Gateway Session and return the payment URL.
     */
    public function createGatewaySession(
        PaymentTransaction $transaction,
        Customer $customer,
        string $redirectUrl,
        string $cancelUrl,
        string $webhookUrl
    ): string {
        $apiKey = setting('uddoktapay_api_key');
        $baseUrl = setting('uddoktapay_base_url');

        if (! $apiKey || ! $baseUrl) {
            throw new RuntimeException('Payment gateway is not configured properly.');
        }

        $payload = [
            'full_name' => $customer->name,
            'email' => $customer->email ?? 'no-email@example.com',
            'amount' => $transaction->amount,
            'metadata' => [
                'transaction_uuid' => $transaction->uuid,
            ],
            'redirect_url' => $redirectUrl,
            'cancel_url' => $cancelUrl,
            'webhook_url' => $webhookUrl,
        ];

        $response = Http::withHeaders([
            'RT-UDDOKTAPAY-API-KEY' => (string) $apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post(rtrim((string) $baseUrl, '/').'/api/checkout-v2', $payload);

        if (! $response->successful()) {
            Log::error('UddoktaPay checkout failed', ['response' => $response->json(), 'status' => $response->status()]);
            throw new RuntimeException('Failed to generate payment URL from gateway.');
        }

        $paymentUrl = $response->json('payment_url');

        $transaction->update([
            'payment_url' => $paymentUrl,
        ]);

        return $paymentUrl;
    }

    /**
     * Process incoming Webhook (IPN) idempotently and safely.
     *
     * @return array{status: int, message: string}
     */
    public function processWebhook(array $payload, ?string $apiKeyHeader): array
    {
        $configuredApiKey = setting('uddoktapay_api_key');

        if (! $configuredApiKey || $apiKeyHeader !== $configuredApiKey) {
            Log::warning('Invalid Webhook API Key attempt', ['header' => $apiKeyHeader]);

            return ['status' => 401, 'message' => 'Unauthorized Access'];
        }

        if (! isset($payload['metadata']['transaction_uuid']) || ! isset($payload['status'])) {
            return ['status' => 400, 'message' => 'Invalid Payload'];
        }

        $transactionUuid = $payload['metadata']['transaction_uuid'];

        return DB::transaction(function () use ($payload, $transactionUuid): array {
            $transaction = PaymentTransaction::where('uuid', $transactionUuid)->lockForUpdate()->first();

            if (! $transaction) {
                return ['status' => 404, 'message' => 'Transaction Not Found'];
            }

            if ($transaction->status !== PaymentTransactionStatus::Pending) {
                // Idempotent bypass
                return ['status' => 200, 'message' => 'Transaction already processed'];
            }

            if ($payload['status'] === 'COMPLETED') {
                $this->handleSuccessfulPayment($transaction, $payload);
            } else {
                $transaction->update([
                    'status' => PaymentTransactionStatus::Failed,
                    'raw_payload' => $payload,
                ]);
            }

            return ['status' => 200, 'message' => 'Processed Successfully'];
        });
    }

    /**
     * Verify payment using API fallback when redirected.
     */
    public function verifyPayment(string $invoiceId): ?PaymentTransaction
    {
        $transaction = PaymentTransaction::where('invoice_id', $invoiceId)->first();

        if ($transaction && $transaction->status !== PaymentTransactionStatus::Pending) {
            return $transaction;
        }

        $apiKey = setting('uddoktapay_api_key');
        $baseUrl = setting('uddoktapay_base_url');

        $response = Http::withHeaders([
            'RT-UDDOKTAPAY-API-KEY' => (string) $apiKey,
            'Accept' => 'application/json',
        ])->post(rtrim((string) $baseUrl, '/').'/api/verify-payment', [
            'invoice_id' => $invoiceId,
        ]);

        if ($response->successful()) {
            $payload = $response->json();
            if (isset($payload['metadata']['transaction_uuid'])) {
                $this->processWebhook($payload, (string) $apiKey);

                return PaymentTransaction::where('invoice_id', $invoiceId)->first();
            }
        }

        return $transaction;
    }

    /**
     * Handle the successful payment state transition logic.
     */
    private function handleSuccessfulPayment(PaymentTransaction $transaction, array $payload): void
    {
        $transaction->update([
            'status' => PaymentTransactionStatus::Completed,
            'invoice_id' => $payload['invoice_id'] ?? null,
            'gateway_transaction_id' => $payload['transaction_id'] ?? null,
            'payment_channel' => $payload['payment_method'] ?? null,
            'fee' => $payload['fee'] ?? 0,
            'paid_at' => now(),
            'raw_payload' => $payload,
        ]);

        if ($transaction->type === PaymentTransactionType::Deposit) {
            $this->walletService->credit(
                customer: $transaction->customer,
                amount: (float) $transaction->amount,
                type: WalletTransactionType::Deposit,
                description: 'Gateway Deposit via '.($payload['payment_method'] ?? 'UddoktaPay'),
                referenceId: $transaction->uuid,
                metadata: ['invoice_id' => $payload['invoice_id'] ?? null]
            );
        } elseif ($transaction->type === PaymentTransactionType::OrderPayment && $transaction->order) {
            $order = $transaction->order;

            // Note: DB transaction is already active due to lockForUpdate wrapper in processWebhook
            $order->update([
                'payment_status' => PaymentStatus::Paid,
            ]);

            if ($order->isService()) {
                $order->update([
                    'status' => OrderStatus::Processing,
                    'fulfillment_status' => FulfillmentStatus::Unfulfilled,
                    'admin_notes' => trim(($order->admin_notes ? $order->admin_notes."\n" : '').'Service order paid via gateway. Awaiting manual admin fulfillment.'),
                ]);
            } else {
                $fulfillmentResult = $this->fulfillmentService->fulfill($order);

                if (! $fulfillmentResult['success']) {
                    // Stock-out auto refund
                    $this->walletService->refund(
                        customer: $transaction->customer,
                        amount: (float) $transaction->amount,
                        description: 'Auto-refund for order #'.$order->id.' due to stock-out',
                        referenceId: $transaction->uuid,
                        metadata: ['order_id' => $order->id, 'reason' => $fulfillmentResult['error']]
                    );
                }
            }
        }
    }
}
