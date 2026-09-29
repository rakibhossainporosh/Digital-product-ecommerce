import React from 'react';
import { Link } from '@inertiajs/react';

export default function Pagination({ links }) {
    if (!links || links.length <= 3) {
        return null;
    }

    return (
        <div className="flex flex-wrap items-center justify-center gap-1.5 pt-6">
            {links.map((link, key) => {
                const label = link.label
                    .replace('&laquo; Previous', '‹ Prev')
                    .replace('Next &raquo;', 'Next ›');

                if (link.url === null) {
                    return (
                        <div
                            key={key}
                            className="px-3.5 py-1.5 text-xs rounded-xl text-gray-600 bg-white dark:bg-gray-900/40 border border-gray-200 dark:border-gray-800/40 cursor-not-allowed select-none"
                            dangerouslySetInnerHTML={{ __html: label }}
                        />
                    );
                }

                return (
                    <Link
                        key={key}
                        href={link.url}
                        preserveScroll
                        className={`px-3.5 py-1.5 text-xs font-semibold rounded-xl transition-all ${
                            link.active
                                ? 'bg-red-500 text-black shadow-lg shadow-red-500/25 border border-red-400 font-bold scale-105'
                                : 'bg-white dark:bg-gray-900/80 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:text-white hover:bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:border-gray-700'
                        }`}
                        dangerouslySetInnerHTML={{ __html: label }}
                    />
                );
            })}
        </div>
    );
}
