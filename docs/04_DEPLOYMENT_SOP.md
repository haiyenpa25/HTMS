# 04. Quy Trình Cài Đặt (SOP) & Triển Khai Deployment

Tài liệu này hướng dẫn các bước từ Setup dự án Local, cấu hình Server cho Hội Thánh mới và nâng cấp Code trên Production.

---

## 1. YÊU CẦU HỆ THỐNG
- PHP >= 8.2 (mbstring, xml, bcmath, openssl). Node.js, NPM, Composer.
- MySQL / MariaDB (Database quản lý).

## 2. SETUP DỰ ÁN MỚI HOẶC LOCAL
```powershell
# 1. Clone Source
git clone [URL_GITHUB] directory_name
cd directory_name

# 2. Cài Packages
composer install
npm install

# 3. Môi trường & Key
cp .env.example .env
php artisan key:generate
```
**Cấu hình .env chuẩn cho Hội Thánh mới:** Cần điền đầy đủ `CHURCH_NAME`, `SYSTEM_DOMAIN`, `CHURCH_EMAIL`.

## 3. SEEDING & DATABASE SKELETON
Thay vì nhập từ đầu, sử dụng lệnh sau để định hình lại hệ thống Data:
```powershell
php artisan migrate

# Tạo cấu trúc hệ thống (Ban ngành, Superadmin Default -> Pass: Abc.1234)
php artisan db:seed --class=SystemSkeletonSeeder

# Nếu Dev Local cần data kiểm tra
php artisan db:seed --class=ThanhTrangFourMonthsSeeder
php artisan portal:seed-test-data
php artisan seed:deacon:attendance
```

## 4. QUY TRÌNH DEPLOY CODE
### Bước 1: Commit & Push (Local/DEV)
*Lưu ý trên Windows Powershell phải chạy từng dòng.*
```powershell
# Chạy Build Nodejs để complie Vue + Tailwind trước khi Up lên Github
npm run build
git add public/build
git commit -m "chore: compile frontend"

# Hoặc commit bình thường
git add -A
git commit -m "feat/fix: update codes"
git push origin main
```

### Bước 2: Kéo Code Từ Server (Production SSH - Bash Linux)
```bash
# Vào source, Pull code:
cd ~/public_html
git pull origin main

# Mệnh lệnh bắt buộc (Kết hợp luôn vào 1 dòng do chạy trên Linux)
composer install --no-dev --optimize-autoloader && php artisan migrate --force && php artisan optimize:clear && php artisan optimize
```
> **VÌ SAO `--force`?** Vì trên Production môi trường không cho phép Migrate Interactive Prompt.

### Bước 3: Build Node trên Server (Nếu cần thiết)
Nếu bạn không Build `npm run build` ở local rồi Push lên Github, bạn bắt buộc phải build trên máy chủ: `npm run build` trong Terminal.

## 5. THAY THẾ LOGO HỘI THÁNH
- Đường dẫn SVG: `resources/js/Layouts/PortalLayout.vue`.
- Đổi file ảnh (vd. `logo.png`) vào `public/images/`. Thay thế đoạn `<svg>...</svg>` thành thẻ hình `<img>`.

## 6. LỖI THƯỜNG GẶP KHU VỰC BUILD
- **Font tiếng Việt bị gãy, hiển thị bậy:** Nguyên nhân do thư mục `public/build` bị lỗi encoding. Code Vue không lưu thành chuẩn `UTF-8`. Hãy check file Code, lưu lại UTF-8 và chạy `npm run build` lần nữa.
