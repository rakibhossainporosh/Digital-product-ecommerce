import React, { useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import {
    Wallet,
    ArrowLeft,
    PlusCircle,
    ArrowUpRight,
    ArrowDownLeft,
    TrendingUp,
    Clock,
    Banknote,
    X,
    Loader2,
    CreditCard,
    Sparkles,
    Info,
    ShieldCheck,
    Zap,
    ShoppingBag,
    Search,
    CheckCircle2,
    Receipt,
} from 'lucide-react';

export default function WalletPage({ balance, formatted_balance, minDeposit, maxDeposit, transactions }) {
    const [showDepositModal, setShowDepositModal] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');

    const { data, setData, post, processing, errors, reset } = useForm({
        amount: '',
    });

    const handleDeposit = (e) => {
        e.preventDefault();
        post('/wallet/deposit', {
            onSuccess: () => {
                reset();
                setShowDepositModal(false);
            },
        });
    };

    const quickAmounts = [100, 250, 500, 1000, 2500, 5000];

    const filteredTransactions = searchQuery
        ? transactions.data.filter(
              (t) =>
                  t.description?.toLowerCase().includes(searchQuery.toLowerCase()) ||
                  t.type_label?.toLowerCase().includes(searchQuery.toLowerCase()) ||
                  t.reference_id?.toLowerCase().includes(searchQuery.toLowerCase()) ||
                  t.formatted_amount?.toLowerCase().includes(searchQuery.toLowerCase())
          )
        : transactions.data;

    return (
        <AppLayout title="My Wallet">
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
                            <span className="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-red-500/10 text-red-500 dark:text-red-400 border border-red-500/20">
                                <span className="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse" />
                                Instant Checkout Ready
                            </span>
                        </div>
                    </div>

                    {/* Cyber VIP Wallet Card */}
                    <div className="relative overflow-hidden rounded-3xl p-6 sm:p-8 glass-panel border border-red-500/25 dark:border-red-500/30 bg-gradient-to-br from-red-500/10 via-slate-900/10 to-rose-600/10 dark:from-red-950/60 dark:via-[#0c101d] dark:to-black shadow-xl">
                        {/* Ambient Glowing Orbs */}
                        <div className="absolute -right-16 -top-16 w-56 h-56 bg-red-500/15 rounded-full blur-3xl pointer-events-none" />
                        <div className="absolute -left-16 -bottom-16 w-56 h-56 bg-rose-600/15 rounded-full blur-3xl pointer-events-none" />

                        <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                            <div className="space-y-3">
                                <div className="flex items-center gap-2">
                                    <div className="w-8 h-8 rounded-xl bg-red-500/15 border border-red-500/30 flex items-center justify-center text-red-500 dark:text-red-400">
                                        <Wallet className="w-4 h-4" />
                                    </div>
                                    <span className="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-widest font-mono">
                                        Available Balance
                                    </span>
                                </div>

                                <div className="flex items-baseline gap-2">
                                    <span className="text-3xl sm:text-5xl font-black font-mono text-[#f82803] dark:text-red-400 tracking-tight drop-shadow-sm">
                                        {formatted_balance}
                                    </span>
                                </div>

                                <div className="flex flex-wrap items-center gap-2 pt-1 text-xs text-gray-600 dark:text-gray-400">
                                    <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-white/60 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-[11px] font-semibold">
                                        <Zap className="w-3 h-3 text-amber-500" />
                                        0s Instant Delivery
                                    </span>
                                    <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-white/60 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-[11px] font-semibold">
                                        <ShieldCheck className="w-3 h-3 text-emerald-500" />
                                        100% Secure
                                    </span>
                                </div>
                            </div>

                            {/* Actions on Card */}
                            <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                <button
                                    onClick={() => setShowDepositModal(true)}
                                    className="px-6 py-3.5 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] hover:from-[#ff411a] hover:to-[#8f0909] text-white font-black text-xs sm:text-sm shadow-xl shadow-red-500/25 flex items-center justify-center gap-2 hover:scale-105 active:scale-95 transition-all"
                                >
                                    <PlusCircle className="w-4 h-4 fill-white" />
                                    <span>Add Balance</span>
                                </button>
                                <Link
                                    href="/#products"
                                    className="px-5 py-3.5 rounded-xl bg-white dark:bg-gray-900/90 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-800 dark:text-gray-200 font-bold text-xs sm:text-sm border border-gray-200 dark:border-gray-800 transition-all flex items-center justify-center gap-2"
                                >
                                    <ShoppingBag className="w-4 h-4 text-red-500" />
                                    <span>Shop Panels</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Transaction History Section */}
                <div className="space-y-4">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div className="flex items-center gap-2">
                            <div className="w-8 h-8 rounded-xl bg-red-500/10 border border-red-500/30 flex items-center justify-center text-red-500 dark:text-red-400">
                                <TrendingUp className="w-4 h-4" />
                            </div>
                            <div>
                                <h2 className="text-base sm:text-lg font-black font-display text-gray-900 dark:text-white">
                                    Transaction History
                                </h2>
                            </div>
                        </div>

                        <span className="text-xs text-gray-500 font-mono self-start sm:self-auto">
                            {transactions.total || 0} total records
                        </span>
                    </div>

                    {/* Search Bar if transactions exist */}
                    {transactions.total > 0 && (
                        <div className="relative w-full">
                            <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search by reference ID, description, amount..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="w-full pl-10 pr-10 py-3 rounded-2xl bg-white dark:bg-[#0f172a]/90 border border-gray-200 dark:border-gray-800 text-gray-900 dark:text-white text-xs sm:text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500/40 focus:border-red-500/50 shadow-sm transition-all"
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
                    )}

                    {/* Transactions List */}
                    {filteredTransactions.length > 0 ? (
                        <div className="space-y-3">
                            {filteredTransactions.map((txn) => {
                                const isCredit = txn.direction === 'credit';
                                const Icon = isCredit ? ArrowDownLeft : ArrowUpRight;

                                return (
                                    <div
                                        key={txn.id}
                                        className="glass-panel p-4 sm:p-5 rounded-2xl border border-gray-200/80 dark:border-gray-800/80 flex items-center justify-between gap-3 sm:gap-4 hover:border-red-500/30 transition-all shadow-sm group"
                                    >
                                        <div className="flex items-center gap-3.5 min-w-0">
                                            <div
                                                className={`w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-sm ${
                                                    isCredit
                                                        ? 'bg-emerald-500/15 border border-emerald-500/30 text-emerald-500 dark:text-emerald-400'
                                                        : 'bg-red-500/15 border border-red-500/30 text-red-500 dark:text-red-400'
                                                }`}
                                            >
                                                <Icon className="w-5 h-5" strokeWidth={2.2} />
                                            </div>

                                            <div className="min-w-0 space-y-1">
                                                <div className="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                                    <span className="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate">
                                                        {txn.description || txn.type_label}
                                                    </span>
                                                    <span
                                                        className={`text-[10px] px-2 py-0.5 rounded-full font-bold uppercase ${
                                                            isCredit
                                                                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20'
                                                                : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20'
                                                        }`}
                                                    >
                                                        {txn.type_label}
                                                    </span>
                                                </div>

                                                <div className="flex flex-wrap items-center gap-3 text-[11px] text-gray-500 font-mono">
                                                    <span className="flex items-center gap-1">
                                                        <Clock className="w-3 h-3 text-gray-400" />
                                                        {txn.created_at}
                                                    </span>
                                                    {txn.reference_id && (
                                                        <span className="truncate max-w-[140px] text-gray-400">
                                                            Ref: #{txn.reference_id}
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                        </div>

                                        <div className="text-right shrink-0 space-y-0.5">
                                            <span
                                                className={`text-sm sm:text-base font-black font-mono ${
                                                    isCredit ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'
                                                }`}
                                            >
                                                {txn.formatted_amount}
                                            </span>
                                            <span className="block text-[10px] text-gray-500 font-mono">
                                                Bal: {txn.formatted_balance_after}
                                            </span>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    ) : (
                        /* High-End Empty State Card */
                        <div className="relative overflow-hidden rounded-3xl glass-panel border border-gray-200 dark:border-gray-800/90 text-center p-6 sm:p-10 space-y-6 shadow-xl">
                            {/* Background Ambient Glow */}
                            <div className="absolute inset-0 bg-gradient-to-b from-red-500/5 via-transparent to-rose-500/5 pointer-events-none" />
                            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-red-500/10 rounded-full blur-3xl pointer-events-none" />

                            <div className="relative z-10 space-y-4 max-w-md mx-auto">
                                {/* Glowing Icon with double ring */}
                                <div className="relative inline-block">
                                    <div className="w-20 h-20 rounded-3xl bg-gradient-to-br from-red-500/20 to-rose-600/10 border border-red-500/30 flex items-center justify-center mx-auto shadow-2xl shadow-red-500/20 ring-8 ring-red-500/5">
                                        <Banknote className="w-10 h-10 text-red-500 dark:text-red-400" strokeWidth={1.75} />
                                    </div>
                                    <div className="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center ring-2 ring-white dark:ring-gray-900">
                                        <Sparkles className="w-3.5 h-3.5" />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <h3 className="text-lg sm:text-xl font-black font-display text-gray-900 dark:text-white">
                                        {searchQuery ? 'No Matching Transactions' : 'No Transactions Yet'}
                                    </h3>
                                    <p className="text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                        {searchQuery
                                            ? 'No wallet history matched your search criteria. Try a different keyword.'
                                            : 'Add balance to your wallet using bKash, Nagad, or Rocket to enjoy 0-second instant checkout on all game panels!'}
                                    </p>
                                </div>

                                {/* Feature Highlights Grid for Trust */}
                                {!searchQuery && (
                                    <div className="grid grid-cols-3 gap-2 py-2">
                                        <div className="p-2.5 rounded-2xl bg-gray-100/80 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 text-center space-y-1">
                                            <Zap className="w-4 h-4 text-amber-500 mx-auto" />
                                            <p className="text-[10px] font-bold text-gray-800 dark:text-gray-200">0s Instant</p>
                                            <p className="text-[9px] text-gray-500">Auto Checkout</p>
                                        </div>
                                        <div className="p-2.5 rounded-2xl bg-gray-100/80 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 text-center space-y-1">
                                            <CreditCard className="w-4 h-4 text-red-500 mx-auto" />
                                            <p className="text-[10px] font-bold text-gray-800 dark:text-gray-200">bKash/Nagad</p>
                                            <p className="text-[9px] text-gray-500">Auto Gateway</p>
                                        </div>
                                        <div className="p-2.5 rounded-2xl bg-gray-100/80 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 text-center space-y-1">
                                            <ShieldCheck className="w-4 h-4 text-emerald-500 mx-auto" />
                                            <p className="text-[10px] font-bold text-gray-800 dark:text-gray-200">100% Safe</p>
                                            <p className="text-[9px] text-gray-500">Encrypted Funds</p>
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
                                            <button
                                                onClick={() => setShowDepositModal(true)}
                                                className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] hover:from-[#ff411a] hover:to-[#8f0909] text-white font-black text-xs sm:text-sm hover:scale-105 active:scale-95 transition-all shadow-xl shadow-red-500/25"
                                            >
                                                <PlusCircle className="w-4 h-4 fill-white" />
                                                <span>Add Balance Now</span>
                                            </button>
                                            <Link
                                                href="/#products"
                                                className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-800 dark:text-gray-200 font-bold text-xs sm:text-sm border border-gray-300 dark:border-gray-700 transition-colors"
                                            >
                                                <ShoppingBag className="w-4 h-4 text-red-500" />
                                                <span>Browse Panels</span>
                                            </Link>
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Pagination */}
                    {!searchQuery && transactions.links && <Pagination links={transactions.links} />}
                </div>
            </div>

            {/* Premium Deposit Modal */}
            {showDepositModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
                    {/* Backdrop */}
                    <div
                        className="absolute inset-0 bg-black/80 backdrop-blur-md animate-fadeIn"
                        onClick={() => setShowDepositModal(false)}
                    />

                    {/* Modal Content */}
                    <div className="relative w-full max-w-md glass-panel rounded-3xl border border-red-500/30 p-5 sm:p-7 space-y-5 animate-toast-slide-in shadow-2xl bg-white dark:bg-[#0c1220]">
                        {/* Close button */}
                        <button
                            onClick={() => setShowDepositModal(false)}
                            className="absolute top-4 right-4 w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800/80 border border-gray-300 dark:border-gray-700 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors"
                        >
                            <X className="w-4 h-4" />
                        </button>

                        {/* Modal Header */}
                        <div className="text-center space-y-2">
                            <div className="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center text-white mx-auto shadow-lg shadow-red-500/25 ring-4 ring-red-500/10">
                                <CreditCard className="w-7 h-7" />
                            </div>
                            <h3 className="text-xl font-black font-display text-gray-900 dark:text-white">
                                Top-up Wallet Balance
                            </h3>
                            <p className="text-xs text-gray-600 dark:text-gray-400">
                                Minimum: <b>৳{minDeposit}</b> · Maximum: <b>৳{maxDeposit.toLocaleString()}</b>
                            </p>
                        </div>

                        {/* Fast Quick Amount Selector Pills */}
                        <div className="space-y-1.5">
                            <label className="text-[11px] font-bold text-gray-500 uppercase tracking-wider block">
                                Quick Select Amount
                            </label>
                            <div className="grid grid-cols-3 gap-2">
                                {quickAmounts.map((amt) => {
                                    const isSelected = String(data.amount) === String(amt);
                                    return (
                                        <button
                                            key={amt}
                                            type="button"
                                            onClick={() => setData('amount', String(amt))}
                                            className={`py-2.5 px-1 rounded-xl text-xs font-black font-mono transition-all border ${
                                                isSelected
                                                    ? 'bg-red-500 text-white border-red-500 shadow-md shadow-red-500/30 scale-[1.02]'
                                                    : 'bg-gray-100 dark:bg-gray-900/90 text-gray-800 dark:text-gray-200 border-gray-200 dark:border-gray-800 hover:border-red-500/30'
                                            }`}
                                        >
                                            ৳ {amt.toLocaleString()}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>

                        {/* Custom Amount Form */}
                        <form onSubmit={handleDeposit} className="space-y-4">
                            <div className="space-y-1.5">
                                <label className="text-[11px] font-bold text-gray-500 uppercase tracking-wider block">
                                    Custom Amount (৳ BDT)
                                </label>
                                <div className="relative">
                                    <span className="absolute left-4 top-1/2 -translate-y-1/2 text-red-500 font-black text-lg">
                                        ৳
                                    </span>
                                    <input
                                        type="number"
                                        min={minDeposit}
                                        max={maxDeposit}
                                        step="1"
                                        value={data.amount}
                                        onChange={(e) => setData('amount', e.target.value)}
                                        placeholder={`Enter amount (${minDeposit} - ${maxDeposit.toLocaleString()})`}
                                        className="w-full pl-10 pr-4 py-3.5 rounded-xl bg-gray-50 dark:bg-black/60 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white text-base font-mono font-bold placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500/40 focus:border-red-500/50 shadow-inner transition-all"
                                    />
                                </div>
                                {errors.amount && (
                                    <p className="text-xs text-red-500 font-semibold">{errors.amount}</p>
                                )}
                            </div>

                            {/* Supported Payment Logos strip */}
                            <div className="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                                <span className="font-semibold text-[11px]">Instant Auto Gateway:</span>
                                <div className="flex items-center gap-1.5 font-bold text-[11px] text-gray-800 dark:text-gray-200">
                                    <span className="px-1.5 py-0.5 rounded bg-pink-500/10 text-pink-600 dark:text-pink-400 border border-pink-500/20">bKash</span>
                                    <span className="px-1.5 py-0.5 rounded bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20">Nagad</span>
                                    <span className="px-1.5 py-0.5 rounded bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">Rocket</span>
                                </div>
                            </div>

                            <button
                                type="submit"
                                disabled={processing || !data.amount}
                                className="w-full py-3.5 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] hover:from-[#ff411a] hover:to-[#8f0909] text-white font-black text-sm shadow-lg shadow-red-500/25 flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                            >
                                {processing ? (
                                    <>
                                        <Loader2 className="w-4 h-4 animate-spin" />
                                        <span>Connecting to Gateway...</span>
                                    </>
                                ) : (
                                    <>
                                        <Sparkles className="w-4 h-4" />
                                        <span>Proceed to Payment</span>
                                    </>
                                )}
                            </button>

                            <p className="text-[10px] text-center text-gray-500 leading-tight">
                                You will be redirected to our secure automated payment gateway to complete your payment instantly.
                            </p>
                        </form>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
