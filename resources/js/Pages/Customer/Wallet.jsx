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
} from 'lucide-react';

const TYPE_ICONS = {
    credit: ArrowDownLeft,
    debit: ArrowUpRight,
};

export default function WalletPage({ balance, formatted_balance, minDeposit, maxDeposit, transactions }) {
    const [showDepositModal, setShowDepositModal] = useState(false);

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

    return (
        <AppLayout title="My Wallet">
            <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

                {/* Header */}
                <div className="space-y-1">
                    <Link
                        href="/dashboard"
                        className="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 hover:text-red-400 transition-colors mb-2"
                    >
                        <ArrowLeft className="w-3.5 h-3.5" />
                        <span>Back to Dashboard</span>
                    </Link>
                    <h1 className="text-2xl sm:text-3xl font-black font-display text-gray-900 dark:text-white flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/30 flex items-center justify-center">
                            <Wallet className="w-5 h-5 text-red-400" />
                        </div>
                        My Wallet
                    </h1>
                </div>

                {/* Balance Card */}
                <div className="glass-panel p-6 sm:p-8 rounded-3xl border border-red-500/20 bg-gradient-to-br from-red-50 dark:from-red-950/50 via-slate-100 dark:via-slate-900 to-gray-100 dark:to-black/50 relative overflow-hidden">
                    {/* Ambient glow */}
                    <div className="absolute -top-20 -right-20 w-60 h-60 bg-red-500/5 rounded-full blur-3xl" />
                    <div className="absolute -bottom-20 -left-20 w-40 h-40 bg-[#f82803]/5 rounded-full blur-3xl" />

                    <div className="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                        <div className="space-y-2">
                            <span className="text-xs text-gray-600 dark:text-gray-400 font-mono uppercase tracking-wider">
                                Available Balance
                            </span>
                            <div className="text-4xl sm:text-5xl font-black font-mono text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-red-600">
                                {formatted_balance}
                            </div>
                            <div className="flex items-center gap-1.5 text-[11px] text-gray-500">
                                <Info className="w-3 h-3" />
                                <span>Use your balance for instant checkout</span>
                            </div>
                        </div>

                        <button
                            onClick={() => setShowDepositModal(true)}
                            className="px-6 py-3 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] hover:from-[#ff411a] hover:to-[#8f0909] text-white font-bold text-sm shadow-lg shadow-red-500/25 flex items-center gap-2 hover:scale-105 transition-all self-start"
                        >
                            <PlusCircle className="w-5 h-5" />
                            <span>Add Balance</span>
                        </button>
                    </div>
                </div>

                {/* Transaction History */}
                <div className="space-y-4">
                    <div className="flex items-center justify-between">
                        <h2 className="text-lg font-bold text-gray-900 dark:text-white font-display flex items-center gap-2">
                            <TrendingUp className="w-5 h-5 text-red-400" />
                            <span>Transaction History</span>
                        </h2>
                        <span className="text-xs text-gray-500 font-mono">
                            {transactions.total} total
                        </span>
                    </div>

                    {transactions.data.length > 0 ? (
                        <div className="space-y-2">
                            {transactions.data.map((txn) => {
                                const isCredit = txn.direction === 'credit';
                                const Icon = isCredit ? ArrowDownLeft : ArrowUpRight;

                                return (
                                    <div
                                        key={txn.id}
                                        className="glass-panel p-4 rounded-xl border border-gray-200 dark:border-gray-800/80 flex items-center justify-between gap-4 hover:border-gray-300 dark:border-gray-700/80 transition-colors"
                                    >
                                        <div className="flex items-center gap-3 min-w-0">
                                            <div className={`w-9 h-9 rounded-xl flex items-center justify-center shrink-0 ${
                                                isCredit
                                                    ? 'bg-emerald-500/10 border border-emerald-500/30'
                                                    : 'bg-red-500/10 border border-red-500/30'
                                            }`}>
                                                <Icon className={`w-4 h-4 ${isCredit ? 'text-emerald-400' : 'text-red-400'}`} />
                                            </div>
                                            <div className="min-w-0 space-y-0.5">
                                                <div className="flex items-center gap-2">
                                                    <span className="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                        {txn.description || txn.type_label}
                                                    </span>
                                                    <span className="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-mono shrink-0">
                                                        {txn.type_label}
                                                    </span>
                                                </div>
                                                <div className="flex items-center gap-3 text-[11px] text-gray-500 font-mono">
                                                    <span className="flex items-center gap-1">
                                                        <Clock className="w-3 h-3" />
                                                        {txn.created_at}
                                                    </span>
                                                    {txn.reference_id && (
                                                        <span className="truncate max-w-[120px]">
                                                            Ref: {txn.reference_id}
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                        </div>

                                        <div className="text-right shrink-0 space-y-0.5">
                                            <span className={`text-sm font-bold font-mono ${
                                                isCredit ? 'text-emerald-400' : 'text-red-400'
                                            }`}>
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
                        <div className="glass-panel rounded-2xl border border-gray-200 dark:border-gray-800 text-center py-16 space-y-4">
                            <div className="w-16 h-16 rounded-2xl bg-red-500/10 border border-red-500/30 flex items-center justify-center mx-auto">
                                <Banknote className="w-8 h-8 text-red-400" />
                            </div>
                            <div className="space-y-1">
                                <p className="text-gray-900 dark:text-white font-semibold">No transactions yet</p>
                                <p className="text-xs text-gray-500">Add balance to your wallet to get started.</p>
                            </div>
                        </div>
                    )}

                    <Pagination links={transactions.links} />
                </div>
            </div>

            {/* Deposit Modal */}
            {showDepositModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
                    {/* Backdrop */}
                    <div
                        className="absolute inset-0 bg-black/70 backdrop-blur-sm"
                        onClick={() => setShowDepositModal(false)}
                    />

                    {/* Modal */}
                    <div className="relative w-full max-w-md glass-panel rounded-3xl border border-red-500/20 p-6 sm:p-8 space-y-6 animate-fadeIn">
                        {/* Close */}
                        <button
                            onClick={() => setShowDepositModal(false)}
                            className="absolute top-4 right-4 w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800/80 border border-gray-300 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white transition-colors"
                        >
                            <X className="w-4 h-4" />
                        </button>

                        {/* Header */}
                        <div className="text-center space-y-2">
                            <div className="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#f82803]/20 to-black/20 border border-red-500/30 flex items-center justify-center mx-auto">
                                <CreditCard className="w-7 h-7 text-red-400" />
                            </div>
                            <h3 className="text-xl font-black font-display text-gray-900 dark:text-white">Add Balance</h3>
                            <p className="text-xs text-gray-600 dark:text-gray-400">
                                Min: ৳{minDeposit} · Max: ৳{maxDeposit.toLocaleString()}
                            </p>
                        </div>

                        {/* Quick Amount Grid */}
                        <div className="grid grid-cols-3 gap-2">
                            {quickAmounts.map((amt) => (
                                <button
                                    key={amt}
                                    onClick={() => setData('amount', String(amt))}
                                    className={`py-2.5 rounded-xl text-xs font-bold font-mono transition-all border ${
                                        String(data.amount) === String(amt)
                                            ? 'bg-red-500/20 text-red-400 border-red-500/40 scale-105 shadow-lg shadow-red-500/10'
                                            : 'bg-white dark:bg-gray-900/80 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:border-gray-700 hover:text-gray-900 dark:text-white'
                                    }`}
                                >
                                    ৳ {amt.toLocaleString()}
                                </button>
                            ))}
                        </div>

                        {/* Custom Amount Input */}
                        <form onSubmit={handleDeposit} className="space-y-4">
                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold text-gray-600 dark:text-gray-400">Custom Amount (৳)</label>
                                <div className="relative">
                                    <span className="absolute left-4 top-1/2 -translate-y-1/2 text-red-400 font-bold text-lg">৳</span>
                                    <input
                                        type="number"
                                        min={minDeposit}
                                        max={maxDeposit}
                                        step="1"
                                        value={data.amount}
                                        onChange={(e) => setData('amount', e.target.value)}
                                        placeholder={`${minDeposit} - ${maxDeposit.toLocaleString()}`}
                                        className="w-full pl-10 pr-4 py-3.5 rounded-xl bg-gray-100 dark:bg-black/50 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white text-lg font-mono font-bold placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500/40 focus:border-red-500/50 transition-all"
                                    />
                                </div>
                                {errors.amount && (
                                    <p className="text-xs text-red-400 font-semibold">{errors.amount}</p>
                                )}
                            </div>

                            <button
                                type="submit"
                                disabled={processing || !data.amount}
                                className="w-full py-3.5 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] hover:from-[#ff411a] hover:to-[#8f0909] text-white font-bold text-sm shadow-lg shadow-red-500/25 flex items-center justify-center gap-2 hover:scale-[1.02] transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                            >
                                {processing ? (
                                    <>
                                        <Loader2 className="w-5 h-5 animate-spin" />
                                        <span>Processing...</span>
                                    </>
                                ) : (
                                    <>
                                        <Sparkles className="w-5 h-5" />
                                        <span>Proceed to Payment</span>
                                    </>
                                )}
                            </button>

                            <p className="text-[10px] text-center text-gray-500">
                                You will be redirected to our payment gateway to complete the deposit.
                            </p>
                        </form>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
