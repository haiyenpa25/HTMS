# 02. Nguyên Tắc Thiết Kế UI/UX & Coding Guidelines

Để duy trì tính đồng nhất trong toàn bộ hệ thống CMS, mọi Frontend (Vue + Tailwind) và Backend Controller cần tuân thủ triệt để các quy tắc sau.

---

## 1. NGUYÊN TẮC CHUNG CHO PORTAL
Tất cả portal đều dùng chung `PortalLayout.vue`. Việc phân biệt giao diện được hiện thực qua prop `portalType`.
- **Header:** Luôn có nền màu Blue/Emerald/Amber tuỳ theo loại. Tab navigation nằm dưới header, cho phép vuốt trên mobile.
- **Tiêu chuẩn vàng:** Mọi thay đổi về thiết kế nên nhìn vào Activities Portal (`/portal`) làm chuẩn để nhân rộng ra.

### Hệ Thống Màu Đóng Đinh
- **Sinh Hoạt:** Primary `emerald-600` | Button `bg-emerald-600 hover:bg-emerald-700` | Icon `text-emerald-500`
- **Mục Vụ:** Primary `blue-600` | Button `bg-blue-600 hover:bg-blue-700` | Icon `text-blue-500`
- **Chấp Sự:** Primary `amber-500` | Button `bg-amber-500 hover:bg-amber-600` | Icon `text-amber-600`
*(Nghiêm cấm tự sáng tạo random hex code vào màn hình).*

## 2. STANDARD COMPONENTS
### 2.1 Cards
```html
<div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-sm 
            hover:shadow-md hover:border-[color]-200 transition-all group cursor-pointer">
```

### 2.2 Form Nhập Liệu
- Chỉ dùng `useForm` của Inertia, **không dùng `axios` thủ công**.
- Dữ liệu dạng số lớn (Ví dụ: Số người tham dự): `text-4xl font-black text-[color]-600 border-2 rounded-[2rem] py-6`.
- Nút Submit chính (Save data): Luôn làm dạng **Floating Bottom Bar** để tiết kiệm thao tác người dùng (vd: `<div class="fixed bottom-0 ... bg-white/80 backdrop-blur-md">`).

### 2.3 SlideOver (Sidebar Component)
- **Component:** `@/Components/SlideOver.vue`
- **BẮT BUỘC Dùng `v-model`** để mở/đóng: `<SlideOver v-model="isOpen">`. (Không dùng event `:show` hay `@close` cũ).

### 2.4 Biểu Đồ (Charts)
- Chỉ dùng **ApexCharts** (`vue3-apexcharts`). KHÔNG dùng Chart.js để tránh nặng Bundle.
- Area chart cho dữ liệu theo tuần/thời gian. Bar chart cho phép so sánh tổng quan.

## 3. CHECKLIST TRƯỚC VÀ TRONG KHI DEV
1. **Hỏi trước khi code:** "Tính năng này thuộc portal nào?". Áp dụng màu sắc đúng.
2. **Safe Prop Access:**
   - Để tránh lỗi "White Screen", **KHÔNG** dùng `$page.props.xxx` thẳng ở thẻ template nếu prop có khả năng undefined khi Inertia vừa load.
   - Luôn dùng: `const page = usePage(); const myProp = computed(() => page.props.myProp)`.
3. **Database Migration:** Không dùng `->after('column')` vào migration nếu cột trước đó không đảm bảo tồn tại ở tất cả các DB. Đặt chúng ở cuối bảng.
4. **Encoding Files:** File Vue/JS phải được lưu ở chuẩn **UTF-8** để `npm run build` không lỗi Font tiếng Việt.
5. **Dữ Liệu Trắng / Lỗi Update:** Khi update Json hoặc Data phức tạp (ví dụ Update object Report), dùng `array_filter` hoặc kiểm soát cẩn thận, tránh tình trạng "Lưu dữ liệu" ghi đè Null làm mất trắng báo cáo của tháng.
