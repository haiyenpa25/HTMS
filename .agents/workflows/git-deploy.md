---
description: Git commit, push lên GitHub và deploy lên server production
---

🚀 WORKFLOW: GIT DEPLOY (WINDOWS ➔ SERVER)
⚠️ LƯU Ý WINDOWS: Tuyệt đối KHÔNG dùng && để nối lệnh trên PowerShell. Hãy chạy từng khối lệnh dưới đây.

🔑 0. THÔNG TIN KẾT NỐI SERVER (SSH)
Host/IP: 172.20.0.240

User: quanl3363

Password: TML@2025

Thư mục: /home/quanl3363/public_html

💻 BƯỚC 1: THỰC THI TẠI MÁY LOCAL (WINDOWS)
Mục tiêu: Đóng gói và đẩy mã nguồn lên GitHub.

PowerShell
# 1.1 Kiểm tra và Commit code logic
git status
git add -A
git commit -m "[Mô tả: Cập nhật tính năng/Sửa lỗi]"

# 1.2 Biên dịch Giao diện (Vite Build)
npm run build

# 1.3 Đóng gói Assets và Push lên GitHub
git add public/build
git commit -m "Deploy: Vite build assets production"
git push origin main
🌐 BƯỚC 2: THỰC THI TẠI SERVER (LINUX/SSH)
Mục tiêu: Kéo code về và tối ưu hóa hệ thống. Copy toàn bộ khối này dán vào Terminal SSH.

Bash
# Di chuyển vào thư mục dự án
cd ~/public_html

# Cập nhật mã nguồn mới nhất
git pull origin main

# Cập nhật Database (Dùng --force để bỏ qua xác nhận trên Production)
php artisan migrate --force

# Dọn dẹp bộ nhớ đệm và tối ưu hóa
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan optimize

# (Tùy chọn) Sửa lỗi quyền ghi file nếu có
chmod -R 775 storage bootstrap/cache