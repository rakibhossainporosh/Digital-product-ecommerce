<?php

use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StorefrontController;
use App\Http\Middleware\RedirectIfCustomerAuthenticated;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Storefront Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/product/{slug}', [StorefrontController::class, 'show'])->name('product.show');

/*
|--------------------------------------------------------------------------
| Customer Guest Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware([RedirectIfCustomerAuthenticated::class])->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login']);

    Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Customer Authenticated Routes (Requires Customer Guard)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:customer'])->group(function () {
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

    // Checkout & Purchase (Rate limited against automated brute-force)
    Route::post('/checkout/validate-promo', [CheckoutController::class, 'validatePromo'])
        ->middleware('throttle:15,1')
        ->name('checkout.validate-promo');

    Route::post('/checkout/wallet', [CheckoutController::class, 'walletCheckout'])
        ->middleware('throttle:10,1')
        ->name('checkout.wallet');

    Route::post('/checkout/gateway', [CheckoutController::class, 'gatewayCheckout'])
        ->middleware('throttle:10,1')
        ->name('checkout.gateway');

    // Customer Portal
    Route::get('/dashboard', [CustomerDashboardController::class, 'dashboard'])->name('customer.dashboard');
    Route::get('/orders', [CustomerDashboardController::class, 'orders'])->name('customer.orders');
    Route::get('/keys', [CustomerDashboardController::class, 'myKeys'])->name('customer.keys');
    Route::get('/wallet', [CustomerDashboardController::class, 'wallet'])->name('customer.wallet');

    Route::post('/wallet/deposit', [CustomerDashboardController::class, 'deposit'])
        ->middleware('throttle:8,1')
        ->name('customer.wallet.deposit');

    Route::get('/referrals', [CustomerDashboardController::class, 'referrals'])->name('customer.referrals');
});

/*
|--------------------------------------------------------------------------
| Payment Gateway Webhook & Callbacks
|--------------------------------------------------------------------------
*/
Route::post('/api/payment/webhook', [PaymentController::class, 'webhook'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->middleware('throttle:60,1')
    ->name('payment.webhook');

Route::get('/payment/verify', [PaymentController::class, 'verify'])->name('payment.verify');
Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
