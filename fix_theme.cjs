const fs = require('fs');

function replaceFile(path, replacements) {
    let content = fs.readFileSync(path, 'utf8');
    let original = content;
    replacements.forEach(({ regex, replace }) => {
        content = content.replace(regex, replace);
    });
    if (content !== original) {
        fs.writeFileSync(path, content, 'utf8');
        console.log(`Fixed ${path}`);
    }
}

// 1. Navbar.jsx
replaceFile('resources/js/Components/Navbar.jsx', [
    // Logo text gradient
    { regex: /from-white via-gray-100 to-cyan-400/g, replace: 'from-gray-900 dark:from-white via-gray-600 dark:via-gray-100 to-cyan-600 dark:to-cyan-400' },
    // "Support" button text
    { regex: /text-emerald-400/g, replace: 'text-emerald-700 dark:text-emerald-400' },
    // Wallet pill text
    { regex: /text-cyan-400/g, replace: 'text-cyan-600 dark:text-cyan-400' },
    { regex: /text-cyan-300/g, replace: 'text-cyan-700 dark:text-cyan-300' }
]);

// 2. MarqueeNotice.jsx
replaceFile('resources/js/Components/MarqueeNotice.jsx', [
    // Make it always dark
    { regex: /bg-gradient-to-r from-cyan-50 dark:from-cyan-950\/60 via-slate-100 dark:via-slate-900 to-cyan-50 dark:to-cyan-950\/60/g, replace: 'bg-gradient-to-r from-cyan-950/60 via-slate-900 to-cyan-950/60' },
    { regex: /text-cyan-100\/90/g, replace: 'text-cyan-100/90' } // leave text as is
]);

// 3. HeroSlider.jsx
replaceFile('resources/js/Components/HeroSlider.jsx', [
    // Revert text to always white/gray-300 since background is always dark
    { regex: /text-gray-900 dark:text-white/g, replace: 'text-white' },
    { regex: /text-gray-700 dark:text-gray-300/g, replace: 'text-gray-300' },
    { regex: /bg-gray-100 dark:bg-black\/50/g, replace: 'bg-black/50' },
    { regex: /text-gray-900 dark:text-gray-100/g, replace: 'text-gray-100' }
]);

// 4. ProductCard.jsx
replaceFile('resources/js/Components/ProductCard.jsx', [
    // Fix fallback image gradient - revert to always dark
    { regex: /from-cyan-50 dark:from-cyan-950\/40 via-gray-100 dark:via-gray-900 to-blue-50 dark:to-blue-950\/40/g, replace: 'from-cyan-950/40 via-gray-900 to-blue-950/40' },
    { regex: /from-cyan-50 dark:from-cyan-950\/40 via-gray-900 to-blue-50 dark:to-blue-950\/40/g, replace: 'from-cyan-950/40 via-gray-900 to-blue-950/40' },
    // Fix text in fallback
    { regex: /text-gray-600 dark:text-gray-400/g, replace: 'text-gray-400' }
]);

// 5. CustomerReviews.jsx
replaceFile('resources/js/Components/CustomerReviews.jsx', [
    // Fix product badge text
    { regex: /text-cyan-300/g, replace: 'text-cyan-700 dark:text-cyan-300' },
    // Fix avatar background
    { regex: /bg-gray-50 dark:bg-cyan-950/g, replace: 'bg-cyan-100 dark:bg-cyan-950' },
    { regex: /text-cyan-400/g, replace: 'text-cyan-700 dark:text-cyan-400' },
    { regex: /border-cyan-500\/30/g, replace: 'border-cyan-500/30 dark:border-cyan-500/30' },
    // Avatar bg if it wasn't replaced properly
    { regex: /bg-cyan-950/g, replace: 'bg-cyan-100 dark:bg-cyan-950' }
]);

// 6. CategoryTabs.jsx
replaceFile('resources/js/Components/CategoryTabs.jsx', [
    // Make tabs light gray in light mode, dark gray in dark mode
    { regex: /bg-gray-100 dark:bg-gray-800\/80/g, replace: 'bg-gray-200 dark:bg-gray-800/80' },
    { regex: /text-gray-700 dark:text-gray-300/g, replace: 'text-gray-800 dark:text-gray-300' }
]);

// 7. Footer.jsx
replaceFile('resources/js/Components/Footer.jsx', [
    // Footer is completely dark, so revert text replacements
    { regex: /bg-gray-100 dark:bg-\[#090d16\]/g, replace: 'bg-[#090d16]' },
    { regex: /text-gray-900 dark:text-white/g, replace: 'text-white' },
    { regex: /text-gray-600 dark:text-gray-400/g, replace: 'text-gray-400' },
    { regex: /text-gray-700 dark:text-gray-300/g, replace: 'text-gray-300' }
]);
