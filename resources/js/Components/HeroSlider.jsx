import React, { useState, useEffect } from 'react';
import { ChevronLeft, ChevronRight, Sparkles, Shield, Zap } from 'lucide-react';

export default function HeroSlider({ sliders = [] }) {
    const [currentIndex, setCurrentIndex] = useState(0);

    // Fallback promotional banners if no sliders are in the DB yet
    const slides = sliders.length > 0 ? sliders : [
        {
            id: 'default-1',
            title: 'Elite Game Panels & Instant License Keys',
            image_url: null,
            link_url: '#products',
            subtitle: '100% Anti-Ban · Instant Key Delivery · 24/7 WhatsApp Engineer Support',
        },
        {
            id: 'default-2',
            title: 'Auto Bypass Modules & Secure Mods',
            image_url: null,
            link_url: '#products',
            subtitle: 'Experience ultra smooth gameplay with our tested non-root & root panels. Dominate every match securely.',
        },
        {
            id: 'default-3',
            title: 'Reseller Program Now Open',
            image_url: null,
            link_url: '/wallet',
            subtitle: 'Join our elite network of resellers. Get huge discounts on bulk key purchases and manage them easily.',
        }
    ];

    useEffect(() => {
        if (slides.length <= 1) return;
        const timer = setInterval(() => {
            setCurrentIndex((prev) => (prev + 1) % slides.length);
        }, 5000);
        return () => clearInterval(timer);
    }, [slides.length]);

    const prevSlide = () => {
        setCurrentIndex((prev) => (prev - 1 + slides.length) % slides.length);
    };

    const nextSlide = () => {
        setCurrentIndex((prev) => (prev + 1) % slides.length);
    };

    const currentSlide = slides[currentIndex];

    return (
        <div className="relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div className="relative w-full aspect-[21/9] rounded-2xl md:rounded-3xl overflow-hidden border border-red-500/20 shadow-2xl shadow-red-950/40 bg-gradient-to-br from-slate-900 via-[#0d1527] to-[#080d1a] group">
                
                {/* Background Banner Image or Graphic Glow */}
                {currentSlide.image_url ? (
                    <>
                        {/* Blurred background layer to fill empty spaces beautifully */}
                        <div 
                            className="absolute inset-0 bg-cover bg-center blur-2xl opacity-40 scale-110"
                            style={{ backgroundImage: `url(${currentSlide.image_url})` }}
                        />
                        {/* Foreground full image without cropping */}
                        <img 
                            src={currentSlide.image_url} 
                            alt={currentSlide.title}
                            className="relative z-10 w-full h-full object-fill transition-transform duration-700 group-hover:scale-105 drop-shadow-2xl" 
                        />
                    </>
                ) : (
                    <div className="w-full h-full relative flex items-center justify-between px-6 sm:px-12 lg:px-16 overflow-hidden">
                        {/* Futuristic Grid pattern overlay */}
                        <div className="absolute inset-0 bg-[linear-gradient(to_right,#1f293d_1px,transparent_1px),linear-gradient(to_bottom,#1f293d_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,#000_70%,transparent_100%)] opacity-20" />
                        
                        <div className="max-w-xl z-10 space-y-3">
                            <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-semibold">
                                <Sparkles className="w-3.5 h-3.5 text-red-400 animate-spin" style={{ animationDuration: '4s' }} />
                                <span>#1 Gaming Panel Provider in Bangladesh</span>
                            </div>

                            <h1 className="text-2xl sm:text-4xl lg:text-5xl font-black font-display text-white leading-tight tracking-tight">
                                {currentSlide.title}
                            </h1>

                            <p className="text-xs sm:text-sm text-gray-300 line-clamp-2 max-w-md">
                                {currentSlide.subtitle || 'Experience ultra smooth gameplay with our tested non-root & root panels, auto-bypass protection and instant code delivery.'}
                            </p>

                            <div className="pt-2 flex items-center gap-3">
                                <a 
                                    href={currentSlide.link_url || '#products'}
                                    className="px-5 py-2.5 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] hover:from-[#ff411a] hover:to-[#8f0909] text-white font-bold text-xs sm:text-sm shadow-lg shadow-red-500/25 transition-all hover:scale-105 flex items-center gap-2"
                                >
                                    <Zap className="w-4 h-4 fill-white" />
                                    <span>Explore Panels</span>
                                </a>

                                <div className="hidden sm:flex items-center gap-2 text-xs text-emerald-400 font-medium bg-emerald-950/40 px-3 py-2 rounded-xl border border-emerald-500/30">
                                    <Shield className="w-4 h-4" />
                                    <span>Safe & Anti-Ban</span>
                                </div>
                            </div>
                        </div>

                        {/* Cyber Emblem Graphic */}
                        <div className="hidden md:block relative z-10">
                            <div className="w-48 h-48 lg:w-56 lg:h-56 rounded-3xl bg-gradient-to-br from-[#f82803]/20 via-rose-500/10 to-transparent border border-red-500/30 backdrop-blur-md flex items-center justify-center p-6 shadow-2xl">
                                <div className="text-center space-y-2">
                                    <div className="w-16 h-16 mx-auto rounded-2xl bg-red-500 flex items-center justify-center text-white font-black text-2xl shadow-lg shadow-red-500/50">
                                        ⚡
                                    </div>
                                    <div className="font-display font-bold text-white text-base">INSTANT KEY</div>
                                    <div className="text-[11px] text-red-300 font-mono">Auto 1-Click Delivery</div>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Dark Gradient Overlay */}
                <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent pointer-events-none" />

                {/* Left / Right Controls */}
                {slides.length > 1 && (
                    <>
                        <button 
                            onClick={prevSlide}
                            className="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/50 hover:bg-red-500 text-white hover:text-black border border-white/10 flex items-center justify-center backdrop-blur-sm transition-all opacity-0 group-hover:opacity-100"
                        >
                            <ChevronLeft className="w-5 h-5" />
                        </button>
                        <button 
                            onClick={nextSlide}
                            className="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/50 hover:bg-red-500 text-white hover:text-black border border-white/10 flex items-center justify-center backdrop-blur-sm transition-all opacity-0 group-hover:opacity-100"
                        >
                            <ChevronRight className="w-5 h-5" />
                        </button>

                        {/* Indicators */}
                        <div className="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5 z-20">
                            {slides.map((_, idx) => (
                                <button
                                    key={idx}
                                    onClick={() => setCurrentIndex(idx)}
                                    className={`h-1.5 rounded-full transition-all ${idx === currentIndex ? 'w-6 bg-red-400' : 'w-2 bg-gray-600'}`}
                                />
                            ))}
                        </div>
                    </>
                )}
            </div>
        </div>
    );
}
