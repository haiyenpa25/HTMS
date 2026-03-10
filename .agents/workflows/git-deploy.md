---
description: Git commit, push lên GitHub và deploy lên server production
---

🚀 WORKFLOW: GIT DEPLOY (WINDOWS ➔ SERVER)
⚠️ **LƯU Ý WINDOWS**: Tuyệt đối KHÔNG dùng `&&` để nối lệnh trên PowerShell. Hãy sử dụng dấu `;` hoặc chạy từng lệnh.

🔑 **0. THÔNG TIN KẾT NỐI SERVER (SSH)**
- **IP**: `172.20.0.240`
- **User/Pass**: `quanl3363` / `TML@2025`
- **Thư mục**: `/home/quanly.httlthanhmyloi.com/public_html`

---

💻 **BƯỚC 1: THỰC THI TẠI MÁY LOCAL (WINDOWS POWERSHELL)**
Mục tiêu: Đóng gói mã nguồn, biên dịch giao diện và đẩy lên GitHub.

**Copy và dán toàn bộ đoạn dưới đây vào PowerShell:**
```powershell
# 1.1 Thêm và Commit code logic
git add -A; git commit -m "Update logic and features"

# 1.2 Biên dịch Giao diện (Vite Build) - BẮT BUỘC
npm run build

# 1.3 Thêm Assets và Push lên GitHub
git add public/build; git commit -m "Deploy: Vite build assets production"; git push origin main
```

---

🌐 **BƯỚC 2: THỰC THI TẠI SERVER (LINUX/SSH)**
Mục tiêu: Kéo code về và tối ưu hóa hệ thống.

**Sau khi SSH vào server, chạy các lệnh sau:**
```bash
# Vào thư mục web
cd ~/public_html

# Pull code mới và Reset Hard (đảm bảo sạch sẽ)
git fetch origin; git reset --hard origin/main

# Chạy Migration (nếu có bảng mới)
php artisan migrate --force

# Dọn dẹp và Tối ưu hóa bộ nhớ đệm
php artisan optimize
php artisan view:clear
```

---
*Lưu ý: Nếu bị lỗi quyền ghi file, chạy lệnh: `chmod -R 775 storage bootstrap/cache`*
