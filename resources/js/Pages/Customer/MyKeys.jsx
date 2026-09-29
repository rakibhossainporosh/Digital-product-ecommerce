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
                  k.key.toLowerCase().includes(searchQuery.toLowerCase()) ||
                  k.product_name?.toLowerCase().includes(searchQuery.toLowerCase()) ||
                  k.order_number?.toLowerCase().includes(searchQuery.toLowerCase())
          )
        : keys.data;

    return (
        <AppLayout title="My License Keys">
            <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                    <div className="space-y-1">
                        <Link
                            href="/dashboard"
                            className="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 hover:text-red-400 transition-colors mb-2"
                        >
                            <ArrowLeft className="w-3.5 h-3.5" />
                            <span>Back to Dashboard</span>
                        </Link>
                        <h1 className="text-2xl sm:text-3xl font-black font-display text-gray-900 dark:text-white flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center">
                                <Key className="w-5 h-5 text-emerald-400" />
                            </div>
                            My License Keys
                        </h1>
                        <p className="text-xs text-gray-500 font-mono">
                            {keys.total} key{keys.total !== 1 && 's'} purchased
                        </p>
                    </div>

                    {/* Search Bar */}
                    <div className="relative max-w-xs w-full">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                        <input
                            type="text"
                            placeholder="Search keys, products..."
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            className="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white dark:bg-gray-900/80 border border-gray-200 dark:border-gray-800 text-gray-900 dark:text-white text-xs placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500/40 focus:border-red-500/50 transition-all"
                        />
                    </div>
                </div>

                {/* Keys Grid */}
                {filteredKeys.length > 0 ? (
                    <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        {filteredKeys.map((k) => (
                            <div
                                key={k.id}
                                className="glass-panel rounded-2xl border border-gray-200 dark:border-gray-800/80 p-5 space-y-4 hover:border-emerald-500/30 transition-all group"
                            >
                                {/* Product Info */}
                                <div className="flex items-start justify-between gap-3">
                                    <div className="space-y-1 min-w-0">
                                        <h3 className="text-sm font-bold text-gray-900 dark:text-white truncate group-hover:text-emerald-300 transition-colors">
                                            {k.product_name}
                                        </h3>
                                        <div className="flex flex-wrap items-center gap-2">
                                            {k.duration_name && (
                                                <span className="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 border border-red-500/20 font-semibold">
                                                    <Sparkles className="w-2.5 h-2.5" />
                                                    {k.duration_name}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                    <div className="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center shrink-0">
                                        <ShieldCheck className="w-4.5 h-4.5 text-emerald-400" />
                                    </div>
                                </div>

                                {/* License Key Display */}
                                <div className="relative">
                                    <div className="px-3.5 py-3 bg-gray-200 dark:bg-black/60 border border-emerald-500/20 rounded-xl flex items-center justify-between gap-2 group/key">
                                        <div className="flex items-center gap-2 min-w-0">
                                            <Hash className="w-3.5 h-3.5 text-emerald-500/40 shrink-0" />
                                            <code className="text-xs font-mono text-emerald-300 select-all truncate">
                                                {k.key}
                                            </code>
                                        </div>
                                        <button
                                            onClick={() => handleCopy(k.key, k.id)}
                                            className={`px-2.5 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-all shrink-0 ${
                                                copiedId === k.id
                                                    ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40'
                                                    : 'bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 hover:scale-105'
                                            }`}
                                        >
                                            {copiedId === k.id ? (
                                                <>
                                                    <Check className="w-3.5 h-3.5" />
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

                                {/* Footer Meta */}
                                <div className="flex items-center justify-between text-[10px] text-gray-500 font-mono pt-1">
                                    <div className="flex items-center gap-3">
                                        <span className="flex items-center gap-1">
                                            <Package className="w-3 h-3" />
                                            {k.order_number}
                                        </span>
                                        <span className="flex items-center gap-1">
                                            <Calendar className="w-3 h-3" />
                                            {k.sold_at}
                                        </span>
                                    </div>
                                    {k.product_slug && (
                                        <Link
                                            href={`/product/${k.product_slug}`}
                                            className="flex items-center gap-1 text-[#f82803]/60 hover:text-red-400 transition-colors"
                                        >
                                            <ExternalLink className="w-3 h-3" />
                                        </Link>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="glass-panel rounded-2xl border border-gray-200 dark:border-gray-800 text-center py-16 space-y-4">
                        <div className="w-16 h-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center mx-auto">
                            <Key className="w-8 h-8 text-emerald-400" />
                        </div>
                        <div className="space-y-1">
                            {searchQuery ? (
                                <>
                                    <p className="text-gray-900 dark:text-white font-semibold">No keys match your search</p>
                                    <p className="text-xs text-gray-500">Try a different keyword or clear the search.</p>
                                </>
                            ) : (
                                <>
                                    <p className="text-gray-900 dark:text-white font-semibold">No license keys yet</p>
                                    <p className="text-xs text-gray-500">Purchase a panel to get your instant license key!</p>
                                </>
                            )}
                        </div>
                        <Link
                            href="/"
                            className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] text-white font-bold text-sm hover:scale-105 transition-transform shadow-lg shadow-red-500/20"
                        >
                            <Sparkles className="w-4 h-4" />
                            Browse Panels
                        </Link>
                    </div>
                )}

                {/* Pagination */}
                {!searchQuery && <Pagination links={keys.links} />}
            </div>
        </AppLayout>
    );
}
