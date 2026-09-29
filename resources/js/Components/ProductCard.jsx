import React from 'react';
import { Link } from '@inertiajs/react';
import { ShoppingCart, Play, Zap, ShieldCheck, Wrench, Smartphone } from 'lucide-react';

export default function ProductCard({ product }) {
    const isService = product.is_service || product.type === 'service';

    return (
        <div className="glass-card rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-800/90 hover:border-red-500/40 transition-all duration-300 flex flex-col group relative">
            {/* Top Image & Badges Container */}
            <div className="relative aspect-[16/10] w-full overflow-hidden bg-gradient-to-br from-slate-900 via-gray-900 to-[#0d1527] flex items-center justify-center p-3">
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
                            className="relative z-10 w-full h-full object-contain rounded-xl transition-transform duration-500 group-hover:scale-105 drop-shadow-xl"
                        />
                    </>
                ) : (
                    <div className="w-full h-full rounded-xl bg-gradient-to-tr from-red-950/40 via-gray-900 to-black/40 border border-red-500/20 flex flex-col items-center justify-center text-center p-4">
                        <div className="w-12 h-12 rounded-2xl bg-red-500/10 border border-red-500/30 flex items-center justify-center text-red-400 mb-2 group-hover:scale-110 transition-transform">
                            {isService ? <Wrench className="w-6 h-6" /> : <Smartphone className="w-6 h-6" />}
                        </div>
                        <span className="text-xs font-mono font-bold text-gray-400 tracking-wider uppercase">
                            {isService ? 'Manual Service' : 'Instant Panel'}
                        </span>
                    </div>
                )}

                {/* Status Badges */}
                <div className="absolute top-3 left-3 flex flex-wrap gap-1.5 z-20">
                    {product.category && (
                        <span className="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/70 backdrop-blur-md text-red-400 border border-red-500/30">
                            {product.category.name}
                        </span>
                    )}

                    {isService ? (
                        <span className="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30 backdrop-blur-md">
                            Custom Service
                        </span>
                    ) : (
                        <span className="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 backdrop-blur-md flex items-center gap-1">
                            <Zap className="w-3 h-3 fill-emerald-300" /> Instant Key
                        </span>
                    )}
                </div>
            </div>

            {/* Content Section */}
            <div className="p-4 sm:p-5 flex-1 flex flex-col justify-between space-y-3">
                <div className="space-y-2">
                    <Link href={`/product/${product.slug}`} className="block group-hover:text-red-400 transition-colors">
                        <h3 className="font-display font-bold text-gray-900 dark:text-white text-base sm:text-lg line-clamp-1">
                            {product.name}
                        </h3>
                    </Link>

                    {/* Features list pills */}
                    {product.features && product.features.length > 0 ? (
                        <div className="flex flex-wrap gap-1.5 pt-1">
                            {product.features.slice(0, 3).map((feat, idx) => (
                                <span 
                                    key={idx}
                                    className="text-[10px] px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700/50"
                                >
                                    ✓ {feat}
                                </span>
                            ))}
                        </div>
                    ) : (
                        <p className="text-xs text-gray-400 line-clamp-2">
                            {isService ? 'Professional service provided directly by engineers.' : 'Safe, anti-ban panel with instant digital license key code.'}
                        </p>
                    )}
                </div>

                {/* Price and Actions Bottom Bar */}
                <div className="pt-3 border-t border-gray-200 dark:border-gray-800/80 flex items-center justify-between gap-1 sm:gap-2">
                    <div className="shrink-0">
                        <span className="block text-[9px] sm:text-[10px] text-gray-400 font-display font-semibold uppercase whitespace-nowrap">
                            {product.variants_count > 1 ? 'Starting from' : 'Price'}
                        </span>
                        <div className="text-sm sm:text-base font-black font-display text-red-500 whitespace-nowrap">
                            {product.formatted_min_price || product.price_range || '৳ 0'}
                        </div>
                    </div>

                    <div className="flex items-center gap-1.5">
                        {product.demo_video_url && (
                            <a
                                href={product.demo_video_url}
                                target="_blank"
                                rel="noreferrer"
                                title="Watch Setup Video"
                                className="w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:text-white border border-gray-300 dark:border-gray-700 flex items-center justify-center transition-colors"
                            >
                                <Play className="w-4 h-4 fill-current ml-0.5" />
                            </a>
                        )}

                        <Link
                            href={`/product/${product.slug}`}
                            className="px-3 sm:px-4 py-2 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] hover:from-[#ff411a] hover:to-[#8f0909] text-white font-bold text-xs sm:text-sm flex items-center gap-1.5 shadow-md shadow-red-500/20 transition-all hover:scale-105 active:scale-95 whitespace-nowrap"
                        >
                            <ShoppingCart className="w-3.5 h-3.5 shrink-0" />
                            <span className="whitespace-nowrap">Buy Now</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}
