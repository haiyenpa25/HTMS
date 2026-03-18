<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Member;
use Illuminate\Http\Request;

class HouseholdController extends Controller
{
    /**
     * Khởi tạo Hộ gia đình mới cho một Tín hữu bơ vơ (Chưa có hộ)
     */
    public function store(Request $request, Member $member)
    {
        abort_if(!$request->user()->isSuperAdmin(), 403, 'Chỉ SuperAdmin mới được thao tác Hộ gia đình.');
        
        $household = Household::create([
            'name' => 'Gia đình ' . $member->full_name,
            'head_member_id' => $member->id,
            'address' => $member->address
        ]);

        $member->update(['household_id' => $household->id]);

        return back()->with('message', 'Đã khởi tạo Hộ gia đình thành công.');
    }

    /**
     * Ghép thêm 1 thành viên hiện có vào Hộ
     */
    public function addMember(Request $request, Household $household)
    {
        abort_if(!$request->user()->isSuperAdmin(), 403, 'Chỉ SuperAdmin mới được thao tác Hộ gia đình.');
        
        $request->validate([
            'member_id' => 'required|exists:members,id'
        ]);

        $member = Member::find($request->member_id);
        
        if ($member->household_id) {
            return back()->with('error', 'Thành viên này đang thuộc một Hộ khác. Vui lòng gỡ khỏi Hộ cũ trước.');
        }

        $member->update(['household_id' => $household->id]);

        return back()->with('message', 'Đã ghép thêm người vào Hộ gia đình.');
    }

    /**
     * Gỡ thành viên khỏi hộ (Rời hộ)
     */
    public function removeMember(Request $request, Household $household, Member $member)
    {
        abort_if(!$request->user()->isSuperAdmin(), 403, 'Chỉ SuperAdmin mới được thao tác Hộ gia đình.');
        
        if ($member->household_id != $household->id) {
            return back()->with('error', 'Tín hữu không nằm trong hộ này.');
        }

        $member->update(['household_id' => null]);
        
        // Cập nhật lại Chủ hộ nếu người rời đi là Chủ hộ hiện tại
        if ($household->head_member_id == $member->id) {
            $nextMember = $household->members()->where('id', '!=', $member->id)->first();
            if ($nextMember) {
                $household->update(['head_member_id' => $nextMember->id]);
            } else {
                // Nếu hộ không còn ai, có thể hủy Chủ hộ
                $household->update(['head_member_id' => null]);
            }
        }

        return back()->with('message', 'Tín hữu đã rời khỏi Hộ gia đình.');
    }
}
