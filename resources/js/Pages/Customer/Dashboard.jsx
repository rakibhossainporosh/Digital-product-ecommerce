import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { 
    Wallet, 
    Key, 
    ShoppingBag, 
    ArrowUpRight, 
    Copy, 
    Check, 
    PlusCircle, 
    Clock, 
    ShieldCheck, 
    ChevronRight,
    Sparkles
} from 'lucide-react';

export default function Dashboard({ stats, recentOrders, recentKeys }) {
    const { auth } = usePage().props;
    const customer = auth?.customer;
    const [copiedKeyId, setCopiedKeyId] = React.useState(null);

    const handleCopy = (keyString, id) => {
        navigator.clipboard.writeText(keyString);
        setCopiedKeyId(id);
        setTimeout(() => setCopiedKeyId(null), 2000);
    };

    return (
        <AppLayout title="Customer Dashboard">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
                
                {/* Greeting & Quick Action Banner */}
                <div className="glass-panel p-6 sm:p-8 rounded-3xl border border-red-500/20 bg-gradient-to-r from-red-50 dark:from-red-950/40 via-slate-100 dark:via-slate-900 to-gray-100 dark:to-black/40 relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div className="space-y-2 z-10">
                        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-semibold">
                            <Sparkles className="w-3.5 h-3.5" />
                            <span>Customer Control Center</span>
                        </div>
                        <h1 className="text-2xl sm:text-3xl font-black font-display text-gray-900 dark:text-white">
                            Hello, {customer?.name}!
                        </h1>
                        <p className="text-xs sm:text-sm text-gray-600 dark:text-gray-400 max-w-md">
                            Manage your wallet balance, instant digital keys, and order fulfillment history all in one place.
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-3 z-10">
                        <Link 
                            href="/wallet" 
                            className="px-5 py-2.5 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] hover:from-[#ff411a] hover:to-[#8f0909] text-white font-bold text-xs sm:text-sm shadow-lg shadow-red-500/20 flex items-center gap-2 hover:scale-105 transition-all"
                        >
                            <PlusCircle className="w-4 h-4 fill-white" />
                            <span>Add Balance</span>
                        </Link>
                        <Link 
                            href="/#products" 
                            className="px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-700 text-gray-900 dark:text-white font-semibold text-xs sm:text-sm border border-gray-300 dark:border-gray-700 transition-colors flex items-center gap-2"
                        >
                            <ShoppingBag className="w-4 h-4 text-red-400" />
                            <span>Shop Panels</span>
                        </Link>
                    </div>
                </div>

                {/* 4 Stat Overview Cards */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {/* Wallet Balance */}
                    <div className="glass-panel p-5 rounded-2xl border border-gray-200 dark:border-gray-800/80 space-y-2">
                        <div className="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/30 flex items-center justify-center text-red-400">
                            <Wallet className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="block text-xs text-gray-600 dark:text-gray-400 font-mono uppercase">Wallet Balance</span>
                            <span className="text-xl sm:text-2xl font-black font-mono text-red-400">
                                {stats.formatted_balance}
                            </span>
                        </div>
                    </div>

                    {/* Total Keys Owned */}
                    <div className="glass-panel p-5 rounded-2xl border border-gray-200 dark:border-gray-800/80 space-y-2">
                        <div className="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                            <Key className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="block text-xs text-gray-600 dark:text-gray-400 font-mono uppercase">License Keys</span>
                            <span className="text-xl sm:text-2xl font-black font-mono text-emerald-400">
                                {stats.total_keys}
                            </span>
                        </div>
                    </div>

                    {/* Total Orders */}
                    <div className="glass-panel p-5 rounded-2xl border border-gray-200 dark:border-gray-800/80 space-y-2">
                        <div className="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400">
                            <ShoppingBag className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="block text-xs text-gray-600 dark:text-gray-400 font-mono uppercase">Total Orders</span>
                            <span className="text-xl sm:text-2xl font-black font-mono text-purple-400">
                                {stats.total_orders}
                            </span>
                        </div>
                    </div>

                    {/* Total Spent */}
                    <div className="glass-panel p-5 rounded-2xl border border-gray-200 dark:border-gray-800/80 space-y-2">
                        <div className="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
                            <ArrowUpRight className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="block text-xs text-gray-600 dark:text-gray-400 font-mono uppercase">Total Spent</span>
                            <span className="text-xl sm:text-2xl font-black font-mono text-amber-400">
                                {stats.total_spent}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Main Content Split: Recent License Keys & Recent Orders */}
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    {/* Left: Recent License Keys (7 cols) */}
                    <div className="lg:col-span-7 space-y-4">
                        <div className="flex items-center justify-between">
                            <h2 className="text-lg font-bold text-gray-900 dark:text-white font-display flex items-center gap-2">
                                <Key className="w-5 h-5 text-red-400" />
                                <span>Recent Purchased Keys</span>
                            </h2>
                            <Link href="/keys" className="text-xs text-red-400 hover:underline flex items-center gap-1">
                                <span>View All ({stats.total_keys})</span>
                                <ChevronRight className="w-3.5 h-3.5" />
                            </Link>
                        </div>

                        {recentKeys.length > 0 ? (
                            <div className="space-y-3">
                                {recentKeys.map((k) => (
                                    <div key={k.id} className="glass-panel p-4 rounded-xl border border-gray-200 dark:border-gray-800 flex items-center justify-between gap-3">
                                        <div className="space-y-1">
                                            <span className="block text-sm font-bold text-gray-900 dark:text-white line-clamp-1">{k.product_name}</span>
                                            <span className="text-xs text-gray-600 dark:text-gray-400 font-mono">{k.duration_name} · {k.sold_at}</span>
                                            <div className="pt-1">
                                                <span className="inline-block px-2.5 py-1 rounded bg-gray-200 dark:bg-black/60 border border-red-500/40 text-red-300 font-mono text-xs select-all">
                                                    {k.key}
                                                </span>
                                            </div>
                                        </div>

                                        <button
                                            onClick={() => handleCopy(k.key, k.id)}
                                            className="px-3 py-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 text-xs font-semibold flex items-center gap-1.5 transition-colors shrink-0"
                                        >
                                            {copiedKeyId === k.id ? (
                                                <>
                                                    <Check className="w-4 h-4 text-emerald-400" />
                                                    <span className="text-emerald-400">Copied!</span>
                                                </>
                                            ) : (
                                                <>
                                                    <Copy className="w-4 h-4" />
                                                    <span>Copy Key</span>
                                                </>
                                            )}
                                        </button>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-10 glass-card rounded-2xl border border-gray-200 dark:border-gray-800 text-sm text-gray-600 dark:text-gray-400">
                                No keys purchased yet. Browse our panels to buy your first key!
                            </div>
                        )}
                    </div>

                    {/* Right: Recent Orders (5 cols) */}
                    <div className="lg:col-span-5 space-y-4">
                        <div className="flex items-center justify-between">
                            <h2 className="text-lg font-bold text-gray-900 dark:text-white font-display flex items-center gap-2">
                                <Clock className="w-5 h-5 text-purple-400" />
                                <span>Recent Orders</span>
                            </h2>
                            <Link href="/orders" className="text-xs text-red-400 hover:underline flex items-center gap-1">
                                <span>View All ({stats.total_orders})</span>
                                <ChevronRight className="w-3.5 h-3.5" />
                            </Link>
                        </div>

                        {recentOrders.length > 0 ? (
                            <div className="space-y-3">
                                {recentOrders.map((o) => (
                                    <div key={o.id} className="glass-panel p-4 rounded-xl border border-gray-200 dark:border-gray-800 space-y-2">
                                        <div className="flex items-center justify-between">
                                            <span className="text-xs font-mono font-bold text-gray-700 dark:text-gray-300">{o.order_number}</span>
                                            <span className={`text-[10px] px-2 py-0.5 rounded-full font-semibold uppercase ${
                                                o.status === 'completed' 
                                                    ? 'bg-emerald-500/20 text-emerald-400' 
                                                    : o.status === 'processing'
                                                    ? 'bg-red-500/20 text-red-400'
                                                    : 'bg-amber-500/20 text-amber-400'
                                            }`}>
                                                {o.status}
                                            </span>
                                        </div>

                                        <div className="flex items-center justify-between text-xs">
                                            <span className="text-gray-900 dark:text-white font-semibold truncate max-w-[180px]">{o.product_name}</span>
                                            <span className="font-mono font-bold text-red-400">{o.formatted_total}</span>
                                        </div>

                                        <div className="text-[11px] text-gray-500 font-mono">
                                            {o.created_at}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-10 glass-card rounded-2xl border border-gray-200 dark:border-gray-800 text-sm text-gray-600 dark:text-gray-400">
                                No order history yet.
                            </div>
                        )}
                    </div>

                </div>

            </div>
        </AppLayout>
    );
}
