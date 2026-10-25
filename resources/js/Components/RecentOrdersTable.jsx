import React, { useRef, useState, useEffect } from 'react';
import { ShoppingCart, Clock, ShieldCheck, ChevronLeft, ChevronRight, ArrowLeftRight } from 'lucide-react';

export default function RecentOrdersTable({ orders }) {
    if (!orders || orders.length === 0) return null;

    const tableContainerRef = useRef(null);
    const trackRef = useRef(null);
    const [scrollProgress, setScrollProgress] = useState(0);
    const [thumbWidthRatio, setThumbWidthRatio] = useState(0.3);
    const [isScrollable, setIsScrollable] = useState(false);
    const [canScrollLeft, setCanScrollLeft] = useState(false);
    const [canScrollRight, setCanScrollRight] = useState(false);

    const updateScrollState = () => {
        const el = tableContainerRef.current;
        if (!el) return;

        const maxScroll = el.scrollWidth - el.clientWidth;
        const scrollable = maxScroll > 5;
        setIsScrollable(scrollable);

        if (scrollable) {
            const ratio = Math.max(0.18, Math.min(1, el.clientWidth / el.scrollWidth));
            setThumbWidthRatio(ratio);
            const progress = Math.max(0, Math.min(1, el.scrollLeft / maxScroll));
            setScrollProgress(progress);
            setCanScrollLeft(el.scrollLeft > 5);
            setCanScrollRight(el.scrollLeft < maxScroll - 5);
        } else {
            setScrollProgress(0);
            setCanScrollLeft(false);
            setCanScrollRight(false);
        }
    };

    useEffect(() => {
        updateScrollState();
        const handleResize = () => updateScrollState();
        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, [orders]);

    const scrollByAmount = (direction) => {
        const el = tableContainerRef.current;
        if (!el) return;
        const delta = 180;
        el.scrollBy({
            left: direction === 'left' ? -delta : delta,
            behavior: 'smooth',
        });
    };

    const handleTrackClick = (e) => {
        const el = tableContainerRef.current;
        const track = trackRef.current;
        if (!el || !track) return;
        const rect = track.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const percentage = Math.max(0, Math.min(1, clickX / rect.width));
        const maxScroll = el.scrollWidth - el.clientWidth;
        el.scrollTo({
            left: percentage * maxScroll,
            behavior: 'smooth',
        });
    };

    return (
        <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-4">
            <div className="glass-panel rounded-2xl border border-gray-200 dark:border-gray-800/80 overflow-hidden shadow-sm">
                <div className="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-800/80 bg-gray-50/50 dark:bg-gray-900/50 flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <div className="w-8 h-8 rounded-lg bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-500 shrink-0">
                            <ShoppingCart className="w-4 h-4" />
                        </div>
                        <h3 className="text-lg font-black font-display text-gray-900 dark:text-white">
                            Live Purchases
                        </h3>
                    </div>
                    <div className="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-500 text-[10px] font-bold uppercase tracking-wider border border-emerald-500/20">
                        <span className="relative flex h-2 w-2">
                            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span className="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        Real-time
                    </div>
                </div>

                {/* Table with Touch Swipe Support */}
                <div 
                    ref={tableContainerRef}
                    onScroll={updateScrollState}
                    className="overflow-x-auto touch-pan-x overscroll-x-contain [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
                    style={{ WebkitOverflowScrolling: 'touch' }}
                >
                    <table className="w-full text-left text-sm whitespace-nowrap">
                        <thead className="bg-gray-100/50 dark:bg-gray-800/30 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider font-semibold">
                            <tr>
                                <th className="px-4 sm:px-6 py-3">Customer</th>
                                <th className="px-4 sm:px-6 py-3">Product</th>
                                <th className="px-4 sm:px-6 py-3">Amount</th>
                                <th className="px-4 sm:px-6 py-3 text-right">Time</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100 dark:divide-gray-800/60 text-sm">
                            {orders.map((order, idx) => (
                                <tr key={`${order.id}-${idx}`} className="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors group">
                                    <td className="px-4 sm:px-6 py-3.5">
                                        <div className="flex items-center gap-2.5">
                                            <div className="w-7 h-7 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-400 font-bold text-[10px]">
                                                {order.customer_name.charAt(0)}
                                            </div>
                                            <span className="font-medium text-gray-900 dark:text-gray-200 group-hover:text-red-500 transition-colors">
                                                {order.customer_name}
                                            </span>
                                        </div>
                                    </td>
                                    <td className="px-4 sm:px-6 py-3.5">
                                        <span className="font-bold text-gray-800 dark:text-gray-100 flex items-center gap-1.5">
                                            <ShieldCheck className="w-3.5 h-3.5 text-red-500" />
                                            {order.product_name}
                                        </span>
                                    </td>
                                    <td className="px-4 sm:px-6 py-3.5">
                                        <span className="font-semibold text-emerald-600 dark:text-emerald-400 font-mono text-xs bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">
                                            ৳ {parseFloat(order.amount || 0).toFixed(2)}
                                        </span>
                                    </td>
                                    <td className="px-4 sm:px-6 py-3.5 text-right">
                                        <span className="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400 font-mono text-xs bg-gray-100 dark:bg-gray-800/60 px-2.5 py-1 rounded-md">
                                            <Clock className="w-3 h-3" />
                                            {order.time_ago}
                                        </span>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Always-Visible Custom Red Scrollbar Track & Controls */}
                {isScrollable && (
                    <div className="px-3.5 sm:px-5 py-2.5 bg-gray-50/90 dark:bg-gray-900/80 border-t border-gray-200/80 dark:border-gray-800/80 flex items-center justify-between gap-2.5 sm:gap-4">
                        <div className="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 font-medium shrink-0">
                            <ArrowLeftRight className="w-3.5 h-3.5 text-red-500 shrink-0" />
                            <span className="text-[11px] sm:text-xs">Scroll Table</span>
                        </div>

                        {/* Interactive Red Scrollbar Track */}
                        <div 
                            ref={trackRef}
                            onClick={handleTrackClick}
                            className="flex-1 max-w-[200px] sm:max-w-xs h-2 sm:h-2.5 bg-gray-200 dark:bg-gray-800 rounded-full relative cursor-pointer overflow-hidden shadow-inner"
                            title="Click anywhere to jump scroll position"
                        >
                            <div 
                                className="absolute top-0 bottom-0 bg-gradient-to-r from-red-500 via-[#f82803] to-[#d01e00] rounded-full shadow-sm transition-all duration-75"
                                style={{
                                    left: `${scrollProgress * (1 - thumbWidthRatio) * 100}%`,
                                    width: `${thumbWidthRatio * 100}%`,
                                }}
                            />
                        </div>

                        {/* Quick Scroll Arrow Buttons */}
                        <div className="flex items-center gap-1 shrink-0">
                            <button
                                type="button"
                                disabled={!canScrollLeft}
                                onClick={() => scrollByAmount('left')}
                                className={`p-1 sm:p-1.5 rounded-lg border transition-colors ${
                                    canScrollLeft 
                                        ? 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:text-red-500 cursor-pointer shadow-xs active:scale-95' 
                                        : 'opacity-40 border-gray-200 dark:border-gray-800 text-gray-400 cursor-not-allowed'
                                }`}
                                aria-label="Scroll left"
                            >
                                <ChevronLeft className="w-3.5 h-3.5" />
                            </button>
                            <button
                                type="button"
                                disabled={!canScrollRight}
                                onClick={() => scrollByAmount('right')}
                                className={`p-1 sm:p-1.5 rounded-lg border transition-colors ${
                                    canScrollRight 
                                        ? 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:text-red-500 cursor-pointer shadow-xs active:scale-95' 
                                        : 'opacity-40 border-gray-200 dark:border-gray-800 text-gray-400 cursor-not-allowed'
                                }`}
                                aria-label="Scroll right"
                            >
                                <ChevronRight className="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </section>
    );
}
