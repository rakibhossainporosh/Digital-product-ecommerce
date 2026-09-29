import React, { useState } from 'react';
import { useForm, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { ShoppingBag, Lock, Mail, Eye, EyeOff, UserPlus, Phone, User, Gift, ShieldCheck } from 'lucide-react';

export default function Register({ referralCode }) {
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        whatsapp_number: '',
        password: '',
        password_confirmation: '',
        referral_code: referralCode || '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/register', {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AppLayout title="Create Customer Account">
            <div className="min-h-[75vh] flex items-center justify-center px-4 py-12">
                <div className="w-full max-w-md space-y-6">
                    
                    {/* Header */}
                    <div className="text-center space-y-2">
                        <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#f82803] to-[#730505] mx-auto flex items-center justify-center text-black shadow-lg shadow-red-500/20">
                            <ShoppingBag className="w-6 h-6 text-black" />
                        </div>
                        <h1 className="text-2xl sm:text-3xl font-black font-display text-gray-900 dark:text-white">
                            Create Your Account
                        </h1>
                        <p className="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                            Join Bangladesh's premier instant game panel & key platform.
                        </p>
                    </div>

                    {/* Form Card */}
                    <div className="glass-panel p-6 sm:p-8 rounded-2xl border border-gray-200 dark:border-gray-800/80 shadow-2xl space-y-5">
                        <form onSubmit={handleSubmit} className="space-y-4">
                            
                            {/* Full Name */}
                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    Full Name <span className="text-red-400">*</span>
                                </label>
                                <div className="relative">
                                    <input
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="John Doe"
                                        className={`w-full pl-10 pr-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border text-gray-900 dark:text-white placeholder-gray-500 text-sm focus:outline-none transition-colors ${
                                            errors.name ? 'border-red-500 focus:border-red-500' : 'border-gray-300 dark:border-gray-700 focus:border-red-500'
                                        }`}
                                        required
                                        autoFocus
                                    />
                                    <User className="w-4 h-4 text-gray-600 dark:text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                                </div>
                                {errors.name && <p className="text-xs text-red-400">{errors.name}</p>}
                            </div>

                            {/* Email */}
                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    Email Address <span className="text-red-400">*</span>
                                </label>
                                <div className="relative">
                                    <input
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        placeholder="john@example.com"
                                        className={`w-full pl-10 pr-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border text-gray-900 dark:text-white placeholder-gray-500 text-sm focus:outline-none transition-colors ${
                                            errors.email ? 'border-red-500 focus:border-red-500' : 'border-gray-300 dark:border-gray-700 focus:border-red-500'
                                        }`}
                                        required
                                    />
                                    <Mail className="w-4 h-4 text-gray-600 dark:text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                                </div>
                                {errors.email && <p className="text-xs text-red-400">{errors.email}</p>}
                            </div>

                            {/* WhatsApp Number */}
                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    WhatsApp Number <span className="text-red-400">*</span>
                                </label>
                                <div className="relative">
                                    <input
                                        type="text"
                                        value={data.whatsapp_number}
                                        onChange={(e) => setData('whatsapp_number', e.target.value)}
                                        placeholder="+8801700000000"
                                        className={`w-full pl-10 pr-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border text-gray-900 dark:text-white placeholder-gray-500 text-sm focus:outline-none transition-colors ${
                                            errors.whatsapp_number ? 'border-red-500 focus:border-red-500' : 'border-gray-300 dark:border-gray-700 focus:border-red-500'
                                        }`}
                                        required
                                    />
                                    <Phone className="w-4 h-4 text-gray-600 dark:text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                                </div>
                                {errors.whatsapp_number && <p className="text-xs text-red-400">{errors.whatsapp_number}</p>}
                            </div>

                            {/* Password */}
                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    Password <span className="text-red-400">*</span>
                                </label>
                                <div className="relative">
                                    <input
                                        type={showPassword ? 'text' : 'password'}
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        placeholder="Min 6 characters"
                                        className={`w-full pl-10 pr-10 py-2.5 rounded-xl bg-white dark:bg-gray-900 border text-gray-900 dark:text-white placeholder-gray-500 text-sm focus:outline-none transition-colors ${
                                            errors.password ? 'border-red-500 focus:border-red-500' : 'border-gray-300 dark:border-gray-700 focus:border-red-500'
                                        }`}
                                        required
                                    />
                                    <Lock className="w-4 h-4 text-gray-600 dark:text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                                    <button
                                        type="button"
                                        onClick={() => setShowPassword(!showPassword)}
                                        className="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white absolute right-3.5 top-1/2 -translate-y-1/2"
                                    >
                                        {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                    </button>
                                </div>
                                {errors.password && <p className="text-xs text-red-400">{errors.password}</p>}
                            </div>

                            {/* Confirm Password */}
                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    Confirm Password <span className="text-red-400">*</span>
                                </label>
                                <div className="relative">
                                    <input
                                        type={showPassword ? 'text' : 'password'}
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        placeholder="Repeat your password"
                                        className="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-500 text-sm focus:outline-none focus:border-red-500"
                                        required
                                    />
                                    <Lock className="w-4 h-4 text-gray-600 dark:text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                                </div>
                            </div>

                            {/* Referral Code (Optional) */}
                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold text-gray-600 dark:text-gray-400 flex items-center gap-1">
                                    <Gift className="w-3.5 h-3.5 text-amber-400" />
                                    <span>Referral Code (Optional)</span>
                                </label>
                                <input
                                    type="text"
                                    value={data.referral_code}
                                    onChange={(e) => setData('referral_code', e.target.value.toUpperCase())}
                                    placeholder="REF12345"
                                    className="w-full px-4 py-2 rounded-xl bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-500 text-xs font-mono uppercase focus:outline-none focus:border-red-500"
                                />
                                {errors.referral_code && <p className="text-xs text-red-400">{errors.referral_code}</p>}
                            </div>

                            {/* Submit Button */}
                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full py-3 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] hover:from-[#ff411a] hover:to-[#8f0909] text-white font-bold text-sm tracking-wide shadow-lg shadow-red-500/20 transition-all hover:scale-[1.02] active:scale-95 disabled:opacity-50 flex items-center justify-center gap-2 pt-2"
                            >
                                <UserPlus className="w-4 h-4" />
                                <span>{processing ? 'Creating Account...' : 'Create Account'}</span>
                            </button>
                        </form>

                        {/* Login Link */}
                        <div className="pt-4 border-t border-gray-200 dark:border-gray-800 text-center text-xs text-gray-600 dark:text-gray-400">
                            Already have an account?{' '}
                            <Link href="/login" className="text-red-400 font-semibold hover:underline">
                                Log in
                            </Link>
                        </div>
                    </div>

                    <div className="text-center text-xs text-gray-500 flex items-center justify-center gap-1">
                        <ShieldCheck className="w-4 h-4 text-emerald-400" />
                        <span>Protected by 256-bit SSL encryption</span>
                    </div>

                </div>
            </div>
        </AppLayout>
    );
}
