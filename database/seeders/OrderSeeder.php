<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\ProductVariant;
use App\Services\OrderService;
use App\Services\WalletService;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orderService = app(OrderService::class);
        $walletService = app(WalletService::class);

        $reseller = Customer::where('email', 'reseller@example.com')->first();
        $customer = Customer::where('email', 'customer@example.com')->first();
        $variant = ProductVariant::first();

        if (! $variant) {
            return;
        }

        // 1. Reseller completed order
        if ($reseller && (float) $reseller->balance >= 1000) {
            $order = $orderService->createOrder(
                customer: $reseller,
                variant: $variant,
                quantity: 2,
                paymentMethod: PaymentMethod::Wallet,
                extra: ['customer_notes' => 'Urgent delivery for retail client']
            );

            $orderService->checkoutWithWallet($order);
        }

        // 2. Regular customer completed order
        if ($customer && (float) $customer->balance >= 500) {
            $order = $orderService->createOrder(
                customer: $customer,
                variant: $variant,
                quantity: 1,
                paymentMethod: PaymentMethod::Wallet
            );

            $orderService->checkoutWithWallet($order);
        }
    }
}
