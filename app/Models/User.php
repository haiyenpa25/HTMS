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
use App\Models\ChronicleEntry;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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

    /**
     * Get the associated member ID.
     */
    public function getMemberIdAttribute()
    {
        return $this->member->id ?? null;
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
        return $this->is_superadmin || $this->email === 'superadmin@httlthanhmyloi.com';
    }

    // ── Announcements Relationships ─────────────────────────────────

    public function readAnnouncements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_reads')
                    ->withPivot('read_at');
    }

    public function authoredAnnouncements()
    {
        return $this->hasMany(Announcement::class, 'author_id');
    }

    /**
     * Get all chronicle entries explicitly related to this user account.
     */
    public function chronicles(): MorphMany
    {
        return $this->morphMany(ChronicleEntry::class, 'subject');
    }
}
