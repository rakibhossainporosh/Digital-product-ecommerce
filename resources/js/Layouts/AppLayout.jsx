import React, { useState, useEffect } from 'react';
import { usePage, Head, router } from '@inertiajs/react';
import Navbar from '../Components/Navbar';
import MarqueeNotice from '../Components/MarqueeNotice';
import Footer from '../Components/Footer';
import MobileBottomNav from '../Components/MobileBottomNav';
import Chatbot from '../Components/Chatbot';
import { CheckCircle2, AlertCircle, X, Info } from 'lucide-react';

export default function AppLayout({ title, children, notice }) {
    const { flash, settings } = usePage().props;
    const [toast, setToast] = useState(null);

    useEffect(() => {
        if (flash?.success) {
            setToast({ type: 'success', message: flash.success, id: Date.now() });
        } else if (flash?.error) {
            setToast({ type: 'error', message: flash.error, id: Date.now() });
        } else if (flash?.info) {
            setToast({ type: 'info', message: flash.info, id: Date.now() });
        }
    }, [flash]);

    useEffect(() => {
        if (toast) {
            const timer = setTimeout(() => {
                setToast(null);
            }, 4500);
            return () => clearTimeout(timer);
        }
    }, [toast?.id]);

    return (
        <div className="min-h-screen flex flex-col bg-gray-50 dark:bg-[#0b0f19] text-gray-900 dark:text-gray-100 font-sans selection:bg-red-500 selection:text-black relative">
            <Head title={title ? `${title} - ${settings?.app_name || 'Panel Sell'}` : settings?.app_name} />

            {/* Glowing background ambient lights */}
            <div className="fixed top-0 left-1/4 w-96 h-96 bg-red-500/10 rounded-full blur-3xl pointer-events-none -z-10" />
            <div className="fixed top-1/3 right-10 w-80 h-80 bg-rose-600/10 rounded-full blur-3xl pointer-events-none -z-10" />

            {/* Top Announcement Bar */}
            {settings?.top_bar_notice_enabled !== false && (
                <MarqueeNotice 
                    notice={notice || settings?.top_bar_notice_text} 
                    label={settings?.top_bar_notice_label} 
                />
            )}

            {/* Main Sticky Navbar */}
            <Navbar />

            {/* Floating Toast Notification */}
            {toast && (
                <div className="fixed top-20 left-3 right-3 sm:left-auto sm:right-6 sm:w-full sm:max-w-md z-50 animate-toast-slide-in">
                    <div className={`relative overflow-hidden rounded-2xl p-4 shadow-2xl border backdrop-blur-xl flex items-start gap-3.5 transition-all ${
                        toast.type === 'success' 
                            ? 'bg-white/95 dark:bg-[#0c1424]/95 border-emerald-500/30 shadow-emerald-500/10 dark:shadow-emerald-950/50' 
                            : toast.type === 'error'
                            ? 'bg-white/95 dark:bg-[#1a0a0f]/95 border-rose-500/30 shadow-rose-500/10 dark:shadow-rose-950/50'
                            : 'bg-white/95 dark:bg-[#0a1224]/95 border-blue-500/30 shadow-blue-500/10 dark:shadow-blue-950/50'
                    }`}>
                        {/* Ambient glow behind icon */}
                        <div className={`absolute -top-6 -left-6 w-24 h-24 rounded-full blur-xl pointer-events-none ${
                            toast.type === 'success' ? 'bg-emerald-500/20' : toast.type === 'error' ? 'bg-rose-500/20' : 'bg-blue-500/20'
                        }`} />

                        {/* Icon badge */}
                        <div className={`w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-md ${
                            toast.type === 'success' 
                                ? 'bg-gradient-to-br from-emerald-500 to-teal-600 shadow-emerald-500/25 ring-4 ring-emerald-500/10' 
                                : toast.type === 'error'
                                ? 'bg-gradient-to-br from-red-500 to-rose-600 shadow-rose-500/25 ring-4 ring-rose-500/10'
                                : 'bg-gradient-to-br from-blue-500 to-indigo-600 shadow-blue-500/25 ring-4 ring-blue-500/10'
                        }`}>
                            {toast.type === 'success' && <CheckCircle2 className="w-5 h-5 text-white" strokeWidth={2.5} />}
                            {toast.type === 'error' && <AlertCircle className="w-5 h-5 text-white" strokeWidth={2.5} />}
                            {toast.type === 'info' && <Info className="w-5 h-5 text-white" strokeWidth={2.5} />}
                        </div>

                        {/* Message content */}
                        <div className="flex-1 min-w-0 pr-1">
                            <div className="flex items-center gap-2 mb-1">
                                <span className={`text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border ${
                                    toast.type === 'success' 
                                        ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' 
                                        : toast.type === 'error'
                                        ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20'
                                        : 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20'
                                }`}>
                                    {toast.type === 'success' ? 'Success' : toast.type === 'error' ? 'Error' : 'Notice'}
                                </span>
                                <span className="text-[11px] text-gray-400 dark:text-gray-500">Just now</span>
                            </div>
                            <p className="text-sm font-semibold text-gray-900 dark:text-gray-100 leading-snug break-words">
                                {toast.message}
                            </p>
                        </div>

                        {/* Close button */}
                        <button 
                            onClick={() => setToast(null)}
                            className="text-gray-400 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 transition-colors shrink-0"
                            aria-label="Close notification"
                        >
                            <X className="w-4 h-4" />
                        </button>

                        {/* Countdown progress bar */}
                        <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-gray-200 dark:bg-gray-800 overflow-hidden">
                            <div className={`h-full animate-toast-progress ${
                                toast.type === 'success' 
                                    ? 'bg-gradient-to-r from-emerald-500 to-teal-400' 
                                    : toast.type === 'error'
                                    ? 'bg-gradient-to-r from-red-500 to-rose-500'
                                    : 'bg-gradient-to-r from-blue-500 to-indigo-500'
                            }`} />
                        </div>
                    </div>
                </div>
            )}

            {/* Main Page Body */}
            <main className="flex-1">
                {children}
            </main>

            {/* Footer */}
            <Footer />

            {/* Support Chatbot */}
            <Chatbot />

            {/* Mobile Bottom Floating App Bar */}
            <MobileBottomNav />
        </div>
    );
}
