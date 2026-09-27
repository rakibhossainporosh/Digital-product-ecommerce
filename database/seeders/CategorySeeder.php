<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Gaming Panels & Injectors',
                'description' => 'Premium VIP panels, injectors, and mod menus for competitive mobile & PC games.',
                'icon' => 'heroicon-o-puzzle-piece',
                'sort_order' => 1,
                'status' => true,
                'children' => [
                    [
                        'name' => 'Free Fire VIP Panels',
                        'description' => 'High-performance undetected Free Fire injectors and VIP panels.',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'PUBG Mobile Tools',
                        'description' => 'Safe and updated gaming tools for PUBG Mobile.',
                        'sort_order' => 2,
                    ],
                ],
            ],
            [
                'name' => 'Software & Subscriptions',
                'description' => 'Genuine software licenses, productivity tools, and cloud subscriptions.',
                'icon' => 'heroicon-o-computer-desktop',
                'sort_order' => 2,
                'status' => true,
                'children' => [
                    [
                        'name' => 'Antivirus & Internet Security',
                        'description' => 'Top tier antivirus activation keys and security suites.',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Streaming & OTT Accounts',
                        'description' => 'Premium entertainment subscriptions and family plans.',
                        'sort_order' => 2,
                    ],
                ],
            ],
            [
                'name' => 'VPN & Private Proxies',
                'description' => 'Ultra-fast dedicated gaming VPNs, residential proxies, and privacy protection.',
                'icon' => 'heroicon-o-globe-alt',
                'sort_order' => 3,
                'status' => true,
                'children' => [],
            ],
            [
                'name' => 'License Keys & Activations',
                'description' => 'Instant delivery serial keys for operating systems and office suites.',
                'icon' => 'heroicon-o-key',
                'sort_order' => 4,
                'status' => true,
                'children' => [
                    [
                        'name' => 'Windows & Office Keys',
                        'description' => '100% genuine retail & OEM activation licenses.',
                        'sort_order' => 1,
                    ],
                ],
            ],
            [
                'name' => 'Game Credits & Gift Cards',
                'description' => 'Fast delivery game points, top-up vouchers, and digital gift cards.',
                'icon' => 'heroicon-o-ticket',
                'sort_order' => 5,
                'status' => true,
                'children' => [],
            ],
        ];

        foreach ($categories as $catData) {
            $children = $catData['children'] ?? [];
            unset($catData['children']);

            $slug = Str::slug($catData['name']);
            $parent = Category::firstOrCreate(
                ['slug' => $slug],
                array_merge($catData, [
                    'slug' => $slug,
                    'meta_title' => $catData['name'].' - Buy Online',
                    'meta_description' => $catData['description'],
                ])
            );

            foreach ($children as $childData) {
                $childSlug = Str::slug($childData['name']);
                Category::firstOrCreate(
                    ['slug' => $childSlug],
                    array_merge($childData, [
                        'parent_id' => $parent->id,
                        'slug' => $childSlug,
                        'status' => true,
                        'icon' => 'heroicon-o-tag',
                        'meta_title' => $childData['name'].' - Buy Online',
                        'meta_description' => $childData['description'],
                    ])
                );
            }
        }
    }
}
