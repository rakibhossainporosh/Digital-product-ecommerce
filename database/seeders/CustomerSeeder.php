<?php

namespace Database\Seeders;

use App\Enums\CustomerStatus;
use App\Enums\TransactionDirection;
use App\Enums\WalletTransactionType;
use App\Models\Customer;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $walletService = app(WalletService::class);
        $admin = User::first();

        // 1. VIP Reseller Customer
        $reseller = Customer::firstOrCreate(
            ['email' => 'reseller@example.com'],
            [
                'name' => 'Tanvir Ahmed (VIP Reseller)',
                'whatsapp_number' => '+8801711223344',
                'password' => Hash::make('password'),
                'status' => CustomerStatus::Active,
                'is_reseller' => true,
                'reseller_discount' => 12.50,
            ]
        );

        if ($reseller->walletTransactions()->count() === 0) {
            $walletService->credit(
                customer: $reseller,
                amount: 5000.00,
                type: WalletTransactionType::Deposit,
                description: 'Initial UddoktaPay Gateway Deposit',
                referenceId: 'DEP-UDD-98124'
            );

            $walletService->debit(
                customer: $reseller,
                amount: 1450.00,
                type: WalletTransactionType::Purchase,
                description: 'Bulk purchase: 5x PUBG Mobile 1 Month Key',
                referenceId: 'ORD-2026-001'
            );
        }

        // 2. Regular Active Customer
        $customer1 = Customer::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Rahim Chowdhury',
                'whatsapp_number' => '+8801812345678',
                'password' => Hash::make('password'),
                'status' => CustomerStatus::Active,
                'is_reseller' => false,
                'referred_by' => $reseller->id,
            ]
        );

        if ($customer1->walletTransactions()->count() === 0) {
            $walletService->credit(
                customer: $customer1,
                amount: 1000.00,
                type: WalletTransactionType::Deposit,
                description: 'bKash Online Deposit',
                referenceId: 'DEP-BK-44219'
            );

            $walletService->debit(
                customer: $customer1,
                amount: 450.00,
                type: WalletTransactionType::Purchase,
                description: 'Purchase: Windows 11 Pro Retail Key',
                referenceId: 'ORD-2026-002'
            );

            if ($admin) {
                $walletService->adminAdjust(
                    customer: $customer1,
                    amount: 50.00,
                    direction: TransactionDirection::Credit,
                    reason: 'Promotional loyalty cash reward',
                    admin: $admin
                );
            }
        }

        // 3. Second Customer with Low/Zero Balance
        Customer::firstOrCreate(
            ['email' => 'sakib@example.com'],
            [
                'name' => 'Sakib Al Hasan',
                'whatsapp_number' => '+8801919876543',
                'password' => Hash::make('password'),
                'status' => CustomerStatus::Active,
                'is_reseller' => false,
            ]
        );

        // 4. Banned Customer
        Customer::firstOrCreate(
            ['email' => 'fraudster@example.com'],
            [
                'name' => 'Suspicious User (Banned)',
                'whatsapp_number' => '+8801500000000',
                'password' => Hash::make('password'),
                'status' => CustomerStatus::Banned,
                'is_reseller' => false,
            ]
        );
    }
}
