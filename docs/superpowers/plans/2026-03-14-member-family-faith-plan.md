# Member Family Tree & Faith Journey Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mở rộng chức năng quản lý Hồ sơ Tín Hữu để hỗ trợ gán Chủ hộ, theo dõi Cây gia phả (Family Tree) và lưu trữ Hành trình đức tin (Faith Journey Timeline).
**Architecture:** Thêm `head_member_id` vào `households`, tạo bảng `faith_journeys`, cập nhật Controllers (MemberController, HouseholdController, Front-end API) và mở rộng Giao diện `Members/Show.vue` chia dạng Tabs/Sections.
**Tech Stack:** Laravel, Vue 3, Inertia.js, Tailwind CSS

---

## Chunk 1: Database Setup & Models

### Task 1: Thêm `head_member_id` vào bảng `households`

**Files:**
- Create: `database/migrations/xxxx_xx_xx_add_head_member_to_households_table.php`
- Modify: `app/Models/Household.php`

- [ ] **Step 1: Tạo Migration file**
Chạy lệnh CLI: `php artisan make:migration add_head_member_to_households_table --table=households`

- [ ] **Step 2: Cập nhật Migration**
```php
public function up()
{
    Schema::table('households', function (Blueprint $table) {
        $table->foreignId('head_member_id')->nullable()->constrained('members')->nullOnDelete();
    });
}
public function down()
{
    Schema::table('households', function (Blueprint $table) {
        $table->dropForeign(['head_member_id']);
        $table->dropColumn('head_member_id');
    });
}
```

- [ ] **Step 3: Cập nhật Model Household**
Thêm `head_member_id` vào `$fillable`.
Tạo Relation:
```php
public function head()
{
    return $this->belongsTo(Member::class, 'head_member_id');
}
```

---

### Task 2: Tạo Bảng và Model `FaithJourney`

**Files:**
- Create: `app/Models/FaithJourney.php`
- Create: `database/migrations/xxxx_xx_xx_create_faith_journeys_table.php`
- Modify: `app/Models/Member.php`

- [ ] **Step 1: Tạo Migration và Model**
Chạy lệnh: `php artisan make:model FaithJourney -m`

- [ ] **Step 2: Cập nhật Migration faith_journeys**
```php
public function up()
{
    Schema::create('faith_journeys', function (Blueprint $table) {
        $table->id();
        $table->foreignId('member_id')->constrained()->cascadeOnDelete();
        $table->date('event_date');
        $table->string('event_type'); // tin_chua, bap_tem, bat_tay, nhan_chuc, thuyen_chuyen, ky_luat, khac
        $table->text('description')->nullable();
        $table->string('related_person_or_church')->nullable();
        $table->timestamps();
    });
}
```

- [ ] **Step 3: Cập nhật Model FaithJourney**
```php
protected $fillable = ['member_id', 'event_date', 'event_type', 'description', 'related_person_or_church'];
protected $casts = ['event_date' => 'date'];

public function member()
{
    return $this->belongsTo(Member::class);
}
```

- [ ] **Step 4: Cập nhật Model Member**
Thêm relation vào `Member.php`:
```php
public function faithJourneys()
{
    return $this->hasMany(FaithJourney::class)->orderBy('event_date', 'asc');
}
```

- [ ] **Step 5: Migrate Database**
Chạy lệnh `php artisan migrate`

---

## Chunk 2: Controllers & Backend Logic

### Task 3: API Cập nhật Chủ Hộ và Thêm/Xóa Quan Hệ (Relationship)

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/MemberController.php`

- [ ] **Step 1: Tạo Endpoint API**
Thêm routes vào trong Admin Middleware hoặc Member resources ở `web.php`:
```php
Route::put('households/{household}/head', [\App\Http\Controllers\MemberController::class, 'setHouseholdHead'])->name('households.set-head');
Route::post('members/{member}/relationships', [\App\Http\Controllers\MemberController::class, 'storeRelationship'])->name('members.relationships.store');
Route::delete('members/{member}/relationships/{relatedMember}', [\App\Http\Controllers\MemberController::class, 'destroyRelationship'])->name('members.relationships.destroy');
```

- [ ] **Step 2: Implement Logic trong `MemberController.php`**
```php
public function setHouseholdHead(Request $request, \App\Models\Household $household)
{
    $request->validate(['head_member_id' => 'required|exists:members,id']);
    $household->update(['head_member_id' => $request->head_member_id]);
    return back()->with('message', 'Đã cài đặt Chủ hộ thành công.');
}

public function storeRelationship(Request $request, Member $member)
{
    $request->validate([
        'related_member_id' => 'required|exists:members,id',
        'type' => 'required|string', // Vd: Cha, Mẹ, Vợ, Chồng, Con
        'inverse_type' => 'nullable|string' // Cho phép tự động tạo quan hệ đối xứng
    ]);

    // Tạo chiều 1
    \App\Models\Relationship::updateOrCreate(
        ['member_id' => $member->id, 'related_member_id' => $request->related_member_id],
        ['type' => $request->type]
    );

    // Tạo chiều 2 nếu có inverse_type
    if ($request->inverse_type) {
        \App\Models\Relationship::updateOrCreate(
            ['member_id' => $request->related_member_id, 'related_member_id' => $member->id],
            ['type' => $request->inverse_type]
        );
    }
    return back()->with('message', 'Thêm quan hệ gia đình thành công.');
}

public function destroyRelationship(Member $member, $relatedMemberId)
{
    \App\Models\Relationship::where('member_id', $member->id)->where('related_member_id', $relatedMemberId)->delete();
    \App\Models\Relationship::where('member_id', $relatedMemberId)->where('related_member_id', $member->id)->delete();
    return back()->with('message', 'Xóa quan hệ thành công.');
}
```

---

### Task 4: API Hành trình Đức tin

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/FaithJourneyController.php` (Tạo mới)

- [ ] **Step 1: Cấu hình Route**
```php
Route::resource('faith-journeys', \App\Http\Controllers\FaithJourneyController::class)->only(['store', 'update', 'destroy']);
```

- [ ] **Step 2: Viết `FaithJourneyController`**
```php
namespace App\Http\Controllers;
use App\Models\FaithJourney;
use Illuminate\Http\Request;

class FaithJourneyController extends Controller
{
    public function store(Request $request) {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'event_date' => 'required|date',
            'event_type' => 'required|string',
            'description' => 'nullable|string',
            'related_person_or_church' => 'nullable|string'
        ]);
        FaithJourney::create($validated);
        return back()->with('message', 'Đã thêm Mốc sự kiện vào Hành trình.');
    }
    public function destroy(FaithJourney $faithJourney) {
        $faithJourney->delete();
        return back()->with('message', 'Đã xóa Mốc sự kiện khỏi Hành trình.');
    }
}
```

- [ ] **Step 3: Cập nhật hàm `show` trong `MemberController`**
```php
$member->load([
    'household.members', // Tải hộ gia đình và mọi thành viên cùng nhà
    'faithJourneys',
    'relatedTo',
    'relatedFrom'
]);
// Trả về Vue props cùng data này.
```

---

## Chunk 3: Frontend UI Components

### Task 5: Setup Component & Props ở `Members/Show.vue`

**Files:**
- Modify: `resources/js/Pages/Members/Show.vue`
- Create: `resources/js/Components/Member/FamilyTreeCard.vue`
- Create: `resources/js/Components/Member/FaithJourneyTimeline.vue`

- [ ] **Step 1: Hiển thị Huy hiệu Chủ Hộ tại thẻ tổng quát (Card Info)**
Vào `Show.vue`: Kiểm tra nếu `member.id === member.household.head_member_id`, hiển thị Text/Badge: `🥇 Chủ Hộ`.
Kế bên tên chức vụ hiển thị ở Card Member Info.

- [ ] **Step 2: Tạo Layout chia Tabs/Khu vực**
Tạo 2 thẻ Card mới ở phía dưới "Chi tiết thông tin":
1. Thẻ "Gia đình & Người thân" nhúng `<FamilyTreeCard :member="member" />`
2. Thẻ "Hành trình Đức tin" nhúng `<FaithJourneyTimeline :member="member" />`

- [ ] **Step 3: Viết component `FamilyTreeCard.vue`**
- Giao diện UI thống kê thành viên Cùng nhà (`member.household.members`).
- Label Chủ hộ nổi bật.
- Cung cấp tính năng gán làm Chủ Hộ: Gửi req `PUT` lên `/households/{id}/head`.
- Danh sách những người ngoài Hộ có cùng `relationships` (Tín hữu liên kết). Box thông báo: "Người thân trực hệ/ngoài hộ".

- [ ] **Step 4: Viết component `FaithJourneyTimeline.vue`**
- Đọc mảng `member.faith_journeys` và render thành list `border-l-2` dọc tạo Timeline.
- Nút "Thêm biến cố". Modal `<form>` nhập thông tin `event_type`, `event_date`, `description`, `related_person_or_church`.
- Xóa mốc sự kiện. Đổi màu icon circle theo `event_type` bằng Computed Class (`tin_chua` = Green, `bap_tem` = Blue, `ky_luat` = Red).

---

## Task Final: User Guide & Documentation (MANDATORY)

- [ ] **Step 1: Write User Guide**
Viết/hoặc cập nhật tài liệu hướng dẫn sử dụng vào `walkthrough.md`. Mô tả luồng phân biệt Chủ Hộ, các cấp Family Tree, hướng dẫn nhập Hành Trình.
- [ ] **Step 2: Capture & Embed Images**
Chụp màn hình giao diện (screenshots) phần Gia phả và Lịch sử Đức tin chèn (embed) trực tiếp vào tài liệu hướng dẫn sử dụng `walkthrough.md`. Việc có hình ảnh minh họa kèm theo là BẮT BUỘC.
