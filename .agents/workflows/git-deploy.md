---
description: Build frontend, commit and push to main branch
---

This workflow automates the process of building the frontend, committing the changes, and pushing them to the main branch on GitHub.

// turbo-all
1. Build the frontend
```bash
npm run build
```

2. Add all changes to git
```bash
git add .
```

3. Commit the changes
```bash
git commit -m "Auto-deploy: Built frontend and updated codes"
```

4. Push to main branch
```bash
git push origin main
```

5. Pull về server (chạy thủ công trên server qua SSH)
```bash
cd /var/www/html/cms
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan db:backup
php artisan migrate --force
php artisan mac:sync-features
php artisan config:cache
php artisan route:cache
php artisan view:cache
```