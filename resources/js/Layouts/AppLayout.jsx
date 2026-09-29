import React, { useState, useEffect } from 'react';
import { usePage, Head, router } from '@inertiajs/react';
import Navbar from '../Components/Navbar';
import MarqueeNotice from '../Components/MarqueeNotice';
import Footer from '../Components/Footer';
import MobileBottomNav from '../Components/MobileBottomNav';
import { CheckCircle2, AlertCircle, X, Info } from 'lucide-react';
import PageSkeleton from '../Components/PageSkeleton';

export default function AppLayout({ title, children, notice }) {
    const { flash, settings } = usePage().props;
    const [toast, setToast] = useState(null);
    const [isLoading, setIsLoading] = useState(false);

    useEffect(() => {
        const removeStart = router.on('start', () => setIsLoading(true));
        const removeFinish = router.on('finish', () => setIsLoading(false));
        const removeException = router.on('exception', () => setIsLoading(false));
        
        return () => {
            removeStart();
            removeFinish();
            removeException();
        };
    }, []);

    useEffect(() => {
        if (flash?.success) {
            setToast({ type: 'success', message: flash.success });
        } else if (flash?.error) {
            setToast({ type: 'error', message: flash.error });
        } else if (flash?.info) {
            setToast({ type: 'info', message: flash.info });
        }
    }, [flash]);

    return (
        <div className="min-h-screen flex flex-col bg-gray-50 dark:bg-[#0b0f19] text-gray-900 dark:text-gray-100 font-sans selection:bg-red-500 selection:text-black relative">
            <Head title={title ? `${title} - ${settings?.app_name || 'Panel Sell'}` : settings?.app_name} />

            {/* Glowing background ambient lights */}
            <div className="fixed top-0 left-1/4 w-96 h-96 bg-red-500/10 rounded-full blur-3xl pointer-events-none -z-10" />
            <div className="fixed top-1/3 right-10 w-80 h-80 bg-rose-600/10 rounded-full blur-3xl pointer-events-none -z-10" />

            {/* Top Announcement Bar */}
            <MarqueeNotice notice={notice} />

            {/* Main Sticky Navbar */}
            <Navbar />

            {/* Floating Toast Notification */}
            {toast && (
                <div className="fixed top-20 right-4 z-50 max-w-sm w-full animate-in slide-in-from-top-4 fade-in duration-300">
                    <div className={`p-4 rounded-xl shadow-2xl border flex items-start gap-3 backdrop-blur-md ${
                        toast.type === 'success' 
                            ? 'bg-emerald-950/90 border-emerald-500/40 text-emerald-100' 
                            : toast.type === 'error'
                            ? 'bg-red-950/90 border-red-500/40 text-red-100'
                            : 'bg-red-950/90 border-red-500/40 text-red-100'
                    }`}>
                        {toast.type === 'success' && <CheckCircle2 className="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" />}
                        {toast.type === 'error' && <AlertCircle className="w-5 h-5 text-red-400 shrink-0 mt-0.5" />}
                        {toast.type === 'info' && <Info className="w-5 h-5 text-red-400 shrink-0 mt-0.5" />}

                        <div className="flex-1 text-sm font-medium">
                            {toast.message}
                        </div>

                        <button 
                            onClick={() => setToast(null)}
                            className="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white p-0.5 rounded-lg"
                        >
                            <X className="w-4 h-4" />
                        </button>
                    </div>
                </div>
            )}

            {/* Main Page Body */}
            <main className="flex-1">
                {isLoading ? <PageSkeleton /> : children}
            </main>

            {/* Footer */}
            <Footer />

            {/* Mobile Bottom Floating App Bar */}
            <MobileBottomNav />
        </div>
    );
}
