import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Home, Layers, Wallet, Key, User } from 'lucide-react';

export default function MobileBottomNav() {
    const { url } = usePage();
    const { auth } = usePage().props;
    const customer = auth?.customer;

    const isActive = (path) => {
        if (path === '/' && url === '/') return true;
        if (path !== '/' && url.startsWith(path)) return true;
        return false;
    };

    return (
        <div className="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white dark:bg-[#0d1322]/95 backdrop-blur-lg border-t border-gray-200 dark:border-gray-800/90 py-2 px-3 safe-area-bottom">
            <div className="grid grid-cols-5 items-center justify-around gap-1 text-center">
                {/* Home */}
                <Link 
                    href="/" 
                    className={`flex flex-col items-center gap-1 py-1 transition-colors ${isActive('/') && !url.includes('#') ? 'text-red-400 font-bold' : 'text-gray-600 dark:text-gray-400'}`}
                >
                    <Home className="w-5 h-5" />
                    <span className="text-[10px]">Home</span>
                </Link>

                {/* Products */}
                <a 
                    href="/#products" 
                    className="flex flex-col items-center gap-1 py-1 text-gray-600 dark:text-gray-400 hover:text-red-400 transition-colors"
                >
                    <Layers className="w-5 h-5" />
                    <span className="text-[10px]">Panels</span>
                </a>

                {/* Wallet (Highlighted Floating Button) */}
                <Link 
                    href={customer ? "/wallet" : "/login"} 
                    className="flex flex-col items-center -mt-4 group"
                >
                    <div className="w-12 h-12 rounded-full bg-gradient-to-br from-[#f82803] to-[#730505] flex items-center justify-center text-black shadow-lg shadow-red-500/30 group-active:scale-95 transition-transform border-2 border-[#0d1322]">
                        <Wallet className="w-6 h-6 text-black" />
                    </div>
                    <span className={`text-[10px] mt-0.5 ${isActive('/wallet') ? 'text-red-400 font-bold' : 'text-gray-600 dark:text-gray-400'}`}>
                        Wallet
                    </span>
                </Link>

                {/* My Keys */}
                <Link 
                    href={customer ? "/keys" : "/login"} 
                    className={`flex flex-col items-center gap-1 py-1 transition-colors ${isActive('/keys') ? 'text-red-400 font-bold' : 'text-gray-600 dark:text-gray-400'}`}
                >
                    <Key className="w-5 h-5" />
                    <span className="text-[10px]">My Keys</span>
                </Link>

                {/* Profile / Login */}
                <Link 
                    href={customer ? "/dashboard" : "/login"} 
                    className={`flex flex-col items-center gap-1 py-1 transition-colors ${isActive('/dashboard') || isActive('/login') ? 'text-red-400 font-bold' : 'text-gray-600 dark:text-gray-400'}`}
                >
                    <User className="w-5 h-5" />
                    <span className="text-[10px]">{customer ? 'Account' : 'Login'}</span>
                </Link>
            </div>
        </div>
    );
}
