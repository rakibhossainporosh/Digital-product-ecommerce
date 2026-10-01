import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { ShoppingBag, ShieldCheck, Zap, Headphones, MessageCircle, Heart } from 'lucide-react';

export default function Footer() {
    const { settings } = usePage().props;

    return (
        <footer className="bg-gray-50 dark:bg-black border-t border-gray-200 dark:border-gray-800/80 text-gray-600 dark:text-gray-400 pt-8 pb-24 md:pb-12">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                    {/* Brand Column */}
                    <div className="md:col-span-2 space-y-4">
                        <div className="flex items-center gap-2.5">
                            {settings?.site_logo ? (
                                <div className="w-9 h-9 rounded-xl overflow-hidden border border-red-500/20 bg-white dark:bg-[#0b0f19] flex items-center justify-center p-1 shrink-0">
                                    <img 
                                        src={settings.site_logo} 
                                        alt={settings?.app_name || 'Logo'} 
                                        className="w-full h-full object-contain"
                                    />
                                </div>
                            ) : (
                                <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] p-0.5 shrink-0">
                                    <div className="w-full h-full bg-gray-50 dark:bg-black rounded-[10px] flex items-center justify-center">
                                        <ShoppingBag className="w-4 h-4 text-[#f82803]" />
                                    </div>
                                </div>
                            )}
                            <span className="text-xl font-bold font-display text-gray-900 dark:text-white">
                                {settings?.app_name || 'Panel Sell'}
                            </span>
                        </div>
                        <p className="text-sm text-gray-500 dark:text-gray-400 max-w-sm leading-relaxed">
                            Discover the ultimate destination for premium game panels, safe non-root & root APK mods, and instant digital license key deliveries in Bangladesh.
                        </p>
                        <div className="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                            <span className="flex items-center gap-1.5 text-[#f82803]">
                                <Zap className="w-4 h-4" /> 1-Second Key Delivery
                            </span>
                            <span className="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                                <ShieldCheck className="w-4 h-4" /> 100% Anti-Ban
                            </span>
                        </div>
                    </div>

                    {/* Quick Links */}
                    <div className="space-y-3">
                        <h4 className="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider font-mono">
                            Quick Links
                        </h4>
                        <ul className="space-y-2 text-sm">
                            <li><Link href="/" className="hover:text-[#f82803] transition-colors">Home Storefront</Link></li>
                            <li><a href="#products" className="hover:text-[#f82803] transition-colors">All Game Panels</a></li>
                            <li><Link href="/wallet" className="hover:text-[#f82803] transition-colors">Wallet & Deposit</Link></li>
                            <li><Link href="/keys" className="hover:text-[#f82803] transition-colors">My License Keys</Link></li>
                        </ul>
                    </div>

                    {/* Payment & Support */}
                    <div className="space-y-3">
                        <h4 className="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider font-mono">
                            24/7 Support
                        </h4>
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            Need help with key setup or rooting? Chat directly with our verified engineers.
                        </p>
                        {settings?.support_whatsapp && (
                            <a
                                href={`https://wa.me/${settings.support_whatsapp.replace(/[^0-9]/g, '')}`}
                                target="_blank"
                                rel="noreferrer"
                                className="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-500/10 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 text-xs font-semibold hover:bg-emerald-500/20 dark:hover:bg-emerald-600/30 transition-colors"
                            >
                                <MessageCircle className="w-4 h-4" />
                                <span>WhatsApp: {settings.support_whatsapp}</span>
                            </a>
                        )}

                        <div className="pt-2">
                            <span className="block text-[11px] text-gray-500 uppercase tracking-wider mb-2 font-mono">Accepted Payments</span>
                            <div className="flex flex-wrap gap-2 text-xs font-semibold">
                                <span className="px-2 py-1 rounded bg-[#E2136E]/10 dark:bg-[#E2136E]/20 text-[#E2136E] border border-[#E2136E]/30">bKash</span>
                                <span className="px-2 py-1 rounded bg-[#F7941D]/10 dark:bg-[#F7941D]/20 text-[#F7941D] border border-[#F7941D]/30">Nagad</span>
                                <span className="px-2 py-1 rounded bg-[#8C3494]/10 dark:bg-[#8C3494]/20 text-[#8C3494] dark:text-[#b348bd] border border-[#8C3494]/30">Rocket</span>
                                <span className="px-2 py-1 rounded bg-[#f82803]/10 dark:bg-[#f82803]/20 text-[#f82803] border border-[#f82803]/30">Wallet Pay</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="border-t border-gray-200 dark:border-gray-800/80 pt-4 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-gray-500">
                    <p>© {new Date().getFullYear()} {settings?.app_name || 'Panel Sell'}. All rights reserved.</p>
                    <p className="flex items-center gap-1">
                        Crafted for Elite Gamers & Resellers <Heart className="w-3.5 h-3.5 text-[#f82803] inline fill-[#f82803]" />
                    </p>
                </div>
            </div>
        </footer>
    );
}
