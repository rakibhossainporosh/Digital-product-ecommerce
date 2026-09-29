const fs = require('fs');
const path = require('path');

const directory = './resources/js';

const replacements = [
    { regex: /cyan-50/g, replace: 'red-50' },
    { regex: /cyan-100/g, replace: 'red-100' },
    { regex: /cyan-200/g, replace: 'red-200' },
    { regex: /cyan-300/g, replace: 'red-300' },
    { regex: /cyan-400/g, replace: 'red-400' },
    { regex: /cyan-500/g, replace: 'red-500' },
    { regex: /cyan-600/g, replace: 'red-600' },
    { regex: /cyan-700/g, replace: 'red-700' },
    { regex: /cyan-800/g, replace: 'red-800' },
    { regex: /cyan-900/g, replace: 'red-900' },
    { regex: /cyan-950/g, replace: 'red-950' },
    
    { regex: /blue-50/g, replace: 'rose-50' },
    { regex: /blue-100/g, replace: 'rose-100' },
    { regex: /blue-200/g, replace: 'rose-200' },
    { regex: /blue-300/g, replace: 'rose-300' },
    { regex: /blue-400/g, replace: 'rose-400' },
    { regex: /blue-500/g, replace: 'rose-500' },
    { regex: /blue-600/g, replace: 'rose-600' },
    { regex: /blue-700/g, replace: 'rose-700' },
    { regex: /blue-800/g, replace: 'rose-800' },
    { regex: /blue-900/g, replace: 'rose-900' },
    { regex: /blue-950/g, replace: 'rose-950' }
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
