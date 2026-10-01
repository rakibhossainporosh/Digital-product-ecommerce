import React from 'react';
import { Layers } from 'lucide-react';

export default function CategoryTabs({ categories = [], activeCategory, onSelectCategory }) {
    return (
        <div className="w-full py-2">
            <div className="flex items-center gap-2.5 overflow-x-auto p-1.5 scrollbar-none no-scrollbar">
                {/* All Products Tab */}
                <button
                    type="button"
                    onClick={() => onSelectCategory(null)}
                    className={`flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold shrink-0 transition-all cursor-pointer ${
                        !activeCategory 
                            ? 'bg-gradient-to-r from-[#f82803] via-red-600 to-[#730505] text-white shadow-lg shadow-red-500/30 ring-2 ring-red-500/50 ring-offset-1 ring-offset-white dark:ring-offset-[#0b0f19]' 
                            : 'bg-gray-100 dark:bg-gray-800/80 hover:bg-gray-200 dark:hover:bg-gray-700/80 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white border border-gray-200 dark:border-gray-700/60'
                    }`}
                >
                    <Layers className="w-4 h-4 shrink-0" />
                    <span>All Products</span>
                </button>

                {/* Dynamic Categories from DB */}
                {categories.map((cat) => {
                    const isSelected = activeCategory === cat.slug;
                    return (
                        <button
                            key={cat.id}
                            type="button"
                            onClick={() => onSelectCategory(cat.slug)}
                            className={`flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold shrink-0 transition-all cursor-pointer ${
                                isSelected 
                                    ? 'bg-gradient-to-r from-[#f82803] via-red-600 to-[#730505] text-white shadow-lg shadow-red-500/30 ring-2 ring-red-500/50 ring-offset-1 ring-offset-white dark:ring-offset-[#0b0f19]' 
                                    : 'bg-gray-100 dark:bg-gray-800/80 hover:bg-gray-200 dark:hover:bg-gray-700/80 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white border border-gray-200 dark:border-gray-700/60'
                            }`}
                        >
                            <span>{cat.name}</span>
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
            </div>
        </div>
    );
}
