# HƯỚNG DẪN CÀI ĐẶT VÀ TRIỂN KHAI (FOR NEW CHURCH)

Tài liệu này hướng dẫn cách sao chép (clone) source code này để triển khai cho một Hội Thánh khác với tên miền và thông tin riêng biệt.

---

## 1. Yêu Cầu Hệ Thống (Prerequisites)

Để chạy được source code Laravel + Vue.js (Inertia), máy chủ (hoặc máy cá nhân) cần cài đặt:
- **Git**: Để quản lý và tải source code.
- **PHP >= 8.2**: Cùng các extension cơ bản (mbstring, xml, bcmath, openssl).
- **Composer**: Trình quản lý thư viện PHP.
- **Node.js & NPM**: Để biên dịch giao diện (Vite).
- **MySQL / MariaDB**: Hệ quản trị cơ sở dữ liệu.

---

## 2. Quy Trình Cài Đặt Ban Đầu (Setup)

### Bước 1: Clone Source và Cài đặt thư viện
```powershell
# Clone mã nguồn
git clone [URL_GITHUB_CUA_BAN] cms_new
cd cms_new

# Cài đặt thư viện PHP
composer install

# Cài đặt thư viện Javascript
npm install
```

### Bước 2: Cấu hình Môi trường (.env)
Sao chép file mẫu và chỉnh sửa:
```powershell
cp .env.example .env
php artisan key:generate
```

Mở file `.env` và cập nhật các thông số quan trọng cho Hội Thánh mới:
```env
# Thông tin Database
DB_DATABASE=ten_db_moi
DB_USERNAME=root
DB_PASSWORD=

# --- THÔNG TIN HỘI THÁNH MỚI (QUAN TRỌNG) ---
CHURCH_NAME="Hội Thánh Tin Lành [Tên Hội Thánh]"
SYSTEM_DOMAIN="tenmienmoi.com"
CHURCH_EMAIL="contact@tenmienmoi.com"
CHURCH_ADDRESS="Địa chỉ của Hội Thánh"
CHURCH_PHONE="0123456789"
```

---

## 3. Khởi Tạo Cơ Sở Dữ Liệu (Database & Seeders)

### Bước 1: Tạo bảng
```powershell
php artisan migrate
```

### Bước 2: Khởi tạo "Khung sườn" (Skeleton)
Để hệ thống có sẵn các Ban ngành, Tài khoản Admin và cấu hình hiển thị, chạy lệnh sau:
```powershell
php artisan db:seed --class=SystemSkeletonSeeder
```
*Lệnh này sẽ tự động tạo tài khoản: **superadmin@tenmienmoi.com** (Mật khẩu mặc định: `Abc.1234`)*

### Bước 3: (Tùy chọn) Chạy dữ liệu mẫu để Test
Nếu muốn xem thử dữ liệu báo cáo và biểu đồ ngay lập tức:
```powershell
php artisan db:seed --class=ThanhTrangFourMonthsSeeder
```

---

## 4. Chạy Ứng Dụng

**Trên máy cá nhân (Local):**
```powershell
# Chạy Server PHP
php artisan serve

# Chạy biên dịch giao diện (mở tab terminal mới)
npm run dev
```

**Trên Server (Production):**
```powershell
# Build giao diện 1 lần duy nhất
npm run build

# Sau đó chỉ cần chạy PHP (Apache/Nginx sẽ trỏ vào thư mục /public)
```

---

## 5. Hướng Dẫn Thay Đổi Logo

Hiện tại logo được xử lý bằng SVG trong file:
`resources/js/Layouts/PortalLayout.vue`

**Để thay bằng ảnh của Hội Thánh bạn:**
1. Chép file ảnh (ví dụ `logo.png`) vào thư mục `public/images/`.
2. Mở file `PortalLayout.vue`.
3. Tìm đoạn mã hiển thị SVG (dòng 12-14) và thay thế bằng thẻ `<img>`:
   ```html
   <img src="/images/logo.png" class="w-10 h-10 object-contain" alt="Logo">
   ```

---

## 6. Tài Khoản Đăng Nhập Mặc Định

Sau khi chạy `SystemSkeletonSeeder`, bạn có thể đăng nhập bằng:
- **URL**: `/login`
- **User**: `superadmin@[domain_cua_ban]`
- **Pass**: `Abc.1234`

---
*Tài liệu được soạn thảo tự động bởi Antigravity.*
