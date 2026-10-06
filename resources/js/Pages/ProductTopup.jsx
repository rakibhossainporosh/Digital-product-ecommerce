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
    Loader2,
    ArrowLeft,
    Check,
    Sparkles,
    Lock,
    Shield,
    Phone,
    Cpu,
    Hash,
    Clock,
    FileText,
    ExternalLink,
    ChevronDown
} from 'lucide-react';

export default function ProductTopup({ product }) {
    const { auth, settings } = usePage().props;
    const customer = auth?.customer;

    // Default to popular variant or first variant
    const [selectedVariantId, setSelectedVariantId] = useState(
        product.variants.find((v) => v.is_popular)?.id || product.variants[0]?.id || null
    );
    const [quantity, setQuantity] = useState(1);
    const [paymentMethod, setPaymentMethod] = useState('wallet'); // 'wallet' or 'gateway'
    
    // UI toggle states
    const [isGuideOpen, setIsGuideOpen] = useState(true);
    const [showPromoInput, setShowPromoInput] = useState(false);

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
                    variant_id: selectedVariant?.id,
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
        if (e) e.preventDefault();

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
            variant_id: selectedVariant?.id,
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
        <AppLayout title={`${product.name} - Instant Key & Panel Delivery`}>
            {/* Top Navigation & Breadcrumb */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-5 pb-1">
                <div className="flex flex-wrap items-center justify-between gap-3 text-xs">
                    <nav className="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                        <Link 
                            href="/" 
                            className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/60 hover:text-red-500 dark:hover:text-red-400 transition-colors shadow-xs"
                        >
                            <ArrowLeft className="w-3.5 h-3.5" />
                            <span>Storefront</span>
                        </Link>
                        <ChevronRight className="w-3 h-3 text-gray-400 dark:text-gray-600" />
                        {product.category && (
                            <>
                                <Link 
                                    href={`/?category=${product.category.slug}`} 
                                    className="hover:text-red-500 dark:hover:text-red-400 transition-colors font-medium"
                                >
                                    {product.category.name}
                                </Link>
                                <ChevronRight className="w-3 h-3 text-gray-400 dark:text-gray-600" />
                            </>
                        )}
                        <span className="text-gray-900 dark:text-gray-200 font-semibold truncate max-w-[200px] sm:max-w-md">
                            {product.name}
                        </span>
                    </nav>

                    {/* Stock Status Badge */}
                    <div className="flex items-center gap-2">
                        {product.is_maintenance ? (
                            <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400">
                                <Wrench className="w-3.5 h-3.5 animate-spin" />
                                Maintenance Mode
                            </span>
                        ) : (
                            <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400">
                                <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
                                In Stock · Instant Key Release
                            </span>
                        )}
                    </div>
                </div>
            </div>

            {/* Main Content Layout */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-28 md:pb-12">
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    {/* Left Column: Product Showcase & Order Configuration (8 cols) */}
                    <div className="lg:col-span-8 space-y-6">
                        
                        {/* 1. Hero Product Showcase Card */}
                        <div className="glass-panel p-5 sm:p-6 rounded-2xl border border-gray-200 dark:border-gray-800/80 shadow-md relative overflow-hidden">
                            {/* Decorative ambient glow */}
                            <div className="absolute top-0 right-0 w-80 h-80 bg-red-500/5 dark:bg-red-500/10 rounded-full blur-3xl pointer-events-none" />

                            <div className="flex flex-col sm:flex-row gap-5 items-start relative z-10">
                                {/* Product Thumbnail */}
                                <div className="w-full sm:w-52 aspect-[16/10] sm:aspect-square rounded-2xl overflow-hidden bg-gradient-to-br from-gray-100 via-gray-50 to-gray-200 dark:from-slate-900 dark:via-gray-900 dark:to-[#0d1527] border border-gray-200 dark:border-gray-800 shrink-0 flex items-center justify-center shadow-lg group relative">
                                    {product.image_url ? (
                                        <>
                                            {/* Blurred ambient background layer */}
                                            <div 
                                                className="absolute inset-0 bg-cover bg-center blur-2xl opacity-40 scale-125"
                                                style={{ backgroundImage: `url(${product.image_url})` }}
                                            />
                                            {/* Main image with object-contain to preserve perfect ratio */}
                                            <img 
                                                src={product.image_url} 
                                                alt={product.name} 
                                                className="relative z-10 w-full h-full object-contain p-2.5 sm:p-3 group-hover:scale-105 transition-transform duration-500 drop-shadow-2xl" 
                                            />
                                        </>
                                    ) : (
                                        <div className="text-center p-4">
                                            {isService ? (
                                                <Wrench className="w-12 h-12 text-red-500 mx-auto mb-2 opacity-80" />
                                            ) : (
                                                <Smartphone className="w-12 h-12 text-red-500 mx-auto mb-2 opacity-80" />
                                            )}
                                            <span className="text-[10px] text-gray-400 font-mono tracking-widest uppercase">PANEL PREVIEW</span>
                                        </div>
                                    )}
                                </div>

                                {/* Details & Badges */}
                                <div className="flex-1 space-y-3.5">
                                    <div className="flex items-center gap-1.5 sm:gap-2 overflow-x-auto no-scrollbar py-0.5">
                                        {product.category && (
                                            <span className="px-2 sm:px-3 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold bg-red-500/10 border border-red-500/30 text-red-600 dark:text-red-400 whitespace-nowrap shrink-0">
                                                {product.category.name}
                                            </span>
                                        )}
                                        {isService ? (
                                            <span className="px-2 sm:px-3 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400 flex items-center gap-1 whitespace-nowrap shrink-0">
                                                <Wrench className="w-3 h-3 sm:w-3.5 sm:h-3.5 shrink-0" />
                                                <span>Manual <span className="hidden sm:inline">Engineer </span>Service</span>
                                            </span>
                                        ) : (
                                            <span className="px-2 sm:px-3 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 flex items-center gap-1 whitespace-nowrap shrink-0">
                                                <Zap className="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-emerald-500 shrink-0" />
                                                <span>Instant <span className="hidden sm:inline">Auto </span>Delivery</span>
                                            </span>
                                        )}
                                        <span className="px-2 sm:px-3 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold bg-blue-500/10 border border-blue-500/30 text-blue-600 dark:text-blue-400 flex items-center gap-1 whitespace-nowrap shrink-0">
                                            <ShieldCheck className="w-3 h-3 sm:w-3.5 sm:h-3.5 text-blue-500 shrink-0" />
                                            <span>100% Anti-Ban</span>
                                        </span>
                                    </div>

                                    <h1 className="text-xl sm:text-2xl lg:text-3xl font-black font-display text-gray-900 dark:text-white leading-tight">
                                        {product.name}
                                    </h1>

                                    {/* Features Pill Tags */}
                                    {product.features && product.features.length > 0 && (
                                        <div className="grid grid-cols-2 gap-1.5 sm:flex sm:flex-wrap sm:gap-2 pt-1">
                                            {product.features.map((feat, i) => (
                                                <span 
                                                    key={i} 
                                                    className="inline-flex items-center gap-1.5 text-[11px] sm:text-xs px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700/60 font-medium"
                                                >
                                                    <Check className="w-3.5 h-3.5 text-emerald-500 stroke-[3] shrink-0" />
                                                    <span className="truncate">{feat}</span>
                                                </span>
                                            ))}
                                        </div>
                                    )}

                                    {/* Interactive Tutorial & Setup Action Buttons */}
                                    {(product.demo_video_url || product.has_setup_file) && (
                                        <div className="pt-2 grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:gap-2.5">
                                            {product.demo_video_url && (
                                                <a
                                                    href={product.demo_video_url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="flex items-center justify-center gap-1.5 sm:gap-2 px-2.5 sm:px-4 py-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 border border-red-500/30 text-[11px] sm:text-xs font-bold transition-all shadow-xs hover:scale-[1.02] text-center"
                                                >
                                                    <Play className="w-3.5 h-3.5 fill-red-500 shrink-0" />
                                                    <span className="truncate">Watch Tutorial</span>
                                                    <ExternalLink className="w-3 h-3 opacity-60 shrink-0 hidden sm:inline" />
                                                </a>
                                            )}
                                            {product.has_setup_file && (
                                                <a
                                                    href={product.setup_file_url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="flex items-center justify-center gap-1.5 sm:gap-2 px-2.5 sm:px-4 py-2 rounded-xl bg-blue-500/10 hover:bg-blue-500/20 text-blue-600 dark:text-blue-400 border border-blue-500/30 text-[11px] sm:text-xs font-bold transition-all shadow-xs hover:scale-[1.02] text-center"
                                                >
                                                    <ArrowRight className="w-3.5 h-3.5 shrink-0" />
                                                    <span className="truncate">Download Setup</span>
                                                    <ExternalLink className="w-3 h-3 opacity-60 shrink-0 hidden sm:inline" />
                                                </a>
                                            )}
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* 2. Step 1: Select Duration & License Package */}
                        <div className="glass-panel p-4 sm:p-6 rounded-2xl border border-gray-200 dark:border-gray-800/80 shadow-md space-y-4">
                            <div className="flex items-center justify-between gap-1.5 sm:gap-2">
                                <div className="flex items-center gap-1.5 sm:gap-2.5 min-w-0">
                                    <span className="w-5 h-5 sm:w-7 sm:h-7 rounded-lg sm:rounded-xl bg-gradient-to-br from-[#f82803] to-[#990a0a] text-white flex items-center justify-center text-[10px] sm:text-xs font-black shadow-md shadow-red-500/20 shrink-0">
                                        1
                                    </span>
                                    <h2 className="text-xs sm:text-base font-bold text-gray-900 dark:text-white font-display whitespace-nowrap">
                                        Select Duration & License Package
                                    </h2>
                                </div>
                                <span className="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-medium whitespace-nowrap shrink-0">
                                    {product.variants.length} Options Available
                                </span>
                            </div>

                            <div className="grid grid-cols-2 md:grid-cols-3 gap-2.5 sm:gap-3.5">
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
                                            className={`p-3 sm:p-4 rounded-xl sm:rounded-2xl border-2 text-left transition-all relative flex flex-col justify-between space-y-2.5 sm:space-y-3 group cursor-pointer ${
                                                isSelected
                                                    ? 'bg-gradient-to-b from-red-50 via-white to-red-50/50 dark:from-red-950/70 dark:via-slate-900 dark:to-slate-900/90 border-[#f82803] shadow-xl shadow-red-500/15 scale-[1.02]'
                                                    : 'bg-white dark:bg-gray-900/60 hover:bg-gray-50 dark:hover:bg-gray-800/60 border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700'
                                            }`}
                                        >
                                            {/* Popular Badge */}
                                            {variant.is_popular && (
                                                <span className="absolute -top-2.5 sm:-top-3 right-2 sm:right-3 px-2 sm:px-2.5 py-0.5 rounded-full text-[9px] sm:text-[10px] font-black uppercase tracking-wider bg-gradient-to-r from-amber-500 to-orange-500 text-black shadow-md shadow-amber-500/20">
                                                    Best Value
                                                </span>
                                            )}

                                            {/* Header with Title and Selection Radio */}
                                            <div className="flex items-start justify-between gap-1 sm:gap-2">
                                                <div className="min-w-0 flex-1">
                                                    <span className="block text-xs sm:text-sm font-extrabold text-gray-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors truncate">
                                                        {variant.duration_name}
                                                    </span>
                                                    <span className="inline-flex items-center gap-1 text-[10px] sm:text-[11px] text-gray-500 dark:text-gray-400 font-mono mt-0.5 whitespace-nowrap">
                                                        <Clock className="w-2.5 h-2.5 sm:w-3 sm:h-3 text-red-500/80 shrink-0" />
                                                        {variant.duration_days} Days
                                                    </span>
                                                </div>

                                                {/* Selection Indicator Circle */}
                                                <div className={`w-4 h-4 sm:w-5 sm:h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-all ${
                                                    isSelected 
                                                        ? 'border-[#f82803] bg-[#f82803] text-white shadow-sm shadow-red-500/30' 
                                                        : 'border-gray-300 dark:border-gray-700 group-hover:border-gray-400'
                                                }`}>
                                                    {isSelected && <Check className="w-2.5 h-2.5 sm:w-3.5 sm:h-3.5 stroke-[3]" />}
                                                </div>
                                            </div>

                                            {/* Price & Savings */}
                                            <div className="pt-1.5 sm:pt-2 border-t border-gray-100 dark:border-gray-800/80">
                                                <div className="flex items-baseline flex-wrap gap-1 sm:gap-2">
                                                    <span className="text-base sm:text-xl font-black font-mono text-[#f82803] dark:text-red-400 whitespace-nowrap">
                                                        {variant.formatted_price}
                                                    </span>
                                                    {variant.discount_percent > 0 && (
                                                        <span className="text-[10px] sm:text-xs text-gray-400 line-through font-mono whitespace-nowrap">
                                                            {variant.formatted_regular_price}
                                                        </span>
                                                    )}
                                                </div>

                                                {variant.discount_percent > 0 && (
                                                    <span className="inline-block mt-1 text-[9px] sm:text-[10px] px-1.5 sm:px-2 py-0.5 rounded-md bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 font-bold whitespace-nowrap">
                                                        Save {variant.discount_percent}% OFF
                                                    </span>
                                                )}
                                            </div>
                                        </button>
                                    );
                                })}
                            </div>
                        </div>

                        {/* 3. Step 2: Quantity Configuration or Service Inputs */}
                        <div className="glass-panel p-4 sm:p-6 rounded-2xl border border-gray-200 dark:border-gray-800/80 shadow-md space-y-4">
                            <div className="flex items-center gap-2 sm:gap-2.5">
                                <span className="w-5 h-5 sm:w-7 sm:h-7 rounded-lg sm:rounded-xl bg-gradient-to-br from-[#f82803] to-[#990a0a] text-white flex items-center justify-center text-[10px] sm:text-xs font-black shadow-md shadow-red-500/20 shrink-0">
                                    2
                                </span>
                                <h2 className="text-xs sm:text-base font-bold text-gray-900 dark:text-white font-display whitespace-nowrap">
                                    {isService ? 'Device & Contact Information' : 'Choose Key Quantity'}
                                </h2>
                            </div>

                            {isService ? (
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div className="space-y-1.5">
                                        <label className="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                            <Phone className="w-3.5 h-3.5 text-red-500" />
                                            WhatsApp Number <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            value={serviceData.whatsapp_number}
                                            onChange={(e) => setServiceData({ ...serviceData, whatsapp_number: e.target.value })}
                                            placeholder="+8801700000000"
                                            className="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:border-red-500 transition-colors shadow-xs"
                                            required
                                        />
                                        <p className="text-[11px] text-gray-500 dark:text-gray-400">Our engineers will message you directly on WhatsApp to setup.</p>
                                    </div>

                                    <div className="space-y-1.5">
                                        <label className="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                            <Smartphone className="w-3.5 h-3.5 text-red-500" />
                                            Device Model Name
                                        </label>
                                        <input
                                            type="text"
                                            value={serviceData.device_model}
                                            onChange={(e) => setServiceData({ ...serviceData, device_model: e.target.value })}
                                            placeholder="e.g. Samsung S23 Ultra / Redmi Note 12"
                                            className="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:border-red-500 transition-colors shadow-xs"
                                        />
                                    </div>

                                    <div className="space-y-1.5">
                                        <label className="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                            <Cpu className="w-3.5 h-3.5 text-red-500" />
                                            Android / iOS Version
                                        </label>
                                        <input
                                            type="text"
                                            value={serviceData.android_version}
                                            onChange={(e) => setServiceData({ ...serviceData, android_version: e.target.value })}
                                            placeholder="e.g. Android 14 / iOS 17.5"
                                            className="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:border-red-500 transition-colors shadow-xs"
                                        />
                                    </div>

                                    <div className="space-y-1.5">
                                        <label className="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                            <Hash className="w-3.5 h-3.5 text-red-500" />
                                            IMEI / UID / Notes (Optional)
                                        </label>
                                        <input
                                            type="text"
                                            value={serviceData.imei_or_uid}
                                            onChange={(e) => setServiceData({ ...serviceData, imei_or_uid: e.target.value })}
                                            placeholder="Optional device IMEI or identifier"
                                            className="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:border-red-500 transition-colors shadow-xs"
                                        />
                                    </div>
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {/* Stepper row with instant delivery status */}
                                    <div className="flex items-center justify-between gap-2">
                                        {/* Stepper buttons */}
                                        <div className="flex items-center border border-gray-300 dark:border-gray-700/80 rounded-xl bg-white dark:bg-gray-900/90 p-1 shadow-xs">
                                            <button
                                                type="button"
                                                onClick={() => setQuantity(Math.max(1, quantity - 1))}
                                                disabled={quantity <= 1}
                                                className="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 flex items-center justify-center text-gray-900 dark:text-white disabled:opacity-30 transition-all active:scale-95 cursor-pointer"
                                            >
                                                <Minus className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                            </button>
                                            <span className="w-10 sm:w-12 text-center font-mono font-black text-gray-900 dark:text-white text-base sm:text-lg">
                                                {quantity}
                                            </span>
                                            <button
                                                type="button"
                                                onClick={() => setQuantity(Math.min(20, quantity + 1))}
                                                disabled={quantity >= 20}
                                                className="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 flex items-center justify-center text-gray-900 dark:text-white disabled:opacity-30 transition-all active:scale-95 cursor-pointer"
                                            >
                                                <Plus className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                            </button>
                                        </div>

                                        {/* Delivery Badge */}
                                        <span className="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-full text-[11px] sm:text-xs font-semibold bg-emerald-500/10 border border-emerald-500/25 text-emerald-600 dark:text-emerald-400">
                                            <Zap className="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-emerald-500 shrink-0" />
                                            <span className="whitespace-nowrap">
                                                {quantity > 1 ? `${quantity} Keys Auto-Deliver` : '1 Key Instant Delivery'}
                                            </span>
                                        </span>
                                    </div>

                                    {/* Quick Select Presets: Balanced 5-Column Grid */}
                                    <div className="grid grid-cols-5 gap-1.5 sm:gap-2">
                                        {[1, 2, 3, 5, 10].map((num) => {
                                            const isSelected = quantity === num;
                                            return (
                                                <button
                                                    key={num}
                                                    type="button"
                                                    onClick={() => setQuantity(num)}
                                                    className={`py-2 px-1 rounded-xl text-xs font-bold transition-all text-center flex flex-col items-center justify-center cursor-pointer ${
                                                        isSelected
                                                            ? 'bg-gradient-to-br from-[#f82803] to-[#b30808] text-white shadow-md shadow-red-500/25 scale-[1.02]'
                                                            : 'bg-gray-100 dark:bg-gray-800/80 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700/60'
                                                    }`}
                                                >
                                                    <span className="font-mono text-xs sm:text-sm font-black leading-none">{num}</span>
                                                    <span className="text-[10px] font-medium opacity-80 mt-0.5">
                                                        {num === 1 ? 'Key' : 'Keys'}
                                                    </span>
                                                </button>
                                            );
                                        })}
                                    </div>

                                    {/* Compact Informational Note */}
                                    <div className="p-2.5 sm:p-3 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-200/70 dark:border-gray-800 text-[11px] sm:text-xs text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                        <ShieldCheck className="w-3.5 h-3.5 text-emerald-500 shrink-0" />
                                        <span>Each key has separate validity & can be activated independently.</span>
                                    </div>
                                </div>
                            )}
                        </div>
                        {/* 4. Product Description & Installation Guide (Collapsible Accordion) */}
                        {product.description && (
                            <div className="glass-panel rounded-2xl border border-gray-200 dark:border-gray-800/80 shadow-md overflow-hidden transition-all">
                                <button
                                    type="button"
                                    onClick={() => setIsGuideOpen(!isGuideOpen)}
                                    className="w-full p-4 sm:p-5 flex items-center justify-between gap-3 text-left hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors cursor-pointer"
                                >
                                    <div className="flex items-center gap-2.5 min-w-0">
                                        <div className="w-7 h-7 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center shrink-0">
                                            <FileText className="w-3.5 h-3.5" />
                                        </div>
                                        <h3 className="text-xs sm:text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider font-display truncate">
                                            Product Setup & Safety Guide
                                        </h3>
                                    </div>
                                    <div className="flex items-center gap-2 shrink-0">
                                        <span className="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-medium hidden xs:inline">
                                            {isGuideOpen ? 'Hide' : 'Show'}
                                        </span>
                                        <ChevronDown className={`w-4 h-4 text-gray-400 transition-transform duration-300 ${isGuideOpen ? 'rotate-180 text-red-500' : ''}`} />
                                    </div>
                                </button>
                                
                                {isGuideOpen && (
                                    <div className="px-4 pb-4 sm:px-5 sm:pb-5 pt-1 border-t border-gray-100 dark:border-gray-800/80">
                                        <div 
                                            className="text-xs sm:text-sm text-gray-700 dark:text-gray-300 leading-relaxed prose prose-sm prose-neutral dark:prose-invert max-w-none pt-2"
                                            dangerouslySetInnerHTML={{ __html: product.description }}
                                        />
                                    </div>
                                )}
                            </div>
                        )}
                    </div>

                    {/* Right Column: Order Summary & Fast Checkout (4 cols sticky) */}
                    <div className="lg:col-span-4 space-y-6">
                        
                        <div className="glass-panel p-4 sm:p-6 rounded-2xl border border-red-500/30 shadow-xl space-y-4 sm:space-y-5 sticky top-24">
                            {/* Summary Header */}
                            <div className="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800">
                                <div className="flex items-center gap-2">
                                    <div className="w-7 h-7 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center font-bold">
                                        <Tag className="w-3.5 h-3.5" />
                                    </div>
                                    <h2 className="text-sm sm:text-base font-extrabold text-gray-900 dark:text-white font-display">
                                        Order Summary
                                    </h2>
                                </div>
                                <span className="text-[10px] sm:text-[11px] font-mono px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-500 font-bold border border-emerald-500/20">
                                    LIVE RECHARGE
                                </span>
                            </div>

                            {/* Itemized Order Details */}
                            <div className="p-3 sm:p-4 rounded-xl bg-gray-50/80 dark:bg-gray-900/60 border border-gray-200/70 dark:border-gray-800/80 space-y-2 text-xs sm:text-sm">
                                <div className="flex justify-between items-center text-gray-600 dark:text-gray-400">
                                    <span>Package:</span>
                                    <span className="font-bold text-gray-900 dark:text-white">{selectedVariant?.duration_name}</span>
                                </div>
                                <div className="flex justify-between items-center text-gray-600 dark:text-gray-400">
                                    <span>Unit Price:</span>
                                    <span className="font-mono text-gray-800 dark:text-gray-200">{selectedVariant?.formatted_price}</span>
                                </div>
                                <div className="flex justify-between items-center text-gray-600 dark:text-gray-400">
                                    <span>Quantity:</span>
                                    <span className="font-mono font-bold text-gray-900 dark:text-white">× {quantity}</span>
                                </div>

                                {appliedPromo && (
                                    <div className="flex justify-between items-center text-emerald-500 font-bold pt-1.5 border-t border-dashed border-emerald-500/30">
                                        <span className="flex items-center gap-1">
                                            <Sparkles className="w-3.5 h-3.5" /> Promo ({appliedPromo.code}):
                                        </span>
                                        <span className="font-mono">- ৳ {appliedPromo.discount_amount}</span>
                                    </div>
                                )}

                                {/* Total Payable */}
                                <div className="flex justify-between items-baseline pt-2 border-t border-gray-200 dark:border-gray-800">
                                    <span className="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">
                                        Total Payable:
                                    </span>
                                    <span className="text-xl sm:text-2xl font-black font-mono text-[#f82803] dark:text-red-400 tracking-tight">
                                        ৳ {totalAmount.toFixed(0)}
                                    </span>
                                </div>
                            </div>

                            {/* Promo Code Input Box */}
                            <div className="pt-2 border-t border-gray-200 dark:border-gray-800">
                                {!showPromoInput && !appliedPromo ? (
                                    <button
                                        type="button"
                                        onClick={() => setShowPromoInput(true)}
                                        className="w-full py-1.5 px-3 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 hover:border-red-500/50 text-[11px] sm:text-xs text-gray-600 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 flex items-center justify-between transition-colors cursor-pointer"
                                    >
                                        <span className="flex items-center gap-1.5">
                                            <Tag className="w-3 h-3 text-red-500" />
                                            <span>Have a Promo Code?</span>
                                        </span>
                                        <span className="font-bold text-red-500 underline">Add Coupon</span>
                                    </button>
                                ) : (
                                    <div className="space-y-2">
                                        <label className="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center justify-between">
                                            <span>Promo / Discount Code</span>
                                            {appliedPromo && (
                                                <span className="text-[11px] text-emerald-500 font-semibold">Active</span>
                                            )}
                                        </label>
                                        <div className="flex gap-2">
                                            <input
                                                type="text"
                                                value={promoInput}
                                                onChange={(e) => setPromoInput(e.target.value.toUpperCase())}
                                                placeholder="ENTER COUPON"
                                                disabled={!!appliedPromo}
                                                className="flex-1 px-3.5 py-2 rounded-xl bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 text-xs font-mono uppercase focus:outline-none focus:border-red-500 transition-colors shadow-xs"
                                            />
                                            {appliedPromo ? (
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        setAppliedPromo(null);
                                                        setPromoInput('');
                                                        setShowPromoInput(false);
                                                    }}
                                                    className="px-3.5 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs text-red-600 dark:text-red-400 hover:bg-gray-200 dark:hover:bg-gray-700 font-bold transition-colors cursor-pointer"
                                                >
                                                    Remove
                                                </button>
                                            ) : (
                                                <button
                                                    type="button"
                                                    onClick={handleApplyPromo}
                                                    disabled={promoLoading || !promoInput.trim()}
                                                    className="px-4 py-2 rounded-xl bg-gray-900 dark:bg-gray-800 hover:bg-red-600 text-white text-xs font-bold transition-colors disabled:opacity-40 cursor-pointer"
                                                >
                                                    {promoLoading ? 'Checking...' : 'Apply'}
                                                </button>
                                            )}
                                        </div>
                                        {promoError && <p className="text-[11px] text-red-500 font-medium">{promoError}</p>}
                                        {appliedPromo && <p className="text-[11px] text-emerald-500 font-semibold">✓ Discount applied successfully!</p>}
                                    </div>
                                )}
                            </div>

                            {/* Step 3: Choose Payment Method (2-Column Grid) */}
                            <div className="space-y-2 pt-2 border-t border-gray-200 dark:border-gray-800">
                                <label className="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider block">
                                    Select Payment Method
                                </label>

                                <div className="grid grid-cols-2 gap-2">
                                    {/* 1. Wallet Balance Pay */}
                                    <button
                                        type="button"
                                        onClick={() => setPaymentMethod('wallet')}
                                        className={`p-3 rounded-xl border-2 text-left transition-all flex flex-col justify-between cursor-pointer relative ${
                                            paymentMethod === 'wallet'
                                                ? 'bg-red-50/70 dark:bg-red-950/60 border-red-500 text-gray-900 dark:text-white shadow-sm shadow-red-500/10 scale-[1.01]'
                                                : 'bg-white dark:bg-gray-900/60 hover:bg-gray-50 dark:hover:bg-gray-800/60 border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300'
                                        }`}
                                    >
                                        <div className="flex items-center justify-between mb-2">
                                            <div className="w-7 h-7 rounded-lg bg-red-500/15 text-red-500 flex items-center justify-center shrink-0">
                                                <Wallet className="w-3.5 h-3.5" />
                                            </div>
                                            <div className={`w-3.5 h-3.5 rounded-full border-2 flex items-center justify-center ${
                                                paymentMethod === 'wallet' ? 'border-red-500 bg-red-500' : 'border-gray-300 dark:border-gray-700'
                                            }`}>
                                                {paymentMethod === 'wallet' && <div className="w-1 h-1 rounded-full bg-white" />}
                                            </div>
                                        </div>
                                        <div className="min-w-0">
                                            <span className="block text-xs font-extrabold text-gray-900 dark:text-white truncate">
                                                Wallet Pay
                                            </span>
                                            <span className="block text-[10px] text-gray-500 dark:text-gray-400 font-mono mt-0.5 truncate">
                                                {customer ? customer.formatted_balance : 'Login required'}
                                            </span>
                                        </div>
                                    </button>

                                    {/* 2. Automated Gateway (bKash/Nagad/Rocket/Cards) */}
                                    <button
                                        type="button"
                                        onClick={() => setPaymentMethod('gateway')}
                                        className={`p-3 rounded-xl border-2 text-left transition-all flex flex-col justify-between cursor-pointer relative ${
                                            paymentMethod === 'gateway'
                                                ? 'bg-red-50/70 dark:bg-red-950/60 border-red-500 text-gray-900 dark:text-white shadow-sm shadow-red-500/10 scale-[1.01]'
                                                : 'bg-white dark:bg-gray-900/60 hover:bg-gray-50 dark:hover:bg-gray-800/60 border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300'
                                        }`}
                                    >
                                        <div className="flex items-center justify-between mb-2">
                                            <div className="w-7 h-7 rounded-lg bg-purple-500/15 text-purple-500 flex items-center justify-center shrink-0">
                                                <CreditCard className="w-3.5 h-3.5" />
                                            </div>
                                            <div className={`w-3.5 h-3.5 rounded-full border-2 flex items-center justify-center ${
                                                paymentMethod === 'gateway' ? 'border-red-500 bg-red-500' : 'border-gray-300 dark:border-gray-700'
                                            }`}>
                                                {paymentMethod === 'gateway' && <div className="w-1 h-1 rounded-full bg-white" />}
                                            </div>
                                        </div>
                                        <div className="min-w-0">
                                            <span className="block text-xs font-extrabold text-gray-900 dark:text-white truncate">
                                                bKash / Nagad
                                            </span>
                                            <span className="block text-[10px] text-purple-500 dark:text-purple-400 font-semibold mt-0.5 truncate">
                                                Instant Auto Pay
                                            </span>
                                        </div>
                                    </button>
                                </div>

                                {/* Wallet Insufficient Balance Alert */}
                                {customer && paymentMethod === 'wallet' && !hasEnoughWalletBalance && (
                                    <div className="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-800 dark:text-amber-200 space-y-1.5 mt-2">
                                        <div className="flex items-center gap-1.5 font-bold text-amber-600 dark:text-amber-400 text-xs">
                                            <AlertCircle className="w-3.5 h-3.5 shrink-0" />
                                            <span>Insufficient Wallet Balance</span>
                                        </div>
                                        <p className="text-[11px] leading-relaxed">
                                            You need ৳ {(totalAmount - customer.balance).toFixed(0)} more to pay via wallet.
                                        </p>
                                        <div className="flex items-center gap-2 pt-0.5">
                                            <Link
                                                href="/wallet"
                                                className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] transition-colors shadow-xs"
                                            >
                                                <Wallet className="w-3 h-3" />
                                                <span>Deposit Wallet</span>
                                            </Link>
                                            <button
                                                type="button"
                                                onClick={() => setPaymentMethod('gateway')}
                                                className="text-[11px] text-amber-700 dark:text-amber-300 underline font-semibold cursor-pointer"
                                            >
                                                Use bKash/Nagad
                                            </button>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Submit Error Message */}
                            {submitError && (
                                <div className="p-3.5 rounded-xl bg-red-500/10 border border-red-500/30 text-xs text-red-600 dark:text-red-300 flex items-start gap-2.5">
                                    <AlertCircle className="w-4 h-4 text-red-500 shrink-0 mt-0.5" />
                                    <span className="font-medium">{submitError}</span>
                                </div>
                            )}

                            {/* Main CTA Order Button */}
                            {product.is_maintenance ? (
                                <div className="p-4 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-300 text-xs text-center font-bold flex flex-col items-center gap-2">
                                    <Wrench className="w-6 h-6 text-gray-500" />
                                    This product is currently under maintenance. Please check back shortly.
                                </div>
                            ) : customer ? (
                                <button
                                    type="button"
                                    onClick={handleCheckout}
                                    disabled={isSubmitting || (paymentMethod === 'wallet' && !hasEnoughWalletBalance)}
                                    className="w-full py-4 rounded-xl bg-gradient-to-r from-[#f82803] via-red-600 to-[#730505] hover:from-[#ff3a17] hover:to-[#8f0909] text-white font-black text-sm tracking-wider shadow-xl shadow-red-500/25 transition-all hover:scale-[1.02] active:scale-95 disabled:opacity-50 disabled:pointer-events-none flex items-center justify-center gap-2 cursor-pointer uppercase"
                                >
                                    {isSubmitting ? (
                                        <>
                                            <Loader2 className="w-4 h-4 animate-spin text-white" />
                                            <span>Processing Secure Order...</span>
                                        </>
                                    ) : (
                                        <>
                                            <Zap className="w-4 h-4 fill-white" />
                                            <span>Order Now · ৳ {totalAmount.toFixed(0)}</span>
                                        </>
                                    )}
                                </button>
                            ) : (
                                <div className="space-y-2.5">
                                    <Link
                                        href="/login"
                                        className="w-full py-3.5 rounded-xl bg-gradient-to-r from-[#f82803] to-[#730505] hover:opacity-90 text-white font-extrabold text-sm text-center block shadow-lg shadow-red-500/20 transition-all uppercase tracking-wider"
                                    >
                                        Log In to Purchase
                                    </Link>
                                    <p className="text-[11px] text-gray-500 text-center">
                                        Don't have an account? <Link href="/register" className="text-red-500 font-bold underline">Register here</Link>
                                    </p>
                                </div>
                            )}

                            {/* Trust & Guarantee Badges */}
                            <div className="pt-2 border-t border-gray-200 dark:border-gray-800/80 grid grid-cols-2 gap-2 text-[10px] text-gray-500 dark:text-gray-400">
                                <div className="flex items-center gap-1.5">
                                    <Lock className="w-3.5 h-3.5 text-emerald-500 shrink-0" />
                                    <span>256-Bit SSL Encrypted</span>
                                </div>
                                <div className="flex items-center gap-1.5">
                                    <Zap className="w-3.5 h-3.5 text-amber-500 shrink-0" />
                                    <span>1-Sec Key Delivery</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {/* Mobile Fixed Bottom Purchase Bar */}
            <div className="md:hidden fixed bottom-14 left-0 right-0 z-40 bg-white/95 dark:bg-[#0d1322]/95 backdrop-blur-lg border-t border-gray-200 dark:border-gray-800 p-3 shadow-2xl">
                <div className="max-w-md mx-auto flex items-center justify-between gap-3">
                    <div>
                        <span className="block text-[10px] text-gray-500 uppercase font-mono tracking-wider">
                            {selectedVariant?.duration_name} (×{quantity})
                        </span>
                        <span className="text-xl font-black font-mono text-[#f82803] dark:text-red-400">
                            ৳ {totalAmount.toFixed(0)}
                        </span>
                    </div>

                    {customer ? (
                        <button
                            type="button"
                            onClick={handleCheckout}
                            disabled={isSubmitting || (paymentMethod === 'wallet' && !hasEnoughWalletBalance)}
                            className="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#f82803] to-[#730505] text-white font-extrabold text-xs uppercase tracking-wider shadow-lg shadow-red-500/25 flex items-center gap-1.5 disabled:opacity-50"
                        >
                            {isSubmitting ? (
                                <Loader2 className="w-4 h-4 animate-spin" />
                            ) : (
                                <>
                                    <Zap className="w-3.5 h-3.5 fill-white" />
                                    <span>Buy Now</span>
                                </>
                            )}
                        </button>
                    ) : (
                        <Link
                            href="/login"
                            className="px-6 py-2.5 rounded-xl bg-[#f82803] text-white font-extrabold text-xs uppercase tracking-wider shadow-lg shadow-red-500/25 block text-center"
                        >
                            Log In
                        </Link>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
