import React, { useState, useEffect } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Home, Layers, Wallet, Key, User } from 'lucide-react';

export default function MobileBottomNav() {
    const { url } = usePage();
    const { auth } = usePage().props;
    const customer = auth?.customer;
    const [activeSection, setActiveSection] = useState('home');

    useEffect(() => {
        if (url.startsWith('/product') || url.includes('category=')) {
            setActiveSection('products');
            return;
        }

        const checkLocation = () => {
            if (url !== '/' && !url.startsWith('/?')) return;
            const hash = window.location.hash;
            if (hash === '#products') {
                setActiveSection('products');
                return;
            }

            const productsEl = document.getElementById('products');
            const scrollPos = window.scrollY + 140;

            if (productsEl && scrollPos >= productsEl.offsetTop) {
                setActiveSection('products');
            } else {
                setActiveSection('home');
            }
        };

        checkLocation();
        window.addEventListener('hashchange', checkLocation);
        window.addEventListener('scroll', checkLocation, { passive: true });

        return () => {
            window.removeEventListener('hashchange', checkLocation);
            window.removeEventListener('scroll', checkLocation);
        };
    }, [url]);

    const isActive = (path) => {
        if (path === '/' && (url === '/' || url.startsWith('/?'))) return activeSection === 'home';
        if (path !== '/' && url.startsWith(path)) return true;
        return false;
    };

    const isPanelsActive = activeSection === 'products' || url.startsWith('/product') || url.includes('category=');

    return (
        <div className="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white dark:bg-[#0d1322]/95 backdrop-blur-lg border-t border-gray-200 dark:border-gray-800/90 py-2 px-3 safe-area-bottom">
            <div className="grid grid-cols-5 items-center justify-around gap-1 text-center">
                {/* Home */}
                <Link 
                    href="/" 
                    onClick={() => setActiveSection('home')}
                    className={`flex flex-col items-center gap-1 py-1 transition-colors ${
                        isActive('/') && !isPanelsActive ? 'text-[#f82803] dark:text-red-400 font-bold' : 'text-gray-600 dark:text-gray-400'
                    }`}
                >
                    <Home className="w-5 h-5" />
                    <span className="text-[10px]">Home</span>
                </Link>

                {/* Products */}
                <a 
                    href="/#products" 
                    onClick={() => setActiveSection('products')}
                    className={`flex flex-col items-center gap-1 py-1 transition-colors ${
                        isPanelsActive ? 'text-[#f82803] dark:text-red-400 font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-red-400'
                    }`}
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
