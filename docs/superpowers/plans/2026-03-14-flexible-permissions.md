# Refactor Permission to Matrix Access Control (MAC) Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor the Access Control logic to remove Spatie Roles dependency and rely purely on user's `is_superadmin` flag and their Matrix features (`UserDepartmentFeature`) at each active portal. Context switching architecture remains perfectly intact.

**Architecture:** We will introduce a nullable `is_superadmin` boolean flag on the User model as the sole ultimate permission exception (God Mode). `PortalService` will be refactored to check `is_superadmin` and supply `allowed_features` (an array of feature slugs) based on the user's active department. Frontend will hide or show elements by looking at `page.props.auth.allowed_features` rather than checking Spatie roles.

**Tech Stack:** Laravel 11, Vue 3, Inertia.

---

## Chunk 1: Database and Model Updates

### Task 1: Introduce Superadmin Flag
**Files:**
- Create: `database/migrations/[timestamp]_add_is_superadmin_to_users_table.php`
- Modify: `app/Models/User.php`

- [ ] **Step 1: Write Migration**
```bash
php artisan make:migration add_is_superadmin_to_users_table --table=users
```
Add `$table->boolean('is_superadmin')->default(false);` into the up() method. And `$table->dropColumn('is_superadmin');` in down().

- [ ] **Step 2: Run Migration**
```bash
php artisan migrate
```

- [ ] **Step 3: Update User Model**
In `app/Models/User.php`:
Change the `isSuperAdmin()` method to:
```php
public function isSuperAdmin(): bool
{
    // Keeping backward compat with Pastor or Super_Admin just in case before Spatie is fully purged,
    // but primarily using the new flag.
    return $this->is_superadmin || $this->email === 'superadmin@httlthanhmyloi.com' || $this->hasRole(['Super_Admin', 'Pastor']);
}
```

- [ ] **Step 4: Commit**
Commit changes of Database & User Model.

---

## Chunk 2: Backend Logic Refactoring

### Task 2: Refactor `PortalService`
**Files:**
- Modify: `app/Services/PortalService.php`
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`

- [ ] **Step 1: Update PortalService.php**
Add a new method `getAllowedFeaturesForDept(User $user, int $deptId)`
```php
public function getAllowedFeaturesForDept(User $user, int $deptId): array
{
    if ($user->isSuperAdmin()) {
        // Return all active features slug within the system
        return \App\Models\Feature::pluck('slug')->toArray();
    }

    return \App\Models\UserDepartmentFeature::where('user_id', $user->id)
        ->where('department_id', $deptId)
        ->where('is_enabled', true)
        ->with('feature')
        ->get()
        ->pluck('feature.slug')
        ->toArray();
}
```

Update `getAvailableDepartments(User $user, string $block)` to:
```php
public function getAvailableDepartments(User $user, string $block = 'activities'): Collection
{
    if ($user->isSuperAdmin()) {
        return Department::where('block', $block)->orderBy('name')->get();
    }
    // ... keep the rest of the MAC logic and legacy member path exactly the same
}
```

- [ ] **Step 2: Update HandleInertiaRequests Middleware**
This provides the allowed_features directly into `page.props.auth` so that any component can check features globally.
In `app/Http/Middleware/HandleInertiaRequests.php` inside `share(Request $request)` `auth` array:
```php
$activeDeptId = session('active_portal_dept_id');
$allowedFeatures = [];
if ($user && $activeDeptId) {
    /** @var \App\Services\PortalService $portalService */
    $portalService = app(\App\Services\PortalService::class);
    $allowedFeatures = $portalService->getAllowedFeaturesForDept($user, $activeDeptId);
}

// Inside auth return:
'user' => $user ? array_merge($user->only('id', 'name', 'email'), [
    // Leave roles for now for backwards compat, eventually drop
    'roles' => $user->getRoleNames(),
    'is_superadmin' => $user->isSuperAdmin(),
]) : null,
'allowed_features' => $allowedFeatures,
```

- [ ] **Step 3: Commit**
Commit Backend Logic Refactoring.

---

## Chunk 3: Frontend Feature Integration

### Task 3: Vue Composable and Frontend Rules
**Files:**
- Modify: `resources/js/Pages/Ministry/Index.vue`
- Modify: `resources/js/Pages/Portal/Index.vue`
- Create: `resources/js/Composables/usePermissions.js`

- [ ] **Step 1: Create `usePermissions.js`**
Create `resources/js/Composables/usePermissions.js`:
```js
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const page = usePage();

    const isSuperAdmin = computed(() => {
        return page.props.auth?.user?.is_superadmin || false;
    });

    const allowedFeatures = computed(() => {
        return page.props.auth?.allowed_features || [];
    });

    const can = (featureSlug) => {
        if (isSuperAdmin.value) return true;
        return allowedFeatures.value.includes(featureSlug);
    };

    return {
        can,
        isSuperAdmin,
        allowedFeatures
    };
}
```

- [ ] **Step 2: Update Portal & Ministry index Vue**
In `Portal/Index.vue` and `Ministry/Index.vue`:
Import `usePermissions`:
```js
import { usePermissions } from '@/Composables/usePermissions';
const { can, isSuperAdmin } = usePermissions();
```
Change all manual role checks for icons.
E.g., for `Phân Công` card, change `v-show="isAdmin || isPastor || isLeader"` mapping to the newly computed `can('assignments')`. Do this for all logical cards:
`Điểm danh => can('attendance')`
`Thăm viếng => can('visitation')`
`Thành viên => can('members')`
`Phân công => can('assignments')`
`Báo cáo => can('reports')`
`Tài chính => can('finance')`
`Lớp học => can('education-classes')`

- [ ] **Step 3: Commit**
Commit Frontend Integration Refactoring.

---

### Task Final: User Guide & Documentation (MANDATORY)

- [ ] **Step 1: Write User Guide**
Update `walkthrough.md` with notes on how to use `usePermissions` and `can('feature')` on the front-end for further development.
- [ ] **Step 2: Capture & Embed Images**
Embed a demonstration logic of the portal reacting purely to Feature Toggle instead of Spatie roles.
