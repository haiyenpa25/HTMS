# Hướng dẫn Deploy lên Server Production

Sau khi tính năng đã được đẩy lên nhánh `main` trên Github, hãy truy cập vào terminal (SSH) trên server production của bạn và chạy lần lượt các lệnh sau:

### 1. Di chuyển vào thư mục dự án
```bash
cd ~/public_html
```

### 2. Cập nhật code mới nhất từ Github
```bash
git pull origin main
```

### 3. Cài đặt các gói phụ thuộc (nếu có cập nhật package.json hoặc composer.json)
```bash
composer install --no-dev --optimize-autoloader
npm install
```

### 4. Build lại giao diện Frontend (Inertia/Vue)
Vì lần này chúng ta đã sửa đổi các file `Dashboard.vue`, `PortalLayout.vue`, v.v... CẦN PHẢI build lại phân hệ frontend:
```bash
npm run build
```

### 5. Cập nhật Database
Chạy migrate với cờ `--force` để cập nhật bảng phân quyền, các block loại ban ngành.
```bash
php artisan migrate --force
```

### 6. Xóa cache và Tối ưu hệ thống
Cuối cùng, xóa toàn bộ cache cũ để hệ thống nhận diện Middleware và cấu hình mới:
```bash
php artisan optimize:clear
php artisan optimize
```

> **Lưu ý:** Đừng quên kiểm tra truy cập Admin (`/users`) và phần **Cấu Hình Tính Năng** sau khi deploy để đảm bảo database mới hoạt động ổn định trên hosting.
