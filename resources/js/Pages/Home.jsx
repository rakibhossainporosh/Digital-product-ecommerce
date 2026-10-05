import React, { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import HeroSlider from '../Components/HeroSlider';
import CategoryTabs from '../Components/CategoryTabs';
import ProductCard from '../Components/ProductCard';
import CustomerReviews from '../Components/CustomerReviews';
import { Search, Zap, Shield, Headphones, Smartphone, Sparkles, Filter } from 'lucide-react';

import RecentOrdersTable from '../Components/RecentOrdersTable';

export default function Home({ sliders, categories, products, recentOrders, activeCategory }) {
    const { settings } = usePage().props;
    const [searchQuery, setSearchQuery] = useState('');

    const handleCategorySelect = (categorySlug) => {
        router.get('/', categorySlug ? { category: categorySlug } : {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    // Client-side instant title search
    const filteredProducts = products.filter((p) =>
        p.name.toLowerCase().includes(searchQuery.toLowerCase())
    );

    return (
        <AppLayout title="Home - Buy Game Panel and Key Online">
            {/* Top Banner Carousel */}
            <HeroSlider sliders={sliders} />

            {/* Why Choose Us Feature Ribbon */}
            {settings?.features_ribbon_enabled !== false && (
                <>
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div className="glass-panel p-3.5 rounded-xl border border-gray-200 dark:border-gray-800/80 flex items-center gap-3">
                                <div className="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/30 flex items-center justify-center text-red-400 shrink-0">
                                    <Zap className="w-5 h-5" />
                                </div>
                                <div>
                                    <span className="block text-xs font-bold text-gray-900 dark:text-white font-display">
                                        {settings?.feature_1_title || '1-Sec Key Delivery'}
                                    </span>
                                    <span className="block text-[11px] text-gray-600 dark:text-gray-400">
                                        {settings?.feature_1_subtitle || 'Instant code generate'}
                                    </span>
                                </div>
                            </div>

                            <div className="glass-panel p-3.5 rounded-xl border border-gray-200 dark:border-gray-800/80 flex items-center gap-3">
                                <div className="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shrink-0">
                                    <Shield className="w-5 h-5" />
                                </div>
                                <div>
                                    <span className="block text-xs font-bold text-gray-900 dark:text-white font-display">
                                        {settings?.feature_2_title || '100% Anti-Ban'}
                                    </span>
                                    <span className="block text-[11px] text-gray-600 dark:text-gray-400">
                                        {settings?.feature_2_subtitle || 'Safest bypass systems'}
                                    </span>
                                </div>
                            </div>

                            <div className="glass-panel p-3.5 rounded-xl border border-gray-200 dark:border-gray-800/80 flex items-center gap-3">
                                <div className="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400 shrink-0">
                                    <Smartphone className="w-5 h-5" />
                                </div>
                                <div>
                                    <span className="block text-xs font-bold text-gray-900 dark:text-white font-display">
                                        {settings?.feature_3_title || 'Root & Non-Root'}
                                    </span>
                                    <span className="block text-[11px] text-gray-600 dark:text-gray-400">
                                        {settings?.feature_3_subtitle || 'All Android & iOS devices'}
                                    </span>
                                </div>
                            </div>

                            <div className="glass-panel p-3.5 rounded-xl border border-gray-200 dark:border-gray-800/80 flex items-center gap-3">
                                <div className="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 shrink-0">
                                    <Headphones className="w-5 h-5" />
                                </div>
                                <div>
                                    <span className="block text-xs font-bold text-gray-900 dark:text-white font-display">
                                        {settings?.feature_4_title || '24/7 Engineer Support'}
                                    </span>
                                    <span className="block text-[11px] text-gray-600 dark:text-gray-400">
                                        {settings?.feature_4_subtitle || 'Direct WhatsApp help'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Divider */}
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <hr className="border-gray-200 dark:border-gray-800/60 my-4 sm:my-6" />
                    </div>
                </>
            )}

            {/* Product Section Header & Search */}
            <section id="products" className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-4">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                    <div>
                        <div className="inline-flex items-center gap-1.5 text-xs text-red-400 font-mono uppercase tracking-wider mb-1">
                            <Sparkles className="w-3.5 h-3.5 text-red-400" />
                            <span>Gaming Catalog</span>
                        </div>
                        <h2 className="text-2xl sm:text-3xl font-black font-display text-gray-900 dark:text-white">
                            Available Game Panels & Services
                        </h2>
                    </div>

                    {/* Instant Search Bar */}
                    <div className="relative w-full md:w-72">
                        <input
                            type="text"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            placeholder="Search game panel..."
                            className="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white dark:bg-gray-900/90 border border-gray-300 dark:border-gray-700/80 text-gray-900 dark:text-white placeholder-gray-500 text-sm focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-colors"
                        />
                        <Search className="w-4 h-4 text-gray-600 dark:text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                        {searchQuery && (
                            <button
                                onClick={() => setSearchQuery('')}
                                className="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white"
                            >
                                Clear
                            </button>
                        )}
                    </div>
                </div>

                {/* Horizontal Category Switcher */}
                <CategoryTabs
                    categories={categories}
                    activeCategory={activeCategory}
                    onSelectCategory={handleCategorySelect}
                />
            </section>

            {/* Products Grid */}
            <section className="max-w-7xl mx-auto px-2.5 sm:px-6 lg:px-8 pb-4">
                {filteredProducts.length > 0 ? (
                    <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-6">
                        {filteredProducts.map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>
                ) : (
                    <div className="text-center py-16 glass-card rounded-2xl border border-gray-200 dark:border-gray-800">
                        <div className="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800/80 mx-auto flex items-center justify-center text-gray-600 dark:text-gray-400 mb-3">
                            <Filter className="w-6 h-6" />
                        </div>
                        <h3 className="text-lg font-bold text-gray-900 dark:text-white">No products found</h3>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {searchQuery ? `No panel matches "${searchQuery}"` : 'No products available in this category currently.'}
                        </p>
                    </div>
                )}
            </section>

            {/* Divider */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <hr className="border-gray-200 dark:border-gray-800/60 my-4 sm:my-6" />
            </div>

            {/* Verified Customer Feedback Section */}
            <CustomerReviews />

            {/* Divider */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <hr className="border-gray-200 dark:border-gray-800/60 my-4 sm:my-6" />
            </div>

            {/* Recent Orders Table (Placed at bottom for professional look) */}
            <RecentOrdersTable orders={recentOrders} />
        </AppLayout>
    );
}
