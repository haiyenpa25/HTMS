<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChurchSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'label', 'description'];

    /**
     * Lấy giá trị setting theo key, có default nếu không tìm thấy
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) return $default;

        return match($setting->type) {
            'integer' => (int) $setting->value,
            'boolean' => (bool) $setting->value,
            'json'    => json_decode($setting->value, true),
            default   => $setting->value,
        };
    }

    /**
     * Lưu hoặc cập nhật setting theo key
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string) $value]
        );
    }

    /**
     * Lấy năm nhiệm kỳ hiện tại
     */
    public static function currentTermYear(): int
    {
        return (int) static::get('current_term_year', date('Y'));
    }
}
