import React, { useState } from 'react';
import { useForm, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { ShoppingBag, Lock, Mail, Eye, EyeOff, LogIn, ArrowRight, ShieldCheck } from 'lucide-react';

export default function Login() {
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: true,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/login', {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AppLayout title="Customer Login">
            <div className="min-h-[75vh] flex items-center justify-center px-4 py-12">
                <div className="w-full max-w-md space-y-6">
                    
                    {/* Header */}
                    <div className="text-center space-y-2">
                        <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#f82803] to-[#730505] mx-auto flex items-center justify-center text-black shadow-lg shadow-red-500/20">
                            <ShoppingBag className="w-6 h-6 text-black" />
                        </div>
                        <h1 className="text-2xl sm:text-3xl font-black font-display text-gray-900 dark:text-white">
                            Welcome Back
                        </h1>
                        <p className="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                            Log in to access your purchased license keys, panels and wallet.
                        </p>
                    </div>

                    {/* Form Card */}
                    <div className="glass-panel p-6 sm:p-8 rounded-2xl border border-gray-200 dark:border-gray-800/80 shadow-2xl space-y-5">
                        <form onSubmit={handleSubmit} className="space-y-4">
                            {/* Email or WhatsApp Input */}
                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    Email Address or WhatsApp Number
                                </label>
                                <div className="relative">
                                    <input
                                        type="text"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        placeholder="your@email.com or +8801700000000"
                                        className={`w-full pl-10 pr-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border text-gray-900 dark:text-white placeholder-gray-500 text-sm focus:outline-none transition-colors ${
                                            errors.email ? 'border-red-500 focus:border-red-500' : 'border-gray-300 dark:border-gray-700 focus:border-red-500'
                                        }`}
                                        required
                                        autoFocus
                                    />
                                    <Mail className="w-4 h-4 text-gray-600 dark:text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                                </div>
                                {errors.email && (
                                    <p className="text-xs text-red-400">{errors.email}</p>
                                )}
                            </div>

                            {/* Password Input */}
                            <div className="space-y-1.5">
                                <div className="flex items-center justify-between">
                                    <label className="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                        Password
                                    </label>
                                </div>
                                <div className="relative">
                                    <input
                                        type={showPassword ? 'text' : 'password'}
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        placeholder="Enter your password"
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
                                {errors.password && (
                                    <p className="text-xs text-red-400">{errors.password}</p>
                                )}
                            </div>

                            {/* Remember Me Checkbox */}
                            <div className="flex items-center justify-between text-xs pt-1">
                                <label className="flex items-center gap-2 cursor-pointer text-gray-700 dark:text-gray-300">
                                    <input
                                        type="checkbox"
                                        checked={data.remember}
                                        onChange={(e) => setData('remember', e.target.checked)}
                                        className="rounded border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-[#f82803] focus:ring-red-500/20"
                                    />
                                    <span>Remember my device</span>
                                </label>
                            </div>

                            {/* Submit Button */}
                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full py-3 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] hover:from-[#ff411a] hover:to-[#8f0909] text-white font-bold text-sm tracking-wide shadow-lg shadow-red-500/20 transition-all hover:scale-[1.02] active:scale-95 disabled:opacity-50 flex items-center justify-center gap-2"
                            >
                                <LogIn className="w-4 h-4" />
                                <span>{processing ? 'Signing In...' : 'Sign In to Account'}</span>
                            </button>
                        </form>

                        {/* Register Link */}
                        <div className="pt-4 border-t border-gray-200 dark:border-gray-800 text-center text-xs text-gray-600 dark:text-gray-400">
                            Don't have an account?{' '}
                            <Link href="/register" className="text-red-400 font-semibold hover:underline">
                                Register now
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
