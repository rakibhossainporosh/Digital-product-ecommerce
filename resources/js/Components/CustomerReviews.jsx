import React, { useRef, useEffect } from 'react';
import { Star, ShieldCheck, CheckCircle2, MessageSquare } from 'lucide-react';

export default function CustomerReviews() {
    const scrollContainerRef = useRef(null);

    const reviews = [
        {
            name: "H4CK AKASH",
            comment: "২ সেকেন্ড এ কোড পেলাম! ১০০% সেফ এবং ফুল ওয়ার্কিং। কোনো ঝামেলা নেই।",
            product: "AIM HACK ANDROID - IOS",
            rating: 5,
            time: "10 mins ago",
        },
        {
            name: "MD HASIF YT",
            comment: "অনেক ধন্যবাদ ভাই। ওয়ালট দিয়ে ১ ক্লিকে সাথে সাথে লাইসেন্স কি পেয়ে গেলাম।",
            product: "XYZ CHEATS NON ROOT",
            rating: 5,
            time: "25 mins ago",
        },
        {
            name: "Silvee",
            comment: "I bought 30 days key without any problem. Good Service and Trusted Seller 💯",
            product: "DRIP CLIENT - ROOT",
            rating: 5,
            time: "1 hour ago",
        },
        {
            name: "Md Mithun",
            comment: "আমি কখনো ভাবতে পারিনি এত তাড়াতাড়ি key কেনা সম্ভব। বেস্ট প্যানেল সেলার ইন বাংলাদেশ।",
            product: "ZREX PANEL ANDROID",
            rating: 5,
            time: "2 hours ago",
        },
        {
            name: "Farhan Ad",
            comment: "খুবই ভালো, এক সেকেন্ডে পেয়েছি, দারুণ কাজ করছে ভাই। রেগুলার নেব এখন থেকে।",
            product: "BALA MOD NON ROOT",
            rating: 5,
            time: "3 hours ago",
        },
        {
            name: "Shakib Munshi",
            comment: "Good service 100% সাথে সাথে কোড পেয়ে গেলাম। কোনো সমস্যা হলে হোয়াটসঅ্যাপে হেল্প করে।",
            product: "PRIME MOD-NON ROOT",
            rating: 5,
            time: "5 hours ago",
        },
    ];

    useEffect(() => {
        const interval = setInterval(() => {
            if (scrollContainerRef.current) {
                const container = scrollContainerRef.current;
                const scrollWidth = container.scrollWidth;
                const clientWidth = container.clientWidth;
                
                // If we've reached the end
                if (container.scrollLeft + clientWidth >= scrollWidth - 10) {
                    container.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    // Scroll by the width of one card + gap (approx)
                    const card = container.children[0];
                    const cardWidth = card ? card.offsetWidth : 350;
                    container.scrollBy({ left: cardWidth + 16, behavior: 'smooth' }); // 16px is the gap
                }
            }
        }, 3500); // Auto slide every 3.5 seconds

        return () => clearInterval(interval);
    }, []);

    return (
        <section id="reviews" className="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-4">
            <div className="text-center max-w-2xl mx-auto mb-8 space-y-2">
                <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                    <CheckCircle2 className="w-3.5 h-3.5" />
                    <span>Verified Gamer Feedback</span>
                </div>
                <h2 className="text-2xl sm:text-3xl font-extrabold font-display text-gray-900 dark:text-white">
                    What Our Customers Say
                </h2>
                <p className="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                    Trusted by over 10,000+ competitive gamers and resellers across Bangladesh.
                </p>
            </div>

            {/* Auto-Scroll Slider without visible scrollbar */}
            <div 
                ref={scrollContainerRef}
                className="flex overflow-x-auto gap-4 pb-4 snap-x snap-mandatory px-2 -mx-2 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
            >
                {reviews.map((rev, idx) => (
                    <div 
                        key={idx}
                        className="glass-card w-[85vw] max-w-[350px] md:max-w-[400px] flex-shrink-0 snap-center p-5 rounded-2xl border border-gray-200 dark:border-gray-800/80 hover:border-red-500/30 dark:border-red-500/30 transition-all flex flex-col justify-between space-y-3"
                    >
                        <div className="space-y-2">
                            {/* Stars & Verified Badge */}
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-1">
                                    {[...Array(rev.rating)].map((_, i) => (
                                        <Star key={i} className="w-4 h-4 fill-amber-400 text-amber-400" />
                                    ))}
                                </div>
                                <span className="text-[10px] text-gray-500 font-mono">{rev.time}</span>
                            </div>

                            {/* Comment */}
                            <p className="text-sm text-gray-700 dark:text-gray-300 leading-relaxed italic">
                                "{rev.comment}"
                            </p>
                        </div>

                        {/* User & Product Info */}
                        <div className="pt-3 border-t border-gray-200 dark:border-gray-800/60 flex items-center justify-between text-xs">
                            <div className="flex items-center gap-2">
                                <div className="w-7 h-7 rounded-lg bg-red-100 dark:bg-red-950 border border-red-500/30 dark:border-red-500/30 text-red-700 dark:text-red-400 flex items-center justify-center font-bold text-xs">
                                    {rev.name.charAt(0)}
                                </div>
                                <div>
                                    <span className="font-semibold text-gray-900 dark:text-white block">{rev.name}</span>
                                    <span className="text-[10px] text-emerald-400 flex items-center gap-0.5">
                                        <ShieldCheck className="w-3 h-3" /> Verified Buyer
                                    </span>
                                </div>
                            </div>

                            <span className="text-[10px] px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800/80 text-red-700 dark:text-red-300 font-mono max-w-[130px] truncate">
                                {rev.product}
                            </span>
                        </div>
                    </div>
                ))}
            </div>
        </section>
    );
}
