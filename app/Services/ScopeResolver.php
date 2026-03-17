<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class ScopeResolver
{
    /**
     * Apply the given security scope to the query builder based on the MAC V2 rules.
     *
     * @param Builder|mixed $query The Eloquent Query Builder (e.g., Member::query())
     * @param string|bool $scope The resolved scope ('global', 'dept', 'group', 'self') or false if denied.
     * @param int|null $deptId Active Department ID context
     * @param int|null $userId Active User ID
     * @param string $deptColumn The DB column representing the department relation (e.g. 'department_id')
     * @param string $userColumn The DB column representing the creator/owner relation (e.g. 'user_id')
     * @return Builder|mixed
     */
    public static function apply($query, $scope, ?int $deptId = null, ?int $userId = null, string $deptColumn = 'department_id', string $userColumn = 'user_id')
    {
        if ($scope === false || $scope === 'none' || $scope === null) {
            return $query->whereRaw('0 = 1');
        }

        return match ($scope) {
            'global' => $query, // No restrictions, query everything
            'dept'   => $query->where($deptColumn, $deptId),
            // 'group' can be added later if group logic is fully defined
            'self'   => $query->where($userColumn, $userId),
             // Fallback to strict false if scope is invalid, preventing data leaks
            default  => $query->whereRaw('0 = 1'),
        };
    }
}
