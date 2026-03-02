# Quy định chung cho hệ thống (Guardrails)

1. Mọi Module mới khi tạo ra, Agent phải cập nhật danh sách Permission vào lệnh `permissions:sync` (`app/Console/Commands/SyncPermissions.php`) và chạy nó ngay lập tức. Điều này giúp đảm bảo tài khoản Quản trị luôn có đủ quyền quản lý và thao tác đối với mọi tính năng.
