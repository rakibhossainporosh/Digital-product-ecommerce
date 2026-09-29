<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\LicenseKey;
use App\Models\Order;
use App\Models\WalletTransaction;
use App\Services\PaymentGatewayService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CustomerDashboardController extends Controller
{
    /**
     * Customer main overview dashboard.
     */
    public function dashboard(): Response
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        $recentOrders = Order::where('customer_id', $customer->id)
            ->with(['product', 'productVariant'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'product_name' => $o->product?->name,
                'variant_name' => $o->productVariant?->duration_name,
                'total_amount' => (float) $o->total_amount,
                'formatted_total' => '৳ '.number_format((float) $o->total_amount, 2),
                'status' => $o->status->value,
                'fulfillment_status' => $o->fulfillment_status->value,
                'created_at' => $o->created_at->format('M d, Y h:i A'),
            ]);

        $recentKeys = LicenseKey::whereHas('order', fn ($q) => $q->where('customer_id', $customer->id))
            ->with(['productVariant.product'])
            ->latest('sold_at')
            ->take(5)
            ->get()
            ->map(fn (LicenseKey $k) => [
                'id' => $k->id,
                'product_name' => $k->productVariant?->product?->name,
                'duration_name' => $k->productVariant?->duration_name,
                'key' => $k->key,
                'sold_at' => $k->sold_at?->format('M d, Y h:i A'),
            ]);

        $totalSpent = Order::where('customer_id', $customer->id)
            ->where('status', OrderStatus::Completed)
            ->sum('total_amount');

        $totalOrdersCount = Order::where('customer_id', $customer->id)->count();
        $totalKeysCount = LicenseKey::whereHas('order', fn ($q) => $q->where('customer_id', $customer->id))->count();

        return Inertia::render('Customer/Dashboard', [
            'stats' => [
                'balance' => (float) $customer->balance,
                'formatted_balance' => '৳ '.number_format((float) $customer->balance, 2),
                'total_spent' => '৳ '.number_format((float) $totalSpent, 2),
                'total_orders' => $totalOrdersCount,
                'total_keys' => $totalKeysCount,
            ],
            'recentOrders' => $recentOrders,
            'recentKeys' => $recentKeys,
        ]);
    }

    /**
     * Customer orders list page.
     */
    public function orders(): Response
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        $orders = Order::where('customer_id', $customer->id)
            ->with(['product', 'productVariant', 'licenseKeys'])
            ->latest()
            ->paginate(10)
            ->through(fn (Order $o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'product_name' => $o->product?->name,
                'is_service' => $o->isService(),
                'variant_name' => $o->productVariant?->duration_name,
                'quantity' => $o->quantity,
                'total_amount' => (float) $o->total_amount,
                'formatted_total' => '৳ '.number_format((float) $o->total_amount, 2),
                'status' => $o->status->value,
                'fulfillment_status' => $o->fulfillment_status->value,
                'service_data' => $o->service_data,
                'keys' => $o->licenseKeys->pluck('key'),
                'created_at' => $o->created_at->format('M d, Y h:i A'),
            ]);

        return Inertia::render('Customer/Orders', [
            'orders' => $orders,
        ]);
    }

    /**
     * Customer purchased license keys page with instant copy.
     */
    public function myKeys(): Response
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        $keys = LicenseKey::whereHas('order', fn ($q) => $q->where('customer_id', $customer->id))
            ->with(['productVariant.product', 'order'])
            ->latest('sold_at')
            ->paginate(12)
            ->through(fn (LicenseKey $k) => [
                'id' => $k->id,
                'key' => $k->key,
                'product_name' => $k->productVariant?->product?->name,
                'product_slug' => $k->productVariant?->product?->slug,
                'duration_name' => $k->productVariant?->duration_name,
                'demo_video_url' => $k->productVariant?->product?->demo_video_url,
                'order_number' => $k->order?->order_number,
                'sold_at' => $k->sold_at?->format('M d, Y h:i A'),
            ]);

        return Inertia::render('Customer/MyKeys', [
            'keys' => $keys,
        ]);
    }

    /**
     * Customer wallet management and deposit page.
     */
    public function wallet(): Response
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        $transactions = WalletTransaction::where('customer_id', $customer->id)
            ->latest()
            ->paginate(15)
            ->through(fn (WalletTransaction $t) => [
                'id' => $t->id,
                'type' => $t->type->value,
                'type_label' => $t->type->getLabel(),
                'direction' => $t->direction->value,
                'amount' => (float) $t->amount,
                'formatted_amount' => ($t->direction->value === 'credit' ? '+ ' : '- ').'৳ '.number_format((float) $t->amount, 2),
                'balance_after' => (float) $t->balance_after,
                'formatted_balance_after' => '৳ '.number_format((float) $t->balance_after, 2),
                'description' => $t->description,
                'reference_id' => $t->reference_id,
                'created_at' => $t->created_at->format('M d, Y h:i A'),
            ]);

        $minDeposit = (float) setting('min_deposit_amount', 50);
        $maxDeposit = (float) setting('max_deposit_amount', 25000);

        return Inertia::render('Customer/Wallet', [
            'balance' => (float) $customer->balance,
            'formatted_balance' => '৳ '.number_format((float) $customer->balance, 2),
            'minDeposit' => $minDeposit,
            'maxDeposit' => $maxDeposit,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Initiate a wallet top-up deposit using UddoktaPay.
     */
    public function deposit(Request $request, PaymentGatewayService $gatewayService): SymfonyResponse
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        $min = (float) setting('min_deposit_amount', 50);
        $max = (float) setting('max_deposit_amount', 25000);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', "min:{$min}", "max:{$max}"],
        ]);

        try {
            $transaction = $gatewayService->initiateDeposit($customer, (float) $validated['amount']);

            $paymentUrl = $gatewayService->createGatewaySession(
                transaction: $transaction,
                customer: $customer,
                redirectUrl: route('payment.verify'),
                cancelUrl: route('payment.cancel'),
                webhookUrl: route('payment.webhook')
            );

            return Inertia::location($paymentUrl);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Referral rewards and referral link page.
     */
    public function referrals(): Response
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        $referredUsers = Customer::where('referred_by', $customer->id)
            ->latest()
            ->get(['id', 'name', 'created_at'])
            ->map(fn ($u) => [
                'name' => Str::mask($u->name, '*', 2, -2),
                'joined_at' => $u->created_at->format('M d, Y'),
            ]);

        $referralRate = (float) setting('referral_commission_percentage', 5);
        $referralUrl = url('/register?ref='.$customer->referral_code);

        return Inertia::render('Customer/Referrals', [
            'referralCode' => $customer->referral_code,
            'referralUrl' => $referralUrl,
            'referralRate' => $referralRate,
            'referredCount' => $referredUsers->count(),
            'referredUsers' => $referredUsers,
        ]);
    }
}
