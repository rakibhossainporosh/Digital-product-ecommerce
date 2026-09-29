const fs = require('fs');
const path = require('path');

const directory = './resources/js';

const replacements = [
    // Gradients
    { regex: /to-rose-600/g, replace: 'to-black' },
    { regex: /hover:to-rose-500/g, replace: 'hover:to-red-950' },
    { regex: /to-rose-500/g, replace: 'to-black' },
    { regex: /via-rose-600/g, replace: 'via-red-900' },
    { regex: /to-rose-950\/40/g, replace: 'to-black/40' },
    { regex: /to-rose-950\/50/g, replace: 'to-black/50' },
    { regex: /to-rose-50/g, replace: 'to-gray-100' },
    { regex: /to-rose-400/g, replace: 'to-red-600' },
    
    // Other Rose utilities
    { regex: /bg-rose-500/g, replace: 'bg-red-600' },
    { regex: /text-rose-400/g, replace: 'text-red-500' },
    { regex: /text-rose-300/g, replace: 'text-red-400' },
    { regex: /border-rose-500/g, replace: 'border-red-600' },
    
    // Deepen primary red
    { regex: /from-red-500/g, replace: 'from-red-600' },
    { regex: /hover:from-red-400/g, replace: 'hover:from-red-500' },
    
    // Change Button text to white for contrast against Red/Black gradient
    { regex: /text-black font-bold/g, replace: 'text-white font-bold' },
    { regex: /text-black font-semibold/g, replace: 'text-white font-semibold' },
    { regex: /text-black font-black/g, replace: 'text-white font-black' },
    { regex: /fill-black/g, replace: 'fill-white' },
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
