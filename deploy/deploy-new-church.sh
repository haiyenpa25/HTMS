#!/bin/bash
# ═══════════════════════════════════════════════════════════════════════════
#  deploy-new-church.sh — Cài đặt HTMS cho Hội Thánh MỚI (chạy trên server)
# ═══════════════════════════════════════════════════════════════════════════
#
#  CÁCH DÙNG (chạy trên server qua SSH):
#
#    Cách 1 — Chạy trực tiếp từ GitHub (nhanh nhất):
#      bash <(curl -s https://raw.githubusercontent.com/haiyenpa25/HTMS/main/deploy/deploy-new-church.sh)
#
#    Cách 2 — Upload file rồi chạy:
#      scp deploy/deploy-new-church.sh user@server:~/
#      ssh user@server "bash ~/deploy-new-church.sh"
#
#  YÊU CẦU TRƯỚC KHI CHẠY:
#    - Đã tạo website + database trong CyberPanel
#    - cd vào thư mục public_html (phải rỗng hoặc chỉ có index.html)
#    - Có kết nối internet để clone GitHub
#
# ═══════════════════════════════════════════════════════════════════════════

set -euo pipefail

# ── Cấu hình ─────────────────────────────────────────────────────────────────
REPO="https://github.com/haiyenpa25/HTMS.git"
PHP_BIN=/usr/local/lsws/lsphp83/bin/php
PHP_ALT=/usr/bin/php8.3

# Phát hiện PHP đúng
if [ -x "$PHP_BIN" ]; then
    PHP="$PHP_BIN"
elif [ -x "$PHP_ALT" ]; then
    PHP="$PHP_ALT"
else
    PHP=$(which php 2>/dev/null || echo "")
    if [ -z "$PHP" ]; then
        echo "❌ Không tìm thấy PHP. Vui lòng cài PHP 8.x trước."
        exit 1
    fi
fi

# Đảm bảo ~/bin/php trỏ đúng
mkdir -p ~/bin
ln -sf "$PHP" ~/bin/php
export PATH="$HOME/bin:/usr/local/lsws/lsphp83/bin:$PATH"

# Phát hiện Composer
if command -v composer &>/dev/null; then
    COMPOSER="composer"
elif [ -f "/usr/local/bin/composer" ]; then
    COMPOSER="$PHP /usr/local/bin/composer"
elif [ -f "$HOME/composer.phar" ]; then
    COMPOSER="$PHP $HOME/composer.phar"
else
    echo "📥 Đang tải Composer về..."
    curl -sS https://getcomposer.org/installer | $PHP -- --install-dir="$HOME/bin" --filename=composer
    COMPOSER="$HOME/bin/composer"
fi

# ── Banner ────────────────────────────────────────────────────────────────────
echo ""
echo "══════════════════════════════════════════════════════════════"
echo "  🏛️  HTMS — HỆ THỐNG QUẢN LÝ HỘI THÁNH"
echo "  Phiên bản: $(date '+%Y-%m-%d')"
echo "══════════════════════════════════════════════════════════════"
echo "  PHP    : $PHP ($($PHP -r 'echo PHP_VERSION;'))"
echo "  Thư mục: $(pwd)"
echo "══════════════════════════════════════════════════════════════"
echo ""

# ── Bước 1: Clone hoặc Pull source code ──────────────────────────────────────
echo "📥 [1/8] Lấy source code từ GitHub..."
if [ -f "artisan" ]; then
    echo "   ℹ️  Source đã tồn tại → git pull..."
    git checkout -- index/ 2>/dev/null || true
    git pull origin main
    echo "   ✅ Đã cập nhật source code."
elif [ "$(ls -A . 2>/dev/null | grep -v '^index$' | grep -v '^\.git$')" ]; then
    echo "   ⚠️  Thư mục không rỗng nhưng chưa có Laravel."
    echo "   Đang xóa file rác và clone lại..."
    find . -maxdepth 1 ! -name '.' ! -name '.git' ! -name 'index' -exec rm -rf {} + 2>/dev/null || true
    git clone "$REPO" . --depth=1
else
    git clone "$REPO" . --depth=1
    echo "   ✅ Clone xong."
fi

# ── Bước 2: Cấu hình .env ────────────────────────────────────────────────────
echo ""
echo "⚙️  [2/8] Cấu hình môi trường (.env)..."
if [ ! -f ".env" ]; then
    cp deploy/.env.church.example .env
    echo ""
    echo "   ┌────────────────────────────────────────────────────────────"
    echo "   │  ⚠️  QUAN TRỌNG: Điền thông tin vào .env trước khi tiếp!"
    echo "   │"
    echo "   │  Mở file .env bằng lệnh:  nano .env"
    echo "   │"
    echo "   │  Cần điền:"
    echo "   │    APP_URL=           https://quanly.ten-mien.com"
    echo "   │    DB_DATABASE=       tên database (tạo trong CyberPanel)"
    echo "   │    DB_USERNAME=       username database"
    echo "   │    DB_PASSWORD=       password database"
    echo "   │    CHURCH_NAME=       \"Hội Thánh Tin Lành Tên HT\""
    echo "   │    CHURCH_EMAIL=      email@ten-mien.com"
    echo "   │    CHURCH_ADDRESS=    \"Số ... Đường ..., Quận/Huyện, Tỉnh\""
    echo "   │    CHURCH_PHONE=      0xxxxxxxxx"
    echo "   │    SYSTEM_DOMAIN=     ten-mien.com"
    echo "   │    SUPERADMIN_PASSWORD= (mật khẩu đăng nhập lần đầu)"
    echo "   └────────────────────────────────────────────────────────────"
    echo ""
    read -p "   → Nhấn ENTER sau khi đã điền xong .env..." _DUMMY
else
    echo "   ℹ️  File .env đã có — giữ nguyên."
fi

# Kiểm tra DB config tối thiểu
DB_NAME=$(grep "^DB_DATABASE=" .env | cut -d= -f2 | tr -d '"')
if [ "$DB_NAME" = "ten_database" ] || [ -z "$DB_NAME" ]; then
    echo ""
    echo "   ❌ Bạn chưa điền DB_DATABASE trong .env!"
    echo "      Chạy lại script sau khi đã cấu hình."
    exit 1
fi

# ── Bước 3: Composer install ─────────────────────────────────────────────────
echo ""
echo "📦 [3/8] Cài PHP packages (composer install)..."
$COMPOSER install --no-dev --optimize-autoloader --ignore-platform-reqs 2>&1
echo "   ✅ Composer xong."

# ── Bước 4: APP_KEY ──────────────────────────────────────────────────────────
echo ""
echo "🔑 [4/8] Kiểm tra Application Key..."
if grep -q "APP_KEY=base64" .env; then
    echo "   ℹ️  APP_KEY đã có."
else
    $PHP artisan key:generate
    echo "   ✅ Đã tạo APP_KEY."
fi

# ── Bước 5: Storage symlink ───────────────────────────────────────────────────
echo ""
echo "🔗 [5/8] Tạo storage symlink..."
$PHP artisan storage:link 2>/dev/null || echo "   ℹ️  Symlink đã tồn tại hoặc bỏ qua."

# ── Bước 6: Database migrate ─────────────────────────────────────────────────
echo ""
echo "🗄️  [6/8] Tạo cấu trúc database (migrate)..."
$PHP artisan migrate --force
echo "   ✅ Migration xong."

# ── Bước 7: Seed dữ liệu khởi đầu ───────────────────────────────────────────
echo ""
echo "🌱 [7/8] Khởi tạo dữ liệu Hội Thánh (FoundationSeeder)..."
$PHP artisan db:seed --class=FoundationSeeder
echo "   ✅ Seed xong."

# ── Bước 8: Cache & permissions ──────────────────────────────────────────────
echo ""
echo "⚡ [8/8] Tối ưu hóa & phân quyền..."
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache
$PHP artisan queue:restart 2>/dev/null || true

chmod -R 755 storage bootstrap/cache
chown -R nobody:nobody storage bootstrap/cache 2>/dev/null || \
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# ── Hoàn tất ─────────────────────────────────────────────────────────────────
echo ""
echo "══════════════════════════════════════════════════════════════"
echo "  ✅  CÀI ĐẶT HOÀN TẤT!"
echo "══════════════════════════════════════════════════════════════"
echo ""

DOMAIN=$(grep "^SYSTEM_DOMAIN=" .env | cut -d= -f2 | tr -d '"')
PASS=$(grep "^SUPERADMIN_PASSWORD=" .env | cut -d= -f2 | tr -d '"')
URL=$(grep "^APP_URL=" .env | cut -d= -f2 | tr -d '"')

echo "  🌐 URL đăng nhập : $URL"
echo "  👤 Tài khoản     : superadmin@$DOMAIN"
echo "  🔑 Mật khẩu      : $PASS"
echo ""
echo "  📋 Bước tiếp theo (tuỳ chọn):"
echo "     Tạo tài khoản ban trưởng:"
echo "     $PHP artisan db:seed --class=OrgStructureSeeder"
echo ""
echo "     Thêm dữ liệu mẫu (demo):"
echo "     $PHP artisan db:seed --class=DemoDataSeeder"
echo ""
