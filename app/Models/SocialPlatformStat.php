<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPlatformStat extends Model
{
    protected $fillable = [
        'platform', 'metric', 'count',
        'recorded_date', 'recorded_by', 'notes',
    ];

    protected $casts = [
        'recorded_date' => 'date',
        'count'         => 'integer',
    ];

    // Platform labels
    public const PLATFORMS = [
        'youtube'   => 'YouTube',
        'facebook'  => 'Facebook',
        'zalo'      => 'Zalo',
        'tiktok'    => 'TikTok',
        'instagram' => 'Instagram',
    ];

    // Metric labels
    public const METRICS = [
        'subscribers' => 'Người đăng ký',
        'followers'   => 'Người theo dõi',
        'members'     => 'Thành viên nhóm',
        'views'       => 'Lượt xem (tuần)',
        'likes'       => 'Lượt thích',
    ];

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Lấy lịch sử N tuần gần nhất theo platform+metric
     */
    public static function getHistory(string $platform, string $metric, int $weeks = 8): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('platform', $platform)
            ->where('metric', $metric)
            ->orderBy('recorded_date', 'desc')
            ->take($weeks)
            ->get();
    }

    /**
     * Lấy snapshot mới nhất của tất cả platforms
     */
    public static function getLatestSnapshot(): array
    {
        $result = [];
        foreach (array_keys(self::PLATFORMS) as $platform) {
            $result[$platform] = static::where('platform', $platform)
                ->orderBy('recorded_date', 'desc')
                ->get()
                ->groupBy('metric')
                ->map(fn($rows) => $rows->first());
        }
        return $result;
    }
}
