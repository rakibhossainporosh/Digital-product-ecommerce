import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import {
    Key,
    Copy,
    Check,
    Search,
    ArrowLeft,
    ExternalLink,
    ShieldCheck,
    Hash,
    Calendar,
    Package,
    Sparkles,
    Zap,
    PlusCircle,
    PlayCircle,
    CheckCircle2,
    X,
    Info,
} from 'lucide-react';

export default function MyKeys({ keys }) {
    const [copiedId, setCopiedId] = useState(null);
    const [searchQuery, setSearchQuery] = useState('');

    const handleCopy = (keyStr, id) => {
        navigator.clipboard.writeText(keyStr);
        setCopiedId(id);
        setTimeout(() => setCopiedId(null), 2000);
    };

    const filteredKeys = searchQuery
        ? keys.data.filter(
              (k) =>
                  k.key?.toLowerCase().includes(searchQuery.toLowerCase()) ||
                  k.product_name?.toLowerCase().includes(searchQuery.toLowerCase()) ||
                  k.order_number?.toLowerCase().includes(searchQuery.toLowerCase()) ||
                  k.duration_name?.toLowerCase().includes(searchQuery.toLowerCase())
          )
        : keys.data;

    return (
        <AppLayout title="My License Keys">
            <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 pb-24 sm:pb-12">
                
                {/* Top Navigation & Header */}
                <div className="space-y-4">
                    <div className="flex items-center justify-between">
                        <Link
                            href="/dashboard"
                            className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/80 dark:bg-gray-900/80 border border-gray-200 dark:border-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:text-[#f82803] dark:hover:text-[#f82803] hover:border-red-500/30 transition-all shadow-sm group"
                        >
                            <ArrowLeft className="w-3.5 h-3.5 group-hover:-translate-x-0.5 transition-transform" />
                            <span>Dashboard</span>
                        </Link>

                        <div className="flex items-center gap-2">
                            <span className="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />
                                {keys.total || 0} {keys.total === 1 ? 'Key' : 'Keys'} Total
                            </span>
                        </div>
                    </div>

                    {/* Page Hero Card */}
                    <div className="relative overflow-hidden rounded-3xl p-5 sm:p-7 glass-panel border border-emerald-500/20 dark:border-emerald-500/20 bg-gradient-to-br from-emerald-500/5 via-transparent to-teal-500/5">
                        <div className="absolute -right-10 -top-10 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none" />
                        <div className="absolute -left-10 -bottom-10 w-40 h-40 bg-red-500/10 rounded-full blur-3xl pointer-events-none" />

                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
                            <div className="flex items-start sm:items-center gap-3.5">
                                <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shrink-0 shadow-lg shadow-emerald-500/25 ring-4 ring-emerald-500/10">
                                    <Key className="w-6 h-6" />
                                </div>
                                <div>
                                    <div className="flex items-center gap-2 mb-1">
                                        <h1 className="text-xl sm:text-2xl font-black font-display text-gray-900 dark:text-white">
                                            My License Keys
                                        </h1>
                                        <span className="hidden sm:inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                                            Instant Vault
                                        </span>
                                    </div>
                                    <p className="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                        Your purchased digital panel keys with one-tap instant copy & auto-activation.
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-center gap-2.5 self-stretch sm:self-auto">
                                <Link
                                    href="/#products"
                                    className="flex-1 sm:flex-none justify-center px-4 py-2.5 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] hover:from-[#ff411a] hover:to-[#8f0909] text-white font-bold text-xs shadow-lg shadow-red-500/20 flex items-center gap-1.5 transition-all hover:scale-105 active:scale-95"
                                >
                                    <Sparkles className="w-3.5 h-3.5" />
                                    <span>Buy New Key</span>
                                </Link>
                                <Link
                                    href="/wallet"
                                    className="flex-1 sm:flex-none justify-center px-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-800 dark:text-gray-200 font-bold text-xs border border-gray-200 dark:border-gray-800 transition-all flex items-center gap-1.5"
                                >
                                    <PlusCircle className="w-3.5 h-3.5 text-red-500" />
                                    <span>Wallet</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Search Bar & Quick Filter */}
                <div className="relative w-full">
                    <div className="relative flex items-center">
                        <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <input
                            type="text"
                            placeholder="Search by key, product name, duration, order ID..."
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            className="w-full pl-10 pr-10 py-3 rounded-2xl bg-white dark:bg-[#0f172a]/90 border border-gray-200 dark:border-gray-800 text-gray-900 dark:text-white text-xs sm:text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500/50 shadow-sm transition-all"
                        />
                        {searchQuery && (
                            <button
                                onClick={() => setSearchQuery('')}
                                className="absolute right-3.5 top-1/2 -translate-y-1/2 p-1 rounded-full text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                            >
                                <X className="w-3.5 h-3.5" />
                            </button>
                        )}
                    </div>
                </div>

                {/* Keys Grid / Content */}
                {filteredKeys.length > 0 ? (
                    <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        {filteredKeys.map((k) => (
                            <div
                                key={k.id}
                                className="relative overflow-hidden rounded-2xl glass-panel border border-gray-200/80 dark:border-gray-800/80 p-4 sm:p-5 space-y-4 hover:border-emerald-500/40 transition-all group shadow-sm hover:shadow-lg hover:shadow-emerald-500/5"
                            >
                                {/* Top Product Badge & Title */}
                                <div className="flex items-start justify-between gap-3">
                                    <div className="space-y-1.5 min-w-0">
                                        <div className="flex items-center gap-2">
                                            <span className="w-2 h-2 rounded-full bg-emerald-500 animate-ping" />
                                            <span className="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                                Active & Valid
                                            </span>
                                        </div>
                                        <h3 className="text-sm sm:text-base font-bold text-gray-900 dark:text-white truncate group-hover:text-emerald-500 dark:group-hover:text-emerald-400 transition-colors">
                                            {k.product_name || 'Game Panel'}
                                        </h3>
                                        <div className="flex flex-wrap items-center gap-1.5">
                                            {k.duration_name && (
                                                <span className="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-lg bg-red-500/10 text-red-500 dark:text-red-400 border border-red-500/20 font-bold">
                                                    <Sparkles className="w-2.5 h-2.5" />
                                                    {k.duration_name}
                                                </span>
                                            )}
                                            <span className="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-semibold">
                                                <ShieldCheck className="w-2.5 h-2.5" />
                                                Anti-Ban
                                            </span>
                                        </div>
                                    </div>

                                    <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500/10 to-teal-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-500 dark:text-emerald-400 shrink-0 shadow-sm">
                                        <Key className="w-5 h-5" />
                                    </div>
                                </div>

                                {/* Monospace License Key Display Box */}
                                <div className="space-y-1.5">
                                    <div className="flex items-center justify-between text-[11px] font-medium text-gray-500 dark:text-gray-400 px-0.5">
                                        <span>License Key String</span>
                                        <span className="text-[10px] text-emerald-600 dark:text-emerald-400 font-mono">1-Tap Copy</span>
                                    </div>
                                    <div className="relative p-2.5 sm:p-3 rounded-xl bg-gray-100/90 dark:bg-black/70 border border-emerald-500/30 dark:border-emerald-500/30 flex items-center justify-between gap-2 transition-all">
                                        <div className="flex items-center gap-2 min-w-0 flex-1">
                                            <Hash className="w-3.5 h-3.5 text-emerald-500/60 shrink-0" />
                                            <code className="text-xs sm:text-sm font-mono font-bold text-gray-900 dark:text-emerald-300 select-all truncate">
                                                {k.key}
                                            </code>
                                        </div>
                                        <button
                                            onClick={() => handleCopy(k.key, k.id)}
                                            className={`px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-all shrink-0 active:scale-95 ${
                                                copiedId === k.id
                                                    ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/30 scale-105'
                                                    : 'bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-300 border border-emerald-500/40 hover:scale-105'
                                            }`}
                                        >
                                            {copiedId === k.id ? (
                                                <>
                                                    <Check className="w-3.5 h-3.5 stroke-[3]" />
                                                    <span>Copied!</span>
                                                </>
                                            ) : (
                                                <>
                                                    <Copy className="w-3.5 h-3.5" />
                                                    <span>Copy</span>
                                                </>
                                            )}
                                        </button>
                                    </div>
                                </div>

                                {/* Footer Meta & Action Links */}
                                <div className="pt-2 border-t border-gray-200/60 dark:border-gray-800/60 flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 font-mono">
                                    <div className="flex flex-wrap items-center gap-2.5">
                                        <span className="flex items-center gap-1">
                                            <Package className="w-3 h-3 text-gray-400" />
                                            {k.order_number || 'N/A'}
                                        </span>
                                        <span className="flex items-center gap-1">
                                            <Calendar className="w-3 h-3 text-gray-400" />
                                            {k.sold_at || 'Just now'}
                                        </span>
                                    </div>

                                    <div className="flex items-center gap-2 shrink-0">
                                        {k.demo_video_url && (
                                            <a
                                                href={k.demo_video_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="inline-flex items-center gap-1 text-[10px] font-bold text-amber-500 hover:text-amber-400 transition-colors"
                                                title="Watch Setup Tutorial"
                                            >
                                                <PlayCircle className="w-3.5 h-3.5" />
                                                <span className="hidden sm:inline">Tutorial</span>
                                            </a>
                                        )}
                                        {k.product_slug && (
                                            <Link
                                                href={`/product/${k.product_slug}`}
                                                className="inline-flex items-center gap-1 text-[10px] font-bold text-[#f82803] hover:text-red-400 transition-colors"
                                                title="View Product Page"
                                            >
                                                <span>Product</span>
                                                <ExternalLink className="w-3 h-3" />
                                            </Link>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    /* High-End Empty State Card */
                    <div className="relative overflow-hidden rounded-3xl glass-panel border border-gray-200 dark:border-gray-800/90 text-center p-6 sm:p-10 space-y-6 shadow-xl">
                        {/* Background Ambient Radial Glow */}
                        <div className="absolute inset-0 bg-gradient-to-b from-emerald-500/5 via-transparent to-red-500/5 pointer-events-none" />
                        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none" />

                        <div className="relative z-10 space-y-4 max-w-md mx-auto">
                            {/* Glowing Icon with double ring */}
                            <div className="relative inline-block">
                                <div className="w-20 h-20 rounded-3xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 border border-emerald-500/30 flex items-center justify-center mx-auto shadow-2xl shadow-emerald-500/20 ring-8 ring-emerald-500/5">
                                    <Key className="w-10 h-10 text-emerald-500 dark:text-emerald-400" strokeWidth={1.75} />
                                </div>
                                <div className="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center ring-2 ring-white dark:ring-gray-900">
                                    <Sparkles className="w-3.5 h-3.5" />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <h3 className="text-lg sm:text-xl font-black font-display text-gray-900 dark:text-white">
                                    {searchQuery ? 'No Matching Keys Found' : 'No License Keys Yet'}
                                </h3>
                                <p className="text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                    {searchQuery
                                        ? 'No license keys matched your search criteria. Try a different query or clear the filter.'
                                        : 'Purchase any game panel or VIP package to get your instant digital license key with 0-second auto delivery!'}
                                </p>
                            </div>

                            {/* Feature Highlights Grid for Trust */}
                            {!searchQuery && (
                                <div className="grid grid-cols-3 gap-2 py-2">
                                    <div className="p-2.5 rounded-2xl bg-gray-100/80 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 text-center space-y-1">
                                        <Zap className="w-4 h-4 text-amber-500 mx-auto" />
                                        <p className="text-[10px] font-bold text-gray-800 dark:text-gray-200">0s Instant</p>
                                        <p className="text-[9px] text-gray-500">Auto Delivery</p>
                                    </div>
                                    <div className="p-2.5 rounded-2xl bg-gray-100/80 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 text-center space-y-1">
                                        <ShieldCheck className="w-4 h-4 text-emerald-500 mx-auto" />
                                        <p className="text-[10px] font-bold text-gray-800 dark:text-gray-200">Anti-Ban</p>
                                        <p className="text-[9px] text-gray-500">100% Safe</p>
                                    </div>
                                    <div className="p-2.5 rounded-2xl bg-gray-100/80 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 text-center space-y-1">
                                        <CheckCircle2 className="w-4 h-4 text-blue-500 mx-auto" />
                                        <p className="text-[10px] font-bold text-gray-800 dark:text-gray-200">Auto-Vault</p>
                                        <p className="text-[9px] text-gray-500">Save Forever</p>
                                    </div>
                                </div>
                            )}

                            {/* Call to action buttons */}
                            <div className="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                                {searchQuery ? (
                                    <button
                                        onClick={() => setSearchQuery('')}
                                        className="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-white font-bold text-xs hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors"
                                    >
                                        Clear Search
                                    </button>
                                ) : (
                                    <>
                                        <Link
                                            href="/#products"
                                            className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] hover:from-[#ff411a] hover:to-[#8f0909] text-white font-black text-xs sm:text-sm hover:scale-105 active:scale-95 transition-all shadow-xl shadow-red-500/25"
                                        >
                                            <Sparkles className="w-4 h-4" />
                                            <span>Browse Game Panels</span>
                                        </Link>
                                        <Link
                                            href="/wallet"
                                            className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-800 dark:text-gray-200 font-bold text-xs sm:text-sm border border-gray-300 dark:border-gray-700 transition-colors"
                                        >
                                            <PlusCircle className="w-4 h-4 text-red-500" />
                                            <span>Add Balance First</span>
                                        </Link>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                )}

                {/* Quick Activation Guide Helper for Mobile & Desktop */}
                <div className="rounded-2xl glass-panel border border-gray-200/80 dark:border-gray-800/80 p-4 sm:p-5 space-y-3">
                    <div className="flex items-center gap-2 text-xs font-bold text-gray-900 dark:text-white">
                        <Info className="w-4 h-4 text-emerald-500" />
                        <span>How to use your license key:</span>
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs text-gray-600 dark:text-gray-400">
                        <div className="flex items-start gap-2.5 p-2.5 rounded-xl bg-gray-100/60 dark:bg-gray-900/40 border border-gray-200/50 dark:border-gray-800/50">
                            <span className="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-500 font-bold text-[10px] flex items-center justify-center shrink-0">1</span>
                            <span className="leading-tight">Click <b>Copy</b> button next to your key string.</span>
                        </div>
                        <div className="flex items-start gap-2.5 p-2.5 rounded-xl bg-gray-100/60 dark:bg-gray-900/40 border border-gray-200/50 dark:border-gray-800/50">
                            <span className="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-500 font-bold text-[10px] flex items-center justify-center shrink-0">2</span>
                            <span className="leading-tight">Open your downloaded panel app on Android.</span>
                        </div>
                        <div className="flex items-start gap-2.5 p-2.5 rounded-xl bg-gray-100/60 dark:bg-gray-900/40 border border-gray-200/50 dark:border-gray-800/50">
                            <span className="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-500 font-bold text-[10px] flex items-center justify-center shrink-0">3</span>
                            <span className="leading-tight">Paste key into login box & enjoy anti-ban gaming!</span>
                        </div>
                    </div>
                </div>

                {/* Pagination */}
                {!searchQuery && keys.links && <Pagination links={keys.links} />}
            </div>
        </AppLayout>
    );
}
