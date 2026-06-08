<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    /**
     * Danh sách câu gốc Kinh Thánh Bản Truyền Thống 1925
     */
    private array $verses = [
        ['text' => 'Đức Giê-hô-va là Đấng chăn giữ tôi: tôi sẽ chẳng thiếu thốn gì.', 'ref' => 'Thi Thiên 23:1'],
        ['text' => 'Vì Đức Chúa Trời yêu thương thế gian, đến nỗi đã ban Con một của Ngài, hầu cho hễ ai tin Con ấy không bị hư mất mà được sự sống đời đời.', 'ref' => 'Giăng 3:16'],
        ['text' => 'Hãy tin Đức Chúa Jêsus, thì ngươi và cả nhà ngươi đều sẽ được cứu rỗi.', 'ref' => 'Công Vụ 16:31'],
        ['text' => 'Ta là đường đi, lẽ thật, và sự sống; chẳng bởi ta thì không ai được đến cùng Cha.', 'ref' => 'Giăng 14:6'],
        ['text' => 'Hãy hết lòng tin cậy Đức Giê-hô-va, chớ nương cậy nơi sự thông sáng của con; phàm trong các việc làm của con, hãy nhận biết Ngài, thì Ngài sẽ chỉ dẫn các nẻo của con.', 'ref' => 'Châm Ngôn 3:5-6'],
        ['text' => 'Đức Chúa Jêsus Christ hôm qua, ngày nay, và cho đến đời đời không hề thay đổi.', 'ref' => 'Hê-bơ-rơ 13:8'],
        ['text' => 'Vì ta biết những ý tưởng ta nghĩ đối cùng các ngươi, là ý tưởng bình an, không phải tai họa, để cho các ngươi được sự trông cậy trong lúc cuối cùng của mình.', 'ref' => 'Giê-rê-mi 29:11'],
        ['text' => 'Hãy trao gánh nặng ngươi cho Đức Giê-hô-va, Ngài sẽ nâng đỡ ngươi; Ngài sẽ chẳng hề cho người công bình bị rúng động.', 'ref' => 'Thi Thiên 55:22'],
        ['text' => 'Chúng ta yêu, vì Chúa đã yêu chúng ta trước.', 'ref' => '1 Giăng 4:19'],
        ['text' => 'Tôi có thể làm được mọi sự nhờ Đấng ban thêm sức cho tôi.', 'ref' => 'Phi-líp 4:13'],
        ['text' => 'Đức Chúa Trời là nơi nương náu và sức lực của chúng tôi, Ngài sẵn sàng phù hộ trong cơn gian truân.', 'ref' => 'Thi Thiên 46:1'],
        ['text' => 'Nhưng những ai trông đợi Đức Giê-hô-va sẽ được thêm sức mới; họ sẽ bay lên với cánh như chim phụng-hoàng.', 'ref' => 'Ê-sai 40:31'],
        ['text' => 'Chớ sợ chi, vì ta ở với ngươi; chớ kinh khủng vì ta là Đức Chúa Trời ngươi! Ta sẽ bổ sức cho ngươi; phải, ta sẽ giúp đỡ ngươi.', 'ref' => 'Ê-sai 41:10'],
        ['text' => 'Hãy nếm thử và hãy xem Đức Giê-hô-va tốt lành biết bao! Phước cho người nào nương náu mình trong Ngài!', 'ref' => 'Thi Thiên 34:8'],
        ['text' => 'Đức Chúa Jêsus phán rằng: Ta là sự sống lại và sự sống; kẻ nào tin ta thì sẽ sống, mặc dù đã chết rồi.', 'ref' => 'Giăng 11:25'],
        ['text' => 'Sự bình an ta ban cho các ngươi, ta không ban cho các ngươi như thế gian ban; lòng các ngươi chớ bối rối và đừng sợ hãi.', 'ref' => 'Giăng 14:27'],
        ['text' => 'Vì mọi vật đều có thể làm được cho kẻ tin.', 'ref' => 'Mác 9:23'],
        ['text' => 'Đức Giê-hô-va phán: Hãy đến, chúng ta hãy biện luận cùng nhau; dù tội các ngươi như hồng điều, sẽ trở nên trắng như tuyết.', 'ref' => 'Ê-sai 1:18'],
        ['text' => 'Ngài cất lấy tội lỗi của thế gian đi.', 'ref' => 'Giăng 1:29'],
        ['text' => 'Trong tình yêu thương không có sự sợ hãi; nhưng tình yêu thương trọn vẹn thì loại bỏ sự sợ hãi ra ngoài.', 'ref' => '1 Giăng 4:18'],
        ['text' => 'Chúa là sức lực và sự ca tụng tôi; Ngài trở nên sự cứu tôi.', 'ref' => 'Xuất Ê-díp-tô Ký 15:2'],
        ['text' => 'Phước cho kẻ nào chẳng theo mưu kế của người hung ác, chẳng đứng trong đường tội nhân, không ngồi chỗ của kẻ nhạo báng.', 'ref' => 'Thi Thiên 1:1'],
        ['text' => 'Ngài đã yêu chúng ta và ban Con Ngài làm của lễ chuộc tội lỗi chúng ta.', 'ref' => '1 Giăng 4:10'],
        ['text' => 'Và chúng ta biết rằng mọi sự hiệp lại làm ích cho kẻ yêu mến Đức Chúa Trời.', 'ref' => 'Rô-ma 8:28'],
        ['text' => 'Chúa là Đấng chăn chiên nhân lành; Đấng chăn chiên nhân lành vì chiên mình phó sự sống mình.', 'ref' => 'Giăng 10:11'],
        ['text' => 'Nếu chúng ta xưng tội mình, thì Ngài là thành tín công bình để tha tội cho chúng ta.', 'ref' => '1 Giăng 1:9'],
        ['text' => 'Vì ân điển của Đức Chúa Trời đã được bày tỏ ra, đem sự cứu rỗi cho mọi người.', 'ref' => 'Tít 2:11'],
        ['text' => 'Đức Chúa Trời là Thần linh, nên ai thờ lạy Ngài thì phải lấy tâm thần và lẽ thật mà thờ lạy.', 'ref' => 'Giăng 4:24'],
        ['text' => 'Hãy vui mừng trong Chúa luôn luôn. Tôi lại còn nói nữa: hãy vui mừng đi.', 'ref' => 'Phi-líp 4:4'],
        ['text' => 'Chớ mệt mỏi về sự làm lành, vì nếu chúng ta không nản lòng, thì đến kỳ chúng ta sẽ gặt.', 'ref' => 'Ga-la-ti 6:9'],
        ['text' => 'Nguyện Đức Chúa Trời của sự bình an ở cùng anh em hết thảy! A-men.', 'ref' => 'Rô-ma 15:33'],
        ['text' => 'Hãy đội mũ trụ của sự cứu rỗi, và cầm gươm của Đức Thánh Linh, là lời Đức Chúa Trời.', 'ref' => 'Ê-phê-sô 6:17'],
        ['text' => 'Muôn ơn của Đức Chúa Trời là tự hữu hằng hữu đến đời đời. A-men.', 'ref' => 'Rô-ma 11:36'],
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        $member = $user->member;

        // Chọn ngẫu nhiên 1 Câu Gốc từ bộ sưu tập
        $verse = $this->verses[array_rand($this->verses)];

        // Lấy danh sách Ban Ngành đang hoạt động từ DB
        $departments = Department::where('is_active', true)
            ->whereNull('parent_id') // Chỉ lấy cấp 1 (Ban Ngành gốc)
            ->select('id', 'name', 'block', 'description')
            ->orderByRaw("CASE block WHEN 'leadership' THEN 1 WHEN 'ministry' THEN 2 WHEN 'activities' THEN 3 WHEN 'finance' THEN 4 ELSE 5 END ASC")
            ->get()
            ->map(function ($dept) {
                $icons = [
                    'leadership' => 'shield',
                    'ministry'   => 'users',
                    'activities' => 'calendar',
                    'finance'    => 'dollar-sign',
                ];
                $colors = [
                    'leadership' => 'indigo',
                    'ministry'   => 'emerald',
                    'activities' => 'amber',
                    'finance'    => 'blue',
                ];
                return [
                    'id'    => $dept->id,
                    'name'  => $dept->name,
                    'block' => $dept->block,
                    'description' => $dept->description,
                    'icon'  => $icons[$dept->block] ?? 'star',
                    'color' => $colors[$dept->block] ?? 'gray',
                ];
            });

        return Inertia::render('Welcome', [
            'verse'       => $verse,
            'departments' => $departments,
            'canAdmin'    => $user->isSuperAdmin(),
        ]);
    }
}
