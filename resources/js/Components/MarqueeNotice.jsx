import React from 'react';
import { Megaphone, Zap } from 'lucide-react';

export default function MarqueeNotice({ notice }) {
    const defaultNotice = "🔥 100% Instant License Key & Panel Delivery · Safe & Anti-Ban Gaming Solutions · 24/7 WhatsApp Customer Support Active";
    const text = notice || defaultNotice;

    return (
        <div className="bg-gradient-to-r from-red-950/60 via-slate-900 to-red-950/60 border-b border-red-500/20 py-2 overflow-hidden relative">
            <div className="max-w-7xl mx-auto px-4 flex items-center gap-3">
                <div className="flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-semibold shrink-0 uppercase tracking-wider">
                    <Zap className="w-3.5 h-3.5 text-red-400 animate-pulse" />
                    <span>Notice</span>
                </div>
                <div className="overflow-hidden w-full relative whitespace-nowrap">
                    <div className="inline-block animate-marquee text-xs md:text-sm text-red-100/90 font-medium">
                        {text} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {text}
                    </div>
                </div>
            </div>
        </div>
    );
}
