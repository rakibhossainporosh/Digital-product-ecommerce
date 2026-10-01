import React, { useState } from 'react';
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
    PlusCircle
} from 'lucide-react';
import ThemeToggle from './ThemeToggle';

export default function Navbar() {
    const { auth, settings } = usePage().props;
    const customer = auth?.customer;
    const [dropdownOpen, setDropdownOpen] = useState(false);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    const handleLogout = (e) => {
        e.preventDefault();
        router.post('/logout');
    };

    return (
        <nav className="sticky top-0 z-40 bg-white dark:bg-[#0d1322]/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-800/80">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex items-center justify-between h-16 md:h-20">
                    {/* Logo & Brand */}
                    <div className="flex items-center gap-3">
                        <Link href="/" className="flex items-center gap-2.5 group">
                            {settings?.site_logo ? (
                                <div className="h-10 w-10 rounded-xl overflow-hidden border border-red-500/20 shadow-lg shadow-red-500/10 group-hover:scale-105 transition-transform bg-white dark:bg-[#0b0f19] flex items-center justify-center p-1 shrink-0">
                                    <img 
                                        src={settings.site_logo} 
                                        alt={settings?.app_name || 'Logo'} 
                                        className="w-full h-full object-contain"
                                    />
                                </div>
                            ) : (
                                <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] p-0.5 shadow-lg shadow-red-500/20 group-hover:scale-105 transition-transform shrink-0">
                                    <div className="w-full h-full bg-gray-50 dark:bg-[#0b0f19] rounded-[10px] flex items-center justify-center">
                                        <ShoppingBag className="w-5 h-5 text-[#f82803] dark:text-red-400 group-hover:text-red-700 dark:text-red-300" />
                                    </div>
                                </div>
                            )}
                            <div>
                                <span className="text-lg md:text-xl font-extrabold tracking-tight font-display bg-gradient-to-r from-gray-900 dark:from-white via-gray-600 dark:via-gray-100 to-red-600 dark:to-red-400 bg-clip-text text-transparent">
                                    {settings?.app_name || 'Panel Sell'}
                                </span>
                                <span className="block text-[10px] text-[#f82803] dark:text-red-400/80 font-mono tracking-widest uppercase">
                                    {settings?.app_tagline ? settings.app_tagline.slice(0, 25) : 'Official Store'}
                                </span>
                            </div>
                        </Link>

                        {/* Desktop Navigation Links */}
                        <div className="hidden md:flex items-center gap-1 ml-8">
                            <Link 
                                href="/" 
                                className="px-3.5 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:text-white hover:bg-gray-100 dark:bg-gray-800/60 transition-colors"
                            >
                                Home
                            </Link>
                            <a 
                                href="#products" 
                                className="px-3.5 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:text-white hover:bg-gray-100 dark:bg-gray-800/60 transition-colors"
                            >
                                Products
                            </a>
                            <a 
                                href="#reviews" 
                                className="px-3.5 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:text-white hover:bg-gray-100 dark:bg-gray-800/60 transition-colors"
                            >
                                Reviews
                            </a>
                        </div>
                    </div>

                    {/* Right Action Icons & Auth */}
                    <div className="hidden md:flex items-center gap-3">
                        <ThemeToggle />
                        
                        {/* WhatsApp Support Button */}
                        {settings?.support_whatsapp && (
                            <a 
                                href={`https://wa.me/${settings.support_whatsapp.replace(/[^0-9]/g, '')}?text=Hello%20support,%20I%20need%20assistance.`}
                                target="_blank"
                                rel="noreferrer"
                                className="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 text-xs font-semibold transition-all hover:scale-105"
                            >
                                <MessageCircle className="w-4 h-4 text-emerald-700 dark:text-emerald-400" />
                                <span>Support</span>
                            </a>
                        )}

                        {customer ? (
                            <div className="flex items-center gap-3">
                                {/* Wallet Balance Pill */}
                                <Link 
                                    href="/wallet" 
                                    className="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-gray-900 to-red-50 dark:to-red-950/60 border border-red-500/30 hover:border-red-500/60 transition-all shadow-sm group"
                                >
                                    <div className="w-6 h-6 rounded-lg bg-red-500/20 flex items-center justify-center text-[#f82803] dark:text-red-400">
                                        <Wallet className="w-3.5 h-3.5" />
                                    </div>
                                    <div className="text-left">
                                        <span className="block text-[9px] text-gray-600 dark:text-gray-400 uppercase tracking-wider font-semibold">Wallet</span>
                                        <span className="block text-xs font-bold text-gray-900 dark:text-white font-mono group-hover:text-red-700 dark:text-red-300">
                                            {customer.formatted_balance}
                                        </span>
                                    </div>
                                    <PlusCircle className="w-4 h-4 text-[#f82803] dark:text-red-400 opacity-60 group-hover:opacity-100 ml-1" />
                                </Link>

                                {/* User Menu Dropdown */}
                                <div className="relative">
                                    <button 
                                        onClick={() => setDropdownOpen(!dropdownOpen)}
                                        className="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-800/80 hover:bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-700/60 text-sm font-medium transition-colors"
                                    >
                                        <div className="w-7 h-7 rounded-lg bg-gradient-to-br from-[#f82803] to-[#730505] flex items-center justify-center text-white font-bold text-xs">
                                            {customer.name?.charAt(0).toUpperCase()}
                                        </div>
                                        <span className="max-w-[100px] truncate">{customer.name}</span>
                                        <ChevronDown className="w-4 h-4 text-gray-600 dark:text-gray-400" />
                                    </button>

                                    {dropdownOpen && (
                                        <div 
                                            className="absolute right-0 mt-2 w-52 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700/80 rounded-xl shadow-2xl py-2 z-50 divide-y divide-gray-800"
                                            onClick={() => setDropdownOpen(false)}
                                        >
                                            <div className="px-4 py-2">
                                                <p className="text-xs text-gray-600 dark:text-gray-400">Signed in as</p>
                                                <p className="text-sm font-semibold text-gray-900 dark:text-white truncate">{customer.email}</p>
                                            </div>

                                            <div className="py-1">
                                                <Link href="/dashboard" className="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:text-white hover:bg-gray-100 dark:bg-gray-800/70">
                                                    <User className="w-4 h-4 text-[#f82803] dark:text-red-400" />
                                                    <span>Dashboard</span>
                                                </Link>
                                                <Link href="/keys" className="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:text-white hover:bg-gray-100 dark:bg-gray-800/70">
                                                    <Key className="w-4 h-4 text-[#f82803] dark:text-red-400" />
                                                    <span>My License Keys</span>
                                                </Link>
                                                <Link href="/orders" className="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:text-white hover:bg-gray-100 dark:bg-gray-800/70">
                                                    <ShoppingBag className="w-4 h-4 text-[#f82803] dark:text-red-400" />
                                                    <span>Order History</span>
                                                </Link>
                                                <Link href="/wallet" className="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:text-white hover:bg-gray-100 dark:bg-gray-800/70">
                                                    <Wallet className="w-4 h-4 text-[#f82803] dark:text-red-400" />
                                                    <span>Wallet & Deposit</span>
                                                </Link>
                                            </div>

                                            <div className="py-1">
                                                <button 
                                                    onClick={handleLogout}
                                                    className="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 text-left"
                                                >
                                                    <LogOut className="w-4 h-4" />
                                                    <span>Sign Out</span>
                                                </button>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        ) : (
                            <div className="flex items-center gap-2">
                                <Link 
                                    href="/login" 
                                    className="px-4 py-2 rounded-xl text-sm font-medium text-gray-800 dark:text-gray-200 hover:text-gray-900 dark:text-white hover:bg-gray-100 dark:bg-gray-800/70 transition-colors"
                                >
                                    Log In
                                </Link>
                                <Link 
                                    href="/register" 
                                    className="px-4 py-2 rounded-xl bg-gradient-to-br from-[#f82803] to-[#730505] hover:from-[#ff411a] hover:to-[#8f0909] text-white font-semibold text-sm shadow-md shadow-red-500/20 transition-all hover:scale-105"
                                >
                                    Register
                                </Link>
                            </div>
                        )}
                    </div>

                    {/* Mobile Hamburger Button */}
                    <div className="flex md:hidden items-center gap-2">
                        <ThemeToggle />
                        
                        {customer && (
                            <Link 
                                href="/wallet" 
                                className="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-red-950/70 border border-red-500/40 text-red-700 dark:text-red-300 text-xs font-mono font-bold"
                            >
                                <Wallet className="w-3.5 h-3.5" />
                                <span>{customer.formatted_balance}</span>
                            </Link>
                        )}
                        <button 
                            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                            className="p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white hover:bg-gray-100 dark:bg-gray-800"
                        >
                            {mobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
                        </button>
                    </div>
                </div>
            </div>

            {/* Mobile Dropdown Menu */}
            {mobileMenuOpen && (
                <div className="md:hidden border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-[#0d1322] px-4 pt-3 pb-6 space-y-3">
                    <Link href="/" className="block py-2 text-base font-medium text-gray-800 dark:text-gray-200 hover:text-gray-900 dark:text-white">
                        Home
                    </Link>
                    <a href="#products" onClick={() => setMobileMenuOpen(false)} className="block py-2 text-base font-medium text-gray-800 dark:text-gray-200 hover:text-gray-900 dark:text-white">
                        Products & Panels
                    </a>
                    <a href="#reviews" onClick={() => setMobileMenuOpen(false)} className="block py-2 text-base font-medium text-gray-800 dark:text-gray-200 hover:text-gray-900 dark:text-white">
                        Reviews
                    </a>

                    {customer ? (
                        <div className="border-t border-gray-200 dark:border-gray-800 pt-3 space-y-2">
                            <div className="py-1">
                                <span className="text-xs text-gray-600 dark:text-gray-400">Signed in as</span>
                                <span className="block text-sm font-semibold text-gray-900 dark:text-white">{customer.name}</span>
                            </div>
                            <Link href="/dashboard" className="flex items-center gap-2 py-2 text-sm text-gray-700 dark:text-gray-300">
                                <User className="w-4 h-4 text-[#f82803] dark:text-red-400" /> Dashboard
                            </Link>
                            <Link href="/keys" className="flex items-center gap-2 py-2 text-sm text-gray-700 dark:text-gray-300">
                                <Key className="w-4 h-4 text-[#f82803] dark:text-red-400" /> My License Keys
                            </Link>
                            <Link href="/orders" className="flex items-center gap-2 py-2 text-sm text-gray-700 dark:text-gray-300">
                                <ShoppingBag className="w-4 h-4 text-[#f82803] dark:text-red-400" /> Order History
                            </Link>
                            <Link href="/wallet" className="flex items-center gap-2 py-2 text-sm text-gray-700 dark:text-gray-300">
                                <Wallet className="w-4 h-4 text-[#f82803] dark:text-red-400" /> Wallet ({customer.formatted_balance})
                            </Link>
                            <button onClick={handleLogout} className="flex items-center gap-2 py-2 text-sm text-red-400 w-full text-left">
                                <LogOut className="w-4 h-4" /> Sign Out
                            </button>
                        </div>
                    ) : (
                        <div className="border-t border-gray-200 dark:border-gray-800 pt-4 flex gap-3">
                            <Link href="/login" className="flex-1 py-2.5 text-center rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-medium text-gray-900 dark:text-white">
                                Log In
                            </Link>
                            <Link href="/register" className="flex-1 py-2.5 text-center rounded-xl bg-red-500 text-sm font-semibold text-black">
                                Register
                            </Link>
                        </div>
                    )}
                </div>
            )}
        </nav>
    );
}
