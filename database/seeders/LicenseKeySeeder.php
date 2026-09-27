<?php

namespace Database\Seeders;

use App\Enums\LicenseKeyStatus;
use App\Models\LicenseKey;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LicenseKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        $variants = ProductVariant::all();

        foreach ($variants as $variant) {
            // Seed 8 Available keys for each variant
            for ($i = 1; $i <= 8; $i++) {
                LicenseKey::create([
                    'product_variant_id' => $variant->id,
                    'key' => 'VIP-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)),
                    'status' => LicenseKeyStatus::Available,
                    'order_id' => null,
                    'order_item_id' => null,
                    'sold_at' => null,
                    'batch_ref' => 'SEED-BATCH-001',
                    'notes' => 'Seeded inventory key',
                    'created_by' => $admin?->id,
                ]);
            }

            // Seed 2 Sold keys for historical reporting
            for ($j = 1; $j <= 2; $j++) {
                LicenseKey::create([
                    'product_variant_id' => $variant->id,
                    'key' => 'VIP-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)),
                    'status' => LicenseKeyStatus::Sold,
                    'order_id' => 1000 + $variant->id * 10 + $j,
                    'order_item_id' => $j,
                    'sold_at' => now()->subDays(rand(1, 10)),
                    'batch_ref' => 'SEED-BATCH-001',
                    'notes' => 'Historical sold key',
                    'created_by' => $admin?->id,
                ]);
            }
        }
    }
}
