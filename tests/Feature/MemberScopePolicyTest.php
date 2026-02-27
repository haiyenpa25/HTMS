<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;

class MemberScopePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Seed dữ liệu mẫu để tiến hành Test Policy
        $this->seed(DemoDataSeeder::class);
    }

    public function test_team_lead_a_cannot_view_member_b_of_another_team()
    {
        // $userTL thuộc tổ chức Ban Mục vụ - Tổ Lời Chúa (và là nhân viên tại Tổ Âm Thanh)
        // Chúng ta lấy Tổ trưởng 1_2 (Tổ Cầu Nguyện) cho Test case thuần khiết hơn.
        $userTL = User::where('email', 'tlead2@church.com')->first();
        
        // Thành viên thuộc Ban Truyền Thông - Tổ Hình ảnh
        $memberC1 = User::where('email', 'mem5@church.com')->first()->member;
        
        // Thành viên thuộc Tổ Lời Chúa (Khác Tổ của tlead2)
        $memberA1 = User::where('email', 'mem1@church.com')->first()->member;

        // Assertion: Tổ trưởng 2 (Của Tổ Cầu Nguyện) KHÔNG THỂ XEM MemberA1 (Tổ Lời Chúa)
        $this->assertFalse($userTL->canViewMember($memberA1));
        
        // Assertion: Tổ trưởng 2 KHÔNG THỂ XEM MemberC1 (Tổ Hình Ảnh)
        $this->assertFalse($userTL->canViewMember($memberC1));
    }

    public function test_multi_team_logic_for_member_in_multiple_teams()
    {
        // $userTL1_1 là người thuộc cả 2 Tổ (Tổ Lời Chúa Ban Mục Vụ và Tổ Âm Thanh Ban Truyền thông)
        $multiScopeUser = User::where('email', 'tlead1@church.com')->first();

        // Target: Member B1 (thuộc Tổ Âm Thanh Ban Truyền Thông)
        $targetB1 = User::where('email', 'mem3@church.com')->first()->member;

        // Assertion: Vì $multiScopeUser có mặt trong Tổ Âm Thanh (Dù chỉ làm thành viên hay Tổ trưởng)
        // Theo luật là sẽ TÌM ĐƯỢC array intersect -> Trả về TRUE (Có thể truy cập)
        $this->assertTrue($multiScopeUser->canViewMember($targetB1));
    }

    public function test_dept_lead_can_view_all_their_team_members()
    {
        // Trưởng ban Mục Vụ
        $deptLead = User::where('email', 'lead1@church.com')->first();
        $memberA1 = User::where('email', 'mem1@church.com')->first()->member;
        
        // Target: Member C1 (Thuộc ban Truyền Thông do đó K_ĐƯỢC truy cập)
        $memberC1 = User::where('email', 'mem5@church.com')->first()->member;
        
        $this->assertTrue($deptLead->canViewMember($memberA1));
        $this->assertFalse($deptLead->canViewMember($memberC1));
    }
}
