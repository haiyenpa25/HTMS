<?php

namespace App\Traits;

use App\Models\Member;

trait HasDataScope
{
    /**
     * Lấy danh sách ID của Department mà User có liên kết (thuộc về)
     */
    public function getViewableDepartmentIds(): array
    {
        if (!$this->member) {
            return [];
        }

        return $this->member->departments()->pluck('departments.id')->toArray();
    }

    /**
     * Lấy danh sách ID của Team mà User có liên kết
     */
    public function getViewableTeamIds(): array
    {
        if (!$this->member) {
            return [];
        }

        return $this->member->teams()->pluck('teams.id')->toArray();
    }

    /**
     * Logic kiểm tra Scope phân quyền
     */
    public function canViewMember(Member $targetMember): bool
    {
        // 1. Quyền "church" thì thấy hết (Tất cả data Church)
        if ($this->isSuperAdmin()) {
            return true;
        }

        // 2. Quyền "self": Chỉ thấy chính mình
        if ($this->member && $this->member->id === $targetMember->id) {
            return true;
        }

        // 3. Quyền "department" (Trưởng ban, Thư ký ban): Thấy các thành viên cùng phòng ban
        if ($this->isSuperAdmin()) {
            $userDeptIds = $this->getViewableDepartmentIds();
            $targetDeptIds = $targetMember->departments()->pluck('departments.id')->toArray();
            $targetTeamDeptIds = $targetMember->teams()->pluck('department_id')->toArray();
            $allTargetDeptIds = array_unique(array_merge($targetDeptIds, $targetTeamDeptIds));
            
            // Xử lý Array IDs: Nếu có ít nhất 1 ban chung thì cho phép xem
            if (count(array_intersect($userDeptIds, $allTargetDeptIds)) > 0) {
                return true;
            }
        }

        // 4. Quyền "team" (Tổ trưởng): Thấy nhân sự trong tổ
        if ($this->isSuperAdmin()) {
            $userTeamIds = $this->getViewableTeamIds();
            $targetTeamIds = $targetMember->teams()->pluck('teams.id')->toArray();

            // Xử lý Array IDs: Nằm chung team thì được xem
            if (count(array_intersect($userTeamIds, $targetTeamIds)) > 0) {
                return true;
            }
        }

        // Khác: Không có quyền
        return false;
    }
}
