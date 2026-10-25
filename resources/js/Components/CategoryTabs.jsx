import React, { useRef, useState, useEffect } from 'react';
import { Layers, ChevronLeft, ChevronRight } from 'lucide-react';

export default function CategoryTabs({ categories = [], activeCategory, onSelectCategory }) {
    const scrollRef = useRef(null);
    const [canScrollLeft, setCanScrollLeft] = useState(false);
    const [canScrollRight, setCanScrollRight] = useState(false);
    const isDraggingRef = useRef(false);
    const startXRef = useRef(0);
    const scrollLeftRef = useRef(0);
    const hasMovedRef = useRef(false);

    const checkScroll = () => {
        if (!scrollRef.current) return;
        const { scrollLeft, scrollWidth, clientWidth } = scrollRef.current;
        setCanScrollLeft(scrollLeft > 5);
        setCanScrollRight(scrollLeft + clientWidth < scrollWidth - 5);
    };

    useEffect(() => {
        checkScroll();
        const handleResize = () => checkScroll();
        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, [categories]);

    // Auto-scroll active category into view
    useEffect(() => {
        if (scrollRef.current) {
            const activeEl = scrollRef.current.querySelector('[data-active="true"]');
            if (activeEl) {
                activeEl.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        }
        checkScroll();
    }, [activeCategory]);

    const scroll = (direction) => {
        if (!scrollRef.current) return;
        const amount = 200;
        scrollRef.current.scrollBy({
            left: direction === 'left' ? -amount : amount,
            behavior: 'smooth',
        });
        setTimeout(checkScroll, 250);
    };

    // Mouse Drag to Scroll handlers
    const handleMouseDown = (e) => {
        if (!scrollRef.current) return;
        isDraggingRef.current = true;
        hasMovedRef.current = false;
        startXRef.current = e.pageX - scrollRef.current.offsetLeft;
        scrollLeftRef.current = scrollRef.current.scrollLeft;
    };

    const handleMouseMove = (e) => {
        if (!isDraggingRef.current || !scrollRef.current) return;
        const x = e.pageX - scrollRef.current.offsetLeft;
        const walk = x - startXRef.current;
        if (Math.abs(walk) > 4) {
            hasMovedRef.current = true;
        }
        scrollRef.current.scrollLeft = scrollLeftRef.current - walk;
        checkScroll();
    };

    const handleMouseUpOrLeave = () => {
        isDraggingRef.current = false;
        setTimeout(() => {
            hasMovedRef.current = false;
        }, 50);
    };

    return (
        <div className="relative w-full py-2 group">
            {/* Desktop Left Arrow Button */}
            {canScrollLeft && (
                <button
                    type="button"
                    onClick={() => scroll('left')}
                    aria-label="Scroll left"
                    className="hidden sm:flex absolute left-0 top-1/2 -translate-y-1/2 z-20 w-7 h-7 rounded-full bg-white/95 dark:bg-gray-800/95 border border-gray-200 dark:border-gray-700 shadow-md items-center justify-center text-gray-700 dark:text-gray-200 hover:text-red-500 dark:hover:text-red-400 transition-colors cursor-pointer"
                >
                    <ChevronLeft className="w-4 h-4" />
                </button>
            )}

            {/* Desktop Right Arrow Button */}
            {canScrollRight && (
                <button
                    type="button"
                    onClick={() => scroll('right')}
                    aria-label="Scroll right"
                    className="hidden sm:flex absolute right-0 top-1/2 -translate-y-1/2 z-20 w-7 h-7 rounded-full bg-white/95 dark:bg-gray-800/95 border border-gray-200 dark:border-gray-700 shadow-md items-center justify-center text-gray-700 dark:text-gray-200 hover:text-red-500 dark:hover:text-red-400 transition-colors cursor-pointer"
                >
                    <ChevronRight className="w-4 h-4" />
                </button>
            )}

            {/* Horizontal Scrollable Track */}
            <div
                ref={scrollRef}
                onScroll={checkScroll}
                onMouseDown={handleMouseDown}
                onMouseMove={handleMouseMove}
                onMouseUp={handleMouseUpOrLeave}
                onMouseLeave={handleMouseUpOrLeave}
                className="flex items-center gap-2.5 overflow-x-auto pb-1.5 pt-1 -mx-4 px-4 sm:mx-0 sm:px-0 touch-pan-x overscroll-x-contain select-none cursor-grab active:cursor-grabbing scroll-smooth [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
                style={{ WebkitOverflowScrolling: 'touch' }}
            >
                {/* All Products Tab */}
                <button
                    type="button"
                    data-active={!activeCategory ? 'true' : 'false'}
                    onClick={(e) => {
                        if (hasMovedRef.current) {
                            e.preventDefault();
                            return;
                        }
                        onSelectCategory(null);
                    }}
                    className={`flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold shrink-0 transition-all cursor-pointer ${
                        !activeCategory 
                            ? 'bg-gradient-to-r from-[#f82803] via-red-600 to-[#730505] text-white shadow-lg shadow-red-500/30 ring-2 ring-red-500/50 ring-offset-1 ring-offset-white dark:ring-offset-[#0b0f19]' 
                            : 'bg-gray-100 dark:bg-gray-800/80 hover:bg-gray-200 dark:hover:bg-gray-700/80 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white border border-gray-200 dark:border-gray-700/60'
                    }`}
                >
                    <Layers className="w-4 h-4 shrink-0" />
                    <span className="whitespace-nowrap">All Products</span>
                </button>

                {/* Dynamic Categories */}
                {categories.map((cat) => {
                    const isSelected = activeCategory === cat.slug;
                    return (
                        <button
                            key={cat.id}
                            type="button"
                            data-active={isSelected ? 'true' : 'false'}
                            onClick={(e) => {
                                if (hasMovedRef.current) {
                                    e.preventDefault();
                                    return;
                                }
                                onSelectCategory(cat.slug);
                            }}
                            className={`flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold shrink-0 transition-all cursor-pointer ${
                                isSelected 
                                    ? 'bg-gradient-to-r from-[#f82803] via-red-600 to-[#730505] text-white shadow-lg shadow-red-500/30 ring-2 ring-red-500/50 ring-offset-1 ring-offset-white dark:ring-offset-[#0b0f19]' 
                                    : 'bg-gray-100 dark:bg-gray-800/80 hover:bg-gray-200 dark:hover:bg-gray-700/80 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white border border-gray-200 dark:border-gray-700/60'
                            }`}
                        >
                            <span className="whitespace-nowrap">{cat.name}</span>
                            {cat.products_count !== undefined && (
                                <span className={`text-[10px] px-2 py-0.5 rounded-full font-bold transition-colors ${
                                    isSelected 
                                        ? 'bg-white/25 text-white' 
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                                }`}>
                                    {cat.products_count}
                                </span>
                            )}
                        </button>
                    );
                })}

                {/* Trailing Spacer for clean right margin when scrolled */}
                <div className="w-4 shrink-0 pointer-events-none" />
            </div>
        </div>
    );
}
