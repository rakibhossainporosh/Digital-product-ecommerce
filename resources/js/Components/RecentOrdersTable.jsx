import React from 'react';
import { ShoppingCart, Clock, ShieldCheck } from 'lucide-react';

export default function RecentOrdersTable({ orders }) {
    if (!orders || orders.length === 0) return null;

    return (
        <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-4">
            <div className="glass-panel rounded-2xl border border-gray-200 dark:border-gray-800/80 overflow-hidden shadow-sm">
                <div className="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-800/80 bg-gray-50/50 dark:bg-gray-900/50 flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <div className="w-8 h-8 rounded-lg bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-500 shrink-0">
                            <ShoppingCart className="w-4 h-4" />
                        </div>
                        <h3 className="text-lg font-black font-display text-gray-900 dark:text-white">
                            Live Purchases
                        </h3>
                    </div>
                    <div className="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-500 text-[10px] font-bold uppercase tracking-wider border border-emerald-500/20">
                        <span className="relative flex h-2 w-2">
                          <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                          <span className="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        Real-time
                    </div>
                </div>
                
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm whitespace-nowrap">
                        <thead className="bg-gray-100/50 dark:bg-gray-800/30 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider font-semibold">
                            <tr>
                                <th className="px-4 sm:px-6 py-3">Customer</th>
                                <th className="px-4 sm:px-6 py-3">Product</th>
                                <th className="px-4 sm:px-6 py-3">Amount</th>
                                <th className="px-4 sm:px-6 py-3 text-right">Time</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100 dark:divide-gray-800/60 text-sm">
                            {orders.map((order, idx) => (
                                <tr key={`${order.id}-${idx}`} className="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors group">
                                    <td className="px-4 sm:px-6 py-3.5">
                                        <div className="flex items-center gap-2.5">
                                            <div className="w-7 h-7 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-400 font-bold text-[10px]">
                                                {order.customer_name.charAt(0)}
                                            </div>
                                            <span className="font-medium text-gray-900 dark:text-gray-200 group-hover:text-red-500 transition-colors">
                                                {order.customer_name}
                                            </span>
                                        </div>
                                    </td>
                                    <td className="px-4 sm:px-6 py-3.5">
                                        <span className="font-bold text-gray-800 dark:text-gray-100 flex items-center gap-1.5">
                                            <ShieldCheck className="w-3.5 h-3.5 text-red-500" />
                                            {order.product_name}
                                        </span>
                                    </td>
                                    <td className="px-4 sm:px-6 py-3.5">
                                        <span className="font-semibold text-emerald-600 dark:text-emerald-400 font-mono text-xs bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">
                                            ৳ {parseFloat(order.amount || 0).toFixed(2)}
                                        </span>
                                    </td>
                                    <td className="px-4 sm:px-6 py-3.5 text-right">
                                        <span className="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400 font-mono text-xs bg-gray-100 dark:bg-gray-800/60 px-2.5 py-1 rounded-md">
                                            <Clock className="w-3 h-3" />
                                            {order.time_ago}
                                        </span>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    );
}
