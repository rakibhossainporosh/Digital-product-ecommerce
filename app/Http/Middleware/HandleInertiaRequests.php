<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var Customer|null $customer */
        $customer = auth('customer')->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'customer' => $customer ? [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'whatsapp_number' => $customer->whatsapp_number,
                    'balance' => (float) $customer->balance,
                    'formatted_balance' => '৳ '.number_format((float) $customer->balance, 2),
                    'referral_code' => $customer->referral_code,
                    'is_reseller' => (bool) $customer->is_reseller,
                    'reseller_discount' => (float) $customer->reseller_discount,
                ] : null,
            ],
            'settings' => [
                'app_name' => setting('app_name', 'Panel Sell BD'),
                'app_tagline' => setting('app_tagline', 'Instant Digital Game Panel & Key Delivery'),
                'site_logo' => setting('site_logo') ? asset('storage/'.setting('site_logo')) : null,
                'site_favicon' => setting('site_favicon') ? asset('storage/'.setting('site_favicon')) : null,
                'currency_symbol' => setting('currency_symbol', '৳'),
                'support_whatsapp' => setting('support_whatsapp', '+8801700000000'),
                'support_email' => setting('support_email', 'support@panelsell.store'),
                'maintenance_mode' => (bool) setting('maintenance_mode', false),
                'top_bar_notice_enabled' => (bool) setting('top_bar_notice_enabled', true),
                'top_bar_notice_label' => (string) setting('top_bar_notice_label', 'Notice'),
                'top_bar_notice_text' => (string) setting('top_bar_notice_text', '🔥 100% Instant License Key & Panel Delivery · Safe & Anti-Ban Gaming Solutions · 24/7 WhatsApp Customer Support Active'),
                'features_ribbon_enabled' => (bool) setting('features_ribbon_enabled', true),
                'feature_1_title' => (string) setting('feature_1_title', '1-Sec Key Delivery'),
                'feature_1_subtitle' => (string) setting('feature_1_subtitle', 'Instant code generate'),
                'feature_2_title' => (string) setting('feature_2_title', '100% Anti-Ban'),
                'feature_2_subtitle' => (string) setting('feature_2_subtitle', 'Safest bypass systems'),
                'feature_3_title' => (string) setting('feature_3_title', 'Root & Non-Root'),
                'feature_3_subtitle' => (string) setting('feature_3_subtitle', 'All Android & iOS devices'),
                'feature_4_title' => (string) setting('feature_4_title', '24/7 Engineer Support'),
                'feature_4_subtitle' => (string) setting('feature_4_subtitle', 'Direct WhatsApp help'),
                'footer_about_text' => (string) setting('footer_about_text', 'Discover the ultimate destination for premium game panels, safe non-root & root APK mods, and instant digital license key deliveries in Bangladesh.'),
                'footer_badge_1' => (string) setting('footer_badge_1', '1-Second Key Delivery'),
                'footer_badge_2' => (string) setting('footer_badge_2', '100% Anti-Ban'),
                'footer_support_title' => (string) setting('footer_support_title', '24/7 Support'),
                'footer_support_text' => (string) setting('footer_support_text', 'Need help with key setup or rooting? Chat directly with our verified engineers.'),
                'footer_quick_links_title' => (string) setting('footer_quick_links_title', 'Quick Links'),
                'footer_payments_title' => (string) setting('footer_payments_title', 'Accepted Payments'),
                'footer_payment_methods' => (string) setting('footer_payment_methods', 'bKash, Nagad, Rocket, Wallet Pay'),
                'footer_copyright_text' => (string) setting('footer_copyright_text', 'All rights reserved.'),
                'footer_credit_text' => (string) setting('footer_credit_text', 'Crafted for Elite Gamers & Resellers'),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ]);
    }
}
