<?php

namespace Database\Seeders;

use App\Services\SettingService;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(SettingService $settingService): void
    {
        $settings = [
            // General
            [
                'group' => 'general',
                'key' => 'app_name',
                'value' => 'Digital Product Store',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Brand name of the digital store',
            ],
            [
                'group' => 'general',
                'key' => 'app_tagline',
                'value' => 'Instant Digital License & Account Delivery Platform',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Tagline displayed on storefront header',
            ],
            [
                'group' => 'general',
                'key' => 'currency_symbol',
                'value' => '৳',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Default currency symbol (e.g. ৳ or $)',
            ],
            [
                'group' => 'general',
                'key' => 'support_email',
                'value' => 'support@example.com',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Customer support email address',
            ],
            [
                'group' => 'general',
                'key' => 'support_whatsapp',
                'value' => '+8801700000000',
                'type' => 'string',
                'is_public' => true,
                'description' => 'WhatsApp support number',
            ],

            // Maintenance
            [
                'group' => 'maintenance',
                'key' => 'maintenance_mode',
                'value' => false,
                'type' => 'boolean',
                'is_public' => true,
                'description' => 'Toggle site maintenance mode on/off',
            ],
            [
                'group' => 'maintenance',
                'key' => 'maintenance_headline',
                'value' => 'Under Scheduled Maintenance',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Headline title on maintenance screen',
            ],
            [
                'group' => 'maintenance',
                'key' => 'maintenance_message',
                'value' => 'Our platform is currently undergoing scheduled maintenance and system optimization. We will be back shortly!',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Message displayed to visitors during maintenance',
            ],
            [
                'group' => 'maintenance',
                'key' => 'maintenance_bypass_key',
                'value' => 'preview-maintenance-key-2026',
                'type' => 'string',
                'is_public' => false,
                'description' => 'Secret bypass query key (?bypass_key=...) to view site during maintenance',
            ],

            // Financial
            [
                'group' => 'financial',
                'key' => 'referral_commission_percentage',
                'value' => 5.0,
                'type' => 'float',
                'is_public' => false,
                'description' => 'Percentage credited to referrer on referred customer orders',
            ],
            [
                'group' => 'financial',
                'key' => 'min_deposit_amount',
                'value' => 50.0,
                'type' => 'float',
                'is_public' => true,
                'description' => 'Minimum customer wallet deposit amount',
            ],
            [
                'group' => 'financial',
                'key' => 'max_deposit_amount',
                'value' => 50000.0,
                'type' => 'float',
                'is_public' => true,
                'description' => 'Maximum customer wallet deposit amount per transaction',
            ],

            // Gateway
            [
                'group' => 'gateway',
                'key' => 'gateway_enabled',
                'value' => true,
                'type' => 'boolean',
                'is_public' => false,
                'description' => 'Enable automated payment gateway',
            ],
            [
                'group' => 'gateway',
                'key' => 'gateway_base_url',
                'value' => 'https://sandbox.uddoktapay.com/api/checkout-v2',
                'type' => 'string',
                'is_public' => false,
                'description' => 'Base endpoint for payment gateway API',
            ],
            [
                'group' => 'gateway',
                'key' => 'gateway_api_key',
                'value' => 'test_api_key_uddoktapay',
                'type' => 'encrypted',
                'is_public' => false,
                'description' => 'Encrypted payment gateway API secret key',
            ],

            // Supplier API
            [
                'group' => 'supplier',
                'key' => 'supplier_api_enabled',
                'value' => false,
                'type' => 'boolean',
                'is_public' => false,
                'description' => 'Enable automated external supplier API fallback',
            ],
            [
                'group' => 'supplier',
                'key' => 'supplier_api_url',
                'value' => 'https://api.supplierpanel.example/v1',
                'type' => 'string',
                'is_public' => false,
                'description' => 'External supplier API endpoint',
            ],
            [
                'group' => 'supplier',
                'key' => 'supplier_api_key',
                'value' => 'test_supplier_secret_key',
                'type' => 'encrypted',
                'is_public' => false,
                'description' => 'Encrypted supplier panel API credentials',
            ],
        ];

        foreach ($settings as $setting) {
            $settingService->set(
                key: $setting['key'],
                value: $setting['value'],
                group: $setting['group'],
                type: $setting['type'],
                isPublic: $setting['is_public'],
                description: $setting['description']
            );
        }
    }
}
