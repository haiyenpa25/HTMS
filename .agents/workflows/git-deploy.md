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

_Server: ConasiHosting / CyberPanel. PHP CLI = `/usr/local/lsws/lsphp83/bin/php` (không phải `/usr/bin/php`)._
_`git checkout -- index/` để discard auto-generated index files tránh conflict._
```bash
cd ~/public_html \
  && mkdir -p ~/bin && ln -sf /usr/local/lsws/lsphp83/bin/php ~/bin/php && export PATH="$HOME/bin:$PATH" \
  && git checkout -- index/ \
  && git pull origin main \
  && composer install --no-dev --optimize-autoloader --ignore-platform-reqs \
  && php artisan migrate --force \
  && php artisan config:cache \
  && php artisan route:cache \
  && php artisan view:cache
```