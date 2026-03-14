const fs = require('fs');
const path = require('path');

function walkDir(dir, callback) {
  if (!fs.existsSync(dir)) return;
  fs.readdirSync(dir).forEach(f => {
    let dirPath = path.join(dir, f);
    let isDirectory = fs.statSync(dirPath).isDirectory();
    isDirectory ? walkDir(dirPath, callback) : callback(path.join(dir, f));
  });
}

function processFile(filePath) {
    if (!filePath.endsWith('.php') && !filePath.endsWith('.vue')) return;
    
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;
    
    // PHP/Routes replacements
    if (filePath.endsWith('.php')) {
        // middleware(['role:Super_Admin|Pastor']) => middleware(['is_superadmin'])
        const routeRegex = /['"]role:[^'"]+['"]/g;
        // The codebase actually might not use a dedicated middleware string replacement easily,
        // let's replace Spatie role middlewares with the new SuperAdmin check or nothing
        // if it's purely SuperAdmin, we can use `\App\Http\Middleware\EnsureSuperAdmin::class`
        content = content.replace(/['"]role:Super_Admin['"]/g, "'\\App\\Http\\Middleware\\EnsureSuperAdmin::class'");
        
        // Remove middleware group for roles if they contain other things, 
        // Note: this is a bit risky to regex globally if we don't know the exact syntax.
    }

    // Vue replacements (often $page.props.auth.roles.includes('...') or can('...'))
    if (filePath.endsWith('.vue')) {
        const vueRoleRegex = /\$page\.props\.auth\.roles\.includes\(['"][^'"]+['"]\)/g;
        content = content.replace(vueRoleRegex, 'isSuperAdmin');
        
        const hasRoleVueRegex = /hasRole\(['"][^'"]+['"]\)/g;
        content = content.replace(hasRoleVueRegex, 'isSuperAdmin');
    }

    if (content !== original) {
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`Updated: ${filePath}`);
    }
}

walkDir(path.join(__dirname, 'routes'), processFile);
walkDir(path.join(__dirname, 'resources', 'js'), processFile);
console.log("Done checking routes and Vue files.");
