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

    // ── Relationships ───────────────────────────────────────────────

    public function userDepartmentFeatures()
    {
        return $this->hasMany(UserDepartmentFeature::class);
    }
}
