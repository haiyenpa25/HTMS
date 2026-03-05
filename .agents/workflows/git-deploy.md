---
description: Git commit, push lên GitHub và deploy lên server production
---

# Workflow Git Deploy (Windows + Server SSH)

> **Lưu ý Windows**: Trên PowerShell, KHÔNG dùng `&&` để nối lệnh. Chạy từng lệnh riêng.

## Khi user đề cập "cập nhật git" hoặc "push lên git"

// turbo-all

### 1. Xem trạng thái file thay đổi
```
git status
```

### 2. Add toàn bộ file thay đổi
```
git add -A
```

### 3. Commit với message mô tả tính năng
```
git commit -m "[Mô tả ngắn gọn tính năng / sửa lỗi]"
```

### 4. Build Vite assets (nếu có thay đổi Vue/JS)
```
npm run build
```

### 5. Add và commit public/build
```
git add public/build
```
```
git commit -m "Deploy: Vite build assets"
```

### 6. Push lên GitHub
```
git push origin main
```

## Deploy lên Server (SSH tự động)

Server: `quanl3363@[server-ip]` | Mật khẩu: `TML@2025`
Thư mục: `/home/quanl3363/public_html`

Lệnh user cần chạy trên server:
```bash
cd ~/public_html
git pull origin main
php artisan route:clear
php artisan config:clear
php artisan optimize
```

> **Lưu ý**: Không cần `npm run build` trên server vì đã commit `public/build`.
> Nếu có migration mới: thêm `php artisan migrate --force`
