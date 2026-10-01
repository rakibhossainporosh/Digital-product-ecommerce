import React, { useState, useEffect } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import { 
    ShoppingBag, 
    Wallet, 
    Key, 
    User, 
    LogOut, 
    MessageCircle, 
    Menu, 
    X, 
    ShieldCheck, 
    ChevronDown,
    PlusCircle,
    LayoutDashboard,
    History
} from 'lucide-react';
import ThemeToggle from './ThemeToggle';

export default function Navbar() {
    const { auth, settings } = usePage().props;
    const { url } = usePage();
    const customer = auth?.customer;
    const [dropdownOpen, setDropdownOpen] = useState(false);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [scrolled, setScrolled] = useState(false);

    useEffect(() => {
        const handleScroll = () => {
            setScrolled(window.scrollY > 10);
        };
        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    const handleLogout = (e) => {
        e.preventDefault();
        router.post('/logout');
    };

    const isActive = (path) => {
        if (path === '/') return url === '/';
        return url.startsWith(path);
    };

    return (
        <nav className={`sticky top-0 z-40 transition-all duration-300 ${scrolled ? 'bg-white/90 dark:bg-[#0b0f19]/90 backdrop-blur-xl shadow-sm border-b border-gray-200/80 dark:border-gray-800/80' : 'bg-white dark:bg-[#0b0f19] border-b border-gray-100 dark:border-gray-800/40'}`}>
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex items-center justify-between h-16 md:h-20">
                    {/* Logo & Brand */}
                    <div className="flex items-center gap-6">
                        <Link href="/" className="flex items-center gap-3 group">
                            {settings?.site_logo ? (
                                <div className="h-10 w-10 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-800 group-hover:border-red-500/50 transition-colors bg-white dark:bg-[#111827] flex items-center justify-center p-1 shrink-0">
                                    <img 
                                        src={settings.site_logo} 
                                        alt={settings?.app_name || 'Logo'} 
                                        className="w-full h-full object-contain group-hover:scale-110 transition-transform duration-300"
                                    />
                                </div>
                            ) : (
                                <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] p-0.5 shadow-lg shadow-red-500/20 group-hover:shadow-red-500/40 transition-shadow shrink-0">
                                    <div className="w-full h-full bg-white dark:bg-[#0b0f19] rounded-[10px] flex items-center justify-center">
                                        <ShoppingBag className="w-5 h-5 text-[#f82803] dark:text-red-400 group-hover:scale-110 transition-transform duration-300" />
                                    </div>
                                </div>
                            )}
                            <div className="flex flex-col justify-center">
                                <span className="text-xl font-bold tracking-tight font-display text-gray-900 dark:text-white leading-tight group-hover:text-[#f82803] dark:group-hover:text-red-400 transition-colors">
                                    {settings?.app_name || 'Panel Sell'}
                                </span>
                                <span className="text-[10px] text-gray-500 dark:text-gray-400 font-medium tracking-wider uppercase leading-none mt-0.5">
                                    {settings?.app_tagline ? settings.app_tagline.slice(0, 30) : 'Official Store'}
                                </span>
                            </div>
                        </Link>

                        {/* Desktop Navigation Links */}
                        <div className="hidden md:flex items-center gap-1 ml-4 pl-6 border-l border-gray-200 dark:border-gray-800">
                            <Link 
                                href="/" 
                                className={`relative px-4 py-2 text-sm font-semibold transition-colors group ${isActive('/') ? 'text-[#f82803] dark:text-red-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'}`}
                            >
                                Home
                                <span className={`absolute inset-x-4 -bottom-1 h-0.5 rounded-t-full transition-transform duration-300 origin-left ${isActive('/') ? 'bg-[#f82803] dark:bg-red-500 scale-x-100' : 'bg-gray-300 dark:bg-gray-600 scale-x-0 group-hover:scale-x-100'}`}></span>
                            </Link>
                            <a 
                                href="/#products" 
                                className="relative px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors group"
                            >
                                Products
                                <span className="absolute inset-x-4 -bottom-1 h-0.5 rounded-t-full transition-transform duration-300 origin-left bg-gray-300 dark:bg-gray-600 scale-x-0 group-hover:scale-x-100"></span>
                            </a>
                            <a 
                                href="/#reviews" 
                                className="relative px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors group"
                            >
                                Reviews
                                <span className="absolute inset-x-4 -bottom-1 h-0.5 rounded-t-full transition-transform duration-300 origin-left bg-gray-300 dark:bg-gray-600 scale-x-0 group-hover:scale-x-100"></span>
                            </a>
                        </div>
                    </div>

                    {/* Right Action Icons & Auth */}
                    <div className="hidden md:flex items-center gap-4">
                        <ThemeToggle />
                        
                        {/* WhatsApp Support Button */}
                        {settings?.support_whatsapp && (
                            <a 
                                href={`https://wa.me/${settings.support_whatsapp.replace(/[^0-9]/g, '')}?text=Hello%20support,%20I%20need%20assistance.`}
                                target="_blank"
                                rel="noreferrer"
                                className="flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-sm font-semibold transition-all duration-300 hover:shadow-sm"
                            >
                                <MessageCircle className="w-4 h-4" />
                                <span>Support</span>
                            </a>
                        )}

                        {customer ? (
                            <div className="flex items-center gap-3">
                                {/* Wallet Balance Pill */}
                                <Link 
                                    href="/wallet" 
                                    className="flex items-center gap-3 pl-1.5 pr-4 py-1.5 rounded-full bg-white dark:bg-[#111827] border border-gray-200 dark:border-gray-800 hover:border-red-500/50 dark:hover:border-red-500/50 hover:shadow-[0_0_15px_rgba(248,40,3,0.1)] dark:hover:shadow-[0_0_15px_rgba(248,40,3,0.2)] transition-all duration-300 group"
                                >
                                    <div className="w-8 h-8 rounded-full bg-red-50 dark:bg-red-500/10 flex items-center justify-center text-[#f82803] dark:text-red-400 group-hover:scale-105 transition-transform">
                                        <Wallet className="w-4 h-4" />
                                    </div>
                                    <div className="text-left flex flex-col justify-center">
                                        <span className="text-[9px] text-gray-500 dark:text-gray-400 uppercase tracking-wider font-bold leading-none mb-0.5">Wallet</span>
                                        <span className="text-sm font-bold text-gray-900 dark:text-white font-mono leading-none group-hover:text-[#f82803] dark:group-hover:text-red-400 transition-colors">
                                            {customer.formatted_balance}
                                        </span>
                                    </div>
                                </Link>

                                {/* User Menu Dropdown */}
                                <div className="relative">
                                    <button 
                                        onClick={() => setDropdownOpen(!dropdownOpen)}
                                        className={`flex items-center gap-2.5 pl-1.5 pr-3 py-1.5 rounded-full border transition-all duration-300 ${dropdownOpen ? 'bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-700' : 'bg-white dark:bg-[#111827] border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700'}`}
                                    >
                                        <div className="w-8 h-8 rounded-full bg-gradient-to-br from-gray-800 to-gray-900 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center text-white font-bold text-sm shadow-sm ring-2 ring-white dark:ring-[#111827]">
                                            {customer.name?.charAt(0).toUpperCase()}
                                        </div>
                                        <span className="max-w-[90px] truncate text-sm font-semibold text-gray-700 dark:text-gray-200">{customer.name}</span>
                                        <ChevronDown className={`w-4 h-4 text-gray-400 transition-transform duration-300 ${dropdownOpen ? 'rotate-180' : ''}`} />
                                    </button>

                                    {dropdownOpen && (
                                        <>
                                            <div className="fixed inset-0 z-40" onClick={() => setDropdownOpen(false)}></div>
                                            <div className="absolute right-0 mt-3 w-56 bg-white dark:bg-[#111827] border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xl py-2 z-50 animate-in fade-in slide-in-from-top-2 duration-200">
                                                <div className="px-5 py-3 border-b border-gray-100 dark:border-gray-800/60">
                                                    <p className="text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold mb-0.5">Signed in as</p>
                                                    <p className="text-sm font-semibold text-gray-900 dark:text-white truncate">{customer.email}</p>
                                                </div>

                                                <div className="p-2 space-y-1">
                                                    <Link href="/dashboard" className="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-xl hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                                        <LayoutDashboard className="w-4 h-4 text-gray-400 group-hover:text-[#f82803] dark:group-hover:text-red-400" />
                                                        <span>Dashboard</span>
                                                    </Link>
                                                    <Link href="/keys" className="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-xl hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                                        <Key className="w-4 h-4 text-gray-400 group-hover:text-[#f82803] dark:group-hover:text-red-400" />
                                                        <span>My License Keys</span>
                                                    </Link>
                                                    <Link href="/orders" className="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-xl hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                                        <History className="w-4 h-4 text-gray-400 group-hover:text-[#f82803] dark:group-hover:text-red-400" />
                                                        <span>Order History</span>
                                                    </Link>
                                                    <Link href="/wallet" className="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-xl hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                                        <Wallet className="w-4 h-4 text-gray-400 group-hover:text-[#f82803] dark:group-hover:text-red-400" />
                                                        <span>Wallet & Deposit</span>
                                                    </Link>
                                                </div>

                                                <div className="p-2 border-t border-gray-100 dark:border-gray-800/60">
                                                    <button 
                                                        onClick={handleLogout}
                                                        className="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors group"
                                                    >
                                                        <LogOut className="w-4 h-4 group-hover:text-red-700 dark:group-hover:text-red-300" />
                                                        <span>Sign Out</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </>
                                    )}
                                </div>
                            </div>
                        ) : (
                            <div className="flex items-center gap-3 pl-2 border-l border-gray-200 dark:border-gray-800">
                                <Link 
                                    href="/login" 
                                    className="px-5 py-2.5 rounded-full text-sm font-semibold text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                >
                                    Log In
                                </Link>
                                <Link 
                                    href="/register" 
                                    className="px-5 py-2.5 rounded-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white font-semibold text-sm shadow-md shadow-red-500/25 transition-all hover:scale-105 hover:shadow-lg hover:shadow-red-500/40"
                                >
                                    Register
                                </Link>
                            </div>
                        )}
                    </div>

                    {/* Mobile Hamburger Button */}
                    <div className="flex md:hidden items-center gap-3">
                        <ThemeToggle />
                        
                        {customer && (
                            <Link 
                                href="/wallet" 
                                className="flex items-center gap-1.5 pl-2 pr-3 py-1 rounded-full bg-white dark:bg-[#111827] border border-gray-200 dark:border-gray-800 shadow-sm"
                            >
                                <div className="w-6 h-6 rounded-full bg-red-50 dark:bg-red-500/10 flex items-center justify-center text-[#f82803] dark:text-red-400">
                                    <Wallet className="w-3 h-3" />
                                </div>
                                <span className="text-xs font-bold text-gray-900 dark:text-white font-mono">{customer.formatted_balance}</span>
                            </Link>
                        )}
                        <button 
                            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                            className="p-2 -mr-2 rounded-full text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        >
                            {mobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
                        </button>
                    </div>
                </div>
            </div>

            {/* Mobile Dropdown Menu */}
            <div className={`md:hidden absolute w-full bg-white dark:bg-[#111827] border-b border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden transition-all duration-300 ease-in-out ${mobileMenuOpen ? 'max-h-[85vh] overflow-y-auto opacity-100 border-t' : 'max-h-0 opacity-0 border-transparent'}`}>
                <div className="px-4 py-4 space-y-1">
                    <Link href="/" onClick={() => setMobileMenuOpen(false)} className={`block px-4 py-3 rounded-xl text-base font-semibold transition-colors ${isActive('/') ? 'bg-red-50 dark:bg-red-500/10 text-[#f82803] dark:text-red-400' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800'}`}>
                        Home
                    </Link>
                    <a href="/#products" onClick={() => setMobileMenuOpen(false)} className="block px-4 py-3 rounded-xl text-base font-semibold text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        Products & Panels
                    </a>
                    <a href="/#reviews" onClick={() => setMobileMenuOpen(false)} className="block px-4 py-3 rounded-xl text-base font-semibold text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        Reviews
                    </a>

                    {customer ? (
                        <div className="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 space-y-1">
                            <div className="px-4 py-2 mb-2">
                                <span className="block text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">Signed in as</span>
                                <span className="block text-base font-bold text-gray-900 dark:text-white truncate">{customer.name}</span>
                            </div>
                            <Link href="/dashboard" onClick={() => setMobileMenuOpen(false)} className="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <LayoutDashboard className="w-5 h-5 text-gray-400" /> Dashboard
                            </Link>
                            <Link href="/keys" onClick={() => setMobileMenuOpen(false)} className="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <Key className="w-5 h-5 text-gray-400" /> My License Keys
                            </Link>
                            <Link href="/orders" onClick={() => setMobileMenuOpen(false)} className="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <History className="w-5 h-5 text-gray-400" /> Order History
                            </Link>
                            <Link href="/wallet" onClick={() => setMobileMenuOpen(false)} className="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <Wallet className="w-5 h-5 text-gray-400" /> Wallet ({customer.formatted_balance})
                            </Link>
                            <button onClick={(e) => { handleLogout(e); setMobileMenuOpen(false); }} className="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors text-left mt-2">
                                <LogOut className="w-5 h-5" /> Sign Out
                            </button>
                        </div>
                    ) : (
                        <div className="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 grid grid-cols-2 gap-3">
                            <Link href="/login" onClick={() => setMobileMenuOpen(false)} className="flex justify-center py-3 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                Log In
                            </Link>
                            <Link href="/register" onClick={() => setMobileMenuOpen(false)} className="flex justify-center py-3 rounded-xl bg-gradient-to-r from-red-600 to-red-700 text-sm font-semibold text-white shadow-md shadow-red-500/20">
                                Register
                            </Link>
                        </div>
                    )}
                </div>
            </div>
        </nav>
    );
}
