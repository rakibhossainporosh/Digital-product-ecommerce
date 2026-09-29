<?php

namespace App\Http\Controllers;

use App\Enums\LicenseKeyStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Slider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    /**
     * Display the public homepage.
     */
    public function index(Request $request): Response
    {
        $selectedCategory = $request->query('category');

        $sliders = Slider::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Slider $slider) => [
                'id' => $slider->id,
                'title' => $slider->title,
                'image_url' => $slider->image_path ? asset('storage/'.$slider->image_path) : null,
                'link_url' => $slider->link_url,
            ]);

        $categories = Category::where('status', true)
            ->withCount(['products' => fn ($q) => $q->where('status', true)])
            ->orderBy('sort_order')
            ->get();

        $products = Product::where('status', true)
            ->with(['category', 'variants' => fn ($q) => $q->orderBy('regular_price')])
            ->when($selectedCategory, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->orderBy('sort_order')
            ->get()
            ->map(function (Product $product) {
                $minPrice = $product->variants->min(fn ($v) => $v->offer_price ?? $v->regular_price);
                $hasStock = $product->isService() || $product->variants->some(fn ($v) => $v->availableLicenseKeys()->exists() || filled($v->api_provider_id));

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'type' => $product->type ?? 'digital_key',
                    'is_service' => $product->isService(),
                    'image_url' => $product->image ? asset('storage/'.$product->image) : null,
                    'icon' => $product->icon,
                    'price_range' => $product->price_range,
                    'min_price' => $minPrice ?? 0,
                    'formatted_min_price' => '৳ '.number_format((float) ($minPrice ?? 0), 2),
                    'variants_count' => $product->variants->count(),
                    'demo_video_url' => $product->demo_video_url,
                    'features' => $product->features_list,
                    'has_stock' => $hasStock,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                        'slug' => $product->category->slug,
                    ] : null,
                ];
            });

        $recentOrders = Order::where('status', \App\Enums\OrderStatus::Completed)
            ->orWhere('fulfillment_status', \App\Enums\FulfillmentStatus::Fulfilled)
            ->with(['customer', 'product'])
            ->latest('created_at')
            ->take(3)
            ->get()
            ->map(function ($order) {
                // Mask customer name for privacy (e.g., "Md Hasan" -> "Md H***")
                $name = $order->customer ? $order->customer->name : 'Guest';
                $parts = explode(' ', $name);
                $maskedName = count($parts) > 1 
                    ? $parts[0] . ' ' . substr($parts[1], 0, 1) . '***'
                    : substr($name, 0, 2) . '***';

                return [
                    'id' => $order->id,
                    'customer_name' => $maskedName,
                    'product_name' => $order->product ? $order->product->name : 'Unknown Product',
                    'amount' => $order->total_amount,
                    'time_ago' => $order->created_at->diffForHumans(null, true, true) . ' ago',
                ];
            });

        return Inertia::render('Home', [
            'sliders' => $sliders,
            'categories' => $categories,
            'products' => $products,
            'recentOrders' => $recentOrders,
            'activeCategory' => $selectedCategory,
        ]);
    }

    /**
     * Display a single product purchase / topup page.
     */
    public function show(string $slug): Response
    {
        $product = Product::where('slug', $slug)
            ->where('status', true)
            ->with([
                'category',
                'variants' => fn ($q) => $q->withCount([
                    'licenseKeys as available_keys' => fn ($k) => $k->where('status', LicenseKeyStatus::Available),
                ]),
            ])
            ->firstOrFail();

        $customer = auth('customer')->user();

        $variants = $product->variants->map(function ($variant) use ($customer) {
            $effectivePrice = (float) ($variant->offer_price ?? $variant->regular_price);
            $regularPrice = (float) $variant->regular_price;

            // Apply reseller discount if customer is an approved reseller
            if ($customer?->is_reseller && $customer->reseller_discount > 0) {
                $discountMultiplier = (100 - (float) $customer->reseller_discount) / 100;
                $effectivePrice = round($effectivePrice * $discountMultiplier, 2);
            }

            $discountPercent = $regularPrice > $effectivePrice && $regularPrice > 0
                ? round((($regularPrice - $effectivePrice) / $regularPrice) * 100)
                : 0;

            return [
                'id' => $variant->id,
                'duration_name' => $variant->duration_name,
                'duration_days' => $variant->duration_days,
                'regular_price' => $regularPrice,
                'effective_price' => $effectivePrice,
                'formatted_price' => '৳ '.number_format($effectivePrice, 2),
                'formatted_regular_price' => '৳ '.number_format($regularPrice, 2),
                'discount_percent' => $discountPercent,
                'is_popular' => (bool) $variant->is_popular,
                'available_stock' => $variant->available_keys,
                'has_supplier_api' => filled($variant->api_provider_id),
            ];
        });

        return Inertia::render('ProductTopup', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'type' => $product->type ?? 'digital_key',
                'is_service' => $product->isService(),
                'image_url' => $product->image ? asset('storage/'.$product->image) : null,
                'demo_video_url' => $product->demo_video_url,
                'description' => $product->description,
                'features' => $product->features_list,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ] : null,
                'variants' => $variants,
            ],
        ]);
    }
}
