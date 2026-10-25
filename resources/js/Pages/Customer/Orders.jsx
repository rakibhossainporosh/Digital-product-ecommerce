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
    Search,
    X,
    Sparkles,
    ShieldCheck,
    PlusCircle,
    CheckCircle2,
    Receipt,
} from 'lucide-react';

const STATUS_CONFIG = {
    pending: {
        label: 'Pending',
        classes: 'bg-amber-50 text-amber-700 border-amber-300 dark:bg-amber-500/15 dark:text-amber-400 dark:border-amber-500/30',
        dot: 'bg-amber-500',
    },
    processing: {
        label: 'Processing',
        classes: 'bg-blue-50 text-blue-700 border-blue-300 dark:bg-blue-500/15 dark:text-blue-400 dark:border-blue-500/30',
        dot: 'bg-blue-500',
    },
    completed: {
        label: 'Completed',
        classes: 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-500/15 dark:text-emerald-400 dark:border-emerald-500/30',
        dot: 'bg-emerald-500',
    },
    cancelled: {
        label: 'Cancelled',
        classes: 'bg-rose-50 text-rose-700 border-rose-300 dark:bg-rose-500/15 dark:text-rose-400 dark:border-rose-500/30',
        dot: 'bg-rose-500',
    },
    failed: {
        label: 'Failed',
        classes: 'bg-rose-50 text-rose-700 border-rose-300 dark:bg-rose-500/15 dark:text-rose-400 dark:border-rose-500/30',
        dot: 'bg-rose-500',
    },
};

const FULFILLMENT_CONFIG = {
    pending: {
        label: 'Unfulfilled',
        classes: 'bg-amber-500/10 text-amber-600 dark:text-amber-300 border border-amber-500/20',
    },
    processing: {
        label: 'In Progress',
        classes: 'bg-blue-500/10 text-blue-600 dark:text-blue-300 border border-blue-500/20',
    },
    fulfilled: {
        label: 'Delivered',
        classes: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-300 border border-emerald-500/20',
    },
    failed: {
        label: 'Failed',
        classes: 'bg-rose-500/10 text-rose-600 dark:text-rose-300 border border-rose-500/20',
    },
};

export default function Orders({ orders }) {
    const [expandedId, setExpandedId] = useState(null);
    const [copiedKey, setCopiedKey] = useState(null);
    const [searchQuery, setSearchQuery] = useState('');

    const toggle = (id) => setExpandedId(expandedId === id ? null : id);

    const handleCopy = (keyStr, idx) => {
        navigator.clipboard.writeText(keyStr);
        setCopiedKey(idx);
        window.dispatchEvent(
            new CustomEvent('app-toast', {
                detail: {
                    type: 'success',
                    message: 'License key copied to clipboard!',
                },
            })
        );
        setTimeout(() => setCopiedKey(null), 2000);
    };

    const filteredOrders = searchQuery
        ? orders.data.filter(
              (o) =>
                  o.order_number?.toLowerCase().includes(searchQuery.toLowerCase()) ||
                  o.product_name?.toLowerCase().includes(searchQuery.toLowerCase()) ||
                  o.variant_name?.toLowerCase().includes(searchQuery.toLowerCase()) ||
                  o.status?.toLowerCase().includes(searchQuery.toLowerCase()) ||
                  o.fulfillment_status?.toLowerCase().includes(searchQuery.toLowerCase())
          )
        : orders.data;

    return (
        <AppLayout title="Order History">
            <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 pb-24 sm:pb-12">

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
                            <span className="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                                <span className="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse" />
                                {orders.total || 0} {orders.total === 1 ? 'Order' : 'Orders'} Total
                            </span>
                        </div>
                    </div>

                    {/* Page Hero Card */}
                    <div className="relative overflow-hidden rounded-3xl p-5 sm:p-7 glass-panel border border-purple-500/20 dark:border-purple-500/20 bg-gradient-to-br from-purple-500/5 via-transparent to-indigo-500/5">
                        <div className="absolute -right-10 -top-10 w-40 h-40 bg-purple-500/10 rounded-full blur-3xl pointer-events-none" />
                        <div className="absolute -left-10 -bottom-10 w-40 h-40 bg-red-500/10 rounded-full blur-3xl pointer-events-none" />

                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
                            <div className="flex items-start sm:items-center gap-3.5">
                                <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white shrink-0 shadow-lg shadow-purple-500/25 ring-4 ring-purple-500/10">
                                    <ShoppingBag className="w-6 h-6" />
                                </div>
                                <div>
                                    <div className="flex items-center gap-2 mb-1">
                                        <h1 className="text-xl sm:text-2xl font-black font-display text-gray-900 dark:text-white">
                                            Order History
                                        </h1>
                                        <span className="hidden sm:inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-500/15 text-purple-600 dark:text-purple-400 border border-purple-500/30">
                                            Fulfillment Records
                                        </span>
                                    </div>
                                    <p className="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                        Track all your purchased digital goods, licenses, topups, and payment records.
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-center gap-2.5 self-stretch sm:self-auto">
                                <Link
                                    href="/#products"
                                    className="flex-1 sm:flex-none justify-center px-4 py-2.5 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] hover:from-[#ff411a] hover:to-[#8f0909] text-white font-bold text-xs shadow-lg shadow-red-500/20 flex items-center gap-1.5 transition-all hover:scale-105 active:scale-95"
                                >
                                    <Zap className="w-3.5 h-3.5" />
                                    <span>Shop More</span>
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
                            placeholder="Search by order ID, product name, status..."
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            className="w-full pl-10 pr-10 py-3 rounded-2xl bg-white dark:bg-[#0f172a]/90 border border-gray-200 dark:border-gray-800 text-gray-900 dark:text-white text-xs sm:text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/50 shadow-sm transition-all"
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

                {/* Orders List */}
                {filteredOrders.length > 0 ? (
                    <div className="space-y-3.5">
                        {filteredOrders.map((order) => {
                            const status = STATUS_CONFIG[order.status] || STATUS_CONFIG.pending;
                            const fulfillment = FULFILLMENT_CONFIG[order.fulfillment_status] || FULFILLMENT_CONFIG.pending;
                            const isExpanded = expandedId === order.id;

                            return (
                                <div
                                    key={order.id}
                                    className={`rounded-2xl glass-panel border transition-all duration-200 overflow-hidden shadow-sm ${
                                        isExpanded
                                            ? 'border-purple-500/40 dark:border-purple-500/40 shadow-md ring-1 ring-purple-500/20'
                                            : 'border-gray-200/80 dark:border-gray-800/80 hover:border-gray-300 dark:hover:border-gray-700'
                                    }`}
                                >
                                    {/* Clickable Header Accordion */}
                                    <button
                                        onClick={() => toggle(order.id)}
                                        className="w-full p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 text-left hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors"
                                    >
                                        <div className="flex items-start sm:items-center gap-3.5 min-w-0 flex-1">
                                            {/* Icon Type */}
                                            <div
                                                className={`w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 shadow-sm ${
                                                    order.is_service
                                                        ? 'bg-gradient-to-br from-red-500/15 to-rose-600/10 border border-red-500/30 text-[#f82803]'
                                                        : 'bg-gradient-to-br from-emerald-500/15 to-teal-600/10 border border-emerald-500/30 text-emerald-500 dark:text-emerald-400'
                                                }`}
                                            >
                                                {order.is_service ? (
                                                    <Server className="w-5 h-5" />
                                                ) : (
                                                    <Key className="w-5 h-5" />
                                                )}
                                            </div>

                                            {/* Details Section */}
                                            <div className="min-w-0 space-y-1.5 flex-1">
                                                {/* Top Status Tags */}
                                                <div className="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                                    <span className="text-xs font-mono font-black text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-800/80 px-2 py-0.5 rounded-lg border border-gray-200 dark:border-gray-700">
                                                        {order.order_number}
                                                    </span>
                                                    <span className={`inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase border ${status.classes}`}>
                                                        <span className={`w-1.5 h-1.5 rounded-full ${status.dot}`} />
                                                        {status.label}
                                                    </span>
                                                    <span className={`inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-full ${fulfillment.classes}`}>
                                                        {fulfillment.label}
                                                    </span>
                                                </div>

                                                {/* Product Name & Variant */}
                                                <p className="text-sm font-bold text-gray-900 dark:text-white truncate">
                                                    {order.product_name}
                                                    {order.variant_name && (
                                                        <span className="text-gray-500 dark:text-gray-400 font-normal ml-1">
                                                            · {order.variant_name}
                                                        </span>
                                                    )}
                                                </p>

                                                {/* Time & Quantity */}
                                                <div className="flex items-center gap-3 text-[11px] text-gray-500 dark:text-gray-400 font-mono">
                                                    <span className="flex items-center gap-1">
                                                        <Clock className="w-3 h-3 text-gray-400" />
                                                        {order.created_at}
                                                    </span>
                                                    {order.quantity > 1 && (
                                                        <span className="inline-flex items-center gap-1 font-semibold text-gray-700 dark:text-gray-300">
                                                            <Package className="w-3 h-3 text-gray-400" />
                                                            Qty: {order.quantity}
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                        </div>

                                        {/* Total Price & Expand Action */}
                                        <div className="flex items-center justify-between sm:justify-end gap-3 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-100 dark:border-gray-800/60 shrink-0">
                                            <div className="text-left sm:text-right">
                                                <span className="text-[10px] text-gray-400 uppercase font-mono block sm:hidden">Total Amount</span>
                                                <span className="text-base sm:text-lg font-black font-mono text-[#f82803]">
                                                    {order.formatted_total}
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                <span className="hidden sm:inline text-[11px]">{isExpanded ? 'Hide' : 'Details'}</span>
                                                {isExpanded ? (
                                                    <ChevronUp className="w-3.5 h-3.5 text-purple-500" />
                                                ) : (
                                                    <ChevronDown className="w-3.5 h-3.5 text-gray-400" />
                                                )}
                                            </div>
                                        </div>
                                    </button>

                                    {/* Expanded Details Drawer */}
                                    {isExpanded && (
                                        <div className="border-t border-gray-200 dark:border-gray-800/90 p-4 sm:p-5 space-y-4 bg-gray-50/50 dark:bg-black/40 animate-fadeIn">
                                            {/* Service Data (if topup/account order) */}
                                            {order.service_data && Object.keys(order.service_data).length > 0 && (
                                                <div className="space-y-2">
                                                    <h4 className="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                                        <Server className="w-3.5 h-3.5 text-[#f82803]" />
                                                        <span>Provided Order Credentials / Details</span>
                                                    </h4>
                                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                        {Object.entries(order.service_data).map(([field, value]) => (
                                                            <div
                                                                key={field}
                                                                className="px-3.5 py-2.5 bg-white dark:bg-gray-900/90 border border-[#f82803]/20 rounded-xl space-y-0.5"
                                                            >
                                                                <span className="text-[10px] text-gray-500 uppercase font-mono block font-semibold">
                                                                    {field.replace(/_/g, ' ')}
                                                                </span>
                                                                <span className="text-xs sm:text-sm text-red-500 dark:text-red-400 font-mono font-bold select-all break-all">
                                                                    {String(value)}
                                                                </span>
                                                            </div>
                                                        ))}
                                                    </div>
                                                </div>
                                            )}

                                            {/* License Keys List */}
                                            {order.keys && order.keys.length > 0 && (
                                                <div className="space-y-2">
                                                    <div className="flex items-center justify-between">
                                                        <h4 className="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                                            <Key className="w-3.5 h-3.5 text-emerald-500" />
                                                            <span>Delivered License Keys ({order.keys.length})</span>
                                                        </h4>
                                                        <span className="text-[10px] text-emerald-600 dark:text-emerald-400 font-mono font-semibold">
                                                            Instant Copy Ready
                                                        </span>
                                                    </div>
                                                    <div className="space-y-2">
                                                        {order.keys.map((keyStr, idx) => (
                                                            <div
                                                                key={idx}
                                                                className="flex items-center justify-between gap-2 p-2.5 sm:p-3 bg-white dark:bg-black/60 border border-emerald-500/30 rounded-xl"
                                                            >
                                                                <div className="flex items-center gap-2 min-w-0 flex-1">
                                                                    <Hash className="w-3.5 h-3.5 text-emerald-500/60 shrink-0" />
                                                                    <code className="text-xs sm:text-sm font-mono font-bold text-gray-900 dark:text-emerald-300 select-all truncate">
                                                                        {keyStr}
                                                                    </code>
                                                                </div>
                                                                <button
                                                                    onClick={() => handleCopy(keyStr, `${order.id}-${idx}`)}
                                                                    className={`px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-all shrink-0 active:scale-95 ${
                                                                        copiedKey === `${order.id}-${idx}`
                                                                            ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/30 scale-105'
                                                                            : 'bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-300 border border-emerald-500/40 hover:scale-105'
                                                                    }`}
                                                                >
                                                                    {copiedKey === `${order.id}-${idx}` ? (
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
                                                        ))}
                                                    </div>
                                                </div>
                                            )}

                                            {/* Empty Keys State if pending or not yet generated */}
                                            {(!order.keys || order.keys.length === 0) && !order.is_service && (
                                                <div className="text-center py-4 px-3 rounded-xl bg-gray-100/50 dark:bg-gray-900/40 border border-dashed border-gray-300 dark:border-gray-800 text-xs text-gray-500 font-mono">
                                                    {order.status === 'completed'
                                                        ? 'No keys assigned to this order. If you need assistance, please contact support.'
                                                        : 'License keys will appear here automatically once the order fulfillment is completed.'}
                                                </div>
                                            )}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                ) : (
                    /* High-End Empty State Card */
                    <div className="relative overflow-hidden rounded-3xl glass-panel border border-gray-200 dark:border-gray-800/90 text-center p-6 sm:p-10 space-y-6 shadow-xl">
                        {/* Background Ambient Radial Glow */}
                        <div className="absolute inset-0 bg-gradient-to-b from-purple-500/5 via-transparent to-red-500/5 pointer-events-none" />
                        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-purple-500/10 rounded-full blur-3xl pointer-events-none" />

                        <div className="relative z-10 space-y-4 max-w-md mx-auto">
                            {/* Glowing Icon with double ring */}
                            <div className="relative inline-block">
                                <div className="w-20 h-20 rounded-3xl bg-gradient-to-br from-purple-500/20 to-indigo-500/10 border border-purple-500/30 flex items-center justify-center mx-auto shadow-2xl shadow-purple-500/20 ring-8 ring-purple-500/5">
                                    <ShoppingBag className="w-10 h-10 text-purple-500 dark:text-purple-400" strokeWidth={1.75} />
                                </div>
                                <div className="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-purple-500 text-white flex items-center justify-center ring-2 ring-white dark:ring-gray-900">
                                    <Sparkles className="w-3.5 h-3.5" />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <h3 className="text-lg sm:text-xl font-black font-display text-gray-900 dark:text-white">
                                    {searchQuery ? 'No Matching Orders Found' : 'No Orders Placed Yet'}
                                </h3>
                                <p className="text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                    {searchQuery
                                        ? 'No orders matched your search criteria. Try a different keyword or clear the search query.'
                                        : 'Browse our gaming store to purchase instant VIP panels, license keys, and topups in seconds!'}
                                </p>
                            </div>

                            {/* Feature Highlights Grid for Trust */}
                            {!searchQuery && (
                                <div className="grid grid-cols-3 gap-2 py-2">
                                    <div className="p-2.5 rounded-2xl bg-gray-100/80 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 text-center space-y-1">
                                        <Zap className="w-4 h-4 text-amber-500 mx-auto" />
                                        <p className="text-[10px] font-bold text-gray-800 dark:text-gray-200">Instant</p>
                                        <p className="text-[9px] text-gray-500">Auto Delivery</p>
                                    </div>
                                    <div className="p-2.5 rounded-2xl bg-gray-100/80 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 text-center space-y-1">
                                        <ShieldCheck className="w-4 h-4 text-emerald-500 mx-auto" />
                                        <p className="text-[10px] font-bold text-gray-800 dark:text-gray-200">100% Safe</p>
                                        <p className="text-[9px] text-gray-500">Verified System</p>
                                    </div>
                                    <div className="p-2.5 rounded-2xl bg-gray-100/80 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 text-center space-y-1">
                                        <Receipt className="w-4 h-4 text-purple-500 mx-auto" />
                                        <p className="text-[10px] font-bold text-gray-800 dark:text-gray-200">Invoices</p>
                                        <p className="text-[9px] text-gray-500">Auto History</p>
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

                {/* Pagination */}
                {!searchQuery && orders.links && <Pagination links={orders.links} />}
            </div>
        </AppLayout>
    );
}
