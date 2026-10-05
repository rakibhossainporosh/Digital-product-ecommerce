import React from 'react';
import { Link } from '@inertiajs/react';
import { ShoppingCart, Play, Zap, ShieldCheck, Wrench, Smartphone } from 'lucide-react';

export default function ProductCard({ product }) {
    const isService = product.is_service || product.type === 'service';

    return (
        <div className="glass-card rounded-xl sm:rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-800/90 hover:border-red-500/40 transition-all duration-300 flex flex-col group relative">
            {/* Top Image & Badges Container */}
            <Link 
                href={`/product/${product.slug}`}
                className="relative aspect-[16/10] w-full overflow-hidden bg-gradient-to-br from-gray-100 via-gray-50 to-gray-200 dark:from-slate-900 dark:via-gray-900 dark:to-[#0d1527] flex items-center justify-center p-2 sm:p-3 block"
            >
                {product.image_url ? (
                    <>
                        {/* Blurred background layer */}
                        <div 
                            className="absolute inset-0 bg-cover bg-center blur-xl opacity-40 scale-110"
                            style={{ backgroundImage: `url(${product.image_url})` }}
                        />
                        {/* Main image */}
                        <img 
                            src={product.image_url} 
                            alt={product.name}
                            className="relative z-10 w-full h-full object-contain rounded-lg sm:rounded-xl transition-transform duration-500 group-hover:scale-105 drop-shadow-xl"
                        />
                    </>
                ) : (
                    <div className="w-full h-full rounded-lg sm:rounded-xl bg-gradient-to-tr from-red-950/40 via-gray-900 to-black/40 border border-red-500/20 flex flex-col items-center justify-center text-center p-2 sm:p-4">
                        <div className="w-8 h-8 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-red-100 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 flex items-center justify-center text-red-500 dark:text-red-400 mb-1 sm:mb-2 group-hover:scale-110 transition-transform">
                            {isService ? <Wrench className="w-4 h-4 sm:w-6 sm:h-6" /> : <Smartphone className="w-4 h-4 sm:w-6 sm:h-6" />}
                        </div>
                        <span className="text-[9px] sm:text-xs font-mono font-bold text-gray-500 dark:text-gray-400 tracking-wider uppercase">
                            {isService ? 'Service' : 'Instant Panel'}
                        </span>
                    </div>
                )}

                {/* Status Badges */}
                <div className="absolute top-2 left-2 sm:top-3 sm:left-3 flex flex-wrap gap-1 sm:gap-1.5 z-20">
                    {product.category && (
                        <span className="px-1.5 sm:px-2.5 py-0.5 rounded-full text-[9px] sm:text-[11px] font-semibold bg-white/80 dark:bg-black/80 backdrop-blur-md text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 shadow-sm truncate max-w-[80px] sm:max-w-none">
                            {product.category.name}
                        </span>
                    )}

                    {product.is_maintenance ? (
                        <span className="px-1.5 sm:px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-semibold bg-amber-100/90 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-500/30 backdrop-blur-md flex items-center gap-1 shadow-sm">
                            <Wrench className="w-2.5 h-2.5 sm:w-3 sm:h-3 text-amber-600 dark:text-amber-400" /> Maintenance
                        </span>
                    ) : isService ? (
                        <span className="px-1.5 sm:px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-semibold bg-amber-100/80 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-500/30 backdrop-blur-md shadow-sm">
                            Custom Service
                        </span>
                    ) : (
                        <span className="px-1.5 sm:px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-semibold bg-emerald-100/80 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-500/30 backdrop-blur-md flex items-center gap-0.5 sm:gap-1 shadow-sm">
                            <Zap className="w-2.5 h-2.5 sm:w-3 sm:h-3 fill-emerald-600 dark:fill-emerald-300" /> Instant Key
                        </span>
                    )}
                </div>
            </Link>

            {/* Content Section */}
            <div className="p-2.5 sm:p-5 flex-1 flex flex-col justify-between space-y-2 sm:space-y-3">
                <div className="space-y-1.5 sm:space-y-2">
                    <Link href={`/product/${product.slug}`} className="block group-hover:text-red-400 transition-colors">
                        <h3 className="font-display font-bold text-gray-900 dark:text-white text-xs sm:text-base md:text-lg line-clamp-2 leading-tight">
                            {product.name}
                        </h3>
                    </Link>

                    {/* Features list pills */}
                    {product.features && product.features.length > 0 ? (
                        <div className="flex flex-wrap gap-1 sm:gap-1.5 pt-0.5 sm:pt-1">
                            {product.features.slice(0, 3).map((feat, idx) => (
                                <span 
                                    key={idx}
                                    className={`text-[9px] sm:text-[10px] px-1.5 sm:px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700/50 truncate max-w-full ${idx >= 2 ? 'hidden sm:inline-flex' : 'inline-flex'}`}
                                >
                                    ✓ {feat}
                                </span>
                            ))}
                        </div>
                    ) : (
                        <p className="text-[10px] sm:text-xs text-gray-400 line-clamp-2 hidden sm:block">
                            {isService ? 'Professional service provided directly by engineers.' : 'Safe, anti-ban panel with instant digital license key code.'}
                        </p>
                    )}
                </div>

                {/* Price and Actions Bottom Bar */}
                <div className="pt-2 sm:pt-3 border-t border-gray-200 dark:border-gray-800/80 flex items-center justify-between gap-1 sm:gap-2">
                    <div className="shrink-0 min-w-0">
                        <span className="block text-[8px] sm:text-[10px] text-gray-400 font-display font-semibold uppercase truncate">
                            {product.variants_count > 1 ? 'From' : 'Price'}
                        </span>
                        <div className="text-xs sm:text-base font-black font-display text-red-500 whitespace-nowrap">
                            {product.formatted_min_price || product.price_range || '৳ 0'}
                        </div>
                    </div>

                    <div className="flex items-center gap-1 sm:gap-1.5 shrink-0">
                        {product.demo_video_url && (
                            <a
                                href={product.demo_video_url}
                                target="_blank"
                                rel="noreferrer"
                                title="Watch Setup Video"
                                className="w-7 h-7 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:text-white border border-gray-300 dark:border-gray-700 flex items-center justify-center transition-colors"
                            >
                                <Play className="w-3 h-3 sm:w-4 sm:h-4 fill-current ml-0.5" />
                            </a>
                        )}

                        {product.is_maintenance ? (
                            <button
                                disabled
                                className="px-2 sm:px-4 py-1.5 sm:py-2 rounded-lg sm:rounded-xl bg-amber-100 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-500/30 font-bold text-[10px] sm:text-sm flex items-center gap-1 sm:gap-1.5 cursor-not-allowed whitespace-nowrap shadow-xs"
                            >
                                <Wrench className="w-3 h-3 sm:w-3.5 sm:h-3.5 shrink-0 text-amber-600 dark:text-amber-400" />
                                <span className="whitespace-nowrap">Updating</span>
                            </button>
                        ) : (
                            <Link
                                href={`/product/${product.slug}`}
                                className="px-2 sm:px-4 py-1.5 sm:py-2 rounded-lg sm:rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] hover:from-[#ff411a] hover:to-[#8f0909] text-white font-bold text-[10px] sm:text-sm flex items-center gap-1 sm:gap-1.5 shadow-md shadow-red-500/20 transition-all hover:scale-105 active:scale-95 whitespace-nowrap"
                            >
                                <ShoppingCart className="w-3 h-3 sm:w-3.5 sm:h-3.5 shrink-0" />
                                <span className="whitespace-nowrap">Buy Now</span>
                            </Link>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
