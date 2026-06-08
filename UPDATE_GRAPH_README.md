# HTMS VibeCode Graph Updater

Script PowerShell cập nhật Knowledge Graph của HTMS codebase sau khi thêm/sửa file.

## Cách sử dụng

```powershell
cd d:\Xampp\htdocs\cms

# Cập nhật thông thường (sau khi thêm file mới)
.\update_graph.ps1

# Cập nhật + mở Visual Graph trên browser
.\update_graph.ps1 -OpenUI

# Bật chế độ auto-update khi lưu file (Ctrl+C để dừng)
.\update_graph.ps1 -WatchMode
```

## Script làm gì?

| Bước | Lệnh | Output |
|------|------|--------|
| 1 | `python -m vibecode enrich` | `vibecode_enrichment.json` + `docs/KNOWLEDGE_GRAPH.md` |
| 2 | `python -m vibecode scan` | Cập nhật `vibecode.db` (SQLite graph index) |

## Sau khi chạy xong

```powershell
# Mở Visual Graph UI trên browser
python -m vibecode ui

# Xem context của 1 file cụ thể
python -m vibecode context app/Http/Controllers/Portal/VisitationController.php

# Xem call graph của 1 function
python -m vibecode graph index

# Tạo AI prompt cho 1 file
python -m vibecode prompt "fix bug" app/Services/ScopeResolver.php
```

## Dependencies (tự động cài nếu thiếu)

File `vibecode/requirements.txt` chứa danh sách. Cài thủ công:
```powershell
python -m pip install -r vibecode/requirements.txt
```
