<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'category_slug' => 'free-fire-vip-panels',
                'name' => 'FF Headshot Master VIP Injector',
                'description' => 'Top rated VIP panel for Free Fire with automated aiming, custom ESP overlay, and anti-ban security protocol.',
                'icon' => 'heroicon-o-fire',
                'demo_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'features' => [
                    'Auto Aim Lock (100% Accuracy)',
                    'Smart ESP Line & Box',
                    'Anti-Ban Security Bypass',
                    'Compatible with Android 10-14',
                    'Zero Root Required',
                ],
                'status' => true,
                'sort_order' => 1,
                'variants' => [
                    ['duration_name' => '1 Day VIP', 'duration_days' => 1, 'regular_price' => 180.00, 'offer_price' => 150.00, 'cost_price' => 100.00, 'is_popular' => false],
                    ['duration_name' => '7 Days Pro', 'duration_days' => 7, 'regular_price' => 600.00, 'offer_price' => 490.00, 'cost_price' => 350.00, 'is_popular' => true],
                    ['duration_name' => '30 Days Master', 'duration_days' => 30, 'regular_price' => 1800.00, 'offer_price' => 1450.00, 'cost_price' => 1050.00, 'is_popular' => false],
                ],
            ],
            [
                'category_slug' => 'pubg-mobile-tools',
                'name' => 'PUBG Safe Vision ESP 64-bit',
                'description' => 'Clean and safe 64-bit overlay for competitive PUBG Mobile gamers. Non-memory modifying external ESP.',
                'icon' => 'heroicon-o-shield-check',
                'demo_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'features' => [
                    'External Visual Overlay',
                    'Item & Vehicle Radar',
                    'Enemy Distance Indicator',
                    'Stream Proof Mode',
                ],
                'status' => true,
                'sort_order' => 2,
                'variants' => [
                    ['duration_name' => '7 Days Pass', 'duration_days' => 7, 'regular_price' => 750.00, 'offer_price' => 650.00, 'cost_price' => 450.00, 'is_popular' => false],
                    ['duration_name' => '30 Days Ultimate', 'duration_days' => 30, 'regular_price' => 2200.00, 'offer_price' => 1850.00, 'cost_price' => 1300.00, 'is_popular' => true],
                ],
            ],
            [
                'category_slug' => 'windows-office-keys',
                'name' => 'Windows 11 Pro Lifetime Retail Key',
                'description' => 'Permanent retail activation key for Windows 11 Professional. Instant digital key delivery with lifetime updates.',
                'icon' => 'heroicon-o-key',
                'demo_video_url' => null,
                'features' => [
                    '100% Genuine Microsoft Retail License',
                    'Supports 32-bit & 64-bit Systems',
                    'Global Region Activation',
                    'Lifetime Validity & Free Updates',
                    'Instant Delivery via SMS & Email',
                ],
                'status' => true,
                'sort_order' => 3,
                'variants' => [
                    ['duration_name' => '1 PC Lifetime Retail', 'duration_days' => 3650, 'regular_price' => 1200.00, 'offer_price' => 750.00, 'cost_price' => 400.00, 'is_popular' => true],
                    ['duration_name' => '3 PC Family Bundle', 'duration_days' => 3650, 'regular_price' => 3200.00, 'offer_price' => 1990.00, 'cost_price' => 1100.00, 'is_popular' => false],
                ],
            ],
            [
                'category_slug' => 'vpn-private-proxies',
                'name' => 'CyberTunnel Ultra Gaming VPN',
                'description' => 'Optimized gaming VPN with low ping routes to Singapore, Europe, and USA servers. Includes DDoS protection.',
                'icon' => 'heroicon-o-globe-alt',
                'demo_video_url' => null,
                'features' => [
                    'Zero Packet Loss Routing',
                    'Dedicated Singapore Gaming Servers',
                    'Unlimited High-Speed Bandwidth',
                    'No Logs Privacy Policy',
                ],
                'status' => true,
                'sort_order' => 4,
                'variants' => [
                    ['duration_name' => '1 Month Subscription', 'duration_days' => 30, 'regular_price' => 450.00, 'offer_price' => 350.00, 'cost_price' => 200.00, 'is_popular' => false],
                    ['duration_name' => '3 Months Plan', 'duration_days' => 90, 'regular_price' => 1250.00, 'offer_price' => 950.00, 'cost_price' => 550.00, 'is_popular' => true],
                    ['duration_name' => '1 Year VIP Plan', 'duration_days' => 365, 'regular_price' => 4200.00, 'offer_price' => 2800.00, 'cost_price' => 1600.00, 'is_popular' => false],
                ],
            ],
        ];

        foreach ($products as $pData) {
            $category = Category::where('slug', $pData['category_slug'])->first()
                ?? Category::first();

            $slug = Str::slug($pData['name']);
            $product = Product::firstOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category?->id,
                    'name' => $pData['name'],
                    'slug' => $slug,
                    'icon' => $pData['icon'],
                    'image' => null,
                    'demo_video_url' => $pData['demo_video_url'],
                    'description' => $pData['description'],
                    'features' => $pData['features'],
                    'status' => $pData['status'],
                    'sort_order' => $pData['sort_order'],
                ]
            );

            foreach ($pData['variants'] as $vData) {
                $product->variants()->firstOrCreate(
                    [
                        'duration_name' => $vData['duration_name'],
                        'duration_days' => $vData['duration_days'],
                    ],
                    [
                        'regular_price' => $vData['regular_price'],
                        'offer_price' => $vData['offer_price'],
                        'cost_price' => $vData['cost_price'],
                        'is_popular' => $vData['is_popular'],
                    ]
                );
            }
        }
    }
}
