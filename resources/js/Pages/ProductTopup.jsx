import React, { useState } from 'react';
import { usePage, Link, router } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { 
    CheckCircle2, 
    ShieldCheck, 
    Zap, 
    Play, 
    Wallet, 
    CreditCard, 
    Tag, 
    ArrowRight, 
    AlertCircle, 
    Plus, 
    Minus, 
    Wrench,
    Smartphone,
    Info,
    ChevronRight,
    Loader2
} from 'lucide-react';

export default function ProductTopup({ product }) {
    const { auth, settings } = usePage().props;
    const customer = auth?.customer;

    // Default to first variant or popular variant
    const [selectedVariantId, setSelectedVariantId] = useState(
        product.variants.find((v) => v.is_popular)?.id || product.variants[0]?.id || null
    );
    const [quantity, setQuantity] = useState(1);
    const [paymentMethod, setPaymentMethod] = useState('wallet'); // 'wallet' or 'gateway'
    
    // Promo code state
    const [promoInput, setPromoInput] = useState('');
    const [appliedPromo, setAppliedPromo] = useState(null);
    const [promoLoading, setPromoLoading] = useState(false);
    const [promoError, setPromoError] = useState(null);

    // Dynamic service form fields (for rooting / service products)
    const [serviceData, setServiceData] = useState({
        whatsapp_number: customer?.whatsapp_number || '',
        device_model: '',
        android_version: '',
        imei_or_uid: '',
    });

    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitError, setSubmitError] = useState(null);

    const selectedVariant = product.variants.find((v) => v.id === selectedVariantId) || product.variants[0];
    const isService = product.is_service;

    // Pricing calculation
    const basePrice = selectedVariant ? selectedVariant.effective_price : 0;
    const subtotal = round(basePrice * quantity);
    const discountAmount = appliedPromo ? appliedPromo.discount_amount : 0;
    const totalAmount = Math.max(0, round(subtotal - discountAmount));

    const hasEnoughWalletBalance = customer && customer.balance >= totalAmount;

    function round(val) {
        return Math.round((val + Number.EPSILON) * 100) / 100;
    }

    const handleApplyPromo = async () => {
        if (!promoInput.trim()) return;
        setPromoLoading(true);
        setPromoError(null);

        try {
            const res = await fetch('/checkout/validate-promo', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    promo_code: promoInput,
                    variant_id: selectedVariant.id,
                    quantity: quantity,
                }),
            });

            const data = await res.json();

            if (res.ok && data.valid) {
                setAppliedPromo(data);
                setPromoError(null);
            } else {
                setAppliedPromo(null);
                setPromoError(data.error || 'Invalid promo code');
            }
        } catch (err) {
            setPromoError('Failed to validate promo code.');
        } finally {
            setPromoLoading(false);
        }
    };

    const handleCheckout = (e) => {
        e.preventDefault();

        if (!customer) {
            router.get('/login');
            return;
        }

        if (isService && !serviceData.whatsapp_number) {
            setSubmitError('Please provide your WhatsApp number for service communication.');
            return;
        }

        setIsSubmitting(true);
        setSubmitError(null);

        const payload = {
            variant_id: selectedVariant.id,
            quantity: quantity,
            promo_code: appliedPromo ? appliedPromo.code : null,
            service_data: isService ? serviceData : null,
        };

        const targetRoute = paymentMethod === 'wallet' ? '/checkout/wallet' : '/checkout/gateway';

        router.post(targetRoute, payload, {
            onError: (errors) => {
                setIsSubmitting(false);
                setSubmitError(Object.values(errors)[0] || 'Order checkout failed.');
            },
            onFinish: () => {
                setIsSubmitting(false);
            },
        });
    };

    return (
        <AppLayout title={`${product.name} - Buy Now`}>
            {/* Breadcrumb Header */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-2">
                <nav className="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                    <Link href="/" className="hover:text-red-400 transition-colors">Home</Link>
                    <ChevronRight className="w-3.5 h-3.5 text-gray-600" />
                    <span>{product.category?.name || 'Panels'}</span>
                    <ChevronRight className="w-3.5 h-3.5 text-gray-600" />
                    <span className="text-gray-800 dark:text-gray-200 truncate">{product.name}</span>
                </nav>
            </div>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    {/* Left Column: Product Info & Order Configuration (8 cols) */}
                    <div className="lg:col-span-8 space-y-6">
                        
                        {/* 1. Product Summary Card */}
                        <div className="glass-panel p-5 sm:p-6 rounded-2xl border border-gray-200 dark:border-gray-800/80">
                            <div className="flex flex-col sm:flex-row gap-5 items-start">
                                {/* Thumbnail Image */}
                                <div className="w-full sm:w-44 aspect-[16/10] sm:aspect-square rounded-xl overflow-hidden bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shrink-0 flex items-center justify-center">
                                    {product.image_url ? (
                                        <img src={product.image_url} alt={product.name} className="w-full h-full object-cover" />
                                    ) : (
                                        <div className="text-center p-4">
                                            {isService ? <Wrench className="w-10 h-10 text-red-400 mx-auto mb-2" /> : <Smartphone className="w-10 h-10 text-red-400 mx-auto mb-2" />}
                                            <span className="text-[10px] text-gray-600 dark:text-gray-400 font-mono">PANEL IMAGE</span>
                                        </div>
                                    )}
                                </div>

                                {/* Details */}
                                <div className="flex-1 space-y-3">
                                    <div className="flex flex-wrap items-center gap-2">
                                        {product.category && (
                                            <span className="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-950 border border-red-500/30 text-red-400">
                                                {product.category.name}
                                            </span>
                                        )}
                                        {isService ? (
                                            <span className="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-950/80 border border-amber-500/40 text-amber-300">
                                                Manual Service
                                            </span>
                                        ) : (
                                            <span className="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 flex items-center gap-1">
                                                <Zap className="w-3.5 h-3.5 fill-emerald-300" /> Instant Delivery
                                            </span>
                                        )}
                                    </div>

                                    <h1 className="text-xl sm:text-2xl lg:text-3xl font-black font-display text-gray-900 dark:text-white">
                                        {product.name}
                                    </h1>

                                    {product.features && product.features.length > 0 && (
                                        <div className="flex flex-wrap gap-2 pt-1">
                                            {product.features.map((feat, i) => (
                                                <span key={i} className="text-xs px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700/50">
                                                    ✓ {feat}
                                                </span>
                                            ))}
                                        </div>
                                    )}

                                    {(product.demo_video_url || product.has_setup_file) && (
                                        <div className="pt-2 flex flex-wrap gap-3">
                                            {product.demo_video_url && (
                                                <a
                                                    href={product.demo_video_url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-[#f82803]/20 text-red-400 border border-red-500/30 text-xs font-semibold hover:bg-[#f82803]/30 transition-colors"
                                                >
                                                    <Play className="w-4 h-4 fill-red-400" />
                                                    <span>Watch Setup Tutorial</span>
                                                </a>
                                            )}
                                            {product.has_setup_file && (
                                                <a
                                                    href={product.setup_file_url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-blue-500/20 text-blue-400 border border-blue-500/30 text-xs font-semibold hover:bg-blue-500/30 transition-colors"
                                                >
                                                    <ArrowRight className="w-4 h-4" />
                                                    <span>Download App / Setup</span>
                                                </a>
                                            )}
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* 2. Step 1: Select Recharge Package / Duration Tier */}
                        <div className="glass-panel p-5 sm:p-6 rounded-2xl border border-gray-200 dark:border-gray-800/80 space-y-4">
                            <div className="flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider font-display">
                                <span className="w-6 h-6 rounded-lg bg-red-500 text-black flex items-center justify-center text-xs font-black">1</span>
                                <span>Select Recharge Package / Duration</span>
                            </div>

                            <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                {product.variants.map((variant) => {
                                    const isSelected = variant.id === selectedVariantId;
                                    return (
                                        <button
                                            key={variant.id}
                                            type="button"
                                            onClick={() => {
                                                setSelectedVariantId(variant.id);
                                                setAppliedPromo(null);
                                            }}
                                            className={`p-4 rounded-xl border text-left transition-all relative flex flex-col justify-between space-y-3 ${
                                                isSelected
                                                    ? 'bg-gradient-to-b from-red-50 dark:from-red-950/70 to-slate-900 border-red-500 shadow-lg shadow-red-500/15 scale-[1.02]'
                                                    : 'bg-white dark:bg-gray-900/60 hover:bg-gray-100 dark:bg-gray-800/60 border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300'
                                            }`}
                                        >
                                            {variant.is_popular && (
                                                <span className="absolute -top-2.5 right-2 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-gradient-to-r from-amber-500 to-orange-500 text-black">
                                                    Best Value
                                                </span>
                                            )}

                                            <div>
                                                <span className="block text-sm font-bold text-gray-900 dark:text-white line-clamp-1">
                                                    {variant.duration_name}
                                                </span>
                                                <span className="block text-[11px] text-gray-600 dark:text-gray-400 font-mono mt-0.5">
                                                    Validity: {variant.duration_days} Days
                                                </span>
                                            </div>

                                            <div>
                                                <div className="flex items-baseline gap-1.5">
                                                    <span className="text-base sm:text-lg font-black font-mono text-red-400">
                                                        {variant.formatted_price}
                                                    </span>
                                                    {variant.discount_percent > 0 && (
                                                        <span className="text-xs text-gray-500 line-through font-mono">
                                                            {variant.formatted_regular_price}
                                                        </span>
                                                    )}
                                                </div>

                                                {variant.discount_percent > 0 && (
                                                    <span className="inline-block mt-1 text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-semibold">
                                                        Save {variant.discount_percent}%
                                                    </span>
                                                )}
                                            </div>
                                        </button>
                                    );
                                })}
                            </div>
                        </div>

                        {/* 3. Step 2: Device & Service Information or Quantity */}
                        <div className="glass-panel p-5 sm:p-6 rounded-2xl border border-gray-200 dark:border-gray-800/80 space-y-4">
                            <div className="flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider font-display">
                                <span className="w-6 h-6 rounded-lg bg-red-500 text-black flex items-center justify-center text-xs font-black">2</span>
                                <span>{isService ? 'Device & Contact Information' : 'Select Quantity'}</span>
                            </div>

                            {isService ? (
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                            WhatsApp Number <span className="text-red-400">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            value={serviceData.whatsapp_number}
                                            onChange={(e) => setServiceData({ ...serviceData, whatsapp_number: e.target.value })}
                                            placeholder="+8801700000000"
                                            className="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-500 text-sm focus:outline-none focus:border-red-500"
                                            required
                                        />
                                        <p className="text-[10px] text-gray-600 dark:text-gray-400">Our engineers will message you on WhatsApp to fulfill the service.</p>
                                    </div>

                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                            Device Model Name
                                        </label>
                                        <input
                                            type="text"
                                            value={serviceData.device_model}
                                            onChange={(e) => setServiceData({ ...serviceData, device_model: e.target.value })}
                                            placeholder="e.g. Samsung S23 Ultra / Redmi Note 12"
                                            className="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-500 text-sm focus:outline-none focus:border-red-500"
                                        />
                                    </div>

                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                            Android / iOS Version
                                        </label>
                                        <input
                                            type="text"
                                            value={serviceData.android_version}
                                            onChange={(e) => setServiceData({ ...serviceData, android_version: e.target.value })}
                                            placeholder="e.g. Android 14 / iOS 17.5"
                                            className="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-500 text-sm focus:outline-none focus:border-red-500"
                                        />
                                    </div>

                                    <div className="space-y-1">
                                        <label className="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                            IMEI / UID / Extra Notes
                                        </label>
                                        <input
                                            type="text"
                                            value={serviceData.imei_or_uid}
                                            onChange={(e) => setServiceData({ ...serviceData, imei_or_uid: e.target.value })}
                                            placeholder="Optional device IMEI or identifier"
                                            className="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-500 text-sm focus:outline-none focus:border-red-500"
                                        />
                                    </div>
                                </div>
                            ) : (
                                <div className="flex items-center gap-4">
                                    <span className="text-sm text-gray-700 dark:text-gray-300 font-medium">Quantity (Keys to buy):</span>
                                    <div className="flex items-center gap-2 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 p-1">
                                        <button
                                            type="button"
                                            onClick={() => setQuantity(Math.max(1, quantity - 1))}
                                            className="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-700 flex items-center justify-center text-gray-900 dark:text-white"
                                        >
                                            <Minus className="w-4 h-4" />
                                        </button>
                                        <span className="w-10 text-center font-mono font-bold text-gray-900 dark:text-white text-sm">
                                            {quantity}
                                        </span>
                                        <button
                                            type="button"
                                            onClick={() => setQuantity(Math.min(20, quantity + 1))}
                                            className="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-700 flex items-center justify-center text-gray-900 dark:text-white"
                                        >
                                            <Plus className="w-4 h-4" />
                                        </button>
                                    </div>
                                    <span className="text-xs text-gray-600 dark:text-gray-400">
                                        {quantity > 1 ? `Generates ${quantity} separate license keys` : '1 key delivered instantly'}
                                    </span>
                                </div>
                            )}
                        </div>

                        {/* 4. Description & Safety Rules Tab */}
                        {product.description && (
                            <div className="glass-panel p-5 sm:p-6 rounded-2xl border border-gray-200 dark:border-gray-800/80 space-y-2">
                                <h3 className="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider font-display">
                                    Product Description & Setup Instructions
                                </h3>
                                <div 
                                    className="text-sm text-gray-700 dark:text-gray-300 prose prose-invert max-w-none prose-p:leading-relaxed"
                                    dangerouslySetInnerHTML={{ __html: product.description }}
                                />
                            </div>
                        )}
                    </div>

                    {/* Right Column: Order Summary, Promo, & Payment Actions (4 cols sticky) */}
                    <div className="lg:col-span-4 space-y-6">
                        
                        <div className="glass-panel p-5 sm:p-6 rounded-2xl border border-red-500/30 shadow-xl space-y-5 sticky top-24">
                            <h2 className="text-base font-bold text-gray-900 dark:text-white uppercase tracking-wider font-display pb-3 border-b border-gray-200 dark:border-gray-800 flex items-center gap-2">
                                <Tag className="w-4 h-4 text-red-400" />
                                <span>Order Summary</span>
                            </h2>

                            {/* Package Details */}
                            <div className="space-y-2 text-sm">
                                <div className="flex justify-between text-gray-700 dark:text-gray-300">
                                    <span>Selected Package:</span>
                                    <span className="font-semibold text-gray-900 dark:text-white">{selectedVariant?.duration_name}</span>
                                </div>
                                <div className="flex justify-between text-gray-700 dark:text-gray-300">
                                    <span>Unit Price:</span>
                                    <span className="font-mono">{selectedVariant?.formatted_price}</span>
                                </div>
                                <div className="flex justify-between text-gray-700 dark:text-gray-300">
                                    <span>Quantity:</span>
                                    <span className="font-mono">× {quantity}</span>
                                </div>

                                {appliedPromo && (
                                    <div className="flex justify-between text-emerald-400 font-semibold pt-1">
                                        <span>Promo Discount ({appliedPromo.code}):</span>
                                        <span className="font-mono">- ৳ {appliedPromo.discount_amount}</span>
                                    </div>
                                )}

                                <div className="flex justify-between text-base font-black text-gray-900 dark:text-white pt-3 border-t border-gray-200 dark:border-gray-800">
                                    <span>Total Payable:</span>
                                    <span className="text-xl font-mono text-red-400">
                                        ৳ {totalAmount.toFixed(2)}
                                    </span>
                                </div>
                            </div>

                            {/* Promo Code Input */}
                            <div className="space-y-1.5 pt-2">
                                <label className="text-xs font-semibold text-gray-600 dark:text-gray-400">Have a Promo Code?</label>
                                <div className="flex gap-2">
                                    <input
                                        type="text"
                                        value={promoInput}
                                        onChange={(e) => setPromoInput(e.target.value.toUpperCase())}
                                        placeholder="PROMOCODE"
                                        disabled={!!appliedPromo}
                                        className="flex-1 px-3.5 py-2 rounded-xl bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-500 text-xs font-mono uppercase focus:outline-none focus:border-red-500"
                                    />
                                    {appliedPromo ? (
                                        <button
                                            type="button"
                                            onClick={() => {
                                                setAppliedPromo(null);
                                                setPromoInput('');
                                            }}
                                            className="px-3 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs text-red-400 hover:bg-gray-700"
                                        >
                                            Remove
                                        </button>
                                    ) : (
                                        <button
                                            type="button"
                                            onClick={handleApplyPromo}
                                            disabled={promoLoading || !promoInput.trim()}
                                            className="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-700 text-red-400 text-xs font-semibold border border-red-500/30 disabled:opacity-50"
                                        >
                                            {promoLoading ? 'Checking...' : 'Apply'}
                                        </button>
                                    )}
                                </div>
                                {promoError && <p className="text-[11px] text-red-400">{promoError}</p>}
                                {appliedPromo && <p className="text-[11px] text-emerald-400">✓ Promo code applied successfully!</p>}
                            </div>

                            {/* Step 3: Choose Payment Method */}
                            <div className="space-y-2 pt-2">
                                <label className="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider block">
                                    Select Payment Method
                                </label>

                                <div className="space-y-2">
                                    {/* 1. Wallet Pay */}
                                    <button
                                        type="button"
                                        onClick={() => setPaymentMethod('wallet')}
                                        className={`w-full p-3.5 rounded-xl border text-left transition-all flex items-center justify-between ${
                                            paymentMethod === 'wallet'
                                                ? 'bg-red-950/60 border-red-500 text-gray-900 dark:text-white'
                                                : 'bg-white dark:bg-gray-900/60 hover:bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300'
                                        }`}
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="w-8 h-8 rounded-lg bg-red-500/20 text-red-400 flex items-center justify-center shrink-0">
                                                <Wallet className="w-4 h-4" />
                                            </div>
                                            <div>
                                                <span className="block text-xs font-bold text-gray-900 dark:text-white">Wallet Balance</span>
                                                <span className="block text-[11px] text-gray-600 dark:text-gray-400 font-mono">
                                                    {customer ? `Current: ${customer.formatted_balance}` : 'Login to view balance'}
                                                </span>
                                            </div>
                                        </div>

                                        <div className={`w-4 h-4 rounded-full border-2 flex items-center justify-center ${paymentMethod === 'wallet' ? 'border-red-400 bg-red-400' : 'border-gray-600'}`}>
                                            {paymentMethod === 'wallet' && <div className="w-1.5 h-1.5 rounded-full bg-white dark:bg-black" />}
                                        </div>
                                    </button>

                                    {/* 2. Automated Gateway (bKash/Nagad/Cards) */}
                                    <button
                                        type="button"
                                        onClick={() => setPaymentMethod('gateway')}
                                        className={`w-full p-3.5 rounded-xl border text-left transition-all flex items-center justify-between ${
                                            paymentMethod === 'gateway'
                                                ? 'bg-red-950/60 border-red-500 text-gray-900 dark:text-white'
                                                : 'bg-white dark:bg-gray-900/60 hover:bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300'
                                        }`}
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center shrink-0">
                                                <CreditCard className="w-4 h-4" />
                                            </div>
                                            <div>
                                                <span className="block text-xs font-bold text-gray-900 dark:text-white">bKash / Nagad / Rocket</span>
                                                <span className="block text-[11px] text-gray-600 dark:text-gray-400">Instant Online Gateway</span>
                                            </div>
                                        </div>

                                        <div className={`w-4 h-4 rounded-full border-2 flex items-center justify-center ${paymentMethod === 'gateway' ? 'border-red-400 bg-red-400' : 'border-gray-600'}`}>
                                            {paymentMethod === 'gateway' && <div className="w-1.5 h-1.5 rounded-full bg-white dark:bg-black" />}
                                        </div>
                                    </button>
                                </div>

                                {/* Wallet Insufficient Balance Warning */}
                                {customer && paymentMethod === 'wallet' && !hasEnoughWalletBalance && (
                                    <div className="p-3 rounded-xl bg-amber-950/40 border border-amber-500/40 text-xs text-amber-200 space-y-1.5">
                                        <div className="flex items-center gap-1.5 font-semibold text-amber-300">
                                            <AlertCircle className="w-4 h-4" />
                                            <span>Insufficient Wallet Balance</span>
                                        </div>
                                        <p className="text-[11px]">You need ৳ {(totalAmount - customer.balance).toFixed(2)} more to complete this order via wallet.</p>
                                        <Link
                                            href="/wallet"
                                            className="inline-block px-3 py-1 rounded-lg bg-amber-500 text-white font-bold text-xs mt-1"
                                        >
                                            Deposit to Wallet
                                        </Link>
                                    </div>
                                )}
                            </div>

                            {/* Submit Error */}
                            {submitError && (
                                <div className="p-3 rounded-xl bg-red-950/60 border border-red-500/40 text-xs text-red-200 flex items-start gap-2">
                                    <AlertCircle className="w-4 h-4 text-red-400 shrink-0 mt-0.5" />
                                    <span>{submitError}</span>
                                </div>
                            )}

                            {/* Main CTA Order Button */}
                            {product.is_maintenance ? (
                                <div className="p-4 rounded-xl bg-gray-800/80 border border-gray-700 text-gray-300 text-sm text-center font-bold flex flex-col items-center gap-2">
                                    <Wrench className="w-6 h-6 text-gray-400" />
                                    This product is currently under maintenance. Please try again later.
                                </div>
                            ) : customer ? (
                                <button
                                    type="button"
                                    onClick={handleCheckout}
                                    disabled={isSubmitting || (paymentMethod === 'wallet' && !hasEnoughWalletBalance)}
                                    className="w-full py-3.5 rounded-xl bg-gradient-to-br from-[#f82803] via-red-900 to-red-500 hover:from-[#ff411a] hover:to-[#8f0909] text-white font-black text-sm tracking-wide shadow-xl shadow-red-500/25 transition-all hover:scale-[1.02] active:scale-95 disabled:opacity-50 disabled:pointer-events-none flex items-center justify-center gap-2"
                                >
                                    {isSubmitting ? (
                                        <>
                                            <Loader2 className="w-4 h-4 animate-spin text-black" />
                                            <span>Processing Secure Order...</span>
                                        </>
                                    ) : (
                                        <>
                                            <Zap className="w-4 h-4 fill-white" />
                                            <span>Order Now (৳ {totalAmount.toFixed(2)})</span>
                                        </>
                                    )}
                                </button>
                            ) : (
                                <div className="space-y-2">
                                    <Link
                                        href="/login"
                                        className="w-full py-3.5 rounded-xl bg-red-500 hover:bg-red-400 text-white font-bold text-sm text-center block shadow-lg shadow-red-500/20"
                                    >
                                        Log In to Purchase
                                    </Link>
                                    <p className="text-[11px] text-gray-600 dark:text-gray-400 text-center">
                                        Don't have an account? <Link href="/register" className="text-red-400 underline">Register here</Link>
                                    </p>
                                </div>
                            )}

                            <div className="pt-2 text-[11px] text-gray-500 text-center space-y-1">
                                <p className="flex items-center justify-center gap-1">
                                    <ShieldCheck className="w-3.5 h-3.5 text-emerald-400" />
                                    <span>Encrypted 256-bit Secure Checkout</span>
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
