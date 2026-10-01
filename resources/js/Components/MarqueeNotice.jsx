import React from 'react';
import { Megaphone, Zap } from 'lucide-react';

export default function MarqueeNotice({ notice, label }) {
    const defaultNotice = "🔥 100% Instant License Key & Panel Delivery · Safe & Anti-Ban Gaming Solutions · 24/7 WhatsApp Customer Support Active";
    const text = notice || defaultNotice;
    const badgeLabel = label || "Notice";

    return (
        <div className="bg-gradient-to-r from-red-950/60 via-slate-900 to-red-950/60 border-b border-red-500/20 py-2 overflow-hidden relative">
            <div className="max-w-7xl mx-auto px-4 flex items-center gap-3">
                <div className="flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-semibold shrink-0 uppercase tracking-wider">
                    <Zap className="w-3.5 h-3.5 text-red-400 animate-pulse" />
                    <span>{badgeLabel}</span>
                </div>
                <div className="overflow-hidden w-full relative whitespace-nowrap">
                    <div className="flex w-max animate-marquee text-xs md:text-sm text-red-100/90 font-medium">
                        <span className="px-8 shrink-0">{text}</span>
                        <span className="px-8 shrink-0">{text}</span>
                        <span className="px-8 shrink-0">{text}</span>
                        <span className="px-8 shrink-0">{text}</span>
                    </div>
                </div>
            </div>
        </div>
    );
}
