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

6. (Tuỳ chọn) Cập nhật tất cả Hội Thánh cùng lúc — nếu đã có nhiều site

_Chỉnh `SITES` theo danh sách thư mục của từng HT trên server._
```bash
SITES=("$HOME/public_html" "/home/httlthanhtuyen/public_html")
PHP=/usr/local/lsws/lsphp83/bin/php
for DIR in "${SITES[@]}"; do
  echo "=== Deploying: $DIR ==="
  cd "$DIR"
  git checkout -- index/ 2>/dev/null || true
  git pull origin main
  $PHP artisan migrate --force
  $PHP artisan config:cache
  $PHP artisan route:cache
  $PHP artisan view:cache
  echo "✅ Done: $DIR"
done
```

---

## 🆕 Triển Khai Hội Thánh Mới

Xem hướng dẫn đầy đủ trong thư mục `deploy/`:

- `deploy/INSTALL.html` — Hướng dẫn HTML từng bước
- `deploy/deploy-new-church.sh` — Script tự động cài đặt
- `deploy/.env.church.example` — Mẫu cấu hình .env cho HT mới

```bash
# Chạy trên server của HT mới (trong ~/public_html rỗng)
bash <(curl -s https://raw.githubusercontent.com/haiyenpa25/HTMS/main/deploy/deploy-new-church.sh)
```