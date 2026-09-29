import React, { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import {
    Users,
    ArrowLeft,
    Copy,
    Check,
    Gift,
    Link2,
    Percent,
    UserPlus,
    Calendar,
    Sparkles,
    Share2,
    Trophy,
} from 'lucide-react';

export default function Referrals({ referralCode, referralUrl, referralRate, referredCount, referredUsers }) {
    const [copiedLink, setCopiedLink] = useState(false);
    const [copiedCode, setCopiedCode] = useState(false);

    const handleCopyLink = () => {
        navigator.clipboard.writeText(referralUrl);
        setCopiedLink(true);
        setTimeout(() => setCopiedLink(false), 2500);
    };

    const handleCopyCode = () => {
        navigator.clipboard.writeText(referralCode);
        setCopiedCode(true);
        setTimeout(() => setCopiedCode(false), 2500);
    };

    return (
        <AppLayout title="Referrals">
            <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

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
                        <div className="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center">
                            <Users className="w-5 h-5 text-purple-400" />
                        </div>
                        Referral Program
                    </h1>
                    <p className="text-xs text-gray-500">
                        Invite friends and earn {referralRate}% commission on their purchases!
                    </p>
                </div>

                {/* Referral Invite Banner */}
                <div className="glass-panel p-6 sm:p-8 rounded-3xl border border-purple-500/20 bg-gradient-to-br from-purple-50 dark:from-purple-950/40 via-slate-100 dark:via-slate-900 to-gray-100 dark:to-black/40 relative overflow-hidden">
                    {/* Ambient glow */}
                    <div className="absolute -top-16 -right-16 w-48 h-48 bg-purple-500/5 rounded-full blur-3xl" />
                    <div className="absolute -bottom-16 -left-16 w-40 h-40 bg-red-500/5 rounded-full blur-3xl" />

                    <div className="relative z-10 space-y-6">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500/20 to-red-500/20 border border-purple-500/30 flex items-center justify-center">
                                <Gift className="w-6 h-6 text-purple-400" />
                            </div>
                            <div>
                                <h2 className="text-lg font-black text-gray-900 dark:text-white font-display">
                                    Earn {referralRate}% Commission
                                </h2>
                                <p className="text-xs text-gray-600 dark:text-gray-400">
                                    Share your link and earn when your friends make their first purchase
                                </p>
                            </div>
                        </div>

                        {/* Stats Row */}
                        <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <div className="p-4 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-200 dark:border-gray-800/80 space-y-1">
                                <div className="flex items-center gap-1.5">
                                    <UserPlus className="w-4 h-4 text-red-400" />
                                    <span className="text-[10px] text-gray-600 dark:text-gray-400 font-mono uppercase">Referred</span>
                                </div>
                                <span className="text-2xl font-black font-mono text-red-400">{referredCount}</span>
                            </div>
                            <div className="p-4 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-200 dark:border-gray-800/80 space-y-1">
                                <div className="flex items-center gap-1.5">
                                    <Percent className="w-4 h-4 text-purple-400" />
                                    <span className="text-[10px] text-gray-600 dark:text-gray-400 font-mono uppercase">Commission</span>
                                </div>
                                <span className="text-2xl font-black font-mono text-purple-400">{referralRate}%</span>
                            </div>
                            <div className="hidden sm:block p-4 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-200 dark:border-gray-800/80 space-y-1">
                                <div className="flex items-center gap-1.5">
                                    <Trophy className="w-4 h-4 text-amber-400" />
                                    <span className="text-[10px] text-gray-600 dark:text-gray-400 font-mono uppercase">Level</span>
                                </div>
                                <span className="text-2xl font-black font-mono text-amber-400">
                                    {referredCount >= 20 ? 'Pro' : referredCount >= 5 ? 'Active' : 'Starter'}
                                </span>
                            </div>
                        </div>

                        {/* Referral Code */}
                        <div className="space-y-3">
                            <label className="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                <Link2 className="w-3.5 h-3.5 text-red-400" />
                                Your Referral Code
                            </label>
                            <div className="flex items-center gap-2">
                                <div className="flex-1 px-4 py-3 bg-gray-100 dark:bg-black/50 border border-purple-500/20 rounded-xl font-mono text-lg text-purple-300 font-bold tracking-widest select-all">
                                    {referralCode}
                                </div>
                                <button
                                    onClick={handleCopyCode}
                                    className={`px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 transition-all shrink-0 ${
                                        copiedCode
                                            ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40'
                                            : 'bg-purple-500/10 hover:bg-purple-500/20 text-purple-400 border border-purple-500/30 hover:scale-105'
                                    }`}
                                >
                                    {copiedCode ? (
                                        <>
                                            <Check className="w-4 h-4" />
                                            <span>Copied!</span>
                                        </>
                                    ) : (
                                        <>
                                            <Copy className="w-4 h-4" />
                                            <span>Copy</span>
                                        </>
                                    )}
                                </button>
                            </div>
                        </div>

                        {/* Referral Link */}
                        <div className="space-y-3">
                            <label className="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                <Share2 className="w-3.5 h-3.5 text-red-400" />
                                Your Referral Link
                            </label>
                            <div className="flex items-center gap-2">
                                <div className="flex-1 px-4 py-3 bg-gray-100 dark:bg-black/50 border border-red-500/20 rounded-xl font-mono text-xs sm:text-sm text-red-300/80 select-all truncate">
                                    {referralUrl}
                                </div>
                                <button
                                    onClick={handleCopyLink}
                                    className={`px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 transition-all shrink-0 ${
                                        copiedLink
                                            ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40'
                                            : 'bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 hover:scale-105'
                                    }`}
                                >
                                    {copiedLink ? (
                                        <>
                                            <Check className="w-4 h-4" />
                                            <span>Copied!</span>
                                        </>
                                    ) : (
                                        <>
                                            <Copy className="w-4 h-4" />
                                            <span>Copy</span>
                                        </>
                                    )}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Referred Users List */}
                <div className="space-y-4">
                    <h2 className="text-lg font-bold text-gray-900 dark:text-white font-display flex items-center gap-2">
                        <UserPlus className="w-5 h-5 text-red-400" />
                        <span>Referred Users</span>
                        <span className="text-xs text-gray-500 font-mono font-normal">({referredCount})</span>
                    </h2>

                    {referredUsers.length > 0 ? (
                        <div className="space-y-2">
                            {referredUsers.map((user, idx) => (
                                <div
                                    key={idx}
                                    className="glass-panel p-4 rounded-xl border border-gray-200 dark:border-gray-800/80 flex items-center justify-between gap-4 hover:border-gray-300 dark:border-gray-700/80 transition-colors"
                                >
                                    <div className="flex items-center gap-3">
                                        <div className="w-9 h-9 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center">
                                            <span className="text-sm font-bold text-purple-400">
                                                {user.name.charAt(0).toUpperCase()}
                                            </span>
                                        </div>
                                        <div>
                                            <span className="text-sm font-semibold text-gray-900 dark:text-white">{user.name}</span>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-1.5 text-[11px] text-gray-500 font-mono shrink-0">
                                        <Calendar className="w-3 h-3" />
                                        <span>{user.joined_at}</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="glass-panel rounded-2xl border border-gray-200 dark:border-gray-800 text-center py-16 space-y-4">
                            <div className="w-16 h-16 rounded-2xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center mx-auto">
                                <UserPlus className="w-8 h-8 text-purple-400" />
                            </div>
                            <div className="space-y-1">
                                <p className="text-gray-900 dark:text-white font-semibold">No referrals yet</p>
                                <p className="text-xs text-gray-500">
                                    Share your referral link with friends to start earning commissions!
                                </p>
                            </div>
                            <button
                                onClick={handleCopyLink}
                                className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-500 to-[#730505] text-gray-900 dark:text-white font-bold text-sm hover:scale-105 transition-transform shadow-lg shadow-purple-500/20"
                            >
                                <Sparkles className="w-4 h-4" />
                                Share Your Link
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
