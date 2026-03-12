# 04. Quy Trình Cài Đặt & Triển Khai (SOP) chuẩn hóa

Tài liệu này là quy trình chuẩn mực nhất để phát triển code trên máy cá nhân (Local) và đẩy lên vận hành trên Server (Production).

---

## 1. YÊU CẦU HỆ THỐNG
- **PHP >= 8.2** (mbstring, xml, bcmath, openssl).
- **Node.js, NPM, Composer.**
- **MySQL / MariaDB** (Database).

## 2. SETUP DỰ ÁN MỚI (LOCAL)
Chạy khi mới clone code về máy cá nhân:
```powershell
# 1. Clone Source
git clone [URL_GITHUB] cms
cd cms

# 2. Cài Packages
composer install
npm install

# 3. Môi trường & Key
cp .env.example .env
php artisan key:generate
```
**Cấu hình .env chuẩn:** Cần điền đầy đủ `CHURCH_NAME`, `SYSTEM_DOMAIN`, `CHURCH_EMAIL`.

```powershell
# 4. Tạo Database và Chạy dữ liệu mẫu
php artisan migrate
php artisan db:seed --class=SystemSkeletonSeeder
```
*(Superadmin Default -> Pass: Abc.1234)*

---

## 3. WORKFLOW: TỪ LOCAL LÊN SERVER (QUAN TRỌNG)

Hệ thống quản lý phiên bản (Git) thường bị lỗi `conflict` ở thư mục `public/build` do thư mục này đang được push lên Github. 
Để tránh xung đột, chúng ta tuân thủ tuyệt đối vai trò: **Máy cá nhân là nơi Build giao diện, Server chỉ là nơi nhận Code**.

### BƯỚC 1: Xử lý tại Máy Của Bạn (Local/Dev)
Mỗi khi bạn code xong một tính năng, bạn tiến hành Build giao diện và đẩy toàn bộ lên Github.
```powershell
# 1. Build giao diện mới nhất
npm run build

# 2. Lưu lại lên Github
git add .
git commit -m "Cập nhật tính năng ABC..."
git push origin main
```
> **Mẹo:** Bạn có thể tạo file `.agents/workflows/git-deploy.md` để dùng lệnh gõ `/git-deploy` nhờ Antigravity tự động làm 3 dòng trên.

### BƯỚC 2: Kéo Web Mới Tự Động Tại Server (Production)
Vào Terminal (SSH) của Server, bạn chỉ cần gõ cụm lệnh sau để nó tự làm mới toàn bộ.
**TUYỆT ĐỐI KHÔNG CHẠY `npm run build` TRÊN SERVER NỮA NHÉ.**

```bash
# Vào thư mục
cd ~/public_html

# 1. Xóa các tệp file rác của Server sinh ra để tránh Conflict với Local
git stash
git clean -d -f public/build/

# 2. Kéo Source Code Mới (Bao gồm cả public/build đã xử xong từ máy cá nhân)
git pull origin main

# 3. Cập nhật Backend & Tối Ưu Hệ Thống
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```
> **VÌ SAO `--force`?** Vì trên máy chủ Production, hệ thống Laravel không cho phép cửa sổ bật lên hỏi Interactive Prompt Y/N. Cần `--force` để ép chạy ngầm.

---

## 4. THAY THẾ LOGO HỘI THÁNH
- Đường dẫn SVG: `resources/js/Layouts/PortalLayout.vue`.
- Đổi file ảnh (vd. `logo.png`) vào `public/images/`.
- Hãy thay thế đoạn `<svg>...</svg>` thành thẻ hình HTML: `<img src="/images/logo.png" ...>`
