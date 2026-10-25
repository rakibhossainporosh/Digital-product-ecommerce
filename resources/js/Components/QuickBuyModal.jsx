import React, { useState, useEffect, useRef } from 'react';
import { router, usePage, Link } from '@inertiajs/react';
import { 
    X, 
    ShoppingCart, 
    Zap, 
    Wrench, 
    Clock, 
    Check, 
    Plus, 
    Minus, 
    Wallet, 
    CreditCard, 
    Tag, 
    Phone, 
    Smartphone, 
    Loader2, 
    ArrowRight, 
    ExternalLink, 
    AlertCircle,
    ChevronDown 
} from 'lucide-react';

export default function QuickBuyModal({ product, isOpen, onClose }) {
    if (!isOpen || !product) return null;

    const { auth } = usePage().props;
    const customer = auth?.customer;
    const isService = Boolean(product?.is_service || product?.type === 'service');

    const variants = product?.variants || [];
    const defaultVariant = variants.find((v) => v.is_popular) || variants[0] || null;

    // Component State Declarations
    const [selectedVariantId, setSelectedVariantId] = useState(defaultVariant?.id || null);
    const [quantity, setQuantity] = useState(1);
    const [paymentMethod, setPaymentMethod] = useState('wallet'); // 'wallet' or 'gateway'

    // Service inputs
    const [serviceData, setServiceData] = useState({
        whatsapp_number: customer?.whatsapp_number || '',
        device_model: '',
    });

    // Promo code state
    const [showPromo, setShowPromo] = useState(false);
    const [promoInput, setPromoInput] = useState('');
    const [appliedPromo, setAppliedPromo] = useState(null);
    const [promoLoading, setPromoLoading] = useState(false);
    const [promoError, setPromoError] = useState(null);

    // Form submission state
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitError, setSubmitError] = useState(null);

    // Drag-to-dismiss states & refs
    const [dragY, setDragY] = useState(0);
    const [isDragging, setIsDragging] = useState(false);
    const isDraggingRef = useRef(false);
    const startYRef = useRef(0);
    const currentDragYRef = useRef(0);
    const hasMovedRef = useRef(false);
    const bodyRef = useRef(null);

    // Re-initialize state when a new product is selected or modal opens
    useEffect(() => {
        if (product) {
            const initialVar = product.variants?.find((v) => v.is_popular) || product.variants?.[0] || null;
            setSelectedVariantId(initialVar?.id || null);
            setQuantity(1);
            setAppliedPromo(null);
            setPromoInput('');
            setShowPromo(false);
            setSubmitError(null);
            setDragY(0);
            setIsDragging(false);
            isDraggingRef.current = false;
            startYRef.current = 0;
            currentDragYRef.current = 0;
            hasMovedRef.current = false;
            setServiceData({
                whatsapp_number: customer?.whatsapp_number || '',
                device_model: '',
            });
        }
    }, [product?.id, isOpen]);

    // Handle ESC key press and body scroll locking
    useEffect(() => {
        const handleKeyDown = (e) => {
            if (e.key === 'Escape') {
                onClose();
            }
        };

        if (isOpen) {
            document.addEventListener('keydown', handleKeyDown);
            document.body.style.overflow = 'hidden';
        }

        return () => {
            document.removeEventListener('keydown', handleKeyDown);
            document.body.style.overflow = 'unset';
        };
    }, [isOpen, onClose]);

    // Start Dragging on Pointer / Touch / Mouse Down
    const handleDragStart = (clientY) => {
        isDraggingRef.current = true;
        startYRef.current = clientY;
        currentDragYRef.current = 0;
        hasMovedRef.current = false;
        setIsDragging(true);
    };

    // Global listeners while dragging (supports Mouse, Touch, Pointer everywhere on screen)
    useEffect(() => {
        if (!isDragging) return;

        const handlePointerMove = (e) => {
            if (!isDraggingRef.current) return;
            const clientY = e.clientY !== undefined ? e.clientY : (e.touches && e.touches[0]?.clientY);
            if (clientY === undefined) return;

            const diff = clientY - startYRef.current;

            if (diff > 4) {
                hasMovedRef.current = true;
            }

            if (diff > 0) {
                currentDragYRef.current = diff;
                setDragY(diff);
            } else {
                currentDragYRef.current = 0;
                setDragY(0);
            }
        };

        const handlePointerUp = () => {
            if (!isDraggingRef.current) return;
            isDraggingRef.current = false;
            setIsDragging(false);

            // If dragged down past 70px threshold: animate down and close
            if (currentDragYRef.current > 70) {
                setDragY(window.innerHeight || 600);
                setTimeout(() => {
                    onClose();
                    setDragY(0);
                    currentDragYRef.current = 0;
                    startYRef.current = 0;
                }, 200);
            } else {
                // Snap back up
                setDragY(0);
                currentDragYRef.current = 0;
                startYRef.current = 0;
            }
        };

        window.addEventListener('pointermove', handlePointerMove, { passive: false });
        window.addEventListener('pointerup', handlePointerUp);
        window.addEventListener('pointercancel', handlePointerUp);
        window.addEventListener('mousemove', handlePointerMove);
        window.addEventListener('mouseup', handlePointerUp);
        window.addEventListener('touchmove', handlePointerMove, { passive: false });
        window.addEventListener('touchend', handlePointerUp);

        return () => {
            window.removeEventListener('pointermove', handlePointerMove);
            window.removeEventListener('pointerup', handlePointerUp);
            window.removeEventListener('pointercancel', handlePointerUp);
            window.removeEventListener('mousemove', handlePointerMove);
            window.removeEventListener('mouseup', handlePointerUp);
            window.removeEventListener('touchmove', handlePointerMove);
            window.removeEventListener('touchend', handlePointerUp);
        };
    }, [isDragging, onClose]);

    const selectedVariant = variants.find((v) => v.id == selectedVariantId) || defaultVariant;

    // Pricing calculation
    const unitPrice = selectedVariant ? Number(selectedVariant.effective_price || 0) : 0;
    const subtotal = Math.round(unitPrice * quantity * 100) / 100;
    const discountAmount = appliedPromo ? Number(appliedPromo.discount_amount || 0) : 0;
    const totalAmount = Math.max(0, Math.round((subtotal - discountAmount) * 100) / 100);

    const customerBalance = customer ? Number(customer.balance || 0) : 0;
    const hasEnoughWalletBalance = customer && customerBalance >= totalAmount;

    // Promo code validation
    const handleApplyPromo = async () => {
        if (!promoInput.trim()) return;

        if (!customer) {
            setPromoError('Please login to use coupon codes.');
            return;
        }

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
                    promo_code: promoInput.trim(),
                    variant_id: selectedVariant?.id,
                    quantity: quantity,
                }),
            });

            const data = await res.json();

            if (res.ok && data.valid) {
                setAppliedPromo(data);
                setPromoError(null);
                const discountText = data.discount_amount ? ` You saved ৳${data.discount_amount}!` : '';
                window.dispatchEvent(
                    new CustomEvent('app-toast', {
                        detail: {
                            type: 'success',
                            message: `Coupon code "${data.code || promoInput.toUpperCase()}" applied successfully!${discountText}`,
                        },
                    })
                );
            } else {
                setAppliedPromo(null);
                setPromoError(data.error || 'Invalid or expired promo code.');
            }
        } catch {
            setPromoError('Unable to validate promo code. Please check your network connection.');
        } finally {
            setPromoLoading(false);
        }
    };

    const handleRemovePromo = () => {
        setAppliedPromo(null);
        setPromoInput('');
        setPromoError(null);
        window.dispatchEvent(
            new CustomEvent('app-toast', {
                detail: {
                    type: 'info',
                    message: 'Coupon code removed from this order.',
                },
            })
        );
    };

    // Checkout submission
    const handlePayNow = (e) => {
        if (e) e.preventDefault();

        if (!customer) {
            router.visit('/login');
            return;
        }

        if (isService && !serviceData.whatsapp_number.trim()) {
            const msg = 'Please enter your WhatsApp contact number so our team can deliver your service.';
            setSubmitError(msg);
            window.dispatchEvent(
                new CustomEvent('app-toast', {
                    detail: {
                        type: 'warning',
                        message: msg,
                    },
                })
            );
            return;
        }

        if (paymentMethod === 'wallet' && !hasEnoughWalletBalance) {
            const msg = 'Insufficient wallet balance. Please choose Online Payment or top up your wallet balance.';
            setSubmitError(msg);
            window.dispatchEvent(
                new CustomEvent('app-toast', {
                    detail: {
                        type: 'warning',
                        message: msg,
                    },
                })
            );
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
                const errMsg = Object.values(errors)[0] || 'Order checkout could not be processed. Please check your inputs and try again.';
                setSubmitError(errMsg);
                window.dispatchEvent(
                    new CustomEvent('app-toast', {
                        detail: {
                            type: 'error',
                            message: errMsg,
                        },
                    })
                );
            },
            onFinish: () => {
                setIsSubmitting(false);
            },
        });
    };

    return (
        <div 
            className="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/80 backdrop-blur-sm transition-opacity duration-300"
            style={{
                opacity: dragY > 0 ? Math.max(0.1, 1 - dragY / 300) : 1,
            }}
            onClick={onClose}
        >
            <div 
                className="relative w-full sm:max-w-lg bg-white dark:bg-[#0c1322] border-t sm:border border-gray-200 dark:border-gray-800 rounded-t-3xl sm:rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[92vh] sm:max-h-[88vh] will-change-transform"
                style={{
                    transform: dragY > 0 ? `translate3d(0, ${dragY}px, 0)` : undefined,
                    transition: isDragging ? 'none' : 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease',
                }}
                onClick={(e) => e.stopPropagation()}
            >
                {/* Mobile Pull Handle Area (Drag/pull down to dismiss or click to close) */}
                <div 
                    className="w-full pt-3.5 pb-2.5 flex flex-col items-center justify-center cursor-grab active:cursor-grabbing sm:hidden touch-none select-none hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group"
                    onPointerDown={(e) => {
                        if (e.button === 0 || e.pointerType === 'touch') {
                            handleDragStart(e.clientY);
                        }
                    }}
                    onTouchStart={(e) => {
                        if (e.touches && e.touches[0]) {
                            handleDragStart(e.touches[0].clientY);
                        }
                    }}
                    onClick={() => {
                        if (!hasMovedRef.current && currentDragYRef.current === 0) {
                            onClose();
                        }
                    }}
                    title="Drag down to close"
                >
                    <div className="w-14 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full transition-all group-active:scale-90" />
                </div>

                {/* Header with Product Summary & Close (Also draggable) */}
                <div 
                    className="px-4 py-2.5 sm:p-5 border-b border-gray-100 dark:border-gray-800/80 bg-gradient-to-r from-gray-50/80 via-white to-gray-50/80 dark:from-gray-900/90 dark:via-[#0c1322] dark:to-gray-900/90 flex items-start justify-between gap-2.5 shrink-0 select-none cursor-grab active:cursor-grabbing touch-none"
                    onPointerDown={(e) => {
                        if (e.button === 0 || e.pointerType === 'touch') {
                            handleDragStart(e.clientY);
                        }
                    }}
                    onTouchStart={(e) => {
                        if (e.touches && e.touches[0]) {
                            handleDragStart(e.touches[0].clientY);
                        }
                    }}
                >
                    <div className="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                        <div className="w-11 h-11 sm:w-14 sm:h-14 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700/60 overflow-hidden flex items-center justify-center shrink-0 shadow-xs">
                            {product.image_url ? (
                                <img 
                                    src={product.image_url} 
                                    alt={product.name} 
                                    className="w-full h-full object-contain p-1"
                                />
                            ) : (
                                <Zap className="w-5 h-5 text-red-500" />
                            )}
                        </div>
                        <div className="min-w-0 flex-1">
                            <div className="flex items-center gap-1.5 flex-wrap mb-1">
                                {product.category && (
                                    <span className="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-semibold bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20 truncate max-w-[120px]">
                                        {product.category.name}
                                    </span>
                                )}
                                {isService ? (
                                    <span className="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center gap-1">
                                        <Wrench className="w-2.5 h-2.5" /> Service
                                    </span>
                                ) : (
                                    <span className="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center gap-0.5">
                                        <Zap className="w-2.5 h-2.5 fill-emerald-500" /> Instant Key
                                    </span>
                                )}
                            </div>
                            <h2 className="font-display font-bold text-gray-900 dark:text-white text-xs sm:text-base leading-tight line-clamp-2">
                                {product.name}
                            </h2>
                        </div>
                    </div>

                    <div className="flex items-center gap-0.5 sm:gap-1 shrink-0">
                        <Link
                            href={`/product/${product.slug}`}
                            title="View full product details"
                            onPointerDown={(e) => e.stopPropagation()}
                            onTouchStart={(e) => e.stopPropagation()}
                            className="p-1.5 sm:p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        >
                            <ExternalLink className="w-4 h-4" />
                        </Link>
                        <button
                            type="button"
                            onPointerDown={(e) => e.stopPropagation()}
                            onTouchStart={(e) => e.stopPropagation()}
                            onClick={(e) => {
                                e.stopPropagation();
                                onClose();
                            }}
                            className="p-1.5 sm:p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer"
                        >
                            <X className="w-5 h-5" />
                        </button>
                    </div>
                </div>

                {/* Modal Scrollable Body */}
                <div 
                    ref={bodyRef}
                    className="p-4 sm:p-5 overflow-y-auto flex-1 space-y-3.5 sm:space-y-4.5"
                >
                    {/* Error Banner */}
                    {submitError && (
                        <div className="p-2.5 sm:p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-600 dark:text-red-400 text-xs flex items-center gap-2">
                            <AlertCircle className="w-4 h-4 shrink-0" />
                            <span>{submitError}</span>
                        </div>
                    )}

                    {/* Step 1: Variant Selection (Dropdown Select Option) */}
                    <div>
                        <div className="flex items-center justify-between mb-2">
                            <label 
                                htmlFor="quick-buy-variant-select"
                                className="text-xs font-bold text-gray-900 dark:text-gray-200 flex items-center gap-1.5 font-display"
                            >
                                <span className="w-4 h-4 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center font-bold shrink-0">1</span>
                                Select Package / Duration
                            </label>
                            <span className="text-[11px] text-gray-500 dark:text-gray-400 font-mono">
                                {variants.length} option{variants.length > 1 ? 's' : ''}
                            </span>
                        </div>

                        {variants.length > 0 ? (
                            <div className="space-y-2">
                                <div className="relative">
                                    <select
                                        id="quick-buy-variant-select"
                                        value={selectedVariantId || ''}
                                        onChange={(e) => {
                                            setSelectedVariantId(Number(e.target.value));
                                            setAppliedPromo(null);
                                        }}
                                        className="w-full appearance-none rounded-xl border-2 border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/60 py-3 pl-3.5 pr-10 text-xs sm:text-sm font-semibold text-gray-900 dark:text-white focus:border-[#f82803] focus:ring-1 focus:ring-[#f82803] focus:outline-hidden transition-all cursor-pointer shadow-xs"
                                    >
                                        {variants.map((v) => (
                                            <option 
                                                key={v.id} 
                                                value={v.id} 
                                                className="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 py-1.5"
                                            >
                                                {v.duration_name} ({v.duration_days} Days) — {v.formatted_price} {v.discount_percent > 0 ? `[Save ${v.discount_percent}%]` : ''} {v.is_popular ? '★ Best Value' : ''}
                                            </option>
                                        ))}
                                    </select>
                                    <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400">
                                        <ChevronDown className="w-4 h-4 sm:w-5 sm:h-5 text-gray-500" />
                                    </div>
                                </div>

                                {/* Active Variant Summary Details Card */}
                                {selectedVariant && (
                                    <div className="p-2.5 sm:p-3 rounded-xl bg-red-500/5 dark:bg-red-950/20 border border-red-500/20 flex items-center justify-between gap-2.5">
                                        <div className="flex items-center gap-2.5 min-w-0">
                                            <div className="w-8 h-8 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center shrink-0">
                                                <Clock className="w-4 h-4 text-red-500" />
                                            </div>
                                            <div className="min-w-0">
                                                <div className="flex items-center gap-1.5 flex-wrap">
                                                    <span className="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate">
                                                        {selectedVariant.duration_name}
                                                    </span>
                                                    {selectedVariant.is_popular && (
                                                        <span className="px-1.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider bg-gradient-to-r from-amber-500 to-orange-500 text-black shrink-0">
                                                            Best
                                                        </span>
                                                    )}
                                                </div>
                                                <span className="text-[10px] sm:text-[11px] text-gray-500 dark:text-gray-400 font-mono">
                                                    Validity: {selectedVariant.duration_days} Days
                                                </span>
                                            </div>
                                        </div>

                                        <div className="text-right shrink-0">
                                            <div className="flex items-baseline justify-end gap-1.5">
                                                <span className="text-xs sm:text-sm font-black font-mono text-[#f82803] dark:text-red-400">
                                                    {selectedVariant.formatted_price}
                                                </span>
                                                {selectedVariant.discount_percent > 0 && (
                                                    <span className="text-[10px] text-gray-400 line-through font-mono">
                                                        {selectedVariant.formatted_regular_price}
                                                    </span>
                                                )}
                                            </div>
                                            {selectedVariant.discount_percent > 0 && (
                                                <span className="inline-block text-[9px] font-bold text-emerald-600 dark:text-emerald-400">
                                                    Save {selectedVariant.discount_percent}% OFF
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>
                        ) : (
                            <p className="text-xs text-gray-500 italic p-3 bg-gray-50 dark:bg-gray-900 rounded-xl">
                                No packages available for this product.
                            </p>
                        )}
                    </div>

                    {/* Step 2: Quantity or Service Information */}
                    {isService ? (
                        <div className="space-y-2 p-3 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800">
                            <label className="text-xs font-bold text-gray-900 dark:text-gray-200 flex items-center gap-1.5 font-display">
                                <span className="w-4 h-4 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center font-bold shrink-0">2</span>
                                Service Details
                            </label>

                            <div className="space-y-2 pt-0.5">
                                <div>
                                    <label className="text-[11px] font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1 mb-1">
                                        <Phone className="w-3 h-3 text-red-500" /> WhatsApp Number <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={serviceData.whatsapp_number}
                                        onChange={(e) => setServiceData({ ...serviceData, whatsapp_number: e.target.value })}
                                        placeholder="+8801700000000"
                                        className="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 text-xs focus:outline-none focus:border-red-500 transition-colors"
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="text-[11px] font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1 mb-1">
                                        <Smartphone className="w-3 h-3 text-red-500" /> Device Model Name
                                    </label>
                                    <input
                                        type="text"
                                        value={serviceData.device_model}
                                        onChange={(e) => setServiceData({ ...serviceData, device_model: e.target.value })}
                                        placeholder="e.g. Samsung S23, Redmi Note 12"
                                        className="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 text-xs focus:outline-none focus:border-red-500 transition-colors"
                                    />
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div>
                            <div className="flex items-center justify-between mb-2">
                                <label className="text-xs font-bold text-gray-900 dark:text-gray-200 flex items-center gap-1.5 font-display">
                                    <span className="w-4 h-4 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center font-bold shrink-0">2</span>
                                    Select Quantity
                                </label>
                                <span className="text-[11px] text-gray-500 dark:text-gray-400">Max 50 keys</span>
                            </div>

                            <div className="flex items-center justify-between p-2.5 sm:p-3 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800">
                                <span className="text-xs text-gray-600 dark:text-gray-400 font-medium">
                                    Instant Serial Codes:
                                </span>
                                <div className="flex items-center gap-2">
                                    <button
                                        type="button"
                                        onClick={() => setQuantity((q) => Math.max(1, q - 1))}
                                        disabled={quantity <= 1}
                                        className="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-700 dark:text-gray-300 hover:text-red-500 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer touch-manipulation"
                                    >
                                        <Minus className="w-3.5 h-3.5" />
                                    </button>
                                    <span className="w-8 text-center text-sm font-bold font-mono text-gray-900 dark:text-white">
                                        {quantity}
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() => setQuantity((q) => Math.min(50, q + 1))}
                                        disabled={quantity >= 50}
                                        className="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-700 dark:text-gray-300 hover:text-red-500 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer touch-manipulation"
                                    >
                                        <Plus className="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Step 3: Payment Method Selection */}
                    <div>
                        <label className="text-xs font-bold text-gray-900 dark:text-gray-200 flex items-center gap-1.5 font-display mb-2">
                            <span className="w-4 h-4 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center font-bold shrink-0">3</span>
                            Payment Method
                        </label>

                        <div className="grid grid-cols-2 gap-2">
                            {/* Wallet Option */}
                            <button
                                type="button"
                                onClick={() => setPaymentMethod('wallet')}
                                className={`p-2.5 sm:p-3 rounded-xl border-2 text-left transition-all relative flex flex-col justify-between cursor-pointer ${
                                    paymentMethod === 'wallet'
                                        ? 'bg-red-500/10 border-[#f82803] dark:bg-red-950/40 shadow-xs'
                                        : 'bg-gray-50 dark:bg-gray-900/60 border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700'
                                }`}
                            >
                                <div className="flex items-center gap-1.5 sm:gap-2 mb-1">
                                    <div className={`p-1.5 rounded-lg ${paymentMethod === 'wallet' ? 'bg-red-500 text-white' : 'bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400'}`}>
                                        <Wallet className="w-3.5 h-3.5" />
                                    </div>
                                    <span className="text-xs font-bold text-gray-900 dark:text-white">
                                        Wallet
                                    </span>
                                </div>
                                <div className="text-[10px] text-gray-500 dark:text-gray-400 flex items-center justify-between">
                                    <span>Balance:</span>
                                    <span className={`font-mono font-bold whitespace-nowrap ${customer && hasEnoughWalletBalance ? 'text-emerald-500' : 'text-amber-500'}`}>
                                        {customer ? `৳ ${customerBalance.toFixed(0)}` : '৳ 0'}
                                    </span>
                                </div>
                            </button>

                            {/* Gateway Option */}
                            <button
                                type="button"
                                onClick={() => setPaymentMethod('gateway')}
                                className={`p-2.5 sm:p-3 rounded-xl border-2 text-left transition-all relative flex flex-col justify-between cursor-pointer ${
                                    paymentMethod === 'gateway'
                                        ? 'bg-red-500/10 border-[#f82803] dark:bg-red-950/40 shadow-xs'
                                        : 'bg-gray-50 dark:bg-gray-900/60 border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700'
                                }`}
                            >
                                <div className="flex items-center gap-1.5 sm:gap-2 mb-1">
                                    <div className={`p-1.5 rounded-lg ${paymentMethod === 'gateway' ? 'bg-red-500 text-white' : 'bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400'}`}>
                                        <CreditCard className="w-3.5 h-3.5" />
                                    </div>
                                    <span className="text-xs font-bold text-gray-900 dark:text-white">
                                        Online Pay
                                    </span>
                                </div>
                                <div className="text-[10px] text-gray-500 dark:text-gray-400 truncate">
                                    bKash, Nagad, Cards
                                </div>
                            </button>
                        </div>

                        {paymentMethod === 'wallet' && customer && !hasEnoughWalletBalance && (
                            <p className="mt-1.5 text-[10px] text-amber-600 dark:text-amber-400 flex items-center gap-1">
                                <AlertCircle className="w-3 h-3 shrink-0" />
                                Short by ৳{(totalAmount - customerBalance).toFixed(0)}. Select "Online Pay" or deposit first.
                            </p>
                        )}
                    </div>

                    {/* Optional Promo Code Toggle */}
                    <div>
                        {!showPromo && !appliedPromo ? (
                            <button
                                type="button"
                                onClick={() => setShowPromo(true)}
                                className="text-[11px] font-semibold text-red-500 dark:text-red-400 hover:underline flex items-center gap-1 cursor-pointer"
                            >
                                <Tag className="w-3 h-3" /> Have a promo code?
                            </button>
                        ) : (
                            <div className="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 space-y-1.5">
                                {appliedPromo ? (
                                    <div className="flex items-center justify-between text-xs text-emerald-600 dark:text-emerald-400">
                                        <span className="font-bold flex items-center gap-1">
                                            <Check className="w-3.5 h-3.5" />
                                            {appliedPromo.code} applied (-৳{appliedPromo.discount_amount})
                                        </span>
                                        <button
                                            type="button"
                                            onClick={handleRemovePromo}
                                            className="text-red-500 text-[10px] font-bold hover:underline cursor-pointer"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                ) : (
                                    <>
                                        <div className="flex items-center gap-1.5">
                                            <input
                                                type="text"
                                                value={promoInput}
                                                onChange={(e) => setPromoInput(e.target.value.toUpperCase())}
                                                placeholder="Enter coupon code"
                                                className="flex-1 px-2.5 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 text-xs uppercase font-mono focus:outline-none focus:border-red-500"
                                            />
                                            <button
                                                type="button"
                                                onClick={handleApplyPromo}
                                                disabled={promoLoading || !promoInput.trim()}
                                                className="px-3 py-1.5 rounded-lg bg-gray-900 dark:bg-gray-800 hover:bg-gray-800 dark:hover:bg-gray-700 text-white font-bold text-xs disabled:opacity-40 transition-colors flex items-center gap-1 cursor-pointer"
                                            >
                                                {promoLoading && <Loader2 className="w-3 h-3 animate-spin" />}
                                                Apply
                                            </button>
                                        </div>
                                        {promoError && (
                                            <p className="text-[10px] text-red-500">{promoError}</p>
                                        )}
                                    </>
                                )}
                            </div>
                        )}
                    </div>
                </div>

                {/* Sticky Footer with Price Breakdown and Pay Now Action */}
                <div className="px-4 py-3 sm:p-5 border-t border-gray-100 dark:border-gray-800/80 bg-white/95 dark:bg-[#0c1322]/95 backdrop-blur-md shrink-0 space-y-2.5 sm:space-y-3 pb-7 sm:pb-5">
                    <div className="flex items-center justify-between gap-2">
                        <div className="min-w-0">
                            <span className="block text-[9px] sm:text-[10px] uppercase font-semibold text-gray-500 dark:text-gray-400 font-display tracking-wider">
                                Total Payable
                            </span>
                            <div className="flex items-baseline gap-1.5">
                                <span className="text-lg sm:text-2xl font-black font-mono text-[#f82803] dark:text-red-400 whitespace-nowrap">
                                    ৳ {totalAmount.toFixed(0)}
                                </span>
                                {discountAmount > 0 && (
                                    <span className="text-[11px] sm:text-xs text-gray-400 line-through font-mono whitespace-nowrap">
                                        ৳ {subtotal.toFixed(0)}
                                    </span>
                                )}
                            </div>
                        </div>

                        <div className="text-right shrink-0">
                            <span className="block text-[10px] sm:text-[11px] text-gray-500 dark:text-gray-400 truncate max-w-[150px]">
                                {quantity} × {selectedVariant?.duration_name || '1 Item'}
                            </span>
                            {discountAmount > 0 && (
                                <span className="inline-block text-[9px] sm:text-[10px] font-bold text-emerald-500 whitespace-nowrap">
                                    Saved ৳ {discountAmount.toFixed(0)}
                                </span>
                            )}
                        </div>
                    </div>

                    {/* Main CTA */}
                    {customer ? (
                        <button
                            type="button"
                            onClick={handlePayNow}
                            disabled={isSubmitting || !selectedVariant}
                            className="w-full py-2.5 sm:py-3 rounded-xl bg-gradient-to-r from-[#f82803] to-[#8f0909] hover:from-[#ff411a] hover:to-[#a00b0b] text-white font-bold text-sm sm:text-base flex items-center justify-center gap-2 shadow-lg shadow-red-500/25 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer touch-manipulation"
                        >
                            {isSubmitting ? (
                                <>
                                    <Loader2 className="w-4 h-4 animate-spin shrink-0" />
                                    <span>Processing Order...</span>
                                </>
                            ) : (
                                <>
                                    <ShoppingCart className="w-4 h-4 shrink-0" />
                                    <span className="truncate">
                                        {paymentMethod === 'wallet' 
                                            ? `Pay ৳${totalAmount.toFixed(0)} with Wallet` 
                                            : `Pay ৳${totalAmount.toFixed(0)} via Gateway`}
                                    </span>
                                </>
                            )}
                        </button>
                    ) : (
                        <Link
                            href="/login"
                            className="w-full py-2.5 sm:py-3 rounded-xl bg-gradient-to-r from-[#f82803] to-[#8f0909] hover:from-[#ff411a] hover:to-[#a00b0b] text-white font-bold text-sm sm:text-base flex items-center justify-center gap-2 shadow-lg shadow-red-500/25 transition-all active:scale-[0.98] text-center touch-manipulation"
                        >
                            <span>Login to Pay Now</span>
                            <ArrowRight className="w-4 h-4 shrink-0" />
                        </Link>
                    )}

                    {/* View Details Link */}
                    <div className="text-center pt-0.5">
                        <Link
                            href={`/product/${product.slug}`}
                            className="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors inline-flex items-center gap-1 font-medium"
                        >
                            <span>Or view complete product description & setup guide</span>
                            <ArrowRight className="w-3 h-3 shrink-0" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}
