const fs = require('fs');
const path = require('path');

const directory = './resources/js';

const replacements = [
    { regex: /(?<!dark:)bg-\[#0b0f19\]/g, replace: 'bg-gray-50 dark:bg-[#0b0f19]' },
    { regex: /(?<!dark:)bg-\[#0d1322\]/g, replace: 'bg-white dark:bg-[#0d1322]' },
    { regex: /(?<!dark:)bg-\[#090d16\]/g, replace: 'bg-gray-100 dark:bg-[#090d16]' },
    { regex: /(?<!dark:)bg-gray-900/g, replace: 'bg-white dark:bg-gray-900' },
    { regex: /(?<!dark:)bg-gray-800/g, replace: 'bg-gray-100 dark:bg-gray-800' },
    { regex: /(?<!dark:)bg-black(?!\/)(?!\s)/g, replace: 'bg-white dark:bg-black' },
    { regex: /(?<!dark:)text-white/g, replace: 'text-gray-900 dark:text-white' },
    { regex: /(?<!dark:)text-gray-100/g, replace: 'text-gray-900 dark:text-gray-100' },
    { regex: /(?<!dark:)text-gray-200/g, replace: 'text-gray-800 dark:text-gray-200' },
    { regex: /(?<!dark:)text-gray-300/g, replace: 'text-gray-700 dark:text-gray-300' },
    { regex: /(?<!dark:)text-gray-400/g, replace: 'text-gray-600 dark:text-gray-400' },
    { regex: /(?<!dark:)border-gray-800/g, replace: 'border-gray-200 dark:border-gray-800' },
    { regex: /(?<!dark:)border-gray-700/g, replace: 'border-gray-300 dark:border-gray-700' },
    { regex: /(?<!dark:)from-cyan-950/g, replace: 'from-cyan-50 dark:from-cyan-950' },
    { regex: /(?<!dark:)from-purple-950/g, replace: 'from-purple-50 dark:from-purple-950' },
    { regex: /(?<!dark:)via-slate-900/g, replace: 'via-slate-100 dark:via-slate-900' },
    { regex: /(?<!dark:)to-cyan-950/g, replace: 'to-cyan-50 dark:to-cyan-950' },
    { regex: /(?<!dark:)to-blue-950/g, replace: 'to-blue-50 dark:to-blue-950' },
    { regex: /(?<!dark:)bg-black\/50/g, replace: 'bg-gray-100 dark:bg-black/50' },
    { regex: /(?<!dark:)bg-black\/30/g, replace: 'bg-gray-50 dark:bg-black/30' },
    { regex: /(?<!dark:)bg-black\/60/g, replace: 'bg-gray-200 dark:bg-black/60' },
];

function walkSync(dir, callback) {
    const files = fs.readdirSync(dir);
    files.forEach((file) => {
        const filepath = path.join(dir, file);
        const stats = fs.statSync(filepath);
        if (stats.isDirectory()) {
            walkSync(filepath, callback);
        } else if (stats.isFile() && filepath.endsWith('.jsx')) {
            callback(filepath);
        }
    });
}

walkSync(directory, (filepath) => {
    let content = fs.readFileSync(filepath, 'utf8');
    let original = content;
    
    replacements.forEach(({ regex, replace }) => {
        content = content.replace(regex, replace);
    });

    if (content !== original) {
        fs.writeFileSync(filepath, content, 'utf8');
        console.log(`Updated ${filepath}`);
    }
});
