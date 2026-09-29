const fs = require('fs');
const path = require('path');

const directory = './resources/js';

const replacements = [
    // Change gradient directions to 135deg (bottom-right)
    { regex: /bg-gradient-to-r from-red-600/g, replace: 'bg-gradient-to-br from-[#f82803]' },
    { regex: /bg-gradient-to-tr from-red-600/g, replace: 'bg-gradient-to-br from-[#f82803]' },
    
    // Catch any remaining from-red-600 that weren't matched above
    { regex: /from-red-600/g, replace: 'from-[#f82803]' },
    
    // Change to-black to the dark red #730505 in gradients
    { regex: /to-black(?![\w/])/g, replace: 'to-[#730505]' },
    
    // Hover states (slightly lighter/brighter versions)
    { regex: /hover:from-red-500/g, replace: 'hover:from-[#ff411a]' },
    { regex: /hover:to-red-950/g, replace: 'hover:to-[#8f0909]' },
    { regex: /hover:to-black/g, replace: 'hover:to-[#8f0909]' },
    
    // Replace text colors that were red-500 or red-600
    { regex: /text-red-500/g, replace: 'text-[#f82803]' },
    { regex: /text-red-600/g, replace: 'text-[#f82803]' },
    
    // Update border and background utilities
    { regex: /border-red-600/g, replace: 'border-[#f82803]' },
    { regex: /bg-red-600/g, replace: 'bg-[#f82803]' },
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
