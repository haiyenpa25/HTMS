<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasDataScope;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasDataScope, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('user_management')
            ->setDescriptionForEvent(fn(string $eventName) => "Người dùng đã được {$eventName}");
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function member()
    {
        return $this->hasOne(Member::class);
    }

    // ── MAC: Matrix Access Control ──────────────────────────────────

    /**
     * Tất cả feature permissions của user này (qua tất cả departments).
     */
    public function departmentFeatures()
    {
        return $this->hasMany(UserDepartmentFeature::class);
    }

    /**
     * Lấy features đã enabled cho 1 department cụ thể.
     */
    public function enabledFeaturesFor(int $deptId)
    {
        return $this->departmentFeatures()
            ->where('department_id', $deptId)
            ->where('is_enabled', true)
            ->with('feature')
            ->get();
    }

    /**
     * Kiểm tra nhanh superadmin bypass (God Mode).
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(['Super_Admin', 'Pastor'])
            || $this->email === 'superadmin@httlthanhmyloi.com';
    }
}

