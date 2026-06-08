<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use App\Models\OrgMembership;
use App\Models\OrgRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Quản lý tài khoản hệ thống:
 * - Xem tất cả accounts (có/chưa gắn member)
 * - Gắn user_id ↔ member
 * - Tạo tài khoản từ member có sẵn
 * - Reset mật khẩu
 * - Cấp/thu hồi is_superadmin
 */
class AccountController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // INDEX — Trang quản lý tài khoản
    // ══════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $query = User::with(['member:id,user_id,full_name,status,member_code'])
            ->orderBy('name');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($filter = $request->input('filter')) {
            match ($filter) {
                'no_member'  => $query->whereDoesntHave('member'),
                'superadmin' => $query->where('is_superadmin', true),
                'has_member' => $query->whereHas('member'),
                default      => null,
            };
        }

        $users = $query->paginate(30)->through(fn ($u) => [
            'id'           => $u->id,
            'name'         => $u->name,
            'email'        => $u->email,
            'is_superadmin'=> $u->is_superadmin,
            'created_at'   => $u->created_at->format('d/m/Y'),
            'member'       => $u->member ? [
                'id'          => $u->member->id,
                'full_name'   => $u->member->full_name,
                'member_code' => $u->member->member_code,
                'status'      => $u->member->status,
            ] : null,
            'roles' => $u->getRoleNames(),
        ]);

        // Thống kê nhanh
        $stats = [
            'total_users'    => User::count(),
            'linked_users'   => User::whereHas('member')->count(),
            'unlinked_users' => User::whereDoesntHave('member')->count(),
            'superadmins'    => User::where('is_superadmin', true)->count(),
        ];

        // Members chưa có tài khoản (để dropdown link)
        $unlinkedMembers = Member::whereNull('user_id')
            ->where('status', '!=', 'Đã mất')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'member_code', 'email', 'status'])
            ->map(fn ($m) => [
                'id'          => $m->id,
                'full_name'   => $m->full_name,
                'member_code' => $m->member_code,
                'email'       => $m->email,
                'status'      => $m->status,
            ]);

        return Inertia::render('Admin/Accounts', [
            'users'           => $users,
            'stats'           => $stats,
            'unlinked_members'=> $unlinkedMembers,
            'filters'         => [
                'search' => $request->input('search'),
                'filter' => $request->input('filter'),
            ],
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // CREATE ACCOUNT — Tạo tài khoản từ member có sẵn
    // ══════════════════════════════════════════════════════════════
    public function createFromMember(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'nullable|string|min:6',
        ]);

        $member = Member::findOrFail($validated['member_id']);

        if ($member->user_id) {
            return back()->withErrors(['member_id' => 'Tín hữu này đã có tài khoản.']);
        }

        $password = $validated['password'] ?? Str::random(10);

        $user = User::create([
            'name'     => $member->full_name,
            'email'    => $validated['email'],
            'password' => Hash::make($password),
        ]);

        $member->update(['user_id' => $user->id]);

        return back()->with('success', "Đã tạo tài khoản cho {$member->full_name}. Mật khẩu tạm: {$password}");
    }

    // ══════════════════════════════════════════════════════════════
    // LINK — Gắn tài khoản với tín hữu
    // ══════════════════════════════════════════════════════════════
    public function linkMember(Request $request, User $user)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
        ]);

        $member = Member::findOrFail($validated['member_id']);

        // Kiểm tra member đã gắn với user khác chưa
        if ($member->user_id && $member->user_id !== $user->id) {
            return back()->withErrors(['member_id' => 'Tín hữu này đã được gắn với tài khoản khác.']);
        }

        // Hủy gắn member cũ (nếu user đã gắn với member khác)
        Member::where('user_id', $user->id)->update(['user_id' => null]);

        // Gắn mới
        $member->update(['user_id' => $user->id]);

        return back()->with('success', "Đã gắn {$user->name} với tín hữu {$member->full_name}.");
    }

    // ══════════════════════════════════════════════════════════════
    // UNLINK — Tháo gắn kết user ↔ member
    // ══════════════════════════════════════════════════════════════
    public function unlinkMember(User $user)
    {
        $member = Member::where('user_id', $user->id)->first();

        if ($member) {
            $member->update(['user_id' => null]);
        }

        return back()->with('success', "Đã tháo gắn kết tài khoản {$user->name} khỏi tín hữu.");
    }

    // ══════════════════════════════════════════════════════════════
    // RESET PASSWORD — Đặt lại mật khẩu
    // ══════════════════════════════════════════════════════════════
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', "Đã đặt lại mật khẩu cho {$user->name}.");
    }

    // ══════════════════════════════════════════════════════════════
    // TOGGLE SUPERADMIN — Cấp/thu hồi quyền superadmin
    // ══════════════════════════════════════════════════════════════
    public function toggleSuperAdmin(Request $request, User $user)
    {
        // Không cho thu hồi chính mình
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'Không thể thay đổi quyền của chính mình.']);
        }

        $user->update(['is_superadmin' => !$user->is_superadmin]);

        $status = $user->is_superadmin ? 'cấp' : 'thu hồi';
        return back()->with('success', "Đã {$status} quyền Super Admin cho {$user->name}.");
    }

    // ══════════════════════════════════════════════════════════════
    // DELETE — Xóa tài khoản (không xóa member)
    // ══════════════════════════════════════════════════════════════
    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'Không thể xóa tài khoản đang đăng nhập.']);
        }

        if ($user->is_superadmin) {
            return back()->withErrors(['user' => 'Không thể xóa tài khoản Super Admin.']);
        }

        // Tháo gắn kết member trước
        Member::where('user_id', $user->id)->update(['user_id' => null]);

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Đã xóa tài khoản {$name}.");
    }

    // ══════════════════════════════════════════════════════════════
    // ORG ROLES — Lấy chức danh org của user (AJAX)
    // ══════════════════════════════════════════════════════════════
    public function orgRoles(User $user)
    {
        $member = Member::where('user_id', $user->id)->first();

        if (!$member) {
            return response()->json(['roles' => [], 'departments' => []]);
        }

        $memberships = OrgMembership::with(['orgRole', 'department'])
            ->where('member_id', $member->id)
            ->where('is_active', true)
            ->get()
            ->map(fn ($m) => [
                'role_name'   => $m->orgRole?->name,
                'role_code'   => $m->orgRole?->code,
                'dept_name'   => optional($m->department())->name ?? '—',
                'model_type'  => $m->model_type,
                'model_id'    => $m->model_id,
                'join_date'   => $m->join_date?->format('d/m/Y'),
            ]);

        return response()->json([
            'member' => ['id' => $member->id, 'full_name' => $member->full_name],
            'roles'  => $memberships,
        ]);
    }
}
