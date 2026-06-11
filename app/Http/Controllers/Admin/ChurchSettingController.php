<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChurchSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChurchSettingController extends Controller
{
    /**
     * Danh sách các setting được quản lý qua UI.
     * Mỗi setting có: key, label, description, type (text|integer|boolean|textarea), group
     */
    private function settingSchema(): array
    {
        return [
            [
                'group' => 'Thông Tin Hội Thánh',
                'icon' => '🏛️',
                'settings' => [
                    ['key' => 'church_name',       'label' => 'Tên Hội Thánh',    'description' => 'Tên chính thức của Hội Thánh', 'type' => 'text'],
                    ['key' => 'church_address',     'label' => 'Địa Chỉ',          'description' => 'Địa chỉ đầy đủ', 'type' => 'textarea'],
                    ['key' => 'church_phone',       'label' => 'Điện Thoại',       'description' => 'Số điện thoại liên hệ', 'type' => 'text'],
                    ['key' => 'church_email',       'label' => 'Email',            'description' => 'Email liên hệ chính thức', 'type' => 'text'],
                    ['key' => 'church_website',     'label' => 'Website',          'description' => 'URL website Hội Thánh', 'type' => 'text'],
                    ['key' => 'church_established', 'label' => 'Năm Thành Lập',   'description' => 'Năm thành lập Hội Thánh', 'type' => 'integer'],
                ],
            ],
            [
                'group' => 'Nhiệm Kỳ & Hành Chính',
                'icon' => '📅',
                'settings' => [
                    ['key' => 'current_term_year',  'label' => 'Năm Nhiệm Kỳ Hiện Tại', 'description' => 'Năm nhiệm kỳ đang hoạt động (dùng cho Bầu cử BKT, Chấp Sự)', 'type' => 'integer'],
                    ['key' => 'fiscal_year_start',  'label' => 'Tháng Bắt Đầu Năm Tài Chính', 'description' => '1–12 (mặc định: 1 = Tháng Giêng)', 'type' => 'integer'],
                ],
            ],
            [
                'group' => 'Tính Năng Hệ Thống',
                'icon' => '⚙️',
                'settings' => [
                    ['key' => 'allow_member_portal',    'label' => 'Cho phép Member Portal', 'description' => 'Tín hữu tự đăng ký / xem thông tin cá nhân', 'type' => 'boolean'],
                    ['key' => 'allow_online_donations',  'label' => 'Cho phép Dâng Hiến Online', 'description' => 'Hiển thị form/link dâng hiến cho tín hữu', 'type' => 'boolean'],
                    ['key' => 'allow_visitation_module', 'label' => 'Cho phép Thăm Viếng', 'description' => 'Kích hoạt module ghi nhận thăm viếng', 'type' => 'boolean'],
                    ['key' => 'require_2fa',             'label' => 'Bắt buộc 2FA cho Admin', 'description' => 'Yêu cầu xác thực hai bước với tài khoản Admin', 'type' => 'boolean'],
                ],
            ],
            [
                'group' => 'Email & Thông Báo',
                'icon' => '📧',
                'settings' => [
                    ['key' => 'mail_from_name',    'label' => 'Tên Người Gửi Email',    'description' => 'Hiển thị trong trường "From" của email', 'type' => 'text'],
                    ['key' => 'mail_from_address', 'label' => 'Địa Chỉ Email Gửi',     'description' => 'Email gửi đi chính thức', 'type' => 'text'],
                    ['key' => 'notification_footer', 'label' => 'Footer Email Thông Báo', 'description' => 'Văn bản cuối trong mọi email hệ thống', 'type' => 'textarea'],
                ],
            ],
        ];
    }

    public function index()
    {
        $schema = $this->settingSchema();

        // Load existing values
        $allKeys = collect($schema)
            ->flatMap(fn($g) => collect($g['settings'])->pluck('key'))
            ->toArray();

        $existing = ChurchSetting::whereIn('key', $allKeys)->pluck('value', 'key');

        // Merge schema with current values
        foreach ($schema as &$group) {
            foreach ($group['settings'] as &$setting) {
                $setting['value'] = $existing[$setting['key']] ?? null;
            }
        }

        return Inertia::render('Admin/Settings/Index', [
            'schema' => $schema,
        ]);
    }

    public function update(Request $request)
    {
        $settings = $request->input('settings', []);

        if (!is_array($settings)) {
            return back()->with('error', 'Dữ liệu không hợp lệ.');
        }

        // Validate known keys only (security: prevent arbitrary key injection)
        $schema = $this->settingSchema();
        $allowedKeys = collect($schema)
            ->flatMap(fn($g) => collect($g['settings'])->pluck('key'))
            ->toArray();

        foreach ($settings as $key => $value) {
            if (!in_array($key, $allowedKeys)) continue;

            // Tìm type của setting để cast đúng
            $settingDef = collect($schema)
                ->flatMap(fn($g) => $g['settings'])
                ->firstWhere('key', $key);
            $type = $settingDef['type'] ?? 'text';

            if ($type === 'boolean') {
                // Chấp nhận true/false/1/0/'1'/'0'
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? '1' : '0';
            } elseif ($type === 'integer') {
                $value = (string) intval($value);
            }

            ChurchSetting::set($key, $value);
        }

        return back()->with('success', 'Đã lưu cấu hình Hội Thánh.');
    }
}
