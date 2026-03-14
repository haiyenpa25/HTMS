# Member Family Tree & Faith Journey Design Spec

## Goal:
Mở rộng chức năng quản lý Hồ sơ Tín Hữu (Member Profile) tại đường dẫn `/members/{id}` để hiển thị 3 vùng thông tin chuyên sâu:
1. **Quản lý Chủ Hộ gia đình:** Dễ dàng nhận diện và gán tín hữu làm chủ hộ.
2. **Cây gia phả (Family Tree):** Hiển thị sơ đồ hoặc danh sách quan hệ gia đình (Cha, Mẹ, Vợ, Chồng, Con, Anh, Chị, Em).
3. **Hành trình Đức tin (Faith Journey):** Một dòng thời gian (Timeline) ghi nhận các cột mốc quan trọng trong quá trình tin Chúa và sinh hoạt tại Hội Thánh.

---

## 🏗 Data Architecture & Models

### 1. Chủ Hộ (Head of Household)
Thay vì tạo thêm một flag `is_head` trong `members` có nguy cơ gây lỗi 1 Hộ gia đình có 2 Chủ Hộ. Phương án tối ưu là cập nhật bảng `households`:
- Thêm trường `head_member_id` trỏ (Foreign Key) trực tiếp đến `members.id`.
- Mối quan hệ: 1 Household có duy nhất 1 `head_member_id`.
- Thêm logic tại Member Controller để khi gán một Member làm chủ hộ, nó sẽ Update ngược về bảng `Household`.

### 2. Cây Gia Phả (Family Relationship)
Tận dụng hệ thống Pivot Database `relationships` chia làm 2 hình thái tổ chức:
- **Cùng nhà (Household Members):** Hiển thị danh sách các thành viên chung `household_id` thành một Card Danh Sách (Danh sách người thân trong hộ).
- **Cây Quan hệ (Family Network):** Truy vấn đệ quy/2 chiều từ bảng `relationships` giữa người A và những người B liên đới. Ở bản thiết kế này, trước hết ta sẽ làm giao diện List (Cha/Mẹ, Vợ/Chồng, Con Cái) dưới góc nhìn UI Card đẹp mắt, thay vì vẽ sơ đồ Canvas phức tạp. Ví dụ:
  - Nếu A là "Cha" -> A có "Con" là B và C. B và C có thể xem mục "Cha mẹ" là A.
  - Chúng ta sẽ thiết kế UI Card "Gia Đình & Người Thân" hiển thị 2 Tab: `<Cùng Hộ Gia Đình>` và `<Quan Hệ Huyết Thống>`.

### 3. Hành Trình Đức Tin (Faith Journey) - Model mới
Cần tracking tiểu sử đức tin như: Tin Chúa, Báp-têm, Nhận chức vụ, Thuyên chuyển, Kỷ luật.
- **Table Name:** `faith_journeys`
- **Fields:**
  - `id` (PK)
  - `member_id` (FK -> members.id)
  - `event_date` (Date)
  - `event_type` (Enum/String: 'tin_chua', 'bap_tem', 'bat_tay', 'nhan_chuc', 'thuyen_chuyen', 'ky_luat', 'khac')
  - `description` (Text)
  - `related_person_or_church` (Vd: Mục sư làm Báp-têm, hoặc tên HT chuyển đến)

---

## 🖥 Frontend UI Design (Vue & Tailwind)

Trong View `resources/js/Pages/Members/Show.vue`, bổ sung các thành phần:

1. **Badge Chủ Hộ:** 
   Tại thẻ Profile tổng quan (Card Info), nếu Tín Hữu đang xem có `id === household.head_member_id`, hiển thị một Badge màu Gold 🌟 "Chủ Hộ".
2. **Tab / Section "Gia Đình":**
   Hiển thị danh sách thành viên cùng Hộ, và các thành viên được liên kết qua bảng Quan Hệ. Có nút bấm "Thêm quan hệ" mở Modal tìm kiếm người thân trong HT.
3. **Tab / Section "Tiểu Sử Đức Tin" (Timeline UI):**
   Vẽ một đường kẻ dọc (Vertical Timeline). Mỗi Item là một `FaithJourney`. 
   - Có màu sắc phân biệt (VD: Báp-têm màu Xanh, Nhận chức màu Vàng, Kỷ luật màu Đỏ).
   - Card hiển thị Ngày Tháng, Tiêu đề sự kiện, và Chi tiết người thực hiện.
   - Nút "Thêm biến cố" bên góc trên cùng để cập nhật nhanh.

---

## 📋 APIs & Controllers

1. **FaithJourneyController.php:** Hàm API để Thêm (`store`), Cập nhật (`update`) và Xóa (`destroy`) một mốc Hành Trình cho Tín Hữu.
2. **MemberRelationshipController.php:** Hàm để Tự tạo Quan hệ 2 chiều (`store`). (Lưu ý logic đối xứng: A là Cha của B, thì tự động B là Con của A).
3. **Set Head Household API:** Nút bấm gọi axios chuyển `head_member_id` vào `Households`. Cập nhật trong Dashboard quản lý Member thành logic `PUT /api/households/{id}/head`.
