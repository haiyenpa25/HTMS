# Nhật Ký Lệnh Deploy Lên Server (Production)

> **Lưu ý Deploy từ máy Windows (Local):**
> ❌ Không dùng `&&` để ghép lệnh trên PowerShell.
> Các lệnh cần chạy từng bước:
> 1. `git add -A`
> 2. `git commit -m "..."`
> 3. `npm run build`
> 4. `git add public/build`
> 5. `git commit -m "update build"`
> 6. `git push origin main`

---

## Tính năng: Phân quyền MAC và Xoá Thành Viên
*Ngày hoàn thành: 2026-03-07*

Chạy các lệnh sau trên **Server Linux (Terminal SSH)**:

```bash
cd ~/public_html
git pull origin main
php artisan migrate --force
php artisan optimize
```
*(Bạn có thể copy lệnh gộp: `cd ~/public_html && git pull origin main && php artisan migrate --force && php artisan optimize`)*

---
*Ghi chú: Khi AI hoàn tất các tính năng tiếp theo và bạn báo "Ok qua tính năng khác", lệnh pull/migrate tương ứng sẽ được tự động nối tiếp vào file này.*
