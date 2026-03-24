<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Department;
use App\Models\Member;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Notifications\PendingMemberNotification;
use App\Notifications\MemberApprovalResultNotification;

class MembersController extends Controller
{
    public function index(Request $request)
    {
        $departmentId = session('active_portal_dept_id');
        if (!$departmentId) {
            return redirect()->route('portal.index');
        }

        $this->authorizeFeature('members');

        $department = Department::findOrFail($departmentId);

        $availableDepartments = app(\App\Services\PortalService::class)
            ->getAvailableDepartments(auth()->user(), 'activities');

        // Load pending members cho ban này (chờ duyệt)
        $pendingMembers = Member::where('status', 'pending')
            ->where('pending_dept_id', $departmentId)
            ->with(['submittedBy:id,name'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($m) => [
                'id'           => $m->id,
                'full_name'    => $m->full_name,
                'phone'        => $m->phone,
                'general_notes'=> $m->general_notes,
                'submitted_by' => $m->submittedBy?->name,
                'created_at'   => $m->created_at?->diffForHumans(),
            ]);

        return Inertia::render('Portal/Members/Index', [
            'department'          => $department,
            'availableDepartments'=> $availableDepartments,
            'isGlobalAdmin'       => auth()->user()->isSuperAdmin(),
            'pendingMembers'      => $pendingMembers,
            'pendingCount'        => $pendingMembers->count(),
        ]);
    }

    /**
     * Tạo thành viên tạm — ai vào được portal đều dùng được.
     */
    public function storePending(Request $request)
    {
        $departmentId = session('active_portal_dept_id');
        if (!$departmentId) return back()->withErrors(['error' => 'Không có ngữ cảnh ban ngành.']);

        $this->authorizeFeature('members');

        $validated = $request->validate([
            'full_name'     => 'required|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'general_notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $department = Department::findOrFail($departmentId);

        // Tạo thành viên với status = 'pending'
        $member = Member::create([
            'full_name'            => $validated['full_name'],
            'phone'                => $validated['phone'] ?? null,
            'general_notes'        => $validated['general_notes'] ?? null,
            'status'               => 'pending',
            'pending_dept_id'      => $departmentId,
            'submitted_by_user_id' => $user->id,
        ]);

        // Tạo ApprovalRequest
        ApprovalRequest::create([
            'requester_id'   => $member->id,
            'requester_type' => Member::class,
            'type'           => 'new_member',
            'content'        => json_encode([
                'name'       => $member->full_name,
                'phone'      => $member->phone,
                'notes'      => $member->general_notes,
                'dept_id'    => $departmentId,
                'dept_name'  => $department->name,
                'submitted_by_name' => $user->name,
            ]),
            'status' => 'pending',
        ]);

        // Notify SuperAdmins + TruongBan của ban này
        $this->notifyApprovers($member, $department, $user);

        return back()->with('success', "Đã ghi nhận \"{$member->full_name}\" — đang chờ Mục Sư / Trưởng Ban duyệt.");
    }

    /**
     * Duyệt thành viên tạm — chỉ SuperAdmin hoặc TruongBan của ban đó.
     */
    public function approvePending(Request $request, Member $member)
    {
        abort_unless(auth()->user()->isSuperAdmin() || $this->isTruongBan($member->pending_dept_id), 403);

        $member->update(['status' => 'Chính thức']);

        // Update ApprovalRequest
        ApprovalRequest::where('requester_id', $member->id)
            ->where('requester_type', Member::class)
            ->where('status', 'pending')
            ->first()?->update([
                'status'      => 'approved',
                'approver_id' => auth()->id(),
            ]);

        // Notify người tạo
        $this->notifySubmitter($member, 'approved');

        return back()->with('success', "Đã duyệt \"{$member->full_name}\" thành tín hữu chính thức.");
    }

    /**
     * Từ chối thành viên tạm.
     */
    public function rejectPending(Request $request, Member $member)
    {
        abort_unless(auth()->user()->isSuperAdmin() || $this->isTruongBan($member->pending_dept_id), 403);

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:255',
        ]);

        // Update ApprovalRequest
        ApprovalRequest::where('requester_id', $member->id)
            ->where('requester_type', Member::class)
            ->where('status', 'pending')
            ->first()?->update([
                'status'           => 'rejected',
                'approver_id'      => auth()->id(),
                'rejection_reason' => $validated['rejection_reason'] ?? null,
            ]);

        // Soft-delete member
        $memberName = $member->full_name;
        $member->delete();

        // Notify người tạo
        $this->notifySubmitter($member, 'rejected', $validated['rejection_reason'] ?? null);

        return back()->with('success', "Đã từ chối \"{$memberName}\".");
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function isTruongBan(int $deptId): bool
    {
        $user = auth()->user();
        if (!$user->member_id) return false;
        $member = Member::find($user->member_id);
        return $member?->hasOrgRoleIn($deptId, ['TruongBan', 'PhoBan']) ?? false;
    }

    private function notifyApprovers(Member $pendingMember, Department $department, User $submitter): void
    {
        try {
            // SuperAdmins
            $superAdmins = User::whereHas('roles', fn($q) => $q->where('name', 'SuperAdmin'))->get();

            // TruongBan của ban đó
            $truongBanUsers = User::whereHas('member', function ($q) use ($department) {
                $q->whereHas('memberships', function ($mq) use ($department) {
                    $mq->where('model_type', Department::class)
                       ->where('model_id', $department->id)
                       ->whereHas('orgRole', fn($r) => $r->whereIn('code', ['tb', 'pb']));
                });
            })->get();

            $recipients = $superAdmins->merge($truongBanUsers)->unique('id');

            foreach ($recipients as $recipient) {
                $recipient->notify(new PendingMemberNotification($pendingMember, $department, $submitter));
            }
        } catch (\Exception $e) {
            // Notification failure không chặn flow chính
            \Log::warning("PendingMember notify failed: " . $e->getMessage());
        }
    }

    private function notifySubmitter(Member $member, string $result, ?string $reason = null): void
    {
        try {
            $submitter = User::find($member->submitted_by_user_id);
            $submitter?->notify(new MemberApprovalResultNotification($member, $result, $reason));
        } catch (\Exception $e) {
            \Log::warning("MemberApprovalResult notify failed: " . $e->getMessage());
        }
    }
}
