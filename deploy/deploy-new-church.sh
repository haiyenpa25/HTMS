#!/bin/bash
# ═══════════════════════════════════════════════════════════════════════════
#  deploy-new-church.sh — Script khởi tạo lần đầu cho Hội Thánh mới
# ═══════════════════════════════════════════════════════════════════════════
#
#  CÁCH DÙNG:
#    1. SSH vào server
#    2. cd ~/public_html (thư mục vừa tạo website trong CyberPanel)
#    3. bash <(curl -s https://raw.githubusercontent.com/haiyenpa25/HTMS/main/deploy/deploy-new-church.sh)
#       HOẶC upload script và chạy: bash deploy/deploy-new-church.sh
#
# ═══════════════════════════════════════════════════════════════════════════

set -e

PHP=/usr/local/lsws/lsphp83/bin/php

# Đảm bảo php và composer có thể gọi được (CyberPanel không có php trong PATH mặc định)
mkdir -p ~/bin
ln -sf "$PHP" ~/bin/php
export PATH="$HOME/bin:/usr/local/lsws/lsphp83/bin:$PATH"

# Tìm composer
if command -v composer &>/dev/null; then
    COMPOSER="composer"
elif [ -f "/usr/local/bin/composer" ]; then
    COMPOSER="$PHP /usr/local/bin/composer"
elif [ -f "$HOME/composer.phar" ]; then
    COMPOSER="$PHP $HOME/composer.phar"
else
    echo "❌ Không tìm thấy composer. Đang tải về..."
    curl -sS https://getcomposer.org/installer | $PHP -- --install-dir="$HOME/bin" --filename=composer
    COMPOSER="$HOME/bin/composer"
fi

echo ""
echo "════════════════════════════════════════════════════════════"
echo "  🏛️  HTMS — CÀI ĐẶT HỆ THỐNG QUẢN LÝ HỘI THÁNH         "
echo "════════════════════════════════════════════════════════════"
echo ""

# ── Bước 1: Clone repo ───────────────────────────────────────────────────────
if [ ! -f "artisan" ]; then
    echo "📥 [1/7] Clone source code từ GitHub..."
    git clone "$REPO" . --depth=1
    echo "✅ Clone xong."
else
    echo "📥 [1/7] Source code đã tồn tại — git pull..."
    git checkout -- index/ 2>/dev/null || true
    git pull origin main
fi

# ── Bước 2: Cấu hình .env ────────────────────────────────────────────────────
echo ""
echo "⚙️  [2/7] Cấu hình .env..."
if [ ! -f ".env" ]; then
    cp deploy/.env.church.example .env
    echo "   ✅ Đã copy .env.church.example → .env"
    echo ""
    echo "   ┌─────────────────────────────────────────────────────────"
    echo "   │  VUI LÒNG CHỈNH SỬA FILE .env TRƯỚC KHI TIẾP TỤC!"
    echo "   │"
    echo "   │  Mở file .env và điền:"
    echo "   │    DB_DATABASE=      ← tên database (đã tạo trên CyberPanel)"
    echo "   │    DB_USERNAME=      ← username DB"
    echo "   │    DB_PASSWORD=      ← password DB"
    echo "   │    APP_URL=          ← https://quanly.ten-mien.com"
    echo "   │    CHURCH_NAME=      ← Tên Hội Thánh đầy đủ"
    echo "   │    CHURCH_EMAIL=     ← email HT"
    echo "   │    CHURCH_ADDRESS=   ← địa chỉ"
    echo "   │    CHURCH_PHONE=     ← SĐT liên hệ"
    echo "   │    SYSTEM_DOMAIN=    ← ten-mien.com (để tạo superadmin@ten-mien.com)"
    echo "   │    SUPERADMIN_PASSWORD= ← mật khẩu tài khoản quản trị"
    echo "   └─────────────────────────────────────────────────────────"
    echo ""
    read -p "   ↩️  Nhấn ENTER sau khi đã chỉnh .env để tiếp tục..."
else
    echo "   ℹ️  File .env đã tồn tại — giữ nguyên."
fi

# ── Bước 3: Composer install ─────────────────────────────────────────────────
echo ""
echo "📦 [3/7] Cài đặt PHP packages (composer)..."
$COMPOSER install --no-dev --optimize-autoloader --ignore-platform-reqs
echo "✅ Composer xong."

# ── Bước 4: Application key ──────────────────────────────────────────────────
echo ""
echo "🔑 [4/7] Generate APP_KEY..."
grep -q "APP_KEY=base64" .env && echo "   ℹ️  APP_KEY đã có." || $PHP artisan key:generate

# ── Bước 5: Migrate database ─────────────────────────────────────────────────
echo ""
echo "🗄️  [5/7] Tạo cấu trúc database (migrate)..."
$PHP artisan migrate --force
echo "✅ Migration xong."

# ── Bước 6: Seed dữ liệu khởi đầu ───────────────────────────────────────────
echo ""
echo "🌱 [6/7] Khởi tạo dữ liệu Hội Thánh (FoundationSeeder)..."
$PHP artisan db:seed --class=FoundationSeeder
echo "✅ Seed xong."

# ── Bước 7: Cache & permissions ──────────────────────────────────────────────
echo ""
echo "⚡ [7/7] Tối ưu hóa & phân quyền thư mục..."
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache
chmod -R 755 storage bootstrap/cache
chown -R nobody:nobody storage bootstrap/cache 2>/dev/null || true

# ── Xong ─────────────────────────────────────────────────────────────────────
echo ""
echo "════════════════════════════════════════════════════════════"
echo "  ✅  CÀI ĐẶT HOÀN TẤT!"
echo "════════════════════════════════════════════════════════════"
echo ""

DOMAIN=$(grep SYSTEM_DOMAIN .env | cut -d= -f2 | tr -d '"')
PASS=$(grep SUPERADMIN_PASSWORD .env | cut -d= -f2 | tr -d '"')
URL=$(grep APP_URL .env | cut -d= -f2 | tr -d '"')

echo "  🌐 URL:      $URL"
echo "  👤 Đăng nhập: superadmin@$DOMAIN"
echo "  🔑 Mật khẩu: $PASS"
echo ""
echo "  📋 Bước tiếp theo (tùy chọn):"
echo "     $PHP artisan db:seed --class=OrgStructureSeeder   # Tạo tài khoản ban trưởng"
echo "     $PHP artisan db:seed --class=DemoDataSeeder        # Thêm dữ liệu mẫu"
echo ""
