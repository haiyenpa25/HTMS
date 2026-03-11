---
description: Git commit, push lên GitHub và deploy lên server production
---

🚀 WORKFLOW: GIT DEPLOY (WINDOWS ➔ SERVER)
⚠️ **LƯU Ý WINDOWS**: Tuyệt đối KHÔNG dùng `&&` để nối lệnh trên PowerShell. Hãy sử dụng dấu `;` hoặc chạy từng lệnh.


---

💻 **BƯỚC 1: LOCAL — Commit + Build + Push**

```powershell
# 1.1 Commit code
git add -A; git commit -m "Update logic and features"

# 1.2 Build (BẮT BUỘC)
npm run build

# 1.3 Commit build assets và push
git add public/build; git commit -m "Deploy: Vite build assets production"
git push origin main
```

---

🌐 **BƯỚC 2: SERVER — In lệnh để bạn chép vào terminal**

Sau khi push xong, thông báo cho người dùng copy đoạn lệnh sau vào SSH terminal của server:

```bash
cd ~/public_html && git fetch origin && git reset --hard origin/main && php artisan optimize && php artisan view:clear
```

*Lưu ý: Nếu bị lỗi quyền ghi file, chạy thêm: `chmod -R 775 storage bootstrap/cache`*