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
                'currency_symbol' => setting('currency_symbol', '৳'),
                'support_whatsapp' => setting('support_whatsapp', '+8801700000000'),
                'support_email' => setting('support_email', 'support@panelsell.store'),
                'maintenance_mode' => (bool) setting('maintenance_mode', false),
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
