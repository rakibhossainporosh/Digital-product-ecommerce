<?php

namespace App\Http\Controllers\Auth;

use App\Enums\CustomerStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CustomerAuthController extends Controller
{
    /**
     * Show customer login page.
     */
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Handle customer login attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($credentials['email']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => ["Too many login attempts. Please try again in {$seconds} seconds."],
            ]);
        }

        $customer = Customer::where('email', $credentials['email'])
            ->orWhere('whatsapp_number', $credentials['email'])
            ->first();

        if (! $customer || ! Hash::check($credentials['password'], $customer->password)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        if ($customer->status === CustomerStatus::Banned) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => ['Your account has been suspended or banned. Please contact customer support.'],
            ]);
        }

        RateLimiter::clear($throttleKey);

        auth('customer')->login($customer, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended('/')->with('success', "Welcome back, {$customer->name}! You're successfully signed in.");
    }

    /**
     * Show customer registration page.
     */
    public function showRegister(Request $request): Response
    {
        return Inertia::render('Auth/Register', [
            'referralCode' => $request->query('ref'),
        ]);
    }

    /**
     * Handle customer registration.
     */
    public function register(Request $request): RedirectResponse
    {
        $ipThrottleKey = 'customer-register|'.$request->ip();

        if (RateLimiter::tooManyAttempts($ipThrottleKey, 5)) {
            $seconds = RateLimiter::availableIn($ipThrottleKey);
            throw ValidationException::withMessages([
                'email' => ["Too many registration requests from your IP. Please try again in {$seconds} seconds."],
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:customers,email'],
            'whatsapp_number' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'referral_code' => ['nullable', 'string', 'exists:customers,referral_code'],
        ]);

        RateLimiter::hit($ipThrottleKey, 300); // Max 5 registrations per 5 minutes per IP

        // Find referrer if provided
        $referrer = null;
        if (! empty($validated['referral_code'])) {
            $referrer = Customer::where('referral_code', $validated['referral_code'])->first();
        }

        // Generate a clean, unique 8-character referral code
        do {
            $myReferralCode = strtoupper(Str::random(8));
        } while (Customer::where('referral_code', $myReferralCode)->exists());

        $customer = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'whatsapp_number' => $validated['whatsapp_number'],
            'password' => Hash::make($validated['password']),
            'referral_code' => $myReferralCode,
            'referred_by' => $referrer?->id,
            'status' => CustomerStatus::Active,
            'is_reseller' => false,
            'reseller_discount' => 0.00,
        ]);

        auth('customer')->login($customer);
        $request->session()->regenerate();

        return redirect('/')->with('success', 'Your account has been created successfully! Welcome to '.setting('app_name', 'Panel Sell').'. Your digital wallet is ready.');
    }

    /**
     * Handle customer logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        auth('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('info', 'You have been safely signed out. See you again soon!');
    }
}
