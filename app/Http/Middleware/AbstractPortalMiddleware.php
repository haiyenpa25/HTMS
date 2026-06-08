<?php

namespace App\Http\Middleware;

use App\Models\Department;
use App\Models\Feature;
use App\Models\Member;
use App\Models\OrgMembership;
use App\Models\OrgRole;
use App\Models\User;
use App\Models\UserDepartmentFeature;
use App\Services\FeatureAssignmentService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * AbstractPortalMiddleware — Base class cho tất cả portal middleware trong HTMS.
 *
 * Triết lý:
 *  - Mỗi portal middleware chỉ cần override ~4 method trừu tượng nhỏ
 *  - Toàn bộ logic MAC (Matrix Access Control) nằm ở đây, không copy-paste
 *  - Inertia props được share nhất quán qua tất cả portal
 *
 * Session Key Registry (trung tâm, dùng trong CheckFeatureAccess):
 *  'activities' => 'active_portal_dept_id'
 *  'ministry'   => 'active_ministry_dept_id'
 *  'finance'    => 'active_finance_dept_id'
 *  'deacon'     => 'active_deacon_dept_id'
 *  'secretary'  => 'active_secretary_dept_id'
 */
abstract class AbstractPortalMiddleware
{
    // ══════════════════════════════════════════════════════════════
    // SESSION KEY REGISTRY — Nguồn duy nhất (single source of truth)
    // ══════════════════════════════════════════════════════════════

    public const SESSION_KEYS = [
        'activities' => 'active_portal_dept_id',
        'ministry'   => 'active_ministry_dept_id',
        'finance'    => 'active_finance_dept_id',
        'deacon'     => 'active_deacon_dept_id',
        'secretary'  => 'active_secretary_dept_id',
    ];

    // ══════════════════════════════════════════════════════════════
    // ABSTRACT — Mỗi subclass phải định nghĩa
    // ══════════════════════════════════════════════════════════════

    /**
     * Loại portal: 'activities' | 'ministry' | 'finance' | 'deacon' | 'secretary'
     */
    abstract protected function getPortalType(): string;

    /**
     * Block của department: 'activities' | 'ministry' | 'leadership'
     */
    abstract protected function getBlock(): string;

    /**
     * Danh sách org_role codes được phép vào portal này.
     * Trả về [] nếu portal dùng MAC feature check thay vì org_role.
     */
    abstract protected function getAllowedOrgRoleCodes(): array;

    /**
     * Tên hiển thị lỗi khi không có quyền.
     */
    abstract protected function getPortalDisplayName(): string;

    // ══════════════════════════════════════════════════════════════
    // TEMPLATE METHOD — Override nếu cần logic đặc biệt
    // ══════════════════════════════════════════════════════════════

    /**
     * Resolve department active cho portal này.
     * Default: lấy dept đầu tiên trong block.
     * Override để dùng logic khác (vd: BCS cụ thể, hay dept từ feature grant).
     */
    protected function resolveActiveDepartment(User $user, bool $isAdmin): ?Department
    {
        $block = $this->getBlock();
        $sessionKey = $this->getSessionKey();
        $activeDeptId = session($sessionKey);

        if ($isAdmin) {
            if (!$activeDeptId) {
                $dept = Department::where('block', $block)->orderBy('name')->first();
                if ($dept) session([$sessionKey => $dept->id]);
                return $dept;
            }
            return Department::find($activeDeptId);
        }

        // Non-admin: validate session dept còn valid
        $validDeptIds = $this->getValidDeptIds($user);

        if ($activeDeptId && in_array($activeDeptId, $validDeptIds)) {
            return Department::find($activeDeptId);
        }

        if (!empty($validDeptIds)) {
            $dept = Department::find($validDeptIds[0]);
            session([$sessionKey => $validDeptIds[0]]);
            return $dept;
        }

        return null;
    }

    /**
     * Lấy danh sách dept IDs user được phép truy cập.
     * Default: kết hợp OrgMembership + MAC feature grants.
     */
    protected function getValidDeptIds(User $user): array
    {
        $block = $this->getBlock();
        $member = $this->getMemberForUser($user);

        $memberDeptIds = [];
        if ($member) {
            $memberDeptIds = $member->departments()
                ->where('block', $block)
                ->pluck('departments.id')
                ->toArray();
        }

        $featureDeptIds = UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('department', fn ($q) => $q->where('block', $block))
            ->pluck('department_id')
            ->toArray();

        return array_unique(array_merge($memberDeptIds, $featureDeptIds));
    }

    /**
     * Kiểm tra access đặc biệt (vd: org_role check cho Deacon/Secretary).
     * Default: dùng getAllowedOrgRoleCodes() nếu có, nếu không thì dùng validDeptIds.
     */
    protected function checkSpecialAccess(User $user): bool
    {
        $codes = $this->getAllowedOrgRoleCodes();

        if (empty($codes)) {
            // Dùng dept access check
            return !empty($this->getValidDeptIds($user));
        }

        // Dùng org_role access check
        $member = $this->getMemberForUser($user);
        if (!$member) return false;

        return $this->hasOrgRoleAccess($member, $codes);
    }

    /**
     * Các props cố định bổ sung để share (vd: activeDeaconRole).
     * Default: không có.
     */
    protected function extraInertiaProps(User $user, bool $isAdmin): array
    {
        return [];
    }

    // ══════════════════════════════════════════════════════════════
    // FINAL HELPERS — Logic dùng chung, không override
    // ══════════════════════════════════════════════════════════════

    /**
     * Session key của portal này.
     */
    final protected function getSessionKey(): string
    {
        return self::SESSION_KEYS[$this->getPortalType()] ?? 'active_portal_dept_id';
    }

    /**
     * Lấy Member gắn với User. Single point, tránh nhiều nơi query riêng.
     */
    final protected function getMemberForUser(User $user): ?Member
    {
        return Member::where('user_id', $user->id)->first();
    }

    /**
     * Kiểm tra member có org_role trong danh sách codes (active).
     */
    final protected function hasOrgRoleAccess(Member $member, array $roleCodes): bool
    {
        $roleIds = OrgRole::whereIn('code', $roleCodes)->pluck('id');

        return OrgMembership::where('member_id', $member->id)
            ->whereIn('org_role_id', $roleIds)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Xây dựng MAC permission map cho user trong department.
     *
     * Level 1: Feature department config (FeatureDepartment)
     * Level 2: User override (UserDepartmentFeature) — strict whitelist
     *
     * @return array{ departmentFeatures: array, userPermissions: array }
     */
    final protected function buildMacProps(User $user, Department $dept, bool $isAdmin): array
    {
        $featureService = app(FeatureAssignmentService::class);
        $departmentFeatures = $featureService->getAvailableFeaturesForDepartment($dept);

        // Default: tất cả false
        $allSlugs = Feature::cachedAll()->pluck('slug');
        $userPermissions = $allSlugs->mapWithKeys(fn ($s) => [$s => false])->toArray();

        if ($isAdmin) {
            // Admin: kế thừa L1 (department features)
            foreach ($allSlugs as $slug) {
                $userPermissions[$slug] = $departmentFeatures[$slug] ?? false;
            }
        } else {
            // Normal user: strict whitelist, chỉ override từ UserDepartmentFeature
            $overrides = UserDepartmentFeature::where('user_id', $user->id)
                ->where('department_id', $dept->id)
                ->with('feature:id,slug')
                ->get();

            foreach ($overrides as $uf) {
                if (!$uf->feature) continue;
                $userPermissions[$uf->feature->slug] = $uf->is_enabled
                    ? ($uf->data_scope ?? 'dept')
                    : false;
            }
        }

        return compact('departmentFeatures', 'userPermissions');
    }

    /**
     * Share Inertia props nhất quán qua tất cả portal.
     */
    final protected function shareInertiaProps(
        User       $user,
        ?Department $dept,
        array      $macProps,
        bool       $isAdmin,
        array      $extra = []
    ): void {
        Inertia::share(array_merge([
            'portalType'         => $this->getPortalType(),
            'activeDepartment'   => $dept,
            'departmentFeatures' => $macProps['departmentFeatures'] ?? [],
            'userPermissions'    => $macProps['userPermissions'] ?? [],
            'isGlobalAdmin'      => $isAdmin,
        ], $extra));
    }

    /**
     * Redirect về login với thông báo lỗi.
     */
    final protected function redirectUnauthorized(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['email' => $message]);
    }

    // ══════════════════════════════════════════════════════════════
    // HANDLE — Template Method Pattern
    // ══════════════════════════════════════════════════════════════

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $isAdmin = $user->isSuperAdmin();

        // Access check cho non-admin
        if (!$isAdmin) {
            if (!$this->checkSpecialAccess($user)) {
                return $this->redirectUnauthorized(
                    "Bạn không có quyền truy cập {$this->getPortalDisplayName()}. Vui lòng liên hệ quản trị viên."
                );
            }
        }

        // Resolve active department
        $dept = $this->resolveActiveDepartment($user, $isAdmin);

        if (!$dept && !$isAdmin) {
            return $this->redirectUnauthorized(
                "Bạn chưa được cấp quyền truy cập {$this->getPortalDisplayName()}. Vui lòng liên hệ quản trị viên."
            );
        }

        // Build MAC props + share Inertia
        $macProps = $dept ? $this->buildMacProps($user, $dept, $isAdmin) : ['departmentFeatures' => [], 'userPermissions' => []];
        $extra = $this->extraInertiaProps($user, $isAdmin);
        $this->shareInertiaProps($user, $dept, $macProps, $isAdmin, $extra);

        // Set request attribute for controllers
        $request->attributes->set('userPermissions', $macProps['userPermissions']);
        $request->attributes->set('activeDept', $dept);
        $request->attributes->set('isGlobalAdmin', $isAdmin);

        // ✨ Feature Scope Pipeline: forward departmentFeatures (chứa data_scope string)
        // để Controller dùng ScopeResolver::apply() với đúng data scope.
        // VD: BTV (ministry) có visitation='global' → query toàn hệ thống
        //     BTTR (activities) có visitation='dept' → query nội bộ ban
        $request->attributes->set('featureScopes', $macProps['departmentFeatures'] ?? []);

        return $next($request);
    }
}
