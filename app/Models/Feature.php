<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'description', 'portal_type'];

    /**
     * Cache static features list to avoid repeated DB lookups.
     */
    public static function slugToId(): array
    {
        return static::pluck('id', 'slug')->toArray();
    }

    public static function cachedAll()
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('system_features_model', function () {
            return static::all();
        });
    }

    protected static function booted()
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('system_features_model'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('system_features_model'));
    }

    // ── Relationships ───────────────────────────────────────────────

    public function userDepartmentFeatures()
    {
        return $this->hasMany(UserDepartmentFeature::class);
    }
}
