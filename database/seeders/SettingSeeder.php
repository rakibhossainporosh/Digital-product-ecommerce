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
                'key' => 'site_logo',
                'value' => null,
                'type' => 'string',
                'is_public' => true,
                'description' => 'Store brand logo image path',
            ],
            [
                'group' => 'general',
                'key' => 'site_favicon',
                'value' => null,
                'type' => 'string',
                'is_public' => true,
                'description' => 'Store browser favicon image path',
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
            [
                'group' => 'general',
                'key' => 'top_bar_notice_enabled',
                'value' => true,
                'type' => 'boolean',
                'is_public' => true,
                'description' => 'Toggle top bar announcement marquee on/off',
            ],
            [
                'group' => 'general',
                'key' => 'top_bar_notice_label',
                'value' => 'Notice',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Badge label displayed in the top bar marquee',
            ],
            [
                'group' => 'general',
                'key' => 'top_bar_notice_text',
                'value' => '🔥 100% Instant License Key & Panel Delivery · Safe & Anti-Ban Gaming Solutions · 24/7 WhatsApp Customer Support Active',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Scrolling text displayed in the top bar marquee',
            ],

            // Feature Highlights Ribbon
            [
                'group' => 'features',
                'key' => 'features_ribbon_enabled',
                'value' => true,
                'type' => 'boolean',
                'is_public' => true,
                'description' => 'Toggle feature highlights ribbon on/off',
            ],
            [
                'group' => 'features',
                'key' => 'feature_1_title',
                'value' => '1-Sec Key Delivery',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Title for feature card 1',
            ],
            [
                'group' => 'features',
                'key' => 'feature_1_subtitle',
                'value' => 'Instant code generate',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Subtitle for feature card 1',
            ],
            [
                'group' => 'features',
                'key' => 'feature_2_title',
                'value' => '100% Anti-Ban',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Title for feature card 2',
            ],
            [
                'group' => 'features',
                'key' => 'feature_2_subtitle',
                'value' => 'Safest bypass systems',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Subtitle for feature card 2',
            ],
            [
                'group' => 'features',
                'key' => 'feature_3_title',
                'value' => 'Root & Non-Root',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Title for feature card 3',
            ],
            [
                'group' => 'features',
                'key' => 'feature_3_subtitle',
                'value' => 'All Android & iOS devices',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Subtitle for feature card 3',
            ],
            [
                'group' => 'features',
                'key' => 'feature_4_title',
                'value' => '24/7 Engineer Support',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Title for feature card 4',
            ],
            [
                'group' => 'features',
                'key' => 'feature_4_subtitle',
                'value' => 'Direct WhatsApp help',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Subtitle for feature card 4',
            ],

            // Footer
            [
                'group' => 'footer',
                'key' => 'footer_about_text',
                'value' => 'Discover the ultimate destination for premium game panels, safe non-root & root APK mods, and instant digital license key deliveries in Bangladesh.',
                'type' => 'string',
                'is_public' => true,
                'description' => 'About and bio text displayed in footer brand column',
            ],
            [
                'group' => 'footer',
                'key' => 'footer_badge_1',
                'value' => '1-Second Key Delivery',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Footer badge 1 text',
            ],
            [
                'group' => 'footer',
                'key' => 'footer_badge_2',
                'value' => '100% Anti-Ban',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Footer badge 2 text',
            ],
            [
                'group' => 'footer',
                'key' => 'footer_support_title',
                'value' => '24/7 Support',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Heading for footer support section',
            ],
            [
                'group' => 'footer',
                'key' => 'footer_support_text',
                'value' => 'Need help with key setup or rooting? Chat directly with our verified engineers.',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Description text in footer support section',
            ],
            [
                'group' => 'footer',
                'key' => 'footer_quick_links_title',
                'value' => 'Quick Links',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Heading for footer quick links section',
            ],
            [
                'group' => 'footer',
                'key' => 'footer_payments_title',
                'value' => 'Accepted Payments',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Label for accepted payments in footer',
            ],
            [
                'group' => 'footer',
                'key' => 'footer_payment_methods',
                'value' => 'bKash, Nagad, Rocket, Wallet Pay',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Comma separated list of accepted payment methods',
            ],
            [
                'group' => 'footer',
                'key' => 'footer_copyright_text',
                'value' => 'All rights reserved.',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Copyright notice suffix in footer bottom bar',
            ],
            [
                'group' => 'footer',
                'key' => 'footer_credit_text',
                'value' => 'Crafted for Elite Gamers & Resellers',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Credit / tagline in footer bottom bar',
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
