<?php

namespace Database\Seeders;

use App\Enums\DiscountType;
use App\Models\Product;
use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstProduct = Product::first();

        // 1. Storewide Fixed Welcome Discount
        PromoCode::firstOrCreate(
            ['code' => 'WELCOME50'],
            [
                'description' => 'Flat ৳50 discount on your first purchase (min spend ৳200)',
                'type' => DiscountType::Fixed,
                'value' => 50.00,
                'min_spend' => 200.00,
                'max_discount' => null,
                'max_uses' => 500,
                'max_uses_per_customer' => 1,
                'exclude_resellers' => true,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ]
        );

        // 2. Percentage Eid Campaign with Max Cap
        PromoCode::firstOrCreate(
            ['code' => 'EID2026'],
            [
                'description' => 'Eid special 15% discount up to ৳300 off',
                'type' => DiscountType::Percentage,
                'value' => 15.00,
                'min_spend' => 500.00,
                'max_discount' => 300.00,
                'max_uses' => 250,
                'max_uses_per_customer' => 2,
                'exclude_resellers' => true,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addMonths(2),
                'is_active' => true,
            ]
        );

        // 3. Product-Specific Flash Sale
        if ($firstProduct) {
            PromoCode::firstOrCreate(
                ['code' => 'FLASH20'],
                [
                    'description' => "Exclusive 20% discount on {$firstProduct->name}",
                    'type' => DiscountType::Percentage,
                    'value' => 20.00,
                    'product_id' => $firstProduct->id,
                    'min_spend' => 300.00,
                    'max_discount' => 200.00,
                    'max_uses' => 100,
                    'max_uses_per_customer' => 1,
                    'exclude_resellers' => true,
                    'starts_at' => now()->subDay(),
                    'expires_at' => now()->addDays(14),
                    'is_active' => true,
                ]
            );
        }

        // 4. Inactive / Expired Code for Admin Testing
        PromoCode::firstOrCreate(
            ['code' => 'EXPIRED10'],
            [
                'description' => 'Past seasonal 10% coupon (Expired)',
                'type' => DiscountType::Percentage,
                'value' => 10.00,
                'min_spend' => null,
                'max_discount' => null,
                'max_uses' => 50,
                'max_uses_per_customer' => 1,
                'exclude_resellers' => false,
                'starts_at' => now()->subDays(60),
                'expires_at' => now()->subDays(10),
                'is_active' => true,
            ]
        );
    }
}
