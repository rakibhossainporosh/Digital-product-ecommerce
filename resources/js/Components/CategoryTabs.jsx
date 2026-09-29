import React from 'react';
import { Layers, Sparkles } from 'lucide-react';

export default function CategoryTabs({ categories = [], activeCategory, onSelectCategory }) {
    return (
        <div className="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div className="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none no-scrollbar">
                {/* All Products Tab */}
                <button
                    onClick={() => onSelectCategory(null)}
                    className={`flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold shrink-0 transition-all ${
                        !activeCategory 
                            ? 'bg-gradient-to-br from-[#f82803] to-[#730505] text-white shadow-lg shadow-red-500/25 scale-105' 
                            : 'bg-gray-200 dark:bg-gray-800/80 hover:bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300 hover:text-gray-900 dark:text-white border border-gray-300 dark:border-gray-700/60'
                    }`}
                >
                    <Layers className="w-4 h-4" />
                    <span>All Products</span>
                </button>

                {/* Dynamic Categories from DB */}
                {categories.map((cat) => {
                    const isSelected = activeCategory === cat.slug;
                    return (
                        <button
                            key={cat.id}
                            onClick={() => onSelectCategory(cat.slug)}
                            className={`flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold shrink-0 transition-all ${
                                isSelected 
                                    ? 'bg-gradient-to-br from-[#f82803] to-[#730505] text-white shadow-lg shadow-red-500/25 scale-105' 
                                    : 'bg-gray-200 dark:bg-gray-800/80 hover:bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300 hover:text-gray-900 dark:text-white border border-gray-300 dark:border-gray-700/60'
                            }`}
                        >
                            <span>{cat.name}</span>
                            {cat.products_count !== undefined && (
                                <span className={`text-[10px] px-1.5 py-0.5 rounded-full font-bold ${isSelected ? 'bg-white/20 text-white' : 'bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200'}`}>
                                    {cat.products_count}
                                </span>
                            )}
                        </button>
                    );
                })}
            </div>
        </div>
    );
}
