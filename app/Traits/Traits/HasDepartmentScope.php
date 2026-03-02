<?php

namespace App\Traits\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasDepartmentScope
{
    /**
     * Scope a query to only include records for the currently active department.
     */
    public function scopeForActiveDepartment(Builder $query): Builder
    {
        $activeDeptId = session('active_portal_dept_id');
        
        if (!$activeDeptId) {
            // Depending on architecture, you might want to return 0 results 
            // instead of all results to prevent data leaks if accessed outside portal.
            return $query->where('department_id', 0); 
        }

        return $query->where('department_id', $activeDeptId);
    }
}
