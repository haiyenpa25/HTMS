<?php

namespace App\Observers;

use App\Models\OrgMembership;
use App\Models\ChronicleEntry;

class OrgMembershipObserver
{
    /**
     * Handle the OrgMembership "created" event.
     */
    public function created(OrgMembership $orgMembership): void
    {
        //
    }

    /**
     * Handle the OrgMembership "updated" event.
     */
    public function updated(OrgMembership $orgMembership): void
    {
        //
    }

    /**
     * Handle the OrgMembership "deleted" event.
     */
    public function deleted(OrgMembership $orgMembership): void
    {
        // Skip tracking if the member was just barely added then removed instantly
        $daysInRole = 0;
        $startedAt = $orgMembership->join_date ?? $orgMembership->created_at;
        
        if ($startedAt) {
            $daysInRole = $startedAt->diffInDays(now());
        }

        if ($daysInRole < 1) {
            return; // Don't log rapid mistake assignments
        }

        // Try to load the related models
        $member = $orgMembership->member;
        $role = $orgMembership->role;
        $model = $orgMembership->model; // The Department or Team

        if (!$member || !$role || !$model) {
            return;
        }

        // Log the period they served
        $title = "Kết thúc nhiệm kỳ: {$role->name} - {$model->name}";
        $description = "Tín hữu {$member->full_name} ({$member->member_code}) đã hoàn thành trách nhiệm với vai trò {$role->name} tại {$model->name}.";

        ChronicleEntry::create([
            'type' => 'auto',
            'category' => 'leadership',
            'title' => $title,
            'description' => $description,
            'occurred_at' => $startedAt->format('Y-m-d'),
            'ended_at' => now()->format('Y-m-d'),
            'subject_type' => get_class($member),
            'subject_id' => $member->id,
            'meta_data' => [
                'role_code' => $role->code,
                'department_id' => $model->id,
                'days_served' => $daysInRole
            ]
        ]);
    }

    /**
     * Handle the OrgMembership "restored" event.
     */
    public function restored(OrgMembership $orgMembership): void
    {
        //
    }

    /**
     * Handle the OrgMembership "force deleted" event.
     */
    public function forceDeleted(OrgMembership $orgMembership): void
    {
        //
    }
}
