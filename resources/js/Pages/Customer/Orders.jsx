import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import {
    ShoppingBag,
    ChevronDown,
    ChevronUp,
    Copy,
    Check,
    Key,
    Clock,
    Package,
    Zap,
    Server,
    Hash,
    ArrowLeft,
} from 'lucide-react';

const STATUS_STYLES = {
    pending: 'bg-amber-500/15 text-amber-400 border-amber-500/30',
    processing: 'bg-red-500/15 text-red-400 border-red-500/30',
    completed: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
    cancelled: 'bg-red-500/15 text-red-400 border-red-500/30',
    failed: 'bg-red-500/15 text-red-400 border-red-500/30',
};

const FULFILLMENT_STYLES = {
    pending: 'bg-amber-500/10 text-amber-300',
    processing: 'bg-[#f82803]/10 text-red-400',
    fulfilled: 'bg-emerald-500/10 text-emerald-300',
    failed: 'bg-red-500/10 text-red-300',
};

export default function Orders({ orders }) {
    const [expandedId, setExpandedId] = useState(null);
    const [copiedKey, setCopiedKey] = useState(null);

    const toggle = (id) => setExpandedId(expandedId === id ? null : id);

    const handleCopy = (keyStr, idx) => {
        navigator.clipboard.writeText(keyStr);
        setCopiedKey(idx);
        setTimeout(() => setCopiedKey(null), 2000);
    };

    return (
        <AppLayout title="My Orders">
            <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div className="space-y-1">
                        <Link
                            href="/dashboard"
                            className="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 hover:text-red-400 transition-colors mb-2"
                        >
                            <ArrowLeft className="w-3.5 h-3.5" />
                            <span>Back to Dashboard</span>
                        </Link>
                        <h1 className="text-2xl sm:text-3xl font-black font-display text-gray-900 dark:text-white flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center">
                                <ShoppingBag className="w-5 h-5 text-purple-400" />
                            </div>
                            Order History
                        </h1>
                        <p className="text-xs text-gray-500 font-mono">
                            {orders.total} total order{orders.total !== 1 && 's'}
                        </p>
                    </div>

                    <Link
                        href="/#products"
                        className="px-5 py-2.5 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] hover:from-[#ff411a] hover:to-[#8f0909] text-white font-bold text-xs shadow-lg shadow-red-500/20 flex items-center gap-2 hover:scale-105 transition-all self-start"
                    >
                        <Zap className="w-4 h-4" />
                        <span>Shop More</span>
                    </Link>
                </div>

                {/* Orders List */}
                {orders.data.length > 0 ? (
                    <div className="space-y-3">
                        {orders.data.map((order) => (
                            <div
                                key={order.id}
                                className="glass-panel rounded-2xl border border-gray-200 dark:border-gray-800/80 overflow-hidden transition-all hover:border-gray-300 dark:border-gray-700/80"
                            >
                                {/* Order Row */}
                                <button
                                    onClick={() => toggle(order.id)}
                                    className="w-full p-4 sm:p-5 flex items-center justify-between gap-4 text-left"
                                >
                                    <div className="flex items-center gap-4 min-w-0 flex-1">
                                        {/* Icon */}
                                        <div className={`w-10 h-10 rounded-xl flex items-center justify-center shrink-0 ${
                                            order.is_service
                                                ? 'bg-[#f82803]/10 border border-[#f82803]/30'
                                                : 'bg-emerald-500/10 border border-emerald-500/30'
                                        }`}>
                                            {order.is_service ? (
                                                <Server className="w-5 h-5 text-[#f82803]" />
                                            ) : (
                                                <Key className="w-5 h-5 text-emerald-400" />
                                            )}
                                        </div>

                                        {/* Details */}
                                        <div className="min-w-0 space-y-1">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <span className="text-xs font-mono font-bold text-gray-700 dark:text-gray-300">
                                                    {order.order_number}
                                                </span>
                                                <span className={`text-[10px] px-2 py-0.5 rounded-full font-bold uppercase border ${STATUS_STYLES[order.status] || STATUS_STYLES.pending}`}>
                                                    {order.status}
                                                </span>
                                                <span className={`text-[10px] px-2 py-0.5 rounded-full font-semibold ${FULFILLMENT_STYLES[order.fulfillment_status] || FULFILLMENT_STYLES.pending}`}>
                                                    {order.fulfillment_status}
                                                </span>
                                            </div>
                                            <p className="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                {order.product_name}
                                                {order.variant_name && (
                                                    <span className="text-gray-600 dark:text-gray-400 font-normal"> · {order.variant_name}</span>
                                                )}
                                            </p>
                                            <div className="flex items-center gap-3 text-[11px] text-gray-500 font-mono">
                                                <span className="flex items-center gap-1">
                                                    <Clock className="w-3 h-3" />
                                                    {order.created_at}
                                                </span>
                                                {order.quantity > 1 && (
                                                    <span className="flex items-center gap-1">
                                                        <Package className="w-3 h-3" />
                                                        Qty: {order.quantity}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Price & Expand Toggle */}
                                    <div className="flex items-center gap-3 shrink-0">
                                        <span className="text-lg font-black font-mono text-red-400">
                                            {order.formatted_total}
                                        </span>
                                        <div className="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800/80 border border-gray-300 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400">
                                            {expandedId === order.id ? (
                                                <ChevronUp className="w-4 h-4" />
                                            ) : (
                                                <ChevronDown className="w-4 h-4" />
                                            )}
                                        </div>
                                    </div>
                                </button>

                                {/* Expanded Details */}
                                {expandedId === order.id && (
                                    <div className="border-t border-gray-200 dark:border-gray-800 p-4 sm:p-5 space-y-4 bg-gray-950/40 animate-fadeIn">

                                        {/* Service Data (for topup orders) */}
                                        {order.service_data && Object.keys(order.service_data).length > 0 && (
                                            <div className="space-y-2">
                                                <h4 className="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                                    <Server className="w-3.5 h-3.5 text-[#f82803]" />
                                                    Service Details
                                                </h4>
                                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                    {Object.entries(order.service_data).map(([field, value]) => (
                                                        <div
                                                            key={field}
                                                            className="px-3 py-2 bg-[#f82803]/5 border border-[#f82803]/20 rounded-xl"
                                                        >
                                                            <span className="text-[10px] text-gray-500 uppercase font-mono block">{field}</span>
                                                            <span className="text-sm text-red-400 font-semibold">{value}</span>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}

                                        {/* License Keys */}
                                        {order.keys && order.keys.length > 0 && (
                                            <div className="space-y-2">
                                                <h4 className="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                                    <Key className="w-3.5 h-3.5 text-emerald-400" />
                                                    License Keys ({order.keys.length})
                                                </h4>
                                                <div className="space-y-1.5">
                                                    {order.keys.map((keyStr, idx) => (
                                                        <div
                                                            key={idx}
                                                            className="flex items-center justify-between gap-3 px-3 py-2.5 bg-gray-100 dark:bg-black/50 border border-emerald-500/20 rounded-xl"
                                                        >
                                                            <div className="flex items-center gap-2 min-w-0">
                                                                <Hash className="w-3.5 h-3.5 text-emerald-500/50 shrink-0" />
                                                                <code className="text-sm font-mono text-emerald-300 select-all truncate">
                                                                    {keyStr}
                                                                </code>
                                                            </div>
                                                            <button
                                                                onClick={() => handleCopy(keyStr, `${order.id}-${idx}`)}
                                                                className="px-2.5 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-xs font-semibold flex items-center gap-1.5 transition-colors shrink-0"
                                                            >
                                                                {copiedKey === `${order.id}-${idx}` ? (
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
                                                    ))}
                                                </div>
                                            </div>
                                        )}

                                        {/* Empty Keys State */}
                                        {(!order.keys || order.keys.length === 0) && !order.is_service && (
                                            <div className="text-center py-4 text-xs text-gray-500 font-mono">
                                                {order.status === 'completed'
                                                    ? 'No keys assigned to this order.'
                                                    : 'Keys will appear here once the order is fulfilled.'}
                                            </div>
                                        )}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="glass-panel rounded-2xl border border-gray-200 dark:border-gray-800 text-center py-16 space-y-4">
                        <div className="w-16 h-16 rounded-2xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center mx-auto">
                            <ShoppingBag className="w-8 h-8 text-purple-400" />
                        </div>
                        <div className="space-y-1">
                            <p className="text-gray-900 dark:text-white font-semibold">No orders yet</p>
                            <p className="text-xs text-gray-500">Browse our store and make your first purchase!</p>
                        </div>
                        <Link
                            href="/"
                            className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] text-white font-bold text-sm hover:scale-105 transition-transform shadow-lg shadow-red-500/20"
                        >
                            <Zap className="w-4 h-4" />
                            Browse Store
                        </Link>
                    </div>
                )}

                {/* Pagination */}
                <Pagination links={orders.links} />
            </div>
        </AppLayout>
    );
}
