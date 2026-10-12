import React, { useState, useRef, useEffect } from 'react';
import { MessageSquare, X, Send, Bot, User, Minimize2, Maximize2, Loader2 } from 'lucide-react';

export default function Chatbot() {
    const [isOpen, setIsOpen] = useState(false);
    const [isMinimized, setIsMinimized] = useState(false);
    const [message, setMessage] = useState('');
    const [messages, setMessages] = useState([
        { id: 1, type: 'bot', text: 'Hi there! 👋 Welcome to our support desk. How can I help you today?' }
    ]);
    const [isTyping, setIsTyping] = useState(false);
    const messagesEndRef = useRef(null);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    useEffect(() => {
        if (isOpen && !isMinimized) {
            scrollToBottom();
        }
    }, [messages, isOpen, isMinimized, isTyping]);

    const handleSend = (e) => {
        e.preventDefault();
        if (!message.trim()) return;

        // Add user message
        const newMsg = { id: Date.now(), type: 'user', text: message };
        setMessages(prev => [...prev, newMsg]);
        setMessage('');
        setIsTyping(true);

        // Simulate bot response
        setTimeout(() => {
            let reply = "I'm a demo assistant. I can't process requests right now, but please reach out to our official support team for further assistance!";
            
            const lowerMsg = newMsg.text.toLowerCase();
            if (lowerMsg.includes('order') || lowerMsg.includes('track')) {
                reply = "You can track your order status in the 'Orders' section of your customer dashboard.";
            } else if (lowerMsg.includes('payment') || lowerMsg.includes('wallet')) {
                reply = "We support multiple payment methods. You can also use your Wallet balance for quick checkouts!";
            } else if (lowerMsg.includes('hello') || lowerMsg.includes('hi')) {
                reply = "Hello! How can I assist you with our digital products today?";
            } else if (lowerMsg.includes('license') || lowerMsg.includes('key')) {
                reply = "Your purchased license keys are safely stored in the 'My License Keys' section. You can easily copy them from there.";
            }

            setMessages(prev => [...prev, { id: Date.now() + 1, type: 'bot', text: reply }]);
            setIsTyping(false);
        }, 1500);
    };

    const toggleChat = () => {
        if (!isOpen) {
            setIsOpen(true);
            setIsMinimized(false);
        } else {
            setIsOpen(false);
        }
    };

    return (
        <div className="fixed bottom-24 right-4 sm:bottom-6 sm:right-6 z-[60]">
            {/* Chat Window */}
            <div 
                className={`absolute bottom-16 right-0 w-[calc(100vw-32px)] sm:w-[350px] transition-all duration-300 ease-in-out origin-bottom-right ${
                    isOpen ? 'scale-100 opacity-100 pointer-events-auto' : 'scale-75 opacity-0 pointer-events-none'
                }`}
            >
                <div className={`flex flex-col bg-white dark:bg-[#0c1424] border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl overflow-hidden transition-all duration-300 ${
                    isMinimized ? 'h-14' : 'h-[500px] max-h-[70vh]'
                }`}>
                    
                    {/* Header */}
                    <div className="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-red-600 to-rose-600 text-white shadow-md relative overflow-hidden shrink-0 cursor-pointer"
                        onClick={() => setIsMinimized(!isMinimized)}
                    >
                        {/* Ambient Header Glow */}
                        <div className="absolute top-0 right-0 w-32 h-32 bg-white/20 rounded-full blur-2xl -translate-y-1/2 translate-x-1/4 pointer-events-none" />
                        
                        <div className="flex items-center gap-3 relative z-10">
                            <div className="relative">
                                <div className="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm border border-white/30 shadow-inner">
                                    <Bot className="w-5 h-5 text-white" />
                                </div>
                                <span className="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-400 border-2 border-rose-600 rounded-full"></span>
                            </div>
                            <div>
                                <h3 className="font-bold text-sm leading-tight">Live Support</h3>
                                <p className="text-[10px] text-white/80 font-medium">We typically reply in minutes</p>
                            </div>
                        </div>
                        <div className="flex items-center gap-1 relative z-10">
                            <button 
                                onClick={(e) => { e.stopPropagation(); setIsMinimized(!isMinimized); }}
                                className="p-1.5 hover:bg-white/20 rounded-lg transition-colors"
                            >
                                {isMinimized ? <Maximize2 className="w-4 h-4" /> : <Minimize2 className="w-4 h-4" />}
                            </button>
                            <button 
                                onClick={(e) => { e.stopPropagation(); setIsOpen(false); }}
                                className="p-1.5 hover:bg-white/20 rounded-lg transition-colors"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    {/* Chat Area */}
                    {!isMinimized && (
                        <>
                            <div className="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50/50 dark:bg-black/20 relative">
                                <div className="text-center pb-2">
                                    <span className="text-[10px] uppercase font-bold text-gray-400 tracking-wider bg-gray-100 dark:bg-gray-800/50 px-3 py-1 rounded-full">
                                        Today
                                    </span>
                                </div>
                                
                                {messages.map((msg) => (
                                    <div key={msg.id} className={`flex ${msg.type === 'user' ? 'justify-end' : 'justify-start'} animate-in slide-in-from-bottom-2 fade-in duration-300`}>
                                        <div className={`flex gap-2 max-w-[85%] ${msg.type === 'user' ? 'flex-row-reverse' : 'flex-row'}`}>
                                            <div className="shrink-0 mt-auto">
                                                <div className={`w-6 h-6 rounded-full flex items-center justify-center shadow-sm ${
                                                    msg.type === 'user' 
                                                        ? 'bg-gradient-to-br from-gray-700 to-gray-900 text-white' 
                                                        : 'bg-gradient-to-br from-red-500 to-rose-600 text-white'
                                                }`}>
                                                    {msg.type === 'user' ? <User className="w-3.5 h-3.5" /> : <Bot className="w-3.5 h-3.5" />}
                                                </div>
                                            </div>
                                            <div className={`px-4 py-2.5 rounded-2xl text-[13px] leading-relaxed shadow-sm relative ${
                                                msg.type === 'user'
                                                    ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900 rounded-br-sm'
                                                    : 'bg-white dark:bg-[#1a2333] border border-gray-100 dark:border-white/5 text-gray-700 dark:text-gray-300 rounded-bl-sm'
                                            }`}>
                                                {msg.text}
                                            </div>
                                        </div>
                                    </div>
                                ))}

                                {isTyping && (
                                    <div className="flex justify-start animate-in fade-in">
                                        <div className="flex gap-2 max-w-[85%] flex-row">
                                            <div className="shrink-0 mt-auto">
                                                <div className="w-6 h-6 rounded-full bg-gradient-to-br from-red-500 to-rose-600 text-white flex items-center justify-center shadow-sm">
                                                    <Bot className="w-3.5 h-3.5" />
                                                </div>
                                            </div>
                                            <div className="px-4 py-3 rounded-2xl bg-white dark:bg-[#1a2333] border border-gray-100 dark:border-white/5 rounded-bl-sm shadow-sm flex gap-1.5 items-center">
                                                <div className="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0ms' }} />
                                                <div className="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '150ms' }} />
                                                <div className="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '300ms' }} />
                                            </div>
                                        </div>
                                    </div>
                                )}
                                <div ref={messagesEndRef} />
                            </div>

                            {/* Input Area */}
                            <div className="p-3 bg-white dark:bg-[#0c1424] border-t border-gray-100 dark:border-white/5 shrink-0">
                                <form onSubmit={handleSend} className="relative flex items-end gap-2">
                                    <div className="relative flex-1">
                                        <textarea
                                            value={message}
                                            onChange={(e) => setMessage(e.target.value)}
                                            onKeyDown={(e) => {
                                                if (e.key === 'Enter' && !e.shiftKey) {
                                                    e.preventDefault();
                                                    handleSend(e);
                                                }
                                            }}
                                            placeholder="Type your message..."
                                            className="w-full bg-gray-100 dark:bg-gray-800/50 border-transparent focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20 rounded-xl py-2.5 pl-4 pr-10 text-sm resize-none h-[42px] max-h-[120px] overflow-hidden text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all scrollbar-hide"
                                            rows="1"
                                            style={{
                                                minHeight: '42px',
                                                height: message ? 'auto' : '42px'
                                            }}
                                        />
                                    </div>
                                    <button
                                        type="submit"
                                        disabled={!message.trim()}
                                        className="shrink-0 p-2.5 bg-gradient-to-r from-red-600 to-rose-600 text-white rounded-xl shadow-md shadow-red-500/20 hover:shadow-red-500/40 disabled:opacity-50 disabled:shadow-none transition-all duration-200"
                                    >
                                        <Send className="w-4 h-4 -ml-0.5" />
                                    </button>
                                </form>
                                <div className="text-center mt-2">
                                    <span className="text-[9px] text-gray-400">Powered by Secure Support</span>
                                </div>
                            </div>
                        </>
                    )}
                </div>
            </div>

            {/* Toggle Button */}
            <button
                onClick={toggleChat}
                className={`relative group flex items-center justify-center w-14 h-14 rounded-full shadow-2xl transition-all duration-300 ${
                    isOpen 
                        ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 rotate-90 scale-0 opacity-0 pointer-events-none absolute' 
                        : 'bg-gradient-to-tr from-red-600 to-rose-500 text-white hover:scale-105 hover:shadow-red-500/30'
                }`}
                aria-label="Toggle chat"
            >
                <div className="absolute inset-0 bg-white/20 rounded-full blur-md group-hover:blur-xl transition-all opacity-0 group-hover:opacity-100"></div>
                <MessageSquare className="w-6 h-6 relative z-10" />
                
                {/* Notification dot */}
                {!isOpen && (
                    <span className="absolute top-0 right-0 flex h-4 w-4">
                        <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                        <span className="relative inline-flex rounded-full h-4 w-4 bg-emerald-400 border-2 border-red-600"></span>
                    </span>
                )}
            </button>
        </div>
    );
}
